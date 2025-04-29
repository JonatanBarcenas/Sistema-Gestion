<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        .project-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $subject }}</h2>
        </div>
        
        <div class="content">
            <p>Hola {{ $notifiable->name }},</p>
            
            <p>{{ $message }}</p>
            
            @if(isset($description))
            <div class="project-details">
                {!! nl2br(e($description)) !!}
            </div>
            @endif
            
            @if(isset($action_url))
            <div style="text-align: center;">
                <a href="{{ $action_url }}" class="button">{{ $action_text ?? 'Ver Detalles' }}</a>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>Este es un correo automático, por favor no responder.</p>
            <p>Sistema de Gestión de Proyectos</p>
        </div>
    </div>
</body>
</html>