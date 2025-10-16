@component('mail::message')
# ✅ Tu ticket ha sido asignado

Hola {{ $ticket->creator->name }}, tu ticket ha sido asignado a un agente.

- **Título del Ticket:** {{ $ticket->subject }}
- **Agente asignado:** {{ $ticket->agent->name }}
- **Fecha de asignación:** {{ now()->format('d/m/Y H:i') }}

@component('mail::button', ['url' => url('/tickets/' . $ticket->id)])
Ver Ticket
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent
