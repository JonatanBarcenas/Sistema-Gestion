<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $comments = $project->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('projects.comments.index', compact('project', 'comments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
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

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Comentario agregado exitosamente',
                    'comment' => $comment->load('user')
                ]);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Comentario agregado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Error al agregar el comentario'], 500);
            }
            return back()->with('error', 'Error al agregar el comentario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, ProjectComment $comment)
    {
        $comment->load('user');
        return view('projects.comments.show', compact('project', 'comment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, ProjectComment $comment)
    {
        return view('projects.comments.form', compact('project', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, ProjectComment $comment)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'attachments' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $comment->update($validated);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Comentario actualizado exitosamente',
                    'comment' => $comment->load('user')
                ]);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Comentario actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Error al actualizar el comentario'], 500);
            }
            return back()->with('error', 'Error al actualizar el comentario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, ProjectComment $comment)
    {
        try {
            DB::beginTransaction();

            $comment->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Comentario eliminado exitosamente']);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Comentario eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Error al eliminar el comentario'], 500);
            }
            return back()->with('error', 'Error al eliminar el comentario: ' . $e->getMessage());
        }
    }
}
