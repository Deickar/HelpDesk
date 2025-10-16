@component('mail::message')
# ✉️ Nueva respuesta del cliente

Hola {{ $ticket->agent->name }}, el cliente **{{ $responder->name }}** ha respondido al ticket.

**Asunto del ticket:** {{ $ticket->subject }}

@component('mail::button', ['url' => url('/tickets/'.$ticket->id)])
Ver Ticket
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent
