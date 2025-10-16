@component('mail::message')
# 🔓 Ticket Reabierto

El ticket **#{{ $ticket->id }} - {{ $ticket->subject }}** ha sido reabierto.

**Reabierto por:** {{ $reopenedBy->name }} (Rol: {{ ucfirst($reopenedBy->role) }})
**Fecha:** {{ now()->format('d/m/Y H:i') }}

@component('mail::button', ['url' => url('/tickets/'.$ticket->id)])
Ver Ticket
@endcomponent

Gracias,
{{ config('app.name') }}
@endcomponent
