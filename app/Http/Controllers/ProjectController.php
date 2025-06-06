<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\ProjectNotification;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['client', 'team']);

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtros
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        $projects = $query->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Customer::where('status', 'active')->get();
        $users = User::all();
        return view('projects.form', compact('clients', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:customers,id',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high',
            'type' => 'required|in:design,printing,advertising,packaging,other',
            'order_number' => 'required|string|unique:projects,order_number', // Agregado para validar duplicados
            'notes' => 'nullable|string',
            'team' => 'required|array|min:1',
            'team.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $project = Project::create($validated);

            // Asignar equipo
            $project->team()->attach($request->team);
            
            // Notificar al cliente sobre la creación del nuevo proyecto
            $this->notifyClientNewProject($project);

            DB::commit();

            return redirect()->route('projects.show', $project)
                ->with('success', 'Proyecto creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el proyecto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['client', 'team', 'tasks' => function ($query) {
            $query->orderBy('position');
        }, 'tasks.assignedUser', 'tasks.dependencies', 'tasks.dependentTasks']);
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $clients = Customer::where('status', 'active')->get();
        $users = User::all();
        $project->load('team');
        return view('projects.form', compact('project', 'clients', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:customers,id',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high',
            'type' => 'required|in:design,printing,advertising,packaging,other',
            'notes' => 'nullable|string',
            'team' => 'required|array|min:1',
            'team.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Guardar el estado anterior para detectar cambios
            $oldStatus = $project->status;
            $oldEndDate = $project->end_date;
            
            $project->update($validated);

            // Actualizar equipo
            $project->team()->sync($request->team);
            
            // Verificar si hubo cambios significativos que requieran notificación
            $statusChanged = $oldStatus !== $project->status;
            $endDateChanged = $oldEndDate !== $project->end_date;
            
            // Notificar al cliente sobre cambios importantes
            if ($statusChanged || $endDateChanged) {
                $this->notifyClient($project, $statusChanged, $endDateChanged);
            }

            DB::commit();

            return redirect()->route('projects.show', $project)
                ->with('success', 'Proyecto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el proyecto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            DB::beginTransaction();

            // Eliminar tareas asociadas
            $project->tasks()->delete();

            // Eliminar el proyecto
            $project->delete();

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Proyecto eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el proyecto: ' . $e->getMessage());
        }
    }

    public function updateTaskOrder(Request $request, Project $project)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->tasks as $task) {
                Task::where('id', $task['id'])->update(['position' => $task['position']]);
            }

            DB::commit();

            return response()->json(['message' => 'Orden actualizado exitosamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el orden'], 500);
        }
    }

    public function addComment(Request $request, Project $project)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'attachments' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $comment = $project->comments()->create([
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'attachments' => $validated['attachments'] ?? null
            ]);
            
            // Notificar al cliente sobre el nuevo comentario si está marcado como importante
            if ($request->has('is_important') && $request->is_important) {
                $this->notifyClient($project, false, false, true, $validated['content']);
            }

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
    
    /**
     * Notifica al cliente sobre cambios importantes en el proyecto.
     *
     * @param  Project  $project
     * @param  bool  $statusChanged
     * @param  bool  $endDateChanged
     * @param  bool  $newComment
     * @param  string|null  $commentContent
     * @return void
     */
    protected function notifyClient(Project $project, $statusChanged = false, $endDateChanged = false, $newComment = false, $commentContent = null)
    {
        // Verificar si el proyecto tiene un cliente asociado
        if (!$project->client_id) {
            return;
        }
        
        $client = $project->client;
        $preferences = $client->getOrCreateNotificationPreference();
        
        // Verificar las preferencias de notificación según el tipo de evento
        if ($statusChanged && !$preferences->project_status_changed) {
            return;
        }
        
        if ($endDateChanged && !$preferences->project_updated) {
            return;
        }
        
        if ($newComment && !$preferences->project_comment_added) {
            return;
        }
        
        if (!$statusChanged && !$endDateChanged && !$newComment && !$preferences->project_updated) {
            return;
        }
        
        // Preparar los datos de la notificación
        $data = [
            'subject' => 'Actualización en tu proyecto: ' . $project->name,
            'action_url' => route('projects.show', $project->id),
            'action_text' => 'Ver Proyecto',
            'project_id' => $project->id,
            'type' => 'project_update'
        ];
        
        // Personalizar el mensaje según el tipo de cambio
        if ($statusChanged) {
            $data['message'] = 'El estado de tu proyecto ha sido actualizado.';
            $data['description'] = 'El proyecto ahora está: ' . $this->getStatusText($project->status);
            $data['type'] = 'project_status_changed';
        } elseif ($endDateChanged) {
            $data['message'] = 'La fecha de finalización de tu proyecto ha sido modificada.';
            $data['description'] = 'Nueva fecha de finalización: ' . $project->end_date->format('d/m/Y');
            $data['type'] = 'project_updated';
        } elseif ($newComment) {
            $data['message'] = 'Se ha agregado un comentario importante a tu proyecto.';
            $data['description'] = $commentContent;
            $data['type'] = 'project_comment_added';
        } else {
            $data['message'] = 'Se han realizado cambios en tu proyecto.';
            $data['type'] = 'project_updated';
        }
        
        // Enviar la notificación al cliente
        $client->notify(new ProjectNotification($data, $project));
    }
    
    /**
     * Obtiene el texto legible del estado del proyecto.
     *
     * @param  string  $status
     * @return string
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            'planning' => 'En Planificación',
            'in_progress' => 'En Progreso',
            'on_hold' => 'En Espera',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado'
        ];
        
        return $statusMap[$status] ?? $status;
    }
    
    /**
     * Notifica al cliente sobre la creación de un nuevo proyecto.
     *
     * @param  Project  $project
     * @return void
     */
    protected function notifyClientNewProject(Project $project)
    {
        // Verificar si el proyecto tiene un cliente asociado
        if (!$project->client_id) {
            return;
        }
        
        $client = $project->client;
        $preferences = $client->getOrCreateNotificationPreference();
        
        // Verificar si el cliente desea recibir notificaciones de creación de proyectos
        if (!$preferences->project_created) {
            return;
        }
        
        // Preparar los datos de la notificación
        $data = [
            'subject' => 'Nuevo proyecto creado: ' . $project->name,
            'message' => 'Se ha creado un nuevo proyecto para ti.',
            'description' => 'Detalles del proyecto:\n' . 
                             'Nombre: ' . $project->name . '\n' .
                             'Estado: ' . $this->getStatusText($project->status) . '\n' .
                             'Prioridad: ' . ucfirst($project->priority),
            'action_url' => route('projects.show', $project->id),
            'action_text' => 'Ver Proyecto',
            'project_id' => $project->id,
            'type' => 'project_created'
        ];
        
        // Enviar la notificación al cliente
        $client->notify(new ProjectNotification($data, $project));
    }
}
