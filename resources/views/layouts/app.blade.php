<!DOCTYPE html>
<html lang="es">
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
<body>
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
                            <li>
                                <a href="{{ route('emails.index') }}" class="flex bg-white hover:bg-gray-100 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-indigo-600 mr-3" viewBox="0 0 16 16">
                                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.758 2.855L15 11.114v-5.73zm-.034 6.878L9.271 8.82 8 9.583 6.728 8.82l-5.694 3.44A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.739zM1 11.114l4.758-2.876L1 5.383v5.73z"/>
                                    </svg>Correos
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenido principal -->
        <main class="ml-64 p-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Notificación de correo enviado -->
        @include('components.email-notification')
    </div>
</body>
</html>
