@extends('reports.pdf.layout')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Reporte de Tareas</h2>
    
    @if($startDate || $endDate)
        <p class="mb-4">Período: {{ $startDate ?? 'Inicio' }} - {{ $endDate ?? 'Actual' }}</p>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left">Tarea</th>
                <th class="px-4 py-2 text-left">Orden</th>
                <th class="px-4 py-2 text-left">Asignado</th>
                <th class="px-4 py-2 text-left">Estado</th>
                <th class="px-4 py-2 text-left">Fecha Límite</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['tasks'] as $task)
                <tr>
                    <td class="px-4 py-2">{{ $task->title }}</td>
                    <td class="px-4 py-2">{{ $task->order ? "#{$task->order->order_number}" : 'Sin orden' }}</td>
                    <td class="px-4 py-2">
                        @if($task->assignees->count() > 0)
                            {{ $task->assignees->pluck('name')->join(', ') }}
                        @else
                            Sin asignar
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ ucfirst($task->status) }}</td>
                    <td class="px-4 py-2">{{ $task->due_date->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection