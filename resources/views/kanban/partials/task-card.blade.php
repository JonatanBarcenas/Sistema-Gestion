<div class="card task-card mb-2" data-task-id="{{ $task->id }}">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">{{ $task->title }}</h6>
                <p class="text-muted small mb-1">{{ Str::limit($task->description, 100) }}</p>
                
                <div class="d-flex align-items-center gap-2">
                    @if($task->assigned_user)
                        <span class="badge bg-info">
                            {{ $task->assigned_user->name }}
                        </span>
                    @endif
                    
                    <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                        {{ $task->priority }}
                    </span>
                    
                    @if($task->due_date)
                        <span class="badge bg-{{ $task->due_date->isPast() ? 'danger' : 'secondary' }}">
                            {{ $task->due_date->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('tasks.edit', $task) }}">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('tasks.show', $task) }}">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Estás seguro de eliminar esta tarea?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        
        @if($task->checklist && count($task->checklist) > 0)
            <div class="mt-2">
                <div class="progress" style="height: 4px;">
                    @php
                        $completed = collect($task->checklist)->where('completed', true)->count();
                        $total = count($task->checklist);
                        $percentage = $total > 0 ? ($completed / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"></div>
                </div>
                <small class="text-muted">{{ $completed }}/{{ $total }} items completados</small>
            </div>
        @endif
    </div>
</div> 