<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm border-0" style="max-width: 440px; width: 100%;">
        <div class="card-body p-4 text-center">
            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
            <h1 class="h4 mt-2">{{ config('app.name') }}</h1>
            <p class="text-muted mb-3">Instalasi dan konfigurasi berhasil.</p>
            <span class="badge text-bg-success">Laravel {{ app()->version() }}</span>
            <span class="badge text-bg-primary">PHP {{ PHP_VERSION }}</span>
            <p class="small text-muted mt-3 mb-0">
                {{ now()->translatedFormat('d M Y, H:i:s') }} ({{ config('app.timezone') }})
            </p>
        </div>
    </div>
</body>
</html>
