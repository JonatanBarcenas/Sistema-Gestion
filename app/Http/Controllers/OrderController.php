<?php

namespace App\Http\Controllers;

use App\Http\Requests\PedidoRequest;
use App\Mail\OrderUpdate;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with('customer');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por estado
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filtro por fecha
        if ($request->has('date_from')) {
            $query->where('delivery_date', '>=', $request->date_from);
        }

        $orders = $query->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('is_active', true)->get();
        
        // Obtener órdenes históricas para la predicción
        $ordenesHistoricas = Order::with('tasks')
            ->where('status', 'completed')
            ->orderBy('order_date', 'desc')
            ->limit(50)
            ->get();

        // Calcular predicción inicial
        $prediccion = $this->calcularPrediccionInicial($ordenesHistoricas);
        
        return view('orders.form', compact('customers', 'products', 'prediccion'));
    }

    /**
     * Calcular predicción inicial basada en datos históricos
     */
    private function calcularPrediccionInicial($ordenesHistoricas)
    {
        if ($ordenesHistoricas->isEmpty()) {
            return [
                'dias_estimados' => 0,
                'confianza' => 0,
                'mensaje' => 'No hay datos históricos suficientes para hacer una predicción',
                'fecha_sugerida' => null
            ];
        }

        // Calcular tiempo promedio por tarea
        $tiempoPromedioPorTarea = $ordenesHistoricas->avg(function ($orden) {
            return $orden->tasks->count() > 0 
                ? $orden->duracion_real / $orden->tasks->count() 
                : 0;
        });

        // Calcular factor de complejidad promedio
        $factorComplejidadPromedio = $ordenesHistoricas->avg(function ($orden) {
            return $orden->tasks->count() * 0.1 + 
                   $orden->tasks->where('status', 'pending')->count() * 0.2 +
                   $orden->tasks->where('status', 'in_progress')->count() * 0.15;
        });

        // Estimar días para una orden promedio (mínimo 1 día)
        $diasEstimados = max(1, ceil($tiempoPromedioPorTarea * $factorComplejidadPromedio));

        // Calcular nivel de confianza basado en la cantidad de datos
        $confianza = min(95, 70 + ($ordenesHistoricas->count() * 0.5));

        // Calcular fecha sugerida (hoy + días estimados)
        $fechaSugerida = now()->addDays($diasEstimados)->format('Y-m-d');

        // Calcular días hábiles (excluyendo fines de semana)
        $diasHabiles = 0;
        $fechaActual = now();
        while ($diasHabiles < $diasEstimados) {
            $fechaActual = $fechaActual->addDay();
            if (!$fechaActual->isWeekend()) {
                $diasHabiles++;
            }
        }
        $fechaSugeridaHabiles = $fechaActual->format('Y-m-d');

        return [
            'dias_estimados' => $diasEstimados,
            'confianza' => round($confianza, 2),
            'mensaje' => "Basado en {$ordenesHistoricas->count()} órdenes históricas",
            'fecha_sugerida' => $fechaSugerida,
            'fecha_sugerida_habiles' => $fechaSugeridaHabiles,
            'dias_habiles' => $diasHabiles
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PedidoRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Generar número de pedido único
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            }

            $validated = $request->validated();

            // Crear el pedido
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $validated['customer_id'],
                'order_date' => now(),
                'delivery_date' => $validated['delivery_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'total_amount' => 0,
                'payment_status' => 'pending',
                'paid_amount' => 0
            ]);

            // Agregar productos y calcular total
            $total = 0;
            foreach ($validated['products'] as $product) {
                $productTotal = $product['quantity'] * $product['unit_price'];
                $order->products()->attach($product['product_id'], [
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total_price' => $productTotal
                ]);
                $total += $productTotal;
            }

            // Actualizar total del pedido
            $order->update(['total_amount' => $total]);

            DB::commit();

            // Enviar notificación
            $this->notifyCustomer($order, 'created');

            return redirect()->route('orders.show', $order)
                ->with('success', 'Pedido creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'products']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('is_active', true)->get();
        $order->load('products');
        return view('orders.form', compact('order', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PedidoRequest $request, Order $order)
    {
        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            
            $validated = $request->validated();

            // Actualizar información básica del pedido
            $order->update([
                'customer_id' => $validated['customer_id'],
                'delivery_date' => $validated['delivery_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            // Eliminar productos existentes
            $order->products()->detach();

            // Agregar nuevos productos y calcular total
            $total = 0;
            foreach ($validated['products'] as $product) {
                $productTotal = $product['quantity'] * $product['unit_price'];
                $order->products()->attach($product['product_id'], [
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total_price' => $productTotal
                ]);
                $total += $productTotal;
            }

            // Actualizar total del pedido
            $order->update(['total_amount' => $total]);

            DB::commit();

            // Determinar tipo de notificación
            $notificationType = $oldStatus !== $validated['status'] ? 'status_changed' : 'updated';
            $this->notifyCustomer($order, $notificationType);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Pedido actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        try {
            DB::beginTransaction();

            // Eliminar productos asociados
            $order->products()->detach();

            // Eliminar el pedido
            $order->delete();

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Pedido eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    protected function notifyCustomer(Order $order, $action)
    {
        try {
            \Log::info('Iniciando notificación al cliente', [
                'order_id' => $order->id,
                'action' => $action
            ]);

            $customer = $order->customer;

            if (!$customer) {
                \Log::warning('Orden sin cliente asociado', ['order_id' => $order->id]);
                return;
            }

            $message = $this->getNotificationMessage($action, $order);
            $actionUrl = route('orders.show', $order->id);

            // Crear registro de email
            $emailLog = EmailLog::create([
                'recipient' => $customer->email,
                'subject' => "Actualización en pedido #{$order->order_number}",
                'message' => $message,
                'status' => 'pending'
            ]);

            Mail::to($customer->email)
                ->send(new OrderUpdate($order, $message, $actionUrl));

            // Actualizar registro de email
            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            \Log::info('Notificación enviada exitosamente');
        } catch (\Exception $e) {
            if (isset($emailLog)) {
                $emailLog->update(['status' => 'error']);
            }
            \Log::error('Error al enviar notificación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function getNotificationMessage($action, $order)
    {
        switch ($action) {
            case 'created':
                return "Su pedido #{$order->order_number} ha sido creado exitosamente.";
            case 'updated':
                return "Su pedido #{$order->order_number} ha sido actualizado.";
            case 'status_changed':
                return "El estado de su pedido #{$order->order_number} ha cambiado a {$order->status}.";
            default:
                return "Ha habido una actualización en su pedido #{$order->order_number}.";
        }
    }
}
