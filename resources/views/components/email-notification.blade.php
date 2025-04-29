@if(session('email_sent'))
<div id="emailNotification" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 max-w-sm border-l-4 border-green-500 animate-slide-up">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="ml-3 w-0 flex-1">
            <p class="text-sm font-medium text-gray-900">Correo enviado exitosamente</p>
            <div class="mt-1 text-sm text-gray-500">
                <p><strong>Para:</strong> {{ session('email_sent')['recipient'] }}</p>
                <p><strong>Asunto:</strong> {{ session('email_sent')['subject'] }}</p>
                <p class="mt-1">{{ session('email_sent')['message'] }}</p>
            </div>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
            <button onclick="document.getElementById('emailNotification').remove()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500">
                <span class="sr-only">Cerrar</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes slide-up {
        0% {
            transform: translateY(100%);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
</style>

<script>
    setTimeout(() => {
        const notification = document.getElementById('emailNotification');
        if (notification) {
            notification.style.transition = 'all 0.3s ease-out';
            notification.style.transform = 'translateY(100%)';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
</script>
@endif