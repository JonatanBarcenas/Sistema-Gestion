<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Gestión') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 bg-white shadow-lg max-h-screen w-64">
            <div class="flex flex-col justify-between h-full">
                <div class="flex-grow">
                    <div class="px-4 py-6 text-center border-b">
                        <h1 class="text-xl font-bold leading-none"><span class="text-gray-700">Sistema de</span> <span class="text-indigo-600">Gestión</span></h1>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('dashboard') }}" class="flex items-center bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zm-3.5-7h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z"/>
                                    </svg>Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customers.index') }}" class="flex bg-white hover:bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M7 14s-1 0-1-1 1-4 4-4 4 3 4 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 4 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                    </svg>Clientes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('orders.index') }}" class="flex bg-white hover:bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M11.5 2a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5h7zm-7-1A1.5 1.5 0 0 0 3 2.5v7A1.5 1.5 0 0 0 4.5 11h7a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 11.5 1h-7z"/>
                                        <path d="M2 3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 3zm0 2a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 2 5zm0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 2 7z"/>
                                    </svg>Pedidos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tasks.index') }}" class="flex bg-white hover:bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H4z"/>
                                        <path d="M9.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 9 9V3.5a.5.5 0 0 1 .5-.5z"/>
                                    </svg>Tareas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('products.index') }}" class="flex bg-white hover:bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6V3a3 3 0 1 0-6 0v5z"/>
                                    </svg>Productos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.index') }}" class="flex bg-white hover:bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                        <path d="M7.5 3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                    </svg>Reportes
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="ml-64">
            <!-- Top navigation -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center">
                        <h1 class="text-3xl font-bold text-gray-900">
                            @yield('title', 'Dashboard')
                        </h1>
                        <div class="flex items-center">
                            <div class="ml-3 relative">
                                @auth
                                    <!-- Notificaciones -->
                                    @include('layouts.partials.notification-dropdown')
                                    <!-- Usuario y Cerrar sesión -->
                                    <div class="flex items-center">
                                        <span class="text-gray-700 mr-4">{{ Auth::user()->name }}</span>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="text-gray-600 hover:text-gray-900">
                                                Cerrar sesión
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-gray-600">No autenticado</span>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
    
    <!-- Session Timeout Modal -->
    <div id="sessionTimeoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 100;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Sesión a punto de expirar</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Tu sesión expirará en <span id="countdown">60</span> segundos.
                        ¿Deseas mantener la sesión activa?
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="extendSession" class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Mantener sesión
                    </button>
                    <button id="logoutSession" class="mt-2 px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timeout;
            let warningTimeout;
            const warningTime = 60; // 60 segundos antes de expirar
            const sessionLifetime = {{ config('session.lifetime') * 60 }}; // Tiempo total de la sesión en segundos

            function resetTimeout() {
                clearTimeout(timeout);
                clearTimeout(warningTimeout);
                
                // Establecer el timeout principal
                timeout = setTimeout(function() {
                    window.location.href = "{{ route('logout') }}";
                }, sessionLifetime * 1000);

                // Establecer el timeout de advertencia
                warningTimeout = setTimeout(function() {
                    showWarning();
                }, (sessionLifetime - warningTime) * 1000);
            }

            function showWarning() {
                const modal = document.getElementById('sessionTimeoutModal');
                const countdown = document.getElementById('countdown');
                let seconds = warningTime;

                modal.classList.remove('hidden');

                const countdownInterval = setInterval(function() {
                    seconds--;
                    countdown.textContent = seconds;

                    if (seconds <= 0) {
                        clearInterval(countdownInterval);
                        modal.classList.add('hidden');
                        window.location.href = "{{ route('logout') }}";
                    }
                }, 1000);
            }

            // Eventos para mantener la sesión activa
            document.addEventListener('mousemove', resetTimeout);
            document.addEventListener('keypress', resetTimeout);
            document.addEventListener('click', resetTimeout);

            // Botones del modal
            document.getElementById('extendSession').addEventListener('click', function() {
                fetch("{{ route('session.extend') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(function() {
                    document.getElementById('sessionTimeoutModal').classList.add('hidden');
                    resetTimeout();
                });
            });

            document.getElementById('logoutSession').addEventListener('click', function() {
                window.location.href = "{{ route('logout') }}";
            });

            // Iniciar el timeout
            resetTimeout();
        });
    </script>
</body>
</html>
