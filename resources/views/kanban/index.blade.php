@extends('layouts.app')

@section('title', 'Tablero Kanban')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Tablero Kanban</h2>
            <p class="text-muted">Gestiona las tareas de impresión y publicidad</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                    <i class="fas fa-plus"></i> Nueva Tarea
                </button>
                <button type="button" class="btn btn-outline-secondary" id="refreshTasks">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="kanban-board">
                <div class="kanban-column" data-status="pending">
                    <div class="kanban-column-header">
                        <h5>Pendiente</h5>
                        <span class="badge bg-secondary task-count">0</span>
                    </div>
                    <div class="kanban-column-content" id="pending-tasks"></div>
                </div>

                <div class="kanban-column" data-status="in_progress">
                    <div class="kanban-column-header">
                        <h5>En Progreso</h5>
                        <span class="badge bg-primary task-count">0</span>
                    </div>
                    <div class="kanban-column-content" id="in-progress-tasks"></div>
                </div>

                <div class="kanban-column" data-status="completed">
                    <div class="kanban-column-header">
                        <h5>Completada</h5>
                        <span class="badge bg-success task-count">0</span>
                    </div>
                    <div class="kanban-column-content" id="completed-tasks"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles de Tarea -->
@include('kanban.partials.task-modal')

<style>
.kanban-board {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    min-height: calc(100vh - 200px);
    background: #f8f9fa;
    border-radius: 8px;
}

