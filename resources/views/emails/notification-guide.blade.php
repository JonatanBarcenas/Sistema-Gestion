@extends('layouts.app')

@section('title', 'Guía de Notificaciones')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Guía de Notificaciones y Correos Electrónicos</h1>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Activación de Notificaciones</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="mb-3">Para activar las notificaciones en el sistema, siga estos pasos:</p>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Configure las variables de entorno en el archivo <code class="bg-gray-200 px-2 py-1 rounded">.env</code>:</li>
                    <div class="bg-gray-800 text-white p-4 rounded-md my-2 overflow-x-auto">
                        <pre>MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=su_correo@gmail.com
MAIL_PASSWORD=su_contraseña_de_aplicación
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=su_correo@gmail.com
MAIL_FROM_NAME="${APP_NAME}"</pre>
                    </div>
                    <li>Si usa Gmail, debe crear una contraseña de aplicación en la configuración de seguridad de su cuenta de Google.</li>
                    <li>Asegúrese de que el servicio de colas esté configurado correctamente:</li>
                    <div class="bg-gray-800 text-white p-4 rounded-md my-2 overflow-x-auto">
                        <pre>QUEUE_CONNECTION=database</pre>
                    </div>
                    <li>Ejecute las migraciones para crear las tablas necesarias:</li>
                    <div class="bg-gray-800 text-white p-4 rounded-md my-2 overflow-x-auto">
                        <pre>php artisan migrate</pre>
                    </div>
                    <li>Para procesar las notificaciones en segundo plano, ejecute el worker de colas:</li>
                    <div class="bg-gray-800 text-white p-4 rounded-md my-2 overflow-x-auto">
                        <pre>php artisan queue:work</pre>
                    </div>
                </ol>
            </div>
        </div>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Verificación de Envío de Correos</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="mb-3">Para verificar que los correos se envían correctamente:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Revise el <a href="{{ route('emails.index') }}" class="text-blue-600 hover:underline">registro de correos enviados</a> donde podrá ver el estado de cada correo.</li>
                    <li>Los correos con estado "Enviado" indican que fueron procesados correctamente por el servidor de correo.</li>
                    <li>Para una verificación más completa, puede configurar servicios como Mailtrap para entornos de desarrollo.</li>
                    <li>En producción, considere usar servicios como Mailgun o SendGrid que ofrecen estadísticas detalladas de entrega.</li>
                </ul>
            </div>
        </div>
        
        <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Solución de Problemas</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="mb-3">Si experimenta problemas con el envío de correos:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Verifique los registros de errores en <code class="bg-gray-200 px-2 py-1 rounded">storage/logs/laravel.log</code></li>
                    <li>Asegúrese de que las credenciales SMTP sean correctas</li>
                    <li>Compruebe que el firewall no esté bloqueando las conexiones SMTP</li>
                    <li>Pruebe enviar un correo de prueba con el comando:</li>
                    <div class="bg-gray-800 text-white p-4 rounded-md my-2 overflow-x-auto">
                        <pre>php artisan mail:send test@example.com</pre>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection