@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Notificaciones</h2>
                @if($notifications->isNotEmpty())
                    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                            Marcar todas como leídas
                        </button>
                    </form>
                @endif
            </div>

            @if($notifications->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500">No tienes notificaciones.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($notifications as $notification)
                        <div class="flex items-start p-4 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }} hover:bg-gray-50 transition duration-150 ease-in-out rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $notification->data['message'] ?? 'Notificación del sistema' }}
                                    </p>
                                    <span class="text-xs text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                @if(isset($notification->data['description']))
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $notification->data['description'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="ml-4 flex-shrink-0 flex space-x-2">
                                @if(!$notification->read_at)
                                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                                            Marcar como leída
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 