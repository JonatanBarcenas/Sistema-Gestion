<div class="relative inline-block">
    <button class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <div class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50" 
         id="notificationMenu"
         aria-labelledby="notificationDropdown">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-700">Notificaciones</h3>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                            Marcar todas como leídas
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-200 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full {{ $notification->data['type'] === 'task' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} text-lg"></i>
                            </span>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['title'] }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $notification->data['message'] }}
                            </p>
                            <div class="mt-2 flex space-x-3">
                                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                                        Marcar como leída
                                    </button>
                                </form>
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="text-sm text-gray-700 hover:text-gray-900">
                                        Ver detalles
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="text-xs text-gray-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="mt-2 text-sm">No hay notificaciones nuevas</p>
                </div>
            @endforelse

            @if(auth()->user()->notifications->count() > 5)
                <a href="{{ route('notifications.index') }}" class="block px-4 py-3 text-center text-sm text-blue-600 hover:text-blue-800 hover:bg-gray-50">
                    Ver todas las notificaciones
                </a>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.getElementById('notificationDropdown');
    const dropdownMenu = document.getElementById('notificationMenu');

    dropdownButton.addEventListener('click', function(e) {
        e.preventDefault();
        dropdownMenu.classList.toggle('hidden');
    });

    // Cerrar el menú cuando se hace clic fuera de él
    document.addEventListener('click', function(e) {
        if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});
</script> 