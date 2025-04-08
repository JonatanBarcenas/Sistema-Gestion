<!-- Modal de Detalles de Tarea -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Detalles de Tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <input type="hidden" id="taskId" name="id">
                    <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                    
                    <div class="task-details">
                        <!-- Panel Principal -->
                        <div class="task-main">
                            <!-- Información Básica -->
                            <div class="task-section">
                                <h6 class="task-section-title">Información Básica</h6>
                                
                                <div class="task-field">
                                    <label for="taskTitle">Título</label>
                                    <input type="text" id="taskTitle" name="title" class="form-control" required>
                                </div>

                                <div class="task-field">
                                    <label for="taskDescription">Descripción</label>
                                    <textarea id="taskDescription" name="description" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="task-field">
                                            <label for="taskStatus">Estado</label>
                                            <select id="taskStatus" name="status" class="form-select">
                                                <option value="pending">Pendiente</option>
                                                <option value="in_progress">En Progreso</option>
                                                <option value="completed">Completada</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="task-field">
                                            <label for="taskPriority">Prioridad</label>
                                            <select id="taskPriority" name="priority" class="form-select">
                                                <option value="low">Baja</option>
                                                <option value="medium">Media</option>
                                                <option value="high">Alta</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="task-field">
                                            <label for="taskType">Tipo</label>
                                            <select id="taskType" name="type" class="form-select">
                                                <option value="design">Diseño</option>
                                                <option value="printing">Impresión</option>
                                                <option value="assembly">Ensamblaje</option>
                                                <option value="delivery">Entrega</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="task-field">
                                            <label for="taskDueDate">Fecha de Entrega</label>
                                            <input type="date" id="taskDueDate" name="due_date" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="task-field">
                                    <label for="taskAssignees">Responsables</label>
                                    <select id="taskAssignees" name="assignees[]" class="form-select" multiple>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Checklist -->
                            <div class="task-section">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="task-section-title">Checklist</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addChecklistItem()">
                                        <i class="fas fa-plus"></i> Agregar Item
                                    </button>
                                </div>
                                <div id="taskChecklist" class="task-checklist"></div>
                            </div>

                            <!-- Comentarios -->
                            <div class="task-section">
                                <h6 class="task-section-title">Comentarios</h6>
                                <div id="taskComments" class="task-comments"></div>
                                <div class="task-field mt-3">
                                    <textarea id="commentContent" class="form-control" rows="2" placeholder="Escribe un comentario..."></textarea>
                                    <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addComment()">
                                        <i class="fas fa-paper-plane"></i> Enviar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Lateral -->
                        <div class="task-sidebar">
                            <!-- Horas -->
                            <div class="task-section">
                                <h6 class="task-section-title">Horas</h6>
                                <div class="task-field">
                                    <label for="taskEstimatedHours">Estimadas</label>
                                    <input type="number" id="taskEstimatedHours" name="estimated_hours" class="form-control" min="0" step="0.5">
                                </div>
                                <div class="task-field">
                                    <label for="taskActualHours">Reales</label>
                                    <input type="number" id="taskActualHours" name="actual_hours" class="form-control" min="0" step="0.5">
                                </div>
                            </div>

                            <!-- Materiales -->
                            <div class="task-section">
                                <h6 class="task-section-title">Materiales Necesarios</h6>
                                <div class="task-field">
                                    <textarea id="taskMaterials" name="materials_needed" class="form-control" rows="3" placeholder="Ingresa los materiales en formato JSON"></textarea>
                                </div>
                            </div>

                            <!-- Dependencias -->
                            <div class="task-section">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="task-section-title">Dependencias</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDependencyItem()">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="taskDependencies" class="task-dependencies"></div>
                            </div>

                            <!-- Bloqueos -->
                            <div class="task-section">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="task-section-title">Bloqueada por</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBlockedByItem()">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="taskBlockedBy" class="task-blocked"></div>
                            </div>

                            <!-- Archivos Adjuntos -->
                            <div class="task-section">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="task-section-title">Archivos Adjuntos</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('attachmentInput').click()">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <input type="file" id="attachmentInput" style="display: none" multiple onchange="handleAttachmentUpload(this.files)">
                                <div id="taskAttachments" class="task-attachments"></div>
                            </div>

                            <!-- Notas -->
                            <div class="task-section">
                                <h6 class="task-section-title">Notas</h6>
                                <div class="task-field">
                                    <textarea id="taskNotes" name="notes" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Configuración -->
                            <div class="task-section">
                                <h6 class="task-section-title">Configuración</h6>
                                <div class="task-field">
                                    <label for="taskColor">Color</label>
                                    <input type="color" id="taskColor" name="color" class="form-control form-control-color">
                                </div>
                                <div class="task-field">
                                    <label for="taskPosition">Posición</label>
                                    <input type="number" id="taskPosition" name="position" class="form-control" min="0">
                                </div>
                                <div class="task-field">
                                    <label for="taskProgress">Progreso</label>
                                    <input type="range" id="taskProgress" name="progress" class="form-range" min="0" max="100" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="task-actions">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="deleteTask(document.getElementById('taskId').value)">Eliminar</button>
                        <button type="button" class="btn btn-primary" onclick="saveTaskChanges()">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 