<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KanbanController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['client', 'team']);

        // Filtro por proyecto
        if ($request->has('project_id') && $request->project_id !== '') {
            $query->where('id', $request->project_id);
        }

        $projects = $query->get();

        // Si no hay proyecto seleccionado, usar el primero o null
        $selectedProject = $request->project_id ? 
            $projects->firstWhere('id', $request->project_id) : 
            $projects->first();

        if ($selectedProject) {
            $tasks = $selectedProject->tasks()
                ->with(['assignedUser', 'dependencies', 'dependentTasks', 'comments.user'])
                ->orderBy('position')
                ->get()
                ->groupBy('status');
        } else {
            $tasks = collect();
        }

        $users = User::all();

        return view('kanban.index', compact('projects', 'selectedProject', 'tasks', 'users'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|in:low,medium,high',
                'due_date' => 'nullable|date',
                'project_id' => 'required|exists:projects,id',
                'order_id' => 'nullable|exists:orders,id',
                'type' => 'required|in:design,printing,assembly,delivery',
                'estimated_hours' => 'nullable|numeric|min:0',
                'materials_needed' => 'nullable|array',
                'notes' => 'nullable|string',
                'attachments' => 'nullable|array',
                'checklist' => 'nullable|array',
                'color' => 'nullable|string',
                'position' => 'required|integer',
                'assignees' => 'required|array',
                'assignees.*' => 'exists:users,id',
                'dependencies' => 'nullable|array',
                'dependencies.*' => 'exists:tasks,id'
            ]);

            $task = Task::create($validated);

            // Asignar usuarios a la tarea
            $task->assignUsers($request->assignees);

            // Guardar checklist si existe
            if ($request->has('checklist')) {
                $task->checklist = $request->checklist;
                $task->save();
            }

            // Guardar dependencias si existen
            if ($request->has('dependencies')) {
                $task->dependencies = $request->dependencies;
                $task->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarea creada exitosamente',
                'task' => $task->load('assignees')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|in:low,medium,high',
                'due_date' => 'nullable|date',
                'project_id' => 'required|exists:projects,id',
                'order_id' => 'nullable|exists:orders,id',
                'type' => 'required|in:design,printing,assembly,delivery',
                'estimated_hours' => 'nullable|numeric|min:0',
                'actual_hours' => 'nullable|numeric|min:0',
                'materials_needed' => 'nullable|array',
                'notes' => 'nullable|string',
                'attachments' => 'nullable|array',
                'checklist' => 'nullable|array',
                'color' => 'nullable|string',
                'position' => 'required|integer',
                'assignees' => 'required|array',
                'assignees.*' => 'exists:users,id',
                'dependencies' => 'nullable|array',
                'dependencies.*' => 'exists:tasks,id',
                'progress' => 'nullable|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'blocked_by' => 'nullable|array',
                'blocked_by.*' => 'exists:tasks,id'
            ]);

            $task->update($validated);

            // Actualizar asignados
            $task->assignUsers($request->assignees);

            // Actualizar checklist si existe
            if ($request->has('checklist')) {
                $task->checklist = $request->checklist;
                $task->save();
            }

            // Actualizar dependencias si existen
            if ($request->has('dependencies')) {
                $task->dependencies = $request->dependencies;
                $task->save();
            }

            // Actualizar bloqueos si existen
            if ($request->has('blocked_by')) {
                $task->blocked_by = $request->blocked_by;
                $task->save();
            }

            // Si se actualiza el progreso
            if ($request->has('progress')) {
                $task->updateProgress($request->progress);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarea actualizada exitosamente',
                'task' => $task->load('assignees')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tarea eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed'
            ]);

            $task->update($validated);

            if ($validated['status'] === 'completed') {
                $task->completed_at = now();
                $task->progress = 100;
                $task->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado de la tarea actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTaskPosition(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'tasks' => 'required|array',
                'tasks.*.id' => 'required|exists:tasks,id',
                'tasks.*.position' => 'required|integer'
            ]);

            foreach ($validated['tasks'] as $taskData) {
                Task::where('id', $taskData['id'])
                    ->where('project_id', $project->id)
                    ->update(['position' => $taskData['position']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Posiciones de tareas actualizadas exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar posiciones de tareas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las posiciones de las tareas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProjectTasks(Project $project)
    {
        try {
            $tasks = $project->tasks()
                ->with(['assignees', 'comments'])
                ->orderBy('position')
                ->get()
                ->groupBy('status');

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Error al obtener tareas del proyecto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las tareas del proyecto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTaskDetails(Task $task)
    {
        try {
            $task->load(['assignees', 'comments.user']);
            return response()->json($task);
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles de la tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addComment(Request $request, Task $task)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string',
                'attachments' => 'nullable|array'
            ]);

            $comment = $task->comments()->create([
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'attachments' => $validated['attachments'] ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comentario agregado exitosamente',
                'comment' => $comment->load('user')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al agregar comentario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteComment(Task $task, TaskComment $comment)
    {
        try {
            $comment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Comentario eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar comentario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el comentario: ' . $e->getMessage()
            ], 500);
        }
    }
}
