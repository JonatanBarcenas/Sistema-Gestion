@component('mail::message')
# Actualización de Tarea #{{ $task->id }}

**Orden:** {{ $orderNumber }}
**Título:** {{ $taskTitle }}
**Estado actual:** {{ ucfirst($taskStatus) }}

{{ $message }}

@if(count($changes) > 0)
## Cambios realizados:
@foreach($changes as $field => $change)
- **{{ ucfirst($field) }}:** {{ is_array($change) ? $change['old'].' → '.$change['new'] : $change }}
@endforeach
@endif

@component('mail::button', ['url' => $actionUrl])
Ver Tarea
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent