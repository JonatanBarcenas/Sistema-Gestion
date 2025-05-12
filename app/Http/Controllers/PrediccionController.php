<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Task;
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
            $ordenes = Order::with(['tasks' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
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

            // Calcular métricas mejoradas
            $totalOrdenes = $ordenes->count();
            $ordenesRetrasadas = $ordenes->filter(function ($orden) {
                return $orden->margen_retraso > 0;
            })->count();

            // Calcular precisión considerando el margen de retraso
            $precision = $this->calcularPrecisionMejorada($ordenes);

            // Analizar tendencia con más detalle
            $tendencia = $this->analizarTendenciaMejorada($ordenes);

            // Identificar órdenes en riesgo con más precisión
            $ordenesRiesgo = $this->identificarOrdenesRiesgoMejorado($ordenes);

            // Generar recomendaciones más detalladas
            $recomendaciones = $this->generarRecomendacionesMejoradas($precision, $tendencia, $ordenesRiesgo, $ordenes);

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
                        'tasks' => $orden->tasks->map(function($task) {
                            return [
                                'id' => $task->id,
                                'title' => $task->title,
                                'status' => $task->status,
                                'created_at' => $task->created_at->format('Y-m-d'),
                                'completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d') : null
                            ];
                        })->values()->toArray()
                    ];
                })->values()->toArray()
            ];

            Log::info('Predicción generada exitosamente', [
                'precision' => $precision,
                'tendencia' => $tendencia,
                'ordenes_riesgo' => $ordenesRiesgo,
                'recomendaciones' => $recomendaciones,
                'datos' => $prediccion['datos']
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
        $ordenesPorMes = $ordenes->groupBy(function($orden) {
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
        $tareasRetrasadas = $ordenes->flatMap->tasks->filter(function($task) {
            return $task->status === 'in_progress' && 
                   Carbon::parse($task->created_at)->addDays(7) < Carbon::now();
        })->count();

        if ($tareasRetrasadas > 0) {
            $recomendaciones[] = "Hay {$tareasRetrasadas} tareas con más de 7 días en progreso. Revisar su estado.";
        }

        // Recomendaciones basadas en complejidad
        $ordenesComplejas = $ordenes->filter(function($orden) {
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