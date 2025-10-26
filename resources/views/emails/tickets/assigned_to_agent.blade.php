@component('mail::message')
# 🎯 Nuevo ticket asignado a ti

Hola {{ $ticket->agent->name }}, tienes un nuevo ticket asignado en el sistema.

- **Título del Ticket:** {{ $ticket->subject }}
- **Usuario que lo creó:** {{ $ticket->creator->name }}
- **Prioridad:** {{ optional($ticket->priority)->name }}
- **Fecha de asignación:** {{ now()->format('d/m/Y H:i') }}

@component('mail::button', ['url' => url('/tickets/' . $ticket->id)])
Gestionar Ticket
@endcomponent

Gracias,
{{ config('app.name') }}
@endcomponent
