<?php

namespace App\Http\Controllers;

use App\Http\Requests\TareaRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\Order;
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
        $tasks = Task::select('id', 'title')->get();
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
        $tasks = Task::where('id', '!=', $task->id)->select('id', 'title')->get();
        $task->load(['dependencies', 'dependentTasks']);
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

            $task->update($validated);
            $task->assignUsers($assignees);
            $task->dependencies()->sync($validated['dependencies'] ?? []);

            DB::commit();
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Tarea actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la tarea: ' . $e->getMessage());
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
}
