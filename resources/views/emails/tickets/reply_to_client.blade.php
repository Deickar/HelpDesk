@component('mail::message')
# 🛠️ Respuesta a tu ticket

Hola {{ $ticket->creator->name }}, el agente **{{ $responder->name }}** ha respondido a tu ticket.

**Asunto del ticket:** {{ $ticket->subject }}

@component('mail::button', ['url' => url('/tickets/'.$ticket->id)])
Ver Conversación
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent
