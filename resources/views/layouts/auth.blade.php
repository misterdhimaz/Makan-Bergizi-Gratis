@props([
    'title' => 'Masuk ke REKAGIZI MBG',
])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — REKAGIZI MBG</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-ground text-ink font-body antialiased min-h-screen flex items-center justify-center p-lg">
    <main class="w-full max-w-md">
        {{ $slot }}
    </main>
</body>
</html>
