@component('mail::message')
# Actualización de Pedido

{{ $message }}

@component('mail::button', ['url' => $actionUrl])
Ver Pedido
@endcomponent

Gracias por su preferencia,<br>
{{ config('app.name') }}
@endcomponent