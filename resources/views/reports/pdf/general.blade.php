<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte General</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .table th, .table td { padding: 8px; border: 1px solid #dee2e6; }
        .table th { background-color: #f8f9fa; }
        h1 { color: #333; margin-bottom: 20px; }
        .period { margin-bottom: 20px; color: #666; }
    </style>
</head>
<body>
    <h1>Reporte General</h1>
    <div class="period">
        Período: {{ $startDate ?? 'Todo' }} - {{ $endDate ?? 'Todo' }}
    </div>

    <table class="table">
        <tr>
            <th>Total de Pedidos</th>
            <td>{{ $data['total_orders'] }}</td>
        </tr>
        <tr>
            <th>Ingresos Totales</th>
            <td>${{ number_format($data['total_revenue'], 2) }}</td>
        </tr>
        <tr>
            <th>Valor Promedio por Pedido</th>
            <td>${{ number_format($data['average_order_value'], 2) }}</td>
        </tr>
    </table>

    <h2>Estado de Pedidos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['orders_by_status'] as $status)
                <tr>
                    <td>{{ ucfirst($status->status) }}</td>
                    <td>{{ $status->count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>