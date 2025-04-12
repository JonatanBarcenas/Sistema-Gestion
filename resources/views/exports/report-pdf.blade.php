<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte {{ ucfirst($type) }}</div>
        <div class="subtitle">Generado el {{ $date }}</div>
    </div>

    @switch($type)
        @case('orders')
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['orders'] as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->customer->name }}</td>
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td>{{ $order->status }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @break

        @case('projects')
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['projects'] as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->client->name }}</td>
                            <td>{{ $project->status }}</td>
                            <td>{{ $project->start_date->format('d/m/Y') }}</td>
                            <td>{{ $project->end_date ? $project->end_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @break

        @case('tasks')
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Asignado a</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['tasks'] as $task)
                        <tr>
                            <td>{{ $task->id }}</td>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->assignedUser->name }}</td>
                            <td>{{ $task->status }}</td>
                            <td>{{ $task->priority }}</td>
                            <td>{{ $task->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @break

        @case('users')
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Tareas Asignadas</th>
                        <th>Proyectos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['users'] as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->tasks->count() }}</td>
                            <td>{{ $user->projects->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @break
    @endswitch

    <div class="footer">
        Página {PAGE_NUM} de {PAGE_COUNT}
    </div>
</body>
</html> 