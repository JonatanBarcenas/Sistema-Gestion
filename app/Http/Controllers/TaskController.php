<?php

namespace App\Http\Controllers;

use App\Http\Requests\TareaRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\Order;
use App\Notifications\TaskNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::with(['assignees']);

        // Búsqueda por título o descripción
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filtro por prioridad
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Filtro por tipo
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $tasks = $query->orderBy('position')->paginate(10);

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        $orders = Order::with('customer')->get();
        $tasks = Task::all(); // Add this line

        return view('tasks.form', compact('users', 'orders', 'tasks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TareaRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $assignees = $validated['assignees'];
            unset($validated['assignees']);

            $task = Task::create($validated);
            $task->assignUsers($assignees);

            if (isset($validated['dependencies'])) {
                $task->dependencies()->attach($validated['dependencies']);
            }

            // Notificar al cliente sobre la nueva tarea
            $this->notifyClient($task, 'created');

            DB::commit();
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Tarea creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la tarea: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load([
            'assignees' => fn($q) => $q->select('users.id', 'name', 'email'),
            'dependencies' => fn($q) => $q->select('tasks.id', 'title', 'status'),
            'dependentTasks' => fn($q) => $q->select('tasks.id', 'title', 'status'),
            'comments' => fn($q) => $q->with(['user' => fn($q) => $q->select('id', 'name', 'email')])->latest()
        ]);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $users = User::all();
        $orders = Order::with('customer')->get();
        $tasks = Task::where('id', '!=', $task->id)->get(); // Exclude current task

        return view('tasks.form', compact('task', 'users', 'orders', 'tasks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TareaRequest $request, Task $task)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $assignees = $validated['assignees'];
            unset($validated['assignees']);

            // Guardar cambios importantes para la notificación
            $changes = [];
            foreach ($validated as $field => $value) {
                if ($task->$field != $value) {
                    $changes[$field] = $value;
                }
            }

            $task->update($validated);
            $task->assignUsers($assignees);
            $task->dependencies()->sync($validated['dependencies'] ?? []);

            // Notificar al cliente sobre los cambios
            if (!empty($changes)) {
                $this->notifyClient($task, 'updated', $changes);
            }

            DB::commit();
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Tarea actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la tarea: ' . $e->getMessage());
        }
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
        \Log::info('Iniciando updateTaskStatus', [
            'task_id' => $task->id,
            'requested_status' => $request->status,
            'task_project' => $task->project ? $task->project->id : 'no project',
            'task_project_client' => $task->project && $task->project->client ? $task->project->client->id : 'no client'
        ]);

        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed'
            ]);

            \Log::info('Estado validado', ['status' => $validated['status']]);

            DB::beginTransaction();

            $task->update($validated);

            if ($validated['status'] === 'completed') {
                \Log::info('Tarea marcada como completada', [
                    'task_id' => $task->id
                ]);
                
                $task->completed_at = now();
                $task->progress = 100;
                $task->save();
                
                // Force eager loading de las relaciones necesarias
                $task->load(['project.customer']);
                $this->notifyClient($task, 'completed');
            } else {
                \Log::info('Cambio de estado normal', [
                    'task_id' => $task->id,
                    'new_status' => $validated['status']
                ]);
                
                $this->notifyClient($task, 'status_changed');
            }

            DB::commit();

            \Log::info('Actualización completada exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Estado de la tarea actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en updateTaskStatus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
            DB::beginTransaction();
            
            $task->dependencies()->detach();
            $task->dependentTasks()->detach();
            $task->comments()->delete();
            $task->delete();

            DB::commit();
            return redirect()->route('tasks.index')
                ->with('success', 'Tarea eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la tarea: ' . $e->getMessage());
        }
    }

    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'attachments' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $comment = $task->comments()->create([
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'attachments' => $validated['attachments'] ?? null
            ]);

            // Notificar al cliente sobre el nuevo comentario
            $this->notifyClient($task, 'comment_added');

            DB::commit();

            return response()->json([
                'message' => 'Comentario agregado exitosamente',
                'comment' => $comment->load('user')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al agregar el comentario'], 500);
        }
    }

    public function updateChecklist(Request $request, Task $task)
    {
        $validated = $request->validate([
            'checklist' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            $task->update(['checklist' => $validated['checklist']]);

            DB::commit();

            return response()->json([
                'message' => 'Checklist actualizado exitosamente',
                'checklist' => $task->checklist
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el checklist'], 500);
        }
    }

    protected function notifyClient(Task $task, $action, $changes = [])
    {
        \Log::info('Iniciando notifyClient', [
            'task_id' => $task->id,
            'action' => $action
        ]);

        try {
            // Verificar si la tarea tiene orden
            if (!$task->order) {
                \Log::warning('La tarea no tiene orden asociada', ['task_id' => $task->id]);
                return;
            }

            // Verificar si la orden tiene customer
            if (!$task->order->customer_id) {
                \Log::warning('La orden no tiene customer asociado', ['order_id' => $task->order->id]);
                return;
            }

            $order = $task->order;
            $customer = $order->customer;

            \Log::info('Customer encontrado', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email
            ]);

            $data = [
                'subject' => "Actualización en tarea de la orden: {$order->id}",
                'action_url' => route('tasks.show', $task->id),
                'action_text' => 'Ver Tarea',
                'task_id' => $task->id,
                'order_id' => $order->id,
                'type' => 'task_update'
            ];

            // Agregar logs
            \Log::info('Intentando enviar notificación', [
                'customer_email' => $customer->email,
                'task_id' => $task->id,
                'action' => $action
            ]);

            // Enviar la notificación al customer
            $customer->notify(new TaskNotification($data, $task));

            \Log::info('Notificación enviada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error en notifyClient', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
