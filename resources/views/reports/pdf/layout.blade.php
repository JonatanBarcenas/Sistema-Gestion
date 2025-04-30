<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Reporte' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .container { margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f3f4f6; }
        h1 { color: #374151; margin-bottom: 20px; }
        .period { margin-bottom: 20px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>