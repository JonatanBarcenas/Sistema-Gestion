<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Task;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $activeCustomers = Customer::where('status', 'active')->count();

        // Próximos pedidos (los próximos 5 pedidos pendientes o en progreso)
        $upcomingOrders = Order::with('customer')
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('delivery_date')
            ->take(5)
            ->get();

        // Tareas pendientes (las próximas 5 tareas pendientes)
        $pendingTasksList = Task::with('order')
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'pendingTasks',
            'activeCustomers',
            'upcomingOrders',
            'pendingTasksList'
        ));
    }
} 