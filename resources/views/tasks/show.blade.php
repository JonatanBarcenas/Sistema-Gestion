@extends('layouts.app')

@section('title', 'Detalles de la Tarea')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('tasks.edit', $task) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Editar
                </a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('¿Estás seguro de que deseas eliminar esta tarea?')">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">Información General</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Descripción:</span> {{ $task->description }}</p>
                    <p><span class="font-medium">Estado:</span> 
                        <span class="px-2 py-1 rounded text-sm 
                            @if($task->status === 'completed') bg-green-100 text-green-800
                            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($task->status) }}
                        </span>
                    </p>
                    <p><span class="font-medium">Prioridad:</span> 
                        <span class="px-2 py-1 rounded text-sm 
                            @if($task->priority === 'high') bg-red-100 text-red-800
                            @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </p>
                    <p><span class="font-medium">Tipo:</span> {{ ucfirst($task->type) }}</p>
                    <p><span class="font-medium">Fecha de vencimiento:</span> {{ $task->due_date->format('d/m/Y') }}</p>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">Asignados</h2>
                <div class="space-y-2">
                    @forelse($task->assignees as $assignee)
                        <div class="flex items-center space-x-2">
                            <span class="font-medium">{{ $assignee->name }}</span>
                            <span class="text-gray-500">({{ $assignee->email }})</span>
                        </div>
                    @empty
                        <p class="text-gray-500">No hay usuarios asignados</p>
                    @endforelse
                </div>
            </div>
        </div>

        @if($task->dependencies && $task->dependencies instanceof \Illuminate\Support\Collection && $task->dependencies->count() > 0)
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-2">Dependencias</h2>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($task->dependencies as $dependency)
                        <li>
                            <a href="{{ route('tasks.show', $dependency) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $dependency->title }}
                            </a>
                            <span class="text-sm text-gray-500">({{ ucfirst($dependency->status) }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($task->dependentTasks->count() > 0)
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-2">Tareas que dependen de esta</h2>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($task->dependentTasks as $dependentTask)
                        <li>
                            <a href="{{ route('tasks.show', $dependentTask) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $dependentTask->title }}
                            </a>
                            <span class="text-sm text-gray-500">({{ ucfirst($dependentTask->status) }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Comentarios</h2>
                <button onclick="document.getElementById('commentForm').classList.toggle('hidden')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Agregar comentario
                </button>
            </div>

            <form id="commentForm" action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-6 hidden">
                @csrf
                <div class="mb-4">
                    <textarea name="content" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Escribe tu comentario aquí..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Publicar comentario
                    </button>
                </div>
            </form>

            <div class="space-y-4">
                @forelse($task->comments as $comment)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-medium">{{ $comment->user->name }}</span>
                                <span class="text-sm text-gray-500">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($comment->user_id === auth()->id())
                                <form action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de que deseas eliminar este comentario?')">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <p class="text-gray-700">{{ $comment->content }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 text-center">No hay comentarios</p>
                @endforelse
            </div>
        </div>

        <div class="mt-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Archivos</h2>
                <a href="{{ route('tasks.files.index', $task) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Gestionar archivos
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($task->attachments ?? [] as $index => $file)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium truncate">{{ $file['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ number_format($file['size'] / 1024, 2) }} KB</p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center col-span-3">No hay archivos adjuntos</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar el formulario de comentarios
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    // Agregar el nuevo comentario a la lista
                    const commentsContainer = document.querySelector('.space-y-4');
                    const commentHtml = `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="font-medium">${data.comment.user.name}</span>
                                    <span class="text-sm text-gray-500">${new Date(data.comment.created_at).toLocaleString()}</span>
                                </div>
                                <form action="/tasks/${data.comment.task_id}/comments/${data.comment.id}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de que deseas eliminar este comentario?')">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <p class="text-gray-700">${data.comment.content}</p>
                        </div>
                    `;
                    
                    if (commentsContainer.querySelector('.text-gray-500.text-center')) {
                        commentsContainer.innerHTML = commentHtml;
                    } else {
                        commentsContainer.insertAdjacentHTML('afterbegin', commentHtml);
                    }
                    
                    // Limpiar el formulario
                    this.reset();
                    this.classList.add('hidden');
                    
                    // Mostrar notificación de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentario agregado',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(data.error || 'Error al agregar el comentario');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            }
        });
    }

    // Manejar la eliminación de comentarios
    document.querySelectorAll('form[action*="/comments/"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
                return;
            }
            
            try {
                const response = await fetch(this.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    // Eliminar el comentario del DOM
                    this.closest('.bg-gray-50').remove();
                    
                    // Si no quedan comentarios, mostrar mensaje
                    const commentsContainer = document.querySelector('.space-y-4');
                    if (!commentsContainer.querySelector('.bg-gray-50')) {
                        commentsContainer.innerHTML = '<p class="text-gray-500 text-center">No hay comentarios</p>';
                    }
                    
                    // Mostrar notificación de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentario eliminado',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(data.error || 'Error al eliminar el comentario');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            }
        });
    });
});
</script>
@endpush