<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TaskFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Task $task)
    {
        $files = $task->attachments ?? [];
        
        if (request()->wantsJson()) {
            return response()->json($files);
        }
        
        return view('tasks.files.index', compact('task', 'files'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:10240' // Máximo 10MB
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $path = $file->store('task-files/' . $task->id, 'public');
            
            $files = $task->attachments ?? [];
            $files[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toDateTimeString(),
                'uploaded_by' => auth()->id()
            ];
            
            $task->attachments = $files;
            $task->save();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Archivo subido exitosamente',
                    'file' => end($files)
                ]);
            }

            return redirect()->route('tasks.show', $task)
                ->with('success', 'Archivo subido exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Error al subir el archivo'], 500);
            }
            return back()->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task, $fileIndex)
    {
        try {
            DB::beginTransaction();

            $files = $task->attachments ?? [];
            
            if (!isset($files[$fileIndex])) {
                throw new \Exception('Archivo no encontrado');
            }

            $file = $files[$fileIndex];
            
            // Eliminar archivo físico
            if (Storage::disk('public')->exists($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }

            // Eliminar del array de archivos
            unset($files[$fileIndex]);
            $files = array_values($files); // Reindexar array
            
            $task->attachments = $files;
            $task->save();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Archivo eliminado exitosamente'
                ]);
            }

            return redirect()->route('tasks.show', $task)
                ->with('success', 'Archivo eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Error al eliminar el archivo'], 500);
            }
            return back()->with('error', 'Error al eliminar el archivo: ' . $e->getMessage());
        }
    }
} 