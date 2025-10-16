@component('mail::message')
# 🛑 Ticket Cerrado

El ticket **#{{ $ticket->id }} - {{ $ticket->subject }}** ha sido cerrado.

**Cerrado por:** {{ $closedBy->name }} (Rol: {{ ucfirst($closedBy->role) }})  
**Fecha de cierre:** {{ now()->format('d/m/Y H:i') }}

@component('mail::button', ['url' => url('/tickets/'.$ticket->id)])
Ver Ticket
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent
