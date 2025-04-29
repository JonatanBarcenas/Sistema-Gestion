@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Preferencias de Notificación para {{ $customer->name }}</span>
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">Volver</a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('notification.preferences.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <h5>Tipos de Notificaciones</h5>
                            <p class="text-muted">Selecciona los tipos de notificaciones que deseas recibir para este cliente.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="project_created" id="project_created" value="1" {{ $preferences->project_created ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_created">
                                    Creación de nuevos proyectos
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="project_updated" id="project_updated" value="1" {{ $preferences->project_updated ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_updated">
                                    Actualizaciones generales de proyectos
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="project_status_changed" id="project_status_changed" value="1" {{ $preferences->project_status_changed ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_status_changed">
                                    Cambios de estado en proyectos
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="project_comment_added" id="project_comment_added" value="1" {{ $preferences->project_comment_added ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_comment_added">
                                    Comentarios importantes en proyectos
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="project_completed" id="project_completed" value="1" {{ $preferences->project_completed ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_completed">
                                    Finalización de proyectos
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Canales de Notificación</h5>
                            <p class="text-muted">Selecciona cómo deseas recibir las notificaciones.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" value="1" {{ $preferences->email_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_notifications">
                                    Correo electrónico ({{ $customer->email }})
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="database_notifications" id="database_notifications" value="1" {{ $preferences->database_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="database_notifications">
                                    Notificaciones en la plataforma
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Guardar Preferencias
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection