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

        @if(isset($task->dependencies) && $task->dependencies->count() > 0)
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

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Comentarios</h2>
            <div class="space-y-4">
                @forelse($task->comments as $comment)
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $comment->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $comment->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <p class="mt-2">{{ $comment->content }}</p>
                    </div>
                @empty
                    <p class="text-gray-500">No hay comentarios</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection