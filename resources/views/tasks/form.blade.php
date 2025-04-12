@extends('layouts.app')

@section('title', isset($task) ? 'Editar Tarea' : 'Nueva Tarea')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">{{ isset($task) ? 'Editar Tarea' : 'Nueva Tarea' }}</h2>
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>

        <form action="{{ isset($task) ? route('tasks.update', $task) : route('tasks.store') }}" method="POST">
            @csrf
            @if(isset($task))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Título -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $task->title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description', $task->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pedido -->
                <div>
                    <label for="order_id" class="block text-sm font-medium text-gray-700">Pedido</label>
                    <select name="order_id" id="order_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('order_id') border-red-500 @enderror">
                        <option value="">Seleccione un pedido</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" {{ old('order_id', $task->order_id ?? '') == $order->id ? 'selected' : '' }}>
                                {{ $order->order_number }} - {{ $order->customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('type') border-red-500 @enderror">
                        <option value="design" {{ old('type', $task->type ?? '') === 'design' ? 'selected' : '' }}>Diseño</option>
                        <option value="printing" {{ old('type', $task->type ?? '') === 'printing' ? 'selected' : '' }}>Impresión</option>
                        <option value="advertising" {{ old('type', $task->type ?? '') === 'advertising' ? 'selected' : '' }}>Publicidad</option>
                        <option value="packaging" {{ old('type', $task->type ?? '') === 'packaging' ? 'selected' : '' }}>Empaque</option>
                        <option value="other" {{ old('type', $task->type ?? '') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Límite -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Fecha Límite</label>
                    <input type="datetime-local" name="due_date" id="due_date" value="{{ old('due_date', isset($task) ? $task->due_date->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('due_date') border-red-500 @enderror">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prioridad -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Prioridad</label>
                    <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('priority') border-red-500 @enderror">
                        <option value="low" {{ old('priority', $task->priority ?? '') === 'low' ? 'selected' : '' }}>Baja</option>
                        <option value="medium" {{ old('priority', $task->priority ?? '') === 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="high" {{ old('priority', $task->priority ?? '') === 'high' ? 'selected' : '' }}>Alta</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status', $task->status ?? '') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="in_progress" {{ old('status', $task->status ?? '') === 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                        <option value="completed" {{ old('status', $task->status ?? '') === 'completed' ? 'selected' : '' }}>Completada</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Asignados -->
                <div class="md:col-span-2">
                    <label for="assignees" class="block text-sm font-medium text-gray-700">Asignados</label>
                    <select name="assignees[]" id="assignees" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('assignees') border-red-500 @enderror">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ in_array($user->id, old('assignees', isset($task) ? $task->assignees->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assignees')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dependencias -->
                <div class="md:col-span-2">
                    <label for="dependencies" class="block text-sm font-medium text-gray-700">Dependencias</label>
                    <select name="dependencies[]" id="dependencies" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('dependencies') border-red-500 @enderror">
                        @foreach($tasks ?? [] as $dependency)
                            @if(!isset($task) || $dependency->id !== $task->id)
                                <option value="{{ $dependency->id }}" {{ in_array($dependency->id, old('dependencies', isset($task) ? $task->dependencies->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $dependency->title }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('dependencies')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ isset($task) ? 'Actualizar' : 'Crear' }} Tarea
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 