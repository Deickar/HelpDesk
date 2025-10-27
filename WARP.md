# WARP.md

Este archivo proporciona orientación a WARP (warp.dev) cuando trabaja con código en este repositorio.

## Descripción del Proyecto

HelpDesk es un sistema de gestión de tickets de soporte técnico desarrollado en Laravel 10 con tres roles de usuario: Administrador, Agente y Cliente. Gestiona el ciclo de vida completo de los tickets desde su creación hasta la resolución, con notificaciones por correo, archivos adjuntos y registro completo de auditoría.

## Comandos de Desarrollo

### Configuración Inicial
```bash
# Instalar dependencias
composer install
npm install

# Configurar entorno y base de datos
cp .env.example .env
php artisan key:generate
php artisan migrate

# Ejecutar seeders (crea datos de demostración: 10 categorías, 10 departamentos, 30 FAQs, 1000 tickets, etc.)
php artisan db:seed
```

### Ejecutar la Aplicación
```bash
# Iniciar servidor de desarrollo
php artisan serve

# Compilar assets del frontend (Vite + Tailwind CSS)
npm run dev

# Compilar para producción
npm run build
```

### Pruebas
```bash
# Ejecutar todas las pruebas
php artisan test
# O
vendor/bin/phpunit

# Ejecutar suite específica de pruebas
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Unit

# Ejecutar un archivo de prueba específico
vendor/bin/phpunit tests/Feature/ExampleTest.php
```

### Calidad de Código
```bash
# Formatear código con Laravel Pint
vendor/bin/pint

# Verificar archivos específicos
vendor/bin/pint app/Http/Controllers/TicketController.php
```

### Operaciones de Base de Datos
```bash
# Migración fresca (ADVERTENCIA: elimina todos los datos)
php artisan migrate:fresh

# Migración fresca con seeders
php artisan migrate:fresh --seed

# Crear nueva migración
php artisan make:migration create_table_name

# Crear modelo con migración y factory
php artisan make:model ModelName -mf
```

### Almacenamiento
```bash
# Crear enlace simbólico de storage (requerido para archivos adjuntos)
php artisan storage:link
```

## Arquitectura

### Control de Acceso Basado en Roles (RBAC)

El sistema utiliza un middleware personalizado basado en roles (`RoleMiddleware`) que verifica el campo `role` del modelo User:

- **admin**: Acceso completo al sistema - gestiona usuarios, categorías, departamentos, FAQs, todos los tickets, asignación de tickets y aprobación de resoluciones
- **agent**: Accede solo a tickets asignados, responde tickets, cierra/reabre tickets
- **client**: Crea tickets, visualiza sus propios tickets, responde a tickets asignados

El middleware de roles se aplica en `routes/web.php` con la sintaxis: `->middleware(['auth', 'role:admin'])`. El middleware verifica `auth()->user()->role` contra los roles permitidos.

### Flujo de Trabajo y Gestión de Estados de Tickets

Los tickets siguen un patrón de máquina de estados mediante la clave foránea `status_id`:

1. **Nuevo** (ID 1): Estado inicial cuando el cliente crea el ticket
2. **En Progreso** (ID 2): Se establece automáticamente cuando el admin asigna el ticket a un agente
3. **En espera del cliente** (ID 3): Se establece automáticamente cuando el agente responde
4. **Respuesta del cliente** (ID 4): Se establece automáticamente cuando el cliente responde
5. **Resuelto** (ID 5): El admin lo marca después de que el cliente confirma el cierre
6. **Cerrado** (ID 6): Cualquier usuario autenticado puede cerrar
7. **Reabierto** (ID 7): Cualquier usuario autenticado puede reabrir si está cerrado
8. **Cancelado** (ID 8): El admin puede cancelar tickets no asignados en estado "Nuevo"

Las transiciones de estado se aplican en los métodos del `TicketController` (`responder`, `cerrar`, `reabrir`, `cancelarticket`, `marcarResuelto`). Cada transición desencadena:
- Notificaciones por correo mediante clases Mail
- Entradas de log mediante el método estático `Log::register()`
- Actualización de estado en `tickets.status_id`

### Sistema de Notificaciones por Correo

Ubicado en `app/Mail/`, los correos se envían en eventos del ciclo de vida del ticket:

- `TicketCreatedMail`: Enviado a las direcciones `MAIL_TICKET_NOTIFICATION` (separadas por comas en .env)
- `TicketAssignedToCreator` y `TicketAssignedToAgent`: Cuando el admin asigna el ticket
- `TicketReplyToClient` y `TicketReplyToAgent`: En respuestas de mensajes
- `TicketClosedMail`, `TicketReopenedMail`, `TicketCanceledMail`: Cambios de estado

Configura `MAIL_TICKET_NOTIFICATION` en .env para notificaciones de nuevos tickets.

### Arquitectura de Archivos Adjuntos

Dos sistemas de adjuntos con modelos separados:

1. **Adjuntos de Ticket** (`AttachmentTicket`): Archivos adjuntados durante la creación del ticket
   - Almacenados en: `storage/app/public/attachments/tickets/{ticket_id}/`
   - Gestionados en: `TicketController@store`

