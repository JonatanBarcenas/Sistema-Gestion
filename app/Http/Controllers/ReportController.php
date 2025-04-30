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
use App\Exports\ReportExcelExport;
use App\Services\ReportPdfExport;
use Maatwebsite\Excel\Facades\Excel;

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
        $query = Order::with(['customer', 'items']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'orders' => $query->get(),
            'total_revenue' => $query->sum('total'),
            'orders_by_status' => $this->getOrdersByStatus(),
            'top_customers' => $query->select('customer_id', DB::raw('count(*) as order_count'), DB::raw('sum(total) as total_spent'))
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
        $query = Task::with(['assignedUser', 'project']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'tasks' => $query->get(),
            'tasks_by_status' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'user_performance' => $query->select('assigned_to', DB::raw('count(*) as task_count'))
                ->groupBy('assigned_to')
                ->get(),
        ];
    }

    private function getUsersReport($startDate, $endDate)
    {
        $query = User::with(['tasks', 'projects']);

        if ($startDate) {
            $query->whereHas('tasks', function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $query->whereHas('tasks', function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            });
        }

        return [
            'users' => $query->get(),
            'user_performance' => User::select('users.id', 'users.name', DB::raw('count(tasks.id) as task_count'))
                ->leftJoin('tasks', 'users.id', '=', 'tasks.assigned_to')
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
        $type = $request->input('type', 'general');
        $format = $request->input('format', 'pdf');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get the report data
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

        if ($format === 'excel') {
            return Excel::download(new ReportExcelExport($data, $type), 'reporte_' . $type . '_' . now()->format('Y-m-d') . '.xlsx');
        }

        // Default to PDF
        $pdf = new ReportPdfExport($data, $type);
        return $pdf->generate();
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

        $query->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', '=', 'completed');
            })
            ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sales'))
            ->groupBy('products.id');

        $totalProducts = Product::where('is_active', true)->count();
        $totalInventoryValue = Product::where('is_active', true)->sum(DB::raw('stock * base_price'));
        $lowStockProducts = Product::where('is_active', true)->where('stock', '<=', 10)->count();
        $outOfStockProducts = Product::where('is_active', true)->where('stock', 0)->count();

        $productsByCategory = Product::where('is_active', true)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        $topSellingProducts = Order::where('orders.status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
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

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();
        $totalRevenue = Order::where('orders.status', 'completed')->sum('total_amount');
        $totalOrders = Order::count();
        $averageTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $customerDistribution = Customer::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

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

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority && $priority !== 'all') {
            $query->where('priority', $priority);
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
