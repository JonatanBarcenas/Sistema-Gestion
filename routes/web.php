<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Rutas públicas (login y register)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Todas las demás rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // Ruta de logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Rutas de recuperación de contraseña
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Ruta raíz
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Recursos y rutas existentes
    Route::resource('customers', CustomerController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('products', ProductController::class);
    Route::resource('reports', ReportController::class);
    Route::resource('projects', ProjectController::class);

    // Rutas de tareas
    Route::post('tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::post('tasks/{task}/checklist', [TaskController::class, 'updateChecklist'])->name('tasks.checklist.update');

    // Rutas de notificaciones
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Rutas de reportes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/show', [ReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Rutas de proyectos
    Route::post('projects/{project}/comments', [ProjectController::class, 'addComment'])->name('projects.comments.store');
    Route::post('projects/{project}/tasks/order', [ProjectController::class, 'updateTaskOrder'])->name('projects.tasks.order');

    // Rutas de comentarios
    Route::resource('tasks.comments', TaskCommentController::class)->except(['create', 'store']);
    Route::resource('projects.comments', ProjectCommentController::class)->except(['create', 'store']);

    // Rutas del tablero Kanban
    Route::get('kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('kanban/tasks', [KanbanController::class, 'store'])->name('kanban.tasks.store');
    Route::put('kanban/tasks/{task}', [KanbanController::class, 'update'])->name('kanban.tasks.update');
    Route::delete('kanban/tasks/{task}', [KanbanController::class, 'destroy'])->name('kanban.tasks.destroy');
    Route::post('kanban/tasks/{task}/status', [KanbanController::class, 'updateTaskStatus'])->name('kanban.tasks.status');
    Route::post('kanban/projects/{project}/tasks/position', [KanbanController::class, 'updateTaskPosition'])->name('kanban.tasks.position');
    Route::get('kanban/projects/{project}/tasks', [KanbanController::class, 'getProjectTasks'])->name('kanban.projects.tasks');
    Route::get('kanban/tasks/{task}', [KanbanController::class, 'getTaskDetails'])->name('kanban.tasks.details');
    Route::post('kanban/tasks/{task}/comments', [KanbanController::class, 'addComment'])->name('kanban.tasks.comments.store');
    Route::delete('kanban/tasks/{task}/comments/{comment}', [KanbanController::class, 'deleteComment'])->name('kanban.tasks.comments.destroy');

    // Extensión de sesión
    Route::post('session/extend', function() {
        session()->put('last_activity', time());
        return response()->json(['success' => true]);
    })->name('session.extend');
});
