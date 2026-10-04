<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - {{ config('app.name') }}</title>
    @vite(['resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center p-4" style="max-width: 460px;">
        <i class="bi @yield('icon') text-primary" style="font-size: 3.2rem;"></i>
        <h1 class="display-5 fw-semibold mt-2">@yield('code')</h1>
        <p class="text-muted">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali</a>
    </div>
</body>
</html>
