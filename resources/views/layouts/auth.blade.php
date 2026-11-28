@props([
    'title' => 'Masuk ke REKAGIZI MBG',
])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>REKAGIZI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-bgn.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-ground text-ink font-body antialiased min-h-screen flex items-center justify-center p-lg relative">
    <div class="fixed top-4 right-4 sm:top-6 sm:right-6 z-20">
        <x-theme-toggle />
    </div>
    <main class="w-full max-w-md">
        {{ $slot }}
    </main>
</body>
</html>
