@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Predicción de Entregas</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ url('/prediccion-entregas') }}" class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label for="meses" class="text-gray-700">Período de análisis:</label>
                    <select name="meses" id="meses" class="form-select rounded-md border-gray-300">
                        <option value="3" {{ request()->get('meses') == 3 ? 'selected' : '' }}>Últimos 3 meses</option>
                        <option value="6" {{ request()->get('meses') == 6 || !request()->has('meses') ? 'selected' : '' }}>Últimos 6 meses</option>
                        <option value="12" {{ request()->get('meses') == 12 ? 'selected' : '' }}>Último año</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Generar Predicción
                </button>
            </div>
        </form>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(isset($prediccion))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Precisión de Predicción</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $prediccion['precision'] }}%</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Tendencia</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $prediccion['tendencia'] }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Órdenes en Riesgo</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ $prediccion['ordenes_riesgo'] }}</p>
                </div>
            </div>

            <!-- Visualización de Predicción -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Visualización de Predicción</h2>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="space-y-4">
                        @foreach($prediccion['datos'] as $orden)
                            <div class="relative">
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">Orden {{ $orden['order_number'] }}</span>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm text-gray-500">
                                            {{ $orden['duracion_real'] }} días / {{ $orden['duracion_estimada'] }} días
                                        </span>
                                        <span class="text-xs px-2 py-1 rounded-full {{ $orden['factor_complejidad'] > 2.0 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                            Complejidad: {{ $orden['factor_complejidad'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    @php
                                        $porcentaje = min(100, ($orden['duracion_real'] / $orden['duracion_estimada']) * 100);
                                        $color = $orden['duracion_real'] > $orden['duracion_estimada'] ? 'bg-red-500' : 'bg-green-500';
                                    @endphp
                                    <div class="{{ $color }} h-4 rounded-full" style="width: {{ $porcentaje }}%"></div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    @if($orden['margen_retraso'] > 0)
                                        <span class="text-red-600">Retraso: {{ $orden['margen_retraso'] }} días ({{ $orden['porcentaje_retraso'] }}%)</span>
                                    @else
                                        <span class="text-green-600">A tiempo</span>
                                    @endif
                                </div>

                                <!-- Información de Tareas -->
                                <div class="mt-4 bg-gray-50 p-3 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="text-sm font-medium text-gray-700">Progreso de Tareas</h4>
                                        <span class="text-sm font-medium {{ $orden['progreso_tareas'] >= 70 ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ $orden['progreso_tareas'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $orden['progreso_tareas'] }}%"></div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2 text-xs text-gray-600">
                                        <div>
                                            <span class="font-medium">Total:</span> {{ $orden['total_tareas'] }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Completadas:</span> {{ $orden['tareas_completadas'] }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Pendientes:</span> {{ $orden['tareas_pendientes'] }}
                                        </div>
                                        <div>
                                            <span class="font-medium">En Progreso:</span> {{ $orden['tareas_en_progreso'] }}
                                        </div>
                                    </div>

                                    <!-- Lista de Tareas -->
                                    @if(count($orden['tasks']) > 0)
                                        <div class="mt-3">
                                            <h5 class="text-xs font-medium text-gray-700 mb-2">Detalle de Tareas:</h5>
                                            <div class="space-y-2">
                                                @foreach($orden['tasks'] as $task)
                                                    <div class="flex justify-between items-center text-xs">
                                                        <span class="text-gray-600">{{ $task['title'] }}</span>
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-gray-500">{{ $task['created_at'] }}</span>
                                                            <span class="px-2 py-1 rounded-full text-xs
                                                                @if($task['status'] === 'completed')
                                                                    bg-green-100 text-green-800
                                                                @elseif($task['status'] === 'in_progress')
                                                                    bg-yellow-100 text-yellow-800
                                                                @else
                                                                    bg-gray-100 text-gray-800
                                                                @endif
                                                            ">
                                                                {{ ucfirst($task['status']) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Recomendaciones</h2>
                <ul class="list-disc pl-5 space-y-2">
                    @foreach($prediccion['recomendaciones'] as $recomendacion)
                        <li class="text-gray-700">{{ $recomendacion }}</li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">Datos Históricos</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número de Orden</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración Estimada</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración Real</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($prediccion['datos'] as $orden)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $orden['order_number'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $orden['duracion_estimada'] }} días</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $orden['duracion_real'] }} días</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($orden['duracion_real'] > $orden['duracion_estimada'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Retrasada</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">A tiempo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection