<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PrediccionController extends Controller
{
    private $deepseekApiKey;
    private $deepseekEndpoint = 'https://api.deepseek.com/v1/predict';

    public function __construct()
    {
        $this->deepseekApiKey = config('services.deepseek.api_key');
    }

    public function index(Request $request)
    {
        try {
            Log::info('Iniciando generación de predicción');
            $meses = $request->input('meses', 6);
            $fechaInicio = Carbon::now()->subMonths($meses);

            Log::info('Parámetros de búsqueda', [
                'meses' => $meses,
                'fecha_inicio' => $fechaInicio->format('Y-m-d')
            ]);

            // Obtener datos históricos de órdenes y sus tareas
            $ordenes = Order::with([
                'tasks' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
                ->select(
                    'orders.id',
                    'orders.order_number',
                    'orders.order_date',
                    'orders.delivery_date',
                    'orders.status',
                    DB::raw('DATEDIFF(orders.delivery_date, orders.order_date) as duracion_estimada'),
                    DB::raw('(SELECT MAX(completed_at) FROM tasks WHERE order_id = orders.id AND status = "completed") as fecha_completado')
                )
                ->where('orders.order_date', '>=', $fechaInicio)
                ->where(function ($query) {
                    $query->where('orders.status', 'completed')
                        ->orWhere('orders.status', 'in_progress');
                })
                ->get();

            $ordenes = $ordenes->map(function ($orden) {
                $orden->duracion_real = $orden->fecha_completado ?
                    Carbon::parse($orden->fecha_completado)->diffInDays($orden->order_date) :
                    Carbon::now()->diffInDays($orden->order_date);

                // Calcular estadísticas de tareas
                $orden->total_tareas = $orden->tasks->count();
                $orden->tareas_completadas = $orden->tasks->where('status', 'completed')->count();
                $orden->tareas_pendientes = $orden->tasks->where('status', 'pending')->count();
                $orden->tareas_en_progreso = $orden->tasks->where('status', 'in_progress')->count();

                // Calcular el progreso general de las tareas
                $orden->progreso_tareas = $orden->total_tareas > 0
                    ? round(($orden->tareas_completadas / $orden->total_tareas) * 100, 2)
                    : 0;

                // Calcular factor de complejidad basado en tareas
                $orden->factor_complejidad = $this->calcularFactorComplejidad($orden);

                // Calcular margen de retraso
                $orden->margen_retraso = $orden->duracion_real - $orden->duracion_estimada;
                $orden->porcentaje_retraso = $orden->duracion_estimada > 0
                    ? round(($orden->margen_retraso / $orden->duracion_estimada) * 100, 2)
                    : 0;

                return $orden;
            });

            Log::info('Órdenes encontradas', [
                'total' => $ordenes->count(),
                'ordenes' => $ordenes->toArray(),
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_actual' => Carbon::now()->format('Y-m-d')
            ]);

            if ($ordenes->isEmpty()) {
                Log::warning('No se encontraron órdenes para el período seleccionado');
                return view('prediccion.index')->with('error', 'No hay datos históricos disponibles para el período seleccionado.');
            }

            // Usar DeepSeek para mejorar las predicciones
            $prediccionIA = $this->obtenerPrediccionDeepSeek($ordenes);

            // Combinar nuestros análisis con los resultados de DeepSeek
            $precision = $prediccionIA['precision'] ?? $this->calcularPrecisionMejorada($ordenes);
            $tendencia = $prediccionIA['tendencia'] ?? $this->analizarTendenciaMejorada($ordenes);
            $ordenesRiesgo = $prediccionIA['ordenes_riesgo'] ?? $this->identificarOrdenesRiesgoMejorado($ordenes);

            // Preparar datos para las gráficas
            $datosGraficas = $this->prepararDatosGraficas($ordenes);

            // Generar recomendaciones
            $recomendaciones = $prediccionIA['recomendaciones'] ??
                $this->generarRecomendacionesMejoradas($precision, $tendencia, $ordenesRiesgo, $ordenes);

            $prediccion = [
                'precision' => $precision,
                'tendencia' => $tendencia,
                'ordenes_riesgo' => $ordenesRiesgo,
                'recomendaciones' => $recomendaciones,
                'datos' => $ordenes->map(function ($orden) {
                    return [
                        'order_number' => $orden->order_number,
                        'duracion_estimada' => (int) $orden->duracion_estimada,
                        'duracion_real' => (int) abs($orden->duracion_real),
                        'margen_retraso' => $orden->margen_retraso,
                        'porcentaje_retraso' => $orden->porcentaje_retraso,
                        'factor_complejidad' => $orden->factor_complejidad,
                        'total_tareas' => $orden->total_tareas,
                        'tareas_completadas' => $orden->tareas_completadas,
                        'tareas_pendientes' => $orden->tareas_pendientes,
                        'tareas_en_progreso' => $orden->tareas_en_progreso,
                        'progreso_tareas' => $orden->progreso_tareas,
                        'tasks' => $orden->tasks->map(function ($task) {
                            return [
                                'id' => $task->id,
                                'title' => $task->title,
                                'status' => $task->status,
                                'created_at' => $task->created_at->format('Y-m-d'),
                                'completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d') : null
                            ];
                        })->values()->toArray()
                    ];
                })->values()->toArray(),
                'graficas' => $datosGraficas
            ];

            Log::info('Predicción generada exitosamente', [
                'precision' => $precision,
                'tendencia' => $tendencia,
                'ordenes_riesgo' => $ordenesRiesgo,
                'recomendaciones' => $recomendaciones
            ]);

            return view('prediccion.index', compact('prediccion'));
        } catch (\Exception $e) {
            Log::error('Error al generar predicción: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return view('prediccion.index')->with('error', 'Error al generar la predicción: ' . $e->getMessage());
        }
    }

    /**
     * Estimar la fecha de entrega para una nueva orden
     */
    public function estimarFechaEntrega(Request $request)
    {
        $request->validate([
            'num_tareas' => 'required|integer|min:1',
            'complejidad' => 'required|string|in:baja,media,alta'
        ]);

        try {
            // Obtener datos para el modelo predictivo
            $numTareas = $request->input('num_tareas');
            $complejidad = $request->input('complejidad');
            $descripcion = $request->input('descripcion', '');

            // Buscar órdenes similares para referencia
            $ordenesHistoricas = Order::with('tasks')
                ->where('status', 'completed')
                ->orderBy('order_date', 'desc')
                ->limit(50)
                ->get();

            if ($ordenesHistoricas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay datos históricos suficientes para hacer una estimación'
                ]);
            }

            // Intentar usar DeepSeek primero
            $estimacionIA = $this->estimarConDeepSeek($numTareas, $complejidad, $descripcion, $ordenesHistoricas);

            if ($estimacionIA) {
                return response()->json([
                    'success' => true,
                    'dias_estimados' => $estimacionIA['dias_estimados'],
                    'fecha_estimada' => Carbon::now()->addDays($estimacionIA['dias_estimados'])->format('Y-m-d'),
                    'confianza' => $estimacionIA['confianza'],
                    'fuente' => 'IA'
                ]);
            }

            // Si DeepSeek falla, usar nuestro modelo más simple
            $multiplicadorComplejidad = [
                'baja' => 1,
                'media' => 1.5,
                'alta' => 2.5
            ];

            // Calcular promedio de duración por tarea de órdenes históricas
            $promedioDiasPorTarea = $ordenesHistoricas->sum('duracion_estimada') /
                max(1, $ordenesHistoricas->sum(function ($orden) {
                    return $orden->tasks->count();
                }));

            // Estimar días necesarios
            $diasEstimados = ceil($numTareas * $promedioDiasPorTarea * $multiplicadorComplejidad[$complejidad]);
            $fechaEstimada = Carbon::now()->addDays($diasEstimados);

            return response()->json([
                'success' => true,
                'dias_estimados' => $diasEstimados,
                'fecha_estimada' => $fechaEstimada->format('Y-m-d'),
                'confianza' => 70, // Confianza más baja con el modelo simple
                'fuente' => 'Histórico'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al estimar fecha: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al estimar la fecha: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Realizar predicciones locales basadas en datos históricos
     */
    private function obtenerPrediccionDeepSeek($ordenes)
    {
        try {
            // Calcular métricas básicas
            $precision = $this->calcularPrecisionMejorada($ordenes);
            $tendencia = $this->analizarTendenciaMejorada($ordenes);
            $ordenesRiesgo = $this->identificarOrdenesRiesgoMejorado($ordenes);

            // Análisis de patrones
            $patrones = $this->analizarPatrones($ordenes);

            // Generar recomendaciones basadas en el análisis
            $recomendaciones = $this->generarRecomendacionesMejoradas(
                $precision,
                $tendencia,
                $ordenesRiesgo,
                $ordenes
            );

            return [
                'precision' => $precision,
                'tendencia' => $tendencia,
                'ordenes_riesgo' => $ordenesRiesgo,
                'recomendaciones' => $recomendaciones,
                'patrones' => $patrones
            ];
        } catch (\Exception $e) {
            Log::error('Error en predicción local: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Estimar fecha de entrega usando análisis local
     */
    private function estimarConDeepSeek($numTareas, $complejidad, $descripcion, $ordenesHistoricas)
    {
        try {
            // Factores de complejidad
            $factoresComplejidad = [
                'baja' => 1.0,
                'media' => 1.5,
                'alta' => 2.5
            ];

            // Calcular tiempo promedio por tarea
            $tiemposPorTarea = $ordenesHistoricas->map(function ($orden) {
                return [
                    'tiempo' => $orden->duracion_real,
                    'tareas' => $orden->tasks->count(),
                    'complejidad' => $orden->factor_complejidad
                ];
            });

            // Filtrar órdenes con complejidad similar
            $ordenesSimilares = $tiemposPorTarea->filter(function ($item) use ($complejidad, $factoresComplejidad) {
                $factorComplejidad = $factoresComplejidad[$complejidad];
                return abs($item['complejidad'] - $factorComplejidad) <= 0.5;
            });

            // Calcular tiempo promedio por tarea
            $tiempoPromedioPorTarea = $ordenesSimilares->avg(function ($item) {
                return $item['tiempo'] / max(1, $item['tareas']);
            });

            // Si no hay órdenes similares, usar el promedio general
            if ($tiempoPromedioPorTarea === null) {
                $tiempoPromedioPorTarea = $tiemposPorTarea->avg(function ($item) {
                    return $item['tiempo'] / max(1, $item['tareas']);
                });
            }

            // Calcular días estimados
            $diasEstimados = ceil($numTareas * $tiempoPromedioPorTarea * $factoresComplejidad[$complejidad]);

            // Calcular nivel de confianza basado en la cantidad de datos disponibles
            $confianza = min(95, 70 + ($ordenesSimilares->count() * 2));

            // Ajustar confianza basado en la descripción
            if (strlen($descripcion) > 100) {
                $confianza += 5; // Más detalles = mayor confianza
            }

            return [
                'dias_estimados' => $diasEstimados,
                'confianza' => min(95, $confianza)
            ];
        } catch (\Exception $e) {
            Log::error('Error en estimación local: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analizar patrones en los datos históricos
     */
    private function analizarPatrones($ordenes)
    {
        $patrones = [];

        // Patrón de retrasos por día de la semana
        $retrasosPorDia = $ordenes->groupBy(function ($orden) {
            return Carbon::parse($orden->order_date)->format('l');
        })->map(function ($grupo) {
            return $grupo->avg('margen_retraso');
        });

        // Patrón de complejidad vs tiempo
        $complejidadVsTiempo = $ordenes->map(function ($orden) {
            return [
                'complejidad' => $orden->factor_complejidad,
                'tiempo_real' => $orden->duracion_real,
                'tiempo_estimado' => $orden->duracion_estimada
            ];
        });

        // Identificar correlaciones
        $patrones['retrasos_por_dia'] = $retrasosPorDia->toArray();
        $patrones['complejidad_vs_tiempo'] = $complejidadVsTiempo->toArray();

        return $patrones;
    }

    /**
     * Preparar datos para gráficas
     */
    private function prepararDatosGraficas($ordenes)
    {
        // 1. Datos para gráfica de precisión por mes
        $precisionPorMes = [];
        $ordenesPorMes = $ordenes->groupBy(function ($orden) {
            return Carbon::parse($orden->order_date)->format('Y-m');
        });

        foreach ($ordenesPorMes as $mes => $ordenesMes) {
            $precisionMes = $this->calcularPrecisionMejorada($ordenesMes);
            $mesFormateado = Carbon::createFromFormat('Y-m', $mes)->format('M Y');
            $precisionPorMes[] = [
                'mes' => $mesFormateado,
                'precision' => $precisionMes
            ];
        }

        // 2. Datos para gráfica de complejidad vs retraso
        $complejidadVsRetraso = $ordenes->map(function ($orden) {
            return [
                'order_number' => $orden->order_number,
                'complejidad' => $orden->factor_complejidad,
                'retraso' => max(0, $orden->margen_retraso)
            ];
        })->values()->toArray();

        // 3. Datos para gráfica de distribución de tareas
        $distribucionTareas = [
            'completadas' => $ordenes->sum('tareas_completadas'),
            'pendientes' => $ordenes->sum('tareas_pendientes'),
            'en_progreso' => $ordenes->sum('tareas_en_progreso')
        ];

        // 4. Datos para gráfica de comparación estimado vs real
        $comparativoTiempos = $ordenes->map(function ($orden) {
            return [
                'order_number' => $orden->order_number,
                'estimado' => $orden->duracion_estimada,
                'real' => $orden->duracion_real
            ];
        })->values()->toArray();

        return [
            'precision_por_mes' => $precisionPorMes,
            'complejidad_vs_retraso' => $complejidadVsRetraso,
            'distribucion_tareas' => $distribucionTareas,
            'comparativo_tiempos' => $comparativoTiempos
        ];
    }

    private function calcularFactorComplejidad($orden)
    {
        $factor = 1.0;

        // Ajustar por número de tareas
        $factor += ($orden->total_tareas * 0.1);

        // Ajustar por tareas pendientes
        $factor += ($orden->tareas_pendientes * 0.2);

        // Ajustar por tareas en progreso
        $factor += ($orden->tareas_en_progreso * 0.15);

        return round($factor, 2);
    }

    private function calcularPrecisionMejorada($ordenes)
    {
        if ($ordenes->isEmpty()) {
            return 0;
        }

        $pesoTotal = 0;
        $precisionPonderada = 0;

        foreach ($ordenes as $orden) {
            // Calcular peso basado en complejidad y antigüedad
            $peso = $orden->factor_complejidad;

            // Calcular precisión individual
            $precisionIndividual = 0;
            if ($orden->margen_retraso <= 0) {
                $precisionIndividual = 100;
            } else {
                // Penalizar retrasos más largos
                $precisionIndividual = max(0, 100 - ($orden->porcentaje_retraso * 2));
            }

            $precisionPonderada += ($precisionIndividual * $peso);
            $pesoTotal += $peso;
        }

        return $pesoTotal > 0 ? round($precisionPonderada / $pesoTotal, 2) : 0;
    }

    private function analizarTendenciaMejorada($ordenes)
    {
        if ($ordenes->isEmpty()) {
            return 'Sin datos suficientes';
        }

        // Agrupar órdenes por mes
        $ordenesPorMes = $ordenes->groupBy(function ($orden) {
            return Carbon::parse($orden->order_date)->format('Y-m');
        });

        $tendencias = [];
        foreach ($ordenesPorMes as $mes => $ordenesMes) {
            $precisionMes = $this->calcularPrecisionMejorada($ordenesMes);
            $tendencias[$mes] = $precisionMes;
        }

        // Analizar tendencia
        if (count($tendencias) < 2) {
            return 'Datos insuficientes para tendencia';
        }

        $tendencias = array_values($tendencias);
        $diferencia = end($tendencias) - reset($tendencias);

        if ($diferencia > 10) {
            return 'Mejorando significativamente';
        } elseif ($diferencia > 0) {
            return 'Mejorando';
        } elseif ($diferencia < -10) {
            return 'Empeorando significativamente';
        } elseif ($diferencia < 0) {
            return 'Empeorando';
        } else {
            return 'Estable';
        }
    }

    private function identificarOrdenesRiesgoMejorado($ordenes)
    {
        return $ordenes->filter(function ($orden) {
            // Orden en progreso con retraso significativo
            $tieneRetraso = $orden->margen_retraso > 0;

            // Orden con muchas tareas pendientes
            $muchasTareasPendientes = $orden->tareas_pendientes > ($orden->total_tareas * 0.3);

            // Orden con bajo progreso de tareas
            $bajoProgreso = $orden->progreso_tareas < 30;

            // Orden con alta complejidad
            $altaComplejidad = $orden->factor_complejidad > 2.0;

            return $tieneRetraso || ($muchasTareasPendientes && $bajoProgreso) || $altaComplejidad;
        })->count();
    }

    private function generarRecomendacionesMejoradas($precision, $tendencia, $ordenesRiesgo, $ordenes)
    {
        $recomendaciones = [];

        // Recomendaciones basadas en precisión
        if ($precision < 70) {
            $recomendaciones[] = 'La precisión de predicción es baja. Revisar el proceso de estimación de tiempos.';
        } elseif ($precision < 85) {
            $recomendaciones[] = 'La precisión de predicción puede mejorarse. Considerar ajustar los márgenes de tiempo.';
        }

        // Recomendaciones basadas en tendencia
        if ($tendencia === 'Empeorando' || $tendencia === 'Empeorando significativamente') {
            $recomendaciones[] = 'La tendencia muestra un deterioro en las entregas. Implementar medidas correctivas inmediatas.';
        }

        // Recomendaciones basadas en órdenes en riesgo
        if ($ordenesRiesgo > 0) {
            $recomendaciones[] = "Hay {$ordenesRiesgo} órdenes en riesgo. Priorizar su atención.";
        }

        // Recomendaciones basadas en análisis de tareas
        $tareasRetrasadas = $ordenes->flatMap->tasks->filter(function ($task) {
            return $task->status === 'in_progress' &&
                Carbon::parse($task->created_at)->addDays(7) < Carbon::now();
        })->count();

        if ($tareasRetrasadas > 0) {
            $recomendaciones[] = "Hay {$tareasRetrasadas} tareas con más de 7 días en progreso. Revisar su estado.";
        }

        // Recomendaciones basadas en complejidad
        $ordenesComplejas = $ordenes->filter(function ($orden) {
            return $orden->factor_complejidad > 2.0;
        })->count();

        if ($ordenesComplejas > 0) {
            $recomendaciones[] = "Hay {$ordenesComplejas} órdenes con alta complejidad. Considerar dividirlas en subórdenes.";
        }

        if (empty($recomendaciones)) {
            $recomendaciones[] = 'El sistema está funcionando correctamente. Mantener las buenas prácticas actuales.';
        }

        return $recomendaciones;
    }
}