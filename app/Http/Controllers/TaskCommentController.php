<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Task $task)
    {
        $comments = $task->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('tasks.comments.index', compact('task', 'comments'));
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
    public function store(Request $request, Task $task)
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

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Comentario agregado exitosamente',
                    'comment' => $comment->load('user')
                ]);
            }

            return redirect()->route('tasks.show', $task)
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
    public function show(Task $task, TaskComment $comment)
    {
        $comment->load('user');
        return view('tasks.comments.show', compact('task', 'comment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task, TaskComment $comment)
    {
        return view('tasks.comments.form', compact('task', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task, TaskComment $comment)
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

            return redirect()->route('tasks.show', $task)
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
    public function destroy(Task $task, TaskComment $comment)
    {
        try {
            DB::beginTransaction();

            $comment->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Comentario eliminado exitosamente']);
            }

            return redirect()->route('tasks.show', $task)
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
