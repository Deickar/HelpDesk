@component('mail::message')
# 🎫 Nuevo Ticket de Soporte Creado

Hola equipo, se ha registrado un nuevo ticket en el sistema HelpDesk.

---

### 🧾 **Detalles del Ticket**

- **Título:** {{ $ticket->subject }}
- **Prioridad:** {{ $ticket->priority->name }}
- **Categoría:** {{ $ticket->category->name }}
- **Estado:** {{ $ticket->status->name }}
- **Fecha de creación:** {{ $ticket->created_at->format('d/m/Y H:i') }}

---

### 👤 **Datos del Usuario**

- **Nombre:** {{ $ticket->creator->name }}
- **Correo electrónico:** {{ $ticket->creator->email }}
@if(!empty($ticket->creator->phone))
- **Teléfono:** {{ $ticket->creator->phone }}
@endif
@if(!empty($ticket->creator->department->name))
- **Departamento:** {{ $ticket->creator->department->name }}
@endif
- **Rol:** {{ ucfirst($ticket->creator->role) }}

---

@component('mail::button', ['url' => url('/tickets/'.$ticket->id)])
📥 Ver Ticket en el Sistema
@endcomponent

Gracias por su atención,
**{{ config('app.name') }}**
@endcomponent
