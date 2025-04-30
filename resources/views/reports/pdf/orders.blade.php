@extends('reports.pdf.layout')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Reporte de Órdenes</h2>
    
    @if($startDate || $endDate)
        <p class="mb-4">Período: {{ $startDate ?? 'Inicio' }} - {{ $endDate ?? 'Actual' }}</p>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left">ID</th>
                <th class="px-4 py-2 text-left">Cliente</th>
                <th class="px-4 py-2 text-left">Total</th>
                <th class="px-4 py-2 text-left">Estado</th>
                <th class="px-4 py-2 text-left">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['orders'] as $order)
                <tr>
                    <td class="px-4 py-2">{{ $order->id }}</td>
                    <td class="px-4 py-2">{{ $order->customer->name }}</td>
                    <td class="px-4 py-2">${{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-2">{{ ucfirst($order->status) }}</td>
                    <td class="px-4 py-2">{{ $order->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection