@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Detalles del Correo</h2>
                <a href="{{ route('emails.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition duration-200">
                    Volver al listado
                </a>
            </div>

            <div class="bg-gray-50 p-6 rounded-lg mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Destinatario</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $emailLog->recipient }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Fecha de Envío</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $emailLog->sent_at ? $emailLog->sent_at->format('d/m/Y H:i:s') : 'N/A' }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500">Asunto</h3>
                    <p class="mt-1 text-lg text-gray-900">{{ $emailLog->subject }}</p>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500">Estado</h3>
                    <span class="mt-1 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $emailLog->status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $emailLog->status === 'sent' ? 'Enviado' : 'Error' }}
                    </span>
                </div>

                @if($emailLog->user)
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500">Usuario Relacionado</h3>
                    <p class="mt-1 text-lg text-gray-900">{{ $emailLog->user->name }}</p>
                </div>
                @endif

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Contenido del Correo</h3>
                    <div class="mt-1 p-4 bg-white border border-gray-200 rounded-lg">
                        <div class="prose max-w-none">
                            {!! $emailLog->content !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('emails.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition duration-200">
                    Volver al listado
                </a>
                <form action="{{ route('emails.destroy', $emailLog) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition duration-200" onclick="return confirm('¿Estás seguro de eliminar este registro?')">
                        Eliminar registro
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection