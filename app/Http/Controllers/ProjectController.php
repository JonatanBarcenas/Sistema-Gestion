<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'notes' => 'nullable|string',
            'team' => 'required|array|min:1',
            'team.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $project = Project::create($validated);

            // Asignar equipo
            $project->team()->attach($request->team);

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

            $project->update($validated);

            // Actualizar equipo
            $project->team()->sync($request->team);

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
}
