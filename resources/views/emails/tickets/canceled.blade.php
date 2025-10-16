@component('mail::message')
# ❌ Ticket Cancelado

Hola {{ $ticket->creator->name }}, lamentamos informarte que tu ticket:

**#{{ $ticket->id }} - {{ $ticket->subject }}**
ha sido cancelado.

**Estado actual:** Cancelado
**Fecha:** {{ now()->format('d/m/Y H:i') }}

Si crees que esto fue un error, puedes generar un nuevo ticket.

@component('mail::button', ['url' => url('/tickets')])
Ver Mis Tickets
@endcomponent

Gracias,
{{ config('app.name') }}
@endcomponent