.kanban-column {
    flex: 1;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.kanban-column-header {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.kanban-column-content {
    padding: 1rem;
    flex: 1;
    overflow-y: auto;
}

.task-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.task-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.task-card.blocked {
    border-left: 4px solid #dc3545;
}

.task-card.overdue {
    border-left: 4px solid #ffc107;
}

.task-card .task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.task-card .task-title {
    font-weight: 600;
    margin: 0;
}

.task-card .task-priority {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

.task-card .task-priority.high {
    background: #dc3545;
    color: white;
}

.task-card .task-priority.medium {
    background: #ffc107;
    color: black;
}

.task-card .task-priority.low {
    background: #28a745;
    color: white;
}

.task-card .task-meta {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.task-card .task-assignees {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.task-card .assignee-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    color: #495057;
}

.task-card .task-progress {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    margin-bottom: 0.5rem;
}

.task-card .task-progress-bar {
    height: 100%;
    background: #007bff;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.task-card .task-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #6c757d;
}

.task-card .task-due-date {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.task-card .task-due-date.overdue {
    color: #dc3545;
}

.task-card .task-hours {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.task-card .task-hours i {
    color: #28a745;
}

.task-card .task-checklist {
    margin-top: 0.5rem;
    font-size: 0.8rem;
}

.task-card .checklist-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.3rem;
}

.task-card .checklist-item.completed {
    color: #28a745;
    text-decoration: line-through;
}

.task-card .task-dependencies {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #6c757d;
}

.task-card .dependency-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.task-card .dependency-item.completed {
    color: #28a745;
}

.task-card .dependency-item.blocked {
    color: #dc3545;
}

.task-card .task-blocked {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #dc3545;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.task-card .task-blocked i {
    color: #dc3545;
}

.task-card .task-actions {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    display: none;
}

.task-card:hover .task-actions {
    display: flex;
    gap: 0.5rem;
}

.task-card .task-actions button {
    padding: 0.2rem 0.4rem;
    font-size: 0.8rem;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    transition: color 0.2s ease;
}

.task-card .task-actions button:hover {
    color: #007bff;
}

.task-card .task-actions button.delete:hover {
    color: #dc3545;
}

/* Estilos para el modal */
.modal-dialog {
    max-width: 800px;
}

.modal-body {
    padding: 1.5rem;
}

.task-details {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.task-main {
    border-right: 1px solid #e9ecef;
    padding-right: 2rem;
}

.task-sidebar {
    padding-left: 1rem;
}

.task-section {
    margin-bottom: 1.5rem;
}

.task-section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #495057;
}

.task-field {
    margin-bottom: 1rem;
}

.task-field label {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.task-field input,
.task-field select,
.task-field textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.875rem;
}

.task-field textarea {
    min-height: 100px;
    resize: vertical;
}

.task-checklist {
    margin-top: 1rem;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.checklist-item input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
}

.checklist-item input[type="text"] {
    flex: 1;
}

.task-comments {
    margin-top: 2rem;
}

.comment {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.comment-user {
    font-weight: 600;
    color: #495057;
}

.comment-date {
    font-size: 0.875rem;
    color: #6c757d;
}

.comment-content {
    color: #212529;
}

.comment-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.comment-actions button {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
}

.comment-actions button:hover {
    color: #007bff;
}

.comment-actions button.delete:hover {
    color: #dc3545;
}

.task-attachments {
    margin-top: 1rem;
}

.attachment-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.attachment-item i {
    color: #6c757d;
}

.attachment-item .attachment-name {
    flex: 1;
    font-size: 0.875rem;
}

.attachment-item .attachment-actions {
    display: flex;
    gap: 0.5rem;
}

.attachment-item .attachment-actions button {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
}

.attachment-item .attachment-actions button:hover {
    color: #007bff;
}

.attachment-item .attachment-actions button.delete:hover {
    color: #dc3545;
}

.task-dependencies {
    margin-top: 1rem;
}

.dependency-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.dependency-item.completed {
    background: #e8f5e9;
    color: #2e7d32;
}

.dependency-item.blocked {
    background: #ffebee;
    color: #c62828;
}

.dependency-item .dependency-status {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.dependency-item .dependency-status.completed {
    background: #2e7d32;
}

.dependency-item .dependency-status.blocked {
    background: #c62828;
}

.dependency-item .dependency-status.pending {
    background: #ffa000;
}

.task-blocked {
    margin-top: 1rem;
}

.blocked-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #ffebee;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    color: #c62828;
}

.blocked-item i {
    color: #c62828;
}

.task-hours {
    margin-top: 1rem;
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.hours-item .hours-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.hours-item .hours-value {
    font-weight: 600;
    color: #495057;
}

.hours-item .hours-value.over {
    color: #dc3545;
}

.hours-item .hours-value.under {
    color: #28a745;
}

.task-materials {
    margin-top: 1rem;
}

.material-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.material-item .material-name {
    flex: 1;
    font-size: 0.875rem;
}

.material-item .material-quantity {
    font-weight: 600;
    color: #495057;
}

.task-notes {
    margin-top: 1rem;
}

.note-item {
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #495057;
}

.task-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.task-actions button {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.task-actions button.save {
    background: #007bff;
    color: white;
    border: none;
}

.task-actions button.save:hover {
    background: #0056b3;
}

.task-actions button.cancel {
    background: none;
    color: #6c757d;
    border: 1px solid #ced4da;
}

.task-actions button.cancel:hover {
    background: #f8f9fa;
}

.task-actions button.delete {
    background: #dc3545;
    color: white;
    border: none;
}

.task-actions button.delete:hover {
    background: #c82333;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Sortable para cada columna
    document.querySelectorAll('.kanban-column-content').forEach(column => {
        new Sortable(column, {
            group: 'tasks',
            animation: 150,
            onEnd: function(evt) {
                const taskId = evt.item.dataset.taskId;
                const newStatus = evt.to.closest('.kanban-column').dataset.status;
                const newPosition = Array.from(evt.to.children).indexOf(evt.item);

                updateTaskStatus(taskId, newStatus, newPosition);
            }
        });
    });

    // Cargar tareas inicialmente
    loadTasks();

    // Actualizar tareas cada 5 minutos
    setInterval(loadTasks, 300000);

    // Manejar clic en el botón de actualizar
    document.getElementById('refreshTasks').addEventListener('click', loadTasks);

    // Manejar clic en las tarjetas de tarea
    document.addEventListener('click', function(e) {
        const taskCard = e.target.closest('.task-card');
        if (taskCard) {
            const taskId = taskCard.dataset.taskId;
            showTaskDetails(taskId);
        }
    });
});

function loadTasks() {
    fetch('{{ route("kanban.projects.tasks", $selectedProject) }}')
        .then(response => response.json())
        .then(data => {
            // Limpiar columnas
            document.querySelectorAll('.kanban-column-content').forEach(column => {
                column.innerHTML = '';
            });

            // Actualizar contadores
            Object.entries(data).forEach(([status, tasks]) => {
                const countBadge = document.querySelector(`.kanban-column[data-status="${status}"] .task-count`);
                if (countBadge) {
                    countBadge.textContent = tasks.length;
                }
            });

            // Agregar tareas a las columnas
            Object.entries(data).forEach(([status, tasks]) => {
                const column = document.querySelector(`.kanban-column[data-status="${status}"] .kanban-column-content`);
                if (column) {
                    tasks.forEach(task => {
                        column.appendChild(createTaskCard(task));
                    });
                }
            });
        })
        .catch(error => {
            console.error('Error al cargar tareas:', error);
            showError('Error al cargar las tareas');
        });
}

function createTaskCard(task) {
    const card = document.createElement('div');
    card.className = 'task-card';
    card.dataset.taskId = task.id;

    // Agregar clases para estados especiales
    if (task.is_blocked) {
        card.classList.add('blocked');
    }
    if (task.is_overdue) {
        card.classList.add('overdue');
    }

    // Crear contenido de la tarjeta
    card.innerHTML = `
        <div class="task-header">
            <h6 class="task-title">${task.title}</h6>
            <span class="task-priority ${task.priority}">${task.priority}</span>
        </div>
        <div class="task-meta">
            <div class="task-type">${task.type}</div>
            <div class="task-assignees">
                ${task.assignees.map(assignee => `
                    <div class="assignee-avatar" title="${assignee.name}">
                        ${assignee.name.charAt(0)}
                    </div>
                `).join('')}
            </div>
        </div>
        <div class="task-progress">
            <div class="task-progress-bar" style="width: ${task.progress}%"></div>
        </div>
        <div class="task-footer">
            <div class="task-due-date ${task.is_overdue ? 'overdue' : ''}">
                <i class="fas fa-calendar"></i>
                ${formatDate(task.due_date)}
            </div>
            <div class="task-hours">
                <i class="fas fa-clock"></i>
                ${task.actual_hours}/${task.estimated_hours}h
            </div>
        </div>
        ${task.checklist ? `
            <div class="task-checklist">
                ${task.checklist.map(item => `
                    <div class="checklist-item ${item.completed ? 'completed' : ''}">
                        <i class="fas ${item.completed ? 'fa-check-circle' : 'fa-circle'}"></i>
                        ${item.text}
                    </div>
                `).join('')}
            </div>
        ` : ''}
        ${task.dependencies && task.dependencies.length > 0 ? `
            <div class="task-dependencies">
                ${task.dependencies.map(dep => `
                    <div class="dependency-item ${dep.completed ? 'completed' : ''} ${dep.blocked ? 'blocked' : ''}">
                        <i class="fas fa-link"></i>
                        ${dep.title}
                    </div>
                `).join('')}
            </div>
        ` : ''}
        ${task.is_blocked ? `
            <div class="task-blocked">
                <i class="fas fa-ban"></i>
                Bloqueada por otras tareas
            </div>
        ` : ''}
        <div class="task-actions">
            <button class="edit" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="delete" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;

    return card;
}

function showTaskDetails(taskId) {
    fetch(`{{ route("kanban.tasks.details", "") }}/${taskId}`)
        .then(response => response.json())
        .then(task => {
            // Actualizar el modal con los detalles de la tarea
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskDescription').value = task.description;
            document.getElementById('taskStatus').value = task.status;
            document.getElementById('taskPriority').value = task.priority;
            document.getElementById('taskDueDate').value = formatDateForInput(task.due_date);
            document.getElementById('taskType').value = task.type;
            document.getElementById('taskEstimatedHours').value = task.estimated_hours;
            document.getElementById('taskActualHours').value = task.actual_hours;
            document.getElementById('taskMaterials').value = JSON.stringify(task.materials_needed);
            document.getElementById('taskNotes').value = task.notes;
            document.getElementById('taskColor').value = task.color;
            document.getElementById('taskPosition').value = task.position;

            // Actualizar asignados
            const assigneesSelect = document.getElementById('taskAssignees');
            assigneesSelect.value = task.assignees.map(a => a.id);

            // Actualizar checklist
            const checklistContainer = document.getElementById('taskChecklist');
            checklistContainer.innerHTML = '';
            if (task.checklist) {
                task.checklist.forEach(item => {
                    addChecklistItem(item.text, item.completed);
                });
            }

            // Actualizar dependencias
            const dependenciesContainer = document.getElementById('taskDependencies');
            dependenciesContainer.innerHTML = '';
            if (task.dependencies) {
                task.dependencies.forEach(depId => {
                    addDependencyItem(depId);
                });
            }

            // Actualizar bloqueos
            const blockedByContainer = document.getElementById('taskBlockedBy');
            blockedByContainer.innerHTML = '';
            if (task.blocked_by) {
                task.blocked_by.forEach(taskId => {
                    addBlockedByItem(taskId);
                });
            }

            // Actualizar comentarios
            const commentsContainer = document.getElementById('taskComments');
            commentsContainer.innerHTML = '';
            if (task.comments) {
                task.comments.forEach(comment => {
                    addCommentToTask(comment);
                });
            }

            // Actualizar archivos adjuntos
            const attachmentsContainer = document.getElementById('taskAttachments');
            attachmentsContainer.innerHTML = '';
            if (task.attachments) {
                task.attachments.forEach(attachment => {
                    addAttachmentToTask(attachment);
                });
            }

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al cargar detalles de la tarea:', error);
            showError('Error al cargar los detalles de la tarea');
        });
}

function updateTaskStatus(taskId, newStatus, newPosition) {
    fetch(`{{ route("kanban.tasks.status", "") }}/${taskId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            status: newStatus,
            position: newPosition
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTasks();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error al actualizar estado:', error);
        showError('Error al actualizar el estado de la tarea');
    });
}

function saveTaskChanges() {
    const formData = new FormData(document.getElementById('taskForm'));
    const taskId = document.getElementById('taskId').value;

    const data = {
        title: formData.get('title'),
        description: formData.get('description'),
        status: formData.get('status'),
        priority: formData.get('priority'),
        due_date: formData.get('due_date'),
        project_id: formData.get('project_id'),
        order_id: formData.get('order_id'),
        type: formData.get('type'),
        estimated_hours: formData.get('estimated_hours'),
        actual_hours: formData.get('actual_hours'),
        materials_needed: JSON.parse(formData.get('materials_needed')),
        notes: formData.get('notes'),
        attachments: [], // Se manejarán por separado
        checklist: getChecklistItems(),
        color: formData.get('color'),
        position: formData.get('position'),
        assignees: Array.from(document.getElementById('taskAssignees').selectedOptions).map(option => option.value),
        dependencies: getDependencyItems(),
        blocked_by: getBlockedByItems(),
        progress: formData.get('progress')
    };

    const url = taskId ? 
        `{{ route("kanban.tasks.update", "") }}/${taskId}` : 
        '{{ route("kanban.tasks.store") }}';
    
    const method = taskId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
            loadTasks();
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error al guardar tarea:', error);
        showError('Error al guardar la tarea');
    });
}

function deleteTask(taskId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        fetch(`{{ route("kanban.tasks.destroy", "") }}/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
                loadTasks();
                showSuccess(data.message);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error al eliminar tarea:', error);
            showError('Error al eliminar la tarea');
        });
    }
}

function addComment() {
    const content = document.getElementById('commentContent').value;
    if (!content.trim()) return;

    const taskId = document.getElementById('taskId').value;
    const formData = new FormData();
    formData.append('content', content);

    fetch(`{{ route("kanban.tasks.comments.store", "") }}/${taskId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addCommentToTask(data.comment);
            document.getElementById('commentContent').value = '';
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error al agregar comentario:', error);
        showError('Error al agregar el comentario');
    });
}

function deleteComment(taskId, commentId) {
    if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
        fetch(`{{ route("kanban.tasks.comments.destroy", "") }}/${taskId}/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-comment-id="${commentId}"]`).remove();
                showSuccess(data.message);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error al eliminar comentario:', error);
            showError('Error al eliminar el comentario');
        });
    }
}

function addChecklistItem(text = '', completed = false) {
    const container = document.getElementById('taskChecklist');
    const item = document.createElement('div');
    item.className = 'checklist-item';
    item.innerHTML = `
        <input type="checkbox" ${completed ? 'checked' : ''}>
        <input type="text" value="${text}" placeholder="Nuevo ítem">
        <button type="button" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(item);
}

function getChecklistItems() {
    const items = [];
    document.querySelectorAll('#taskChecklist .checklist-item').forEach(item => {
        items.push({
            text: item.querySelector('input[type="text"]').value,
            completed: item.querySelector('input[type="checkbox"]').checked
        });
    });
    return items;
}

function addDependencyItem(taskId = '') {
    const container = document.getElementById('taskDependencies');
    const item = document.createElement('div');
    item.className = 'dependency-item';
    item.innerHTML = `
        <select>
            <option value="">Seleccionar tarea</option>
            ${getAvailableTasks().map(task => `
                <option value="${task.id}" ${task.id === taskId ? 'selected' : ''}>
                    ${task.title}
                </option>
            `).join('')}
        </select>
        <button type="button" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(item);
}

function getDependencyItems() {
    const items = [];
    document.querySelectorAll('#taskDependencies .dependency-item select').forEach(select => {
        if (select.value) {
            items.push(select.value);
        }
    });
    return items;
}

function addBlockedByItem(taskId = '') {
    const container = document.getElementById('taskBlockedBy');
    const item = document.createElement('div');
    item.className = 'blocked-by-item';
    item.innerHTML = `
        <select>
            <option value="">Seleccionar tarea</option>
            ${getAvailableTasks().map(task => `
                <option value="${task.id}" ${task.id === taskId ? 'selected' : ''}>
                    ${task.title}
                </option>
            `).join('')}
        </select>
        <button type="button" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(item);
}

function getBlockedByItems() {
    const items = [];
    document.querySelectorAll('#taskBlockedBy .blocked-by-item select').forEach(select => {
        if (select.value) {
            items.push(select.value);
        }
    });
    return items;
}

function addAttachmentToTask(attachment) {
    const container = document.getElementById('taskAttachments');
    const item = document.createElement('div');
    item.className = 'attachment-item';
    item.innerHTML = `
        <i class="fas fa-paperclip"></i>
        <span class="attachment-name">${attachment.name}</span>
        <div class="attachment-actions">
            <button type="button" onclick="downloadAttachment('${attachment.id}')">
                <i class="fas fa-download"></i>
            </button>
            <button type="button" onclick="deleteAttachment('${attachment.id}')">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(item);
}

function getAvailableTasks() {
    const tasks = [];
    document.querySelectorAll('.task-card').forEach(card => {
        tasks.push({
            id: card.dataset.taskId,
            title: card.querySelector('.task-title').textContent
        });
    });
    return tasks;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateForInput(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

function showSuccess(message) {
    // Implementar notificación de éxito
    alert(message);
}

function showError(message) {
    // Implementar notificación de error
    alert(message);
}
</script>
@endpush
@endsection 