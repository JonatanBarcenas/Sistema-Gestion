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

            <!-- Sección de Gráficas -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Análisis Gráfico</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Gráfica de Precisión por Mes -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Precisión por Mes</h3>
                        <div class="h-64 relative">
                            @if(isset($prediccion['graficas']['precision_por_mes']) && count($prediccion['graficas']['precision_por_mes']) > 0)
                                <div class="flex items-end h-48 space-x-1">
                                    @foreach($prediccion['graficas']['precision_por_mes'] as $item)
                                        <div class="flex flex-col items-center">
                                            <div class="w-12 bg-blue-500 rounded-t-md" style="height: {{ ($item['precision'] / 100) * 100 }}%"></div>
                                            <span class="text-xs mt-2 transform -rotate-45 origin-top-left text-gray-600 w-16 truncate">{{ $item['mes'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- Eje Y -->
                                <div class="absolute left-0 top-0 h-48 flex flex-col justify-between py-2">
                                    <span class="text-xs text-gray-500">100%</span>
                                    <span class="text-xs text-gray-500">75%</span>
                                    <span class="text-xs text-gray-500">50%</span>
                                    <span class="text-xs text-gray-500">25%</span>
                                    <span class="text-xs text-gray-500">0%</span>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    No hay datos suficientes
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Gráfica de Distribución de Tareas -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Distribución de Tareas</h3>
                        <div class="h-64 flex items-center justify-center">
                            @if(isset($prediccion['graficas']['distribucion_tareas']))
                                @php
                                    $total = $prediccion['graficas']['distribucion_tareas']['completadas'] + 
                                            $prediccion['graficas']['distribucion_tareas']['pendientes'] + 
                                            $prediccion['graficas']['distribucion_tareas']['en_progreso'];
                                    $completadasPct = $total > 0 ? round(($prediccion['graficas']['distribucion_tareas']['completadas'] / $total) * 100) : 0;
                                    $pendientesPct = $total > 0 ? round(($prediccion['graficas']['distribucion_tareas']['pendientes'] / $total) * 100) : 0;
                                    $enProgresoPct = $total > 0 ? round(($prediccion['graficas']['distribucion_tareas']['en_progreso'] / $total) * 100) : 0;
                                @endphp
                                
                                <div class="w-full">
                                    <!-- Gráfico de dona simplificado con Tailwind -->
                                    <div class="relative pt-1">
                                        <div class="flex mb-2 items-center justify-between">
                                            <div>
                                                <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full bg-green-200 text-green-800">
                                                    Completadas ({{ $completadasPct }}%)
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs font-semibold inline-block text-green-800">
                                                    {{ $prediccion['graficas']['distribucion_tareas']['completadas'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="bg-green-500 h-4 rounded-full" style="width: {{ $completadasPct }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="relative pt-3">
                                        <div class="flex mb-2 items-center justify-between">
                                            <div>
                                                <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full bg-yellow-200 text-yellow-800">
                                                    En Progreso ({{ $enProgresoPct }}%)
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs font-semibold inline-block text-yellow-800">
                                                    {{ $prediccion['graficas']['distribucion_tareas']['en_progreso'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="bg-yellow-500 h-4 rounded-full" style="width: {{ $enProgresoPct }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="relative pt-3">
                                        <div class="flex mb-2 items-center justify-between">
                                            <div>
                                                <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full bg-red-200 text-red-800">
                                                    Pendientes ({{ $pendientesPct }}%)
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs font-semibold inline-block text-red-800">
                                                    {{ $prediccion['graficas']['distribucion_tareas']['pendientes'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="bg-red-500 h-4 rounded-full" style="width: {{ $pendientesPct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    No hay datos suficientes
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Gráfica de Complejidad vs Retraso -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Complejidad vs Retraso</h3>
                        <div class="h-64 relative">
                            @if(isset($prediccion['graficas']['complejidad_vs_retraso']) && count($prediccion['graficas']['complejidad_vs_retraso']) > 0)
                                <!-- Gráfico de dispersión simple con Tailwind -->
                                <div class="relative h-48 border-b border-l border-gray-300">
                                    @foreach($prediccion['graficas']['complejidad_vs_retraso'] as $item)
                                        @php
                                            $maxComplejidad = collect($prediccion['graficas']['complejidad_vs_retraso'])->max('complejidad') ?: 3;
                                            $maxRetraso = collect($prediccion['graficas']['complejidad_vs_retraso'])->max('retraso') ?: 1;
                                            
                                            $posX = ($item['complejidad'] / $maxComplejidad) * 100;
                                            $posY = 100 - (($item['retraso'] / $maxRetraso) * 100);
                                            
                                            // Ajustar para evitar posiciones fuera del gráfico
                                            $posX = min(max($posX, 5), 95);
                                            $posY = min(max($posY, 5), 95);
                                            
                                            $color = $item['retraso'] > 0 ? 'bg-red-500' : 'bg-green-500';
                                        @endphp
                                        <div class="absolute {{ $color }} w-3 h-3 rounded-full transform -translate-x-1/2 -translate-y-1/2" 
                                             style="left: {{ $posX }}%; top: {{ $posY }}%" 
                                             title="Orden: {{ $item['order_number'] }} - Complejidad: {{ $item['complejidad'] }} - Retraso: {{ $item['retraso'] }} días"></div>
                                    @endforeach
                                    
                                    <!-- Etiquetas de los ejes -->
                                    <div class="absolute bottom-0 left-0 w-full flex justify-between px-2 -mb-6">
                                        <span class="text-xs text-gray-500">0</span>
                                        <span class="text-xs text-gray-500">Complejidad</span>
                                        <span class="text-xs text-gray-500">{{ number_format($maxComplejidad, 1) }}</span>
                                    </div>
                                    <div class="absolute top-0 left-0 h-full flex flex-col justify-between py-2 -ml-6">
                                        <span class="text-xs text-gray-500">{{ $maxRetraso }}</span>
                                        <span class="text-xs text-gray-500">Retraso</span>
                                        <span class="text-xs text-gray-500">0</span>
                                    </div>
                                </div>
                                
                                <!-- Leyenda -->
                                <div class="flex items-center justify-center mt-6 space-x-4">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-red-500 rounded-full mr-1"></div>
                                        <span class="text-xs text-gray-600">Con retraso</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                                        <span class="text-xs text-gray-600">A tiempo</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    No hay datos suficientes
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Gráfica de Tiempo Estimado vs Real -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Estimado vs Real</h3>
                        <div class="h-64">
                            @if(isset($prediccion['graficas']['comparativo_tiempos']) && count($prediccion['graficas']['comparativo_tiempos']) > 0)
                                <div class="h-64 overflow-x-auto">
                                    <div class="min-w-max">
                                        <div class="flex items-end h-48 space-x-4 pl-8">
                                            @foreach($prediccion['graficas']['comparativo_tiempos'] as $item)
                                                <div class="flex flex-col items-center space-x-1">
                                                    <div class="flex items-end space-x-1">
                                                        <div class="w-8 bg-blue-400 rounded-t-md" style="height: {{ min(100, $item['estimado'] * 3) }}px"></div>
                                                        <div class="w-8 bg-purple-500 rounded-t-md" style="height: {{ min(100, $item['real'] * 3) }}px"></div>
                                                    </div>
                                                    <span class="text-xs mt-2 text-gray-600 transform -rotate-45 origin-top-left w-12 truncate">{{ substr($item['order_number'], -4) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Leyenda -->
                                <div class="flex items-center justify-center mt-2 space-x-4">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-400 mr-1"></div>
                                        <span class="text-xs text-gray-600">Estimado</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-purple-500 mr-1"></div>
                                        <span class="text-xs text-gray-600">Real</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    No hay datos suficientes
                                </div>
                            @endif
                        </div>
                    </div>
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