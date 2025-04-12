<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with('customer');

        // Búsqueda
        if ($request->has('search')) {
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
        return view('orders.form', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generar número de pedido único
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            }

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
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

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
}
