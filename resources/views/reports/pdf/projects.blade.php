@extends('reports.pdf.layout')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Reporte de Proyectos</h2>
    
    @if($startDate || $endDate)
        <p class="mb-4">Período: {{ $startDate ?? 'Inicio' }} - {{ $endDate ?? 'Actual' }}</p>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left">Nombre</th>
                <th class="px-4 py-2 text-left">Cliente</th>
                <th class="px-4 py-2 text-left">Estado</th>
                <th class="px-4 py-2 text-left">Fecha Inicio</th>
                <th class="px-4 py-2 text-left">Fecha Fin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['projects'] as $project)
                <tr>
                    <td class="px-4 py-2">{{ $project->name }}</td>
                    <td class="px-4 py-2">{{ $project->client->name }}</td>
                    <td class="px-4 py-2">{{ ucfirst($project->status) }}</td>
                    <td class="px-4 py-2">{{ $project->start_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $project->end_date ? $project->end_date->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection