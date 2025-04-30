<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function show(Request $request)
    {
        $type = $request->input('type', 'general');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        switch ($type) {
            case 'orders':
                $data = $this->getOrdersReport($startDate, $endDate);
                break;
            case 'projects':
                $data = $this->getProjectsReport($startDate, $endDate);
                break;
            case 'tasks':
                $data = $this->getTasksReport($startDate, $endDate);
                break;
            case 'users':
                $data = $this->getUsersReport($startDate, $endDate);
                break;
            default:
                $data = $this->getGeneralReport($startDate, $endDate);
        }

        return view('reports.show', compact('data', 'type'));
    }

    private function getGeneralReport($startDate, $endDate)
    {
        $query = Order::query();
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total_amount'),
            'average_order_value' => $query->avg('total_amount'),
            'orders_by_status' => $this->getOrdersByStatus(),
            'recent_orders' => $query->latest()->take(5)->get(),
        ];
    }

    private function getOrdersReport($startDate, $endDate)
    {
        $query = Order::with(['customer', 'products']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'orders' => $query->get(),
            'total_revenue' => $query->sum('total_amount'),
            'orders_by_status' => $this->getOrdersByStatus(),
            'top_customers' => $query->select('customer_id', 
                DB::raw('count(*) as order_count'), 
                DB::raw('sum(total_amount) as total_spent'))
                ->groupBy('customer_id')
                ->orderByDesc('total_spent')
                ->take(5)
                ->get(),
        ];
    }

    private function getProjectsReport($startDate, $endDate)
    {
        $query = Project::with(['client', 'team']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'projects' => $query->get(),
            'projects_by_status' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'team_performance' => DB::table('project_team')
                ->join('users', 'project_team.user_id', '=', 'users.id')
                ->select('users.name', DB::raw('count(*) as project_count'))
                ->groupBy('users.id', 'users.name')
                ->get(),
        ];
    }

    private function getTasksReport($startDate, $endDate)
    {
        $query = Task::with(['assignees', 'order.customer']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'tasks' => $query->get(),
            'tasks_by_status' => Task::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'user_performance' => DB::table('users')
                ->leftJoin('task_users', 'users.id', '=', 'task_users.user_id')
                ->select('users.id', 'users.name', DB::raw('COUNT(task_users.task_id) as task_count'))
                ->groupBy('users.id', 'users.name')
                ->get()
        ];
    }

    private function getUsersReport($startDate, $endDate)
    {
        $query = User::with(['tasks' => function($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereDate('tasks.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('tasks.created_at', '<=', $endDate);
            }
        }]);

        return [
            'users' => $query->get(),
            'user_performance' => DB::table('users')
                ->select(
                    'users.id',
                    'users.name',
                    DB::raw('COUNT(DISTINCT task_users.task_id) as task_count')
                )
                ->leftJoin('task_users', 'users.id', '=', 'task_users.user_id')
                ->leftJoin('tasks', 'task_users.task_id', '=', 'tasks.id')
                ->when($startDate, function($query) use ($startDate) {
                    return $query->whereDate('tasks.created_at', '>=', $startDate);
                })
                ->when($endDate, function($query) use ($endDate) {
                    return $query->whereDate('tasks.created_at', '<=', $endDate);
                })
                ->groupBy('users.id', 'users.name')
                ->get(),
            'project_teams' => DB::table('project_team')
                ->join('users', 'project_team.user_id', '=', 'users.id')
                ->join('projects', 'project_team.project_id', '=', 'projects.id')
                ->select('users.name as user_name', 'projects.name as project_name', 'project_team.role')
                ->get(),
        ];
    }

    public function export(Request $request)
    {
        try {
            $type = $request->input('type');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $format = $request->input('format');

            // Get report data
            $data = $this->getReportData($type, $startDate, $endDate);
            $filename = "reporte_{$type}_" . now()->format('Y-m-d_His');

            if ($format === 'pdf') {
                // Validate view exists
                $view = "reports.pdf.{$type}";
                if (!view()->exists($view)) {
                    return back()->with('error', 'Plantilla de reporte no encontrada');
                }

                try {
                    // Configure PDF with proper settings
                    $pdf = PDF::loadView($view, [
                        'data' => $data,
                        'startDate' => $startDate ? date('d/m/Y', strtotime($startDate)) : null,
                        'endDate' => $endDate ? date('d/m/Y', strtotime($endDate)) : null,
                        'title' => ucfirst($type)
                    ]);

                    $pdf->setPaper('a4', 'landscape');
                    $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
                    $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
                    
                    // Return the PDF as a download
                    return $pdf->download($filename . '.pdf');
                } catch (\Exception $e) {
                    \Log::error('Error en generación de PDF', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
                }
            } elseif ($format === 'excel') {
                return Excel::download(new ReportExport($data, $type), $filename . '.xlsx');
            }

            return back()->with('error', 'Formato no soportado');
        } catch (\Exception $e) {
            \Log::error('Error en exportación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    private function getReportData($type, $startDate, $endDate)
    {
        switch ($type) {
            case 'general':
                return $this->getGeneralReport($startDate, $endDate);
            case 'orders':
                return $this->getOrdersReport($startDate, $endDate);
            case 'tasks':
                return $this->getTasksReport($startDate, $endDate);
            case 'users':
                return $this->getUsersReport($startDate, $endDate);
            default:
                return [];
        }
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        $totalSales = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->sum('total_amount');

        $totalOrders = Order::whereBetween('orders.created_at', [$startDate, $endDate])->count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $productsSold = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');

        $dailySales = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->get();

        $salesByCategory = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.category', DB::raw('SUM(order_items.quantity * order_items.price) as total'))
            ->groupBy('products.category')
            ->get();

        $recentOrders = Order::with('customer')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get();

        return view('reports.sales', compact(
            'totalSales',
            'totalOrders',
            'activeCustomers',
            'productsSold',
            'dailySales',
            'salesByCategory',
            'recentOrders',
            'startDate',
            'endDate'
        ));
    }

    public function products(Request $request)
    {
        $category = $request->input('category');
        $stockStatus = $request->input('stock_status');

        $query = Product::query()->where('is_active', true);

        if ($category) {
            $query->where('category', $category);
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $query->where('stock', '>', 10);
                    break;
                case 'low_stock':
                    $query->whereBetween('stock', [1, 10]);
                    break;
                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;
            }
        }

        // Get total products stats
        $totalProducts = Product::where('is_active', true)->count();
        $totalInventoryValue = Product::where('is_active', true)->sum(DB::raw('stock * base_price'));
        $lowStockProducts = Product::where('is_active', true)->where('stock', '<=', 10)->count();
        $outOfStockProducts = Product::where('is_active', true)->where('stock', 0)->count();

        // Get products by category
        $productsByCategory = Product::where('is_active', true)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        // Get top selling products
        $topSellingProducts = Order::where('orders.status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $products = $query->get();

        return view('reports.products', compact(
            'totalProducts',
            'totalInventoryValue',
            'lowStockProducts',
            'outOfStockProducts',
            'productsByCategory',
            'topSellingProducts',
            'products',
            'category',
            'stockStatus'
        ));
    }

    public function customers(Request $request)
    {
        $status = $request->input('status');
        $query = Customer::query();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Get customer statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();
        $totalRevenue = Order::where('orders.status', 'completed')->sum('total_amount');
        $totalOrders = Order::count();
        $averageTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Get customer distribution by status
        $customerDistribution = Customer::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Get top customers
        $topCustomers = Order::where('orders.status', 'completed')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('customers.name', DB::raw('SUM(orders.total_amount) as total_spent'))
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        $customers = $query->get();

        return view('reports.customers', compact(
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers',
            'totalRevenue',
            'totalOrders',
            'averageTicket',
            'customerDistribution',
            'topCustomers',
            'customers',
            'status'
        ));
    }

    public function tasks(Request $request)
    {
        $status = $request->input('status');
        $priority = $request->input('priority');
        $query = Task::query();

        if ($priority && $priority !== 'all') {
            $query->where('priority', $priority);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $totalTasks = Task::count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $completedTasks = Task::where('status', 'completed')->count();
        $highPriorityTasks = Task::where('priority', 'high')->count();
        $mediumPriorityTasks = Task::where('priority', 'medium')->count();
        $lowPriorityTasks = Task::where('priority', 'low')->count();

        $tasksByStatus = Task::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $tasksByPriority = Task::select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->get();

        $tasks = $query->with('order')->get();

        return view('reports.tasks', compact(
            'totalTasks',
            'pendingTasks',
            'inProgressTasks',
            'completedTasks',
            'highPriorityTasks',
            'mediumPriorityTasks',
            'lowPriorityTasks',
            'tasksByStatus',
            'tasksByPriority',
            'tasks',
            'status',
            'priority'
        ));
    }

    private function getOrdersByStatus()
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }
}