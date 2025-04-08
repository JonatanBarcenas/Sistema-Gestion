<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::with(['project.client', 'assignedUser']);

        // Búsqueda por título o descripción
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
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

        // Filtro por proyecto
        if ($request->has('project_id') && $request->project_id !== '') {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->orderBy('position')->paginate(10);

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::with('client')->get();
        $users = User::all();
        return view('tasks.form', compact('projects', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'type' => 'required|in:design,printing,advertising,packaging,other',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'materials_needed' => 'nullable|array',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'checklist' => 'nullable|array',
            'color' => 'nullable|string|max:7',
            'assigned_to' => 'required|exists:users,id',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id'
        ]);

        try {
            DB::beginTransaction();

            $task = Task::create($validated);

            // Asignar dependencias
            if ($request->has('dependencies')) {
                $task->dependencies()->attach($request->dependencies);
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
            'project.client',
            'assignedUser',
            'dependencies',
            'dependentTasks',
            'comments' => function ($query) {
                $query->with('user')->latest();
            }
        ]);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $projects = Project::with('client')->get();
        $users = User::all();
        $task->load(['dependencies', 'dependentTasks']);
        return view('tasks.form', compact('task', 'projects', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'type' => 'required|in:design,printing,advertising,packaging,other',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'materials_needed' => 'nullable|array',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'checklist' => 'nullable|array',
            'color' => 'nullable|string|max:7',
            'assigned_to' => 'required|exists:users,id',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id'
        ]);

        try {
            DB::beginTransaction();

            $task->update($validated);

            // Actualizar dependencias
            $task->dependencies()->sync($request->dependencies ?? []);

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

            // Eliminar dependencias
            $task->dependencies()->detach();
            $task->dependentTasks()->detach();

            // Eliminar comentarios
            $task->comments()->delete();

            // Eliminar la tarea
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