2. **Adjuntos de Mensaje** (`AttachmentMessage`): Archivos adjuntados a respuestas de tickets
   - Almacenados en: `storage/app/public/attachments/messages/{message_id}/`
   - Gestionados en: `TicketController@responder`

Tamaño máximo de archivo: 2MB por archivo (validado con regla `max:2048`). Ambos usan la facade `Storage` de Laravel con disco público.

### Sistema de Registro de Auditoría

El modelo `Log` proporciona el método estático `Log::register($ticket_id, $action)` usado en todo `TicketController` para rastrear:
- Creación de tickets, asignación, cambios de estado
- Cambios de prioridad, reasignación de agentes
- Respuestas de mensajes, cierre, reapertura
- Generación de PDF

Los logs incluyen `user_id` (de `auth()->id()`), `ticket_id`, texto de `action` y timestamp `created_at`. Se muestran en vistas de detalle de tickets y PDFs.

### Arquitectura del Dashboard

`DashboardController@dashboardRouter` usa expresión match de PHP 8 para enrutar usuarios autenticados a dashboards específicos por rol:

- **Dashboard de Admin**: Estadísticas de todo el sistema con filtros por mes/año - conteos de tickets por estado/categoría/prioridad/departamento/agente, vista de calendario de todos los tickets
- **Dashboard de Agente**: Estadísticas personales de tickets asignados - filtrados por mes/año, calendario de tickets asignados
- **Dashboard de Cliente**: Estadísticas personales de tickets - filtradas por mes/año, vista más simple sin calendario

Cada dashboard agrega datos usando colecciones Eloquent y `groupBy()`, retornando conteos de estados con mapeos predefinidos de color/icono.

### Relaciones del Modelo Ticket

El modelo `Ticket` es la entidad central con relaciones a:
- `creator()` (belongsTo User vía `user_id`): Creador del ticket
- `agent()` (belongsTo User vía `assigned_to`): Agente asignado
- `status()`, `priority()`, `category()`: relaciones belongsTo para clasificación
- `messages()`: hasMany TicketMessage - historial de conversación
- `attachments()`: hasMany AttachmentTicket - archivos iniciales del ticket
- `logs()`: hasMany Log - registro de auditoría

Siempre carga relaciones eager al mostrar tickets para evitar consultas N+1: `Ticket::with(['status', 'priority', 'category', 'agent'])`

### Generación de PDF

Usa el paquete `barryvdh/laravel-dompdf`. `TicketController@generarPDF` renderiza la vista blade `tickets.pdf` con datos completos del ticket (mensajes, adjuntos, logs) y retorna PDF descargable. Registra la acción de generación de PDF.

### Stack Frontend

- **Framework**: Laravel Breeze (scaffolding de autenticación con plantillas Blade)
- **CSS**: Tailwind CSS con Alpine.js para interactividad
- **Herramienta de Build**: Vite (configurado en `vite.config.js`)
- **Vistas**: Plantillas Blade en `resources/views/` organizadas por funcionalidad (tickets, categories, departments, faqs, users)

Directorios clave:
- `resources/views/tickets/`: Vistas CRUD de tickets (create, index, detalle, gestion, conformidad, asignados, pdf)
- `resources/views/components/`: Componentes Blade reutilizables
- `resources/views/layouts/`: Plantillas de layout con navegación

### Estructura de Base de Datos

Archivos de migración clave muestran el esquema:
- Users pertenecen a departments (nullable), tienen campo role
- Tickets referencian user_id (creador), assigned_to (agente), status_id, priority_id, category_id
- TicketMessages referencian ticket_id y user_id
- Ambas tablas de adjuntos usan estructura similar a polimórfica (ticket vs message)
- Tabla logs rastrea ticket_id, user_id, action, created_at

## Configuración Importante

### Variables de Entorno Requeridas

Además del .env estándar de Laravel, este proyecto requiere:
```
MAIL_TICKET_NOTIFICATION=email1@example.com,email2@example.com
```

### Requisitos de Almacenamiento

Ejecuta `php artisan storage:link` después de la configuración para habilitar acceso público a los adjuntos.

### Seeders de Base de Datos

`DatabaseSeeder` crea datos de demostración sustanciales (1000 tickets, 3000 mensajes, 500 logs). Para producción, comenta las llamadas a factories y solo ejecuta `UserDemoSeeder` para cuentas de usuario iniciales.

## Patrones Comunes

### Crear Controladores
```bash
php artisan make:controller NameController
```

### Patrón de Transacciones
Usado en `TicketController` para operaciones atómicas:
```php
DB::beginTransaction();
try {
    // operaciones
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // manejar error
}
```

### Patrón de Logging
Siempre registra eventos significativos de tickets:
```php
Log::register($ticket->id, "El usuario {$userName} realizó una acción");
```

### Patrón de Notificaciones por Correo
Envía correos después de commits de base de datos, envueltos en try-catch para prevenir bloqueos:
```php
try {
    Mail::to($user->email)->send(new MailClass($data));
} catch (\Exception $e) {
    // Registrar error pero no fallar la petición
}
```
