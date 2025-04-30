@extends('reports.pdf.layout')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Reporte de Usuarios</h2>
    
    @if($startDate || $endDate)
        <p class="mb-4">Período: {{ $startDate ?? 'Inicio' }} - {{ $endDate ?? 'Actual' }}</p>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left">Nombre</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Tareas Asignadas</th>
                <th class="px-4 py-2 text-left">Proyectos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['users'] as $user)
                <tr>
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">{{ $user->tasks_count }}</td>
                    <td class="px-4 py-2">{{ $user->projects_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection