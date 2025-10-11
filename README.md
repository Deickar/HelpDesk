📋 Descripción del Proyecto
Sistema de HelpDesk desarrollado en Laravel 10 diseñado para gestionar tickets de soporte técnico, permitiendo a los usuarios reportar incidencias y al personal de soporte dar seguimiento y resolverlas de manera eficiente.

✨ Características Principales
🎫 Gestión de Tickets: Creación, seguimiento y cierre de tickets de soporte

👥 Sistema de Roles: Administradores, Agentes de Soporte y Usuarios

📊 Panel de Control: Dashboard con métricas y estadísticas

🔔 Notificaciones: Sistema de notificaciones por email y en la aplicación

📁 Gestión de Archivos: Adjuntar archivos a los tickets

🏷️ Categorías y Etiquetas: Organización de tickets por categorías

💬 Sistema de Comentarios: Comunicación entre usuarios y agentes

📈 Reportes: Generación de reportes de actividad y rendimiento

🔐 Autenticación Segura: Sistema de login y registro seguro

🚀 Requisitos del Sistema
PHP: 8.1 o superior

Composer: 2.0 o superior

Base de datos: MySQL 8.0, PostgreSQL, SQLite o SQL Server

Servidor Web: Apache o Nginx

Node.js: 14 o superior (para assets)

NPM: 6.0 o superior

📦 Instalación
Sigue estos pasos para instalar y configurar el proyecto:

1. Clonar el repositorio
bash
git clone https://github.com/tu-usuario/helpdesk.git
cd helpdesk
2. Instalar dependencias de PHP
bash
composer install
3. Configurar variables de entorno
bash
cp .env.example .env
php artisan key:generate
4. Configurar la base de datos
Edita el archivo .env con tus credenciales de base de datos:

env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
5. Ejecutar migraciones y seeders
bash
php artisan migrate --seed
6. Instalar dependencias de frontend
bash
npm install
npm run build
7. Configurar almacenamiento
bash
php artisan storage:link
8. Configurar colas (opcional para procesamiento en segundo plano)
bash
# Configurar supervisor o ejecutar en desarrollo
php artisan queue:work
9. Iniciar el servidor
bash
php artisan serve
👤 Usuarios por Defecto
El seeder crea los siguientes usuarios de prueba:

Administrador:

Email: admin@helpdesk.com

Contraseña: password

Agente de Soporte:

Email: agent@helpdesk.com

Contraseña: password

Usuario:

Email: user@helpdesk.com

Contraseña: password

🛠️ Configuración Adicional
Configuración de Email
Edita el archivo .env para configurar el servicio de email:

env
MAIL_MAILER=smtp
MAIL_HOST=tu-servidor-smtp
MAIL_PORT=587
MAIL_USERNAME=tu-email
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@helpdesk.com"
MAIL_FROM_NAME="HelpDesk System"
Configuración de Colas
Para procesamiento en segundo plano, configura tu driver de colas:

env
QUEUE_CONNECTION=database
🧪 Ejecución de Tests
bash
# Ejecutar tests PHPUnit
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage
📁 Estructura del Proyecto
text
helpdesk/
├── app/
│   ├── Models/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Middleware/
│   ├── Providers/
│   ├── Policies/
│   └── Notifications/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   └── assets/
├── config/
├── public/
├── routes/
└── storage/
🔧 Comandos Artisan Útiles
bash
# Limpiar cache de la aplicación
php artisan optimize:clear

# Crear un nuevo usuario administrador
php artisan make:admin

# Generar reportes
php artisan reports:generate

# Backup de la base de datos
php artisan backup:run
🎨 Personalización
Cambiar el tema de colores
Edita el archivo resources/css/app.css para personalizar los colores:

css
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
}
Configurar categorías de tickets
Las categorías pueden ser gestionadas desde el panel de administración o editando el seeder en database/seeders/CategorySeeder.php.

🤝 Contribución
Fork el proyecto

Crea una rama para tu feature (git checkout -b feature/AmazingFeature)

Commit tus cambios (git commit -m 'Add some AmazingFeature')

Push a la rama (git push origin feature/AmazingFeature)

Abre un Pull Request

📄 Licencia
Este proyecto está bajo la Licencia MIT. Ver el archivo LICENSE para más detalles.

🐛 Reportar Issues
Si encuentras algún problema, por favor reportalo en la sección de Issues del repositorio.

📞 Soporte
Si necesitas ayuda con la instalación o configuración:

Revisa la documentación en Wiki del Proyecto

Abre un issue en GitHub

Contacta al equipo de desarrollo

🔄 Actualizaciones
Para mantener tu instalación actualizada:

bash
# Actualizar dependencias de PHP
composer update

# Actualizar dependencias de JavaScript
npm update

# Ejecutar migraciones nuevas
php artisan migrate
