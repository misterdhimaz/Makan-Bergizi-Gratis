@props([
    'title' => 'Dashboard Asisten Lapangan',
    'breadcrumb' => 'Asisten Lapangan · SPPG Sungai Selayur 1',
    'complaintBadge' => 0,
])
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim((string) ($user?->name ?? 'BS'))) ?: ['BS'])
        ->filter()
        ->take(2)
        ->map(static fn (string $p): string => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $todayLabel = now('Asia/Jakarta')->translatedFormat('l, j M Y');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>REKAGIZI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-bgn.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ground text-ink font-body antialiased">
    <div
        x-data="{ mobileSidebarOpen: false }"
        @keydown.escape.window="mobileSidebarOpen = false"
        class="min-h-screen lg:h-screen flex flex-col lg:flex-row lg:p-lg lg:gap-lg bg-ground lg:overflow-hidden"
    >
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            x-cloak
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-obsidian/50 lg:hidden"
            aria-hidden="true"
        ></div>

        <aside
            id="sidebar"
            :class="{'!flex fixed inset-y-0 left-0 z-50 w-72 rounded-none sm:rounded-r-xl shadow-2xl h-screen overflow-y-auto': mobileSidebarOpen}"
            class="hidden lg:flex lg:flex-col lg:w-64 bg-surface rounded-xl p-lg justify-between shrink-0 border border-border-soft lg:h-full lg:overflow-y-auto print:hidden"
        >
            <div class="flex flex-col gap-lg">
                <div class="flex items-center justify-between px-xs">
                    <a href="{{ route('aslap.dashboard') }}" class="flex items-center gap-sm min-w-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Badan Gizi Nasional" class="w-9 h-9 rounded-full object-contain shrink-0">
                        <span class="font-display text-section-title text-ink truncate">REKAGIZI MBG</span>
                    </a>
                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        aria-label="Tutup menu"
                        class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                    </button>
                </div>

                <div class="w-full flex items-center gap-sm p-md rounded-md bg-surface-subtle border border-border-soft">
                    <div class="w-8 h-8 rounded-sm bg-surface border border-border-soft flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-card-title text-ink" aria-hidden="true">storefront</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">SPPG Sungai Selayur 1</span>
                        <span class="font-body text-micro text-ink-muted">Palembang · 7 titik</span>
                    </div>
                </div>

                <nav aria-label="Menu Asisten Lapangan" class="flex flex-col gap-xs">
                    <a href="{{ route('aslap.dashboard') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('aslap.dashboard'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('aslap.dashboard'),
                        ])>
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">grid_view</span>
                        <span class="font-body text-body-md">Dashboard</span>
                    </a>

                    <a href="{{ route('aslap.pengadaan.index') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('aslap.pengadaan.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('aslap.pengadaan.*'),
                        ])>
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">inventory_2</span>
                        <span class="font-body text-body-md truncate">Pengadaan &amp; Bahan Baku</span>
                    </a>

                    <a href="{{ route('aslap.distribusi.index') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('aslap.distribusi.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('aslap.distribusi.*'),
                        ])>
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">local_shipping</span>
                        <span class="font-body text-body-md truncate">Jadwal Distribusi</span>
                    </a>

                    <a href="{{ route('aslap.keluhan.index') }}"
                        @class([
                            'min-h-[44px] flex items-center justify-between gap-sm px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('aslap.keluhan.*') || request()->routeIs('aslap.laporan.*'),
                            'text-ink hover:bg-surface-subtle' => ! (request()->routeIs('aslap.keluhan.*') || request()->routeIs('aslap.laporan.*')),
                        ])>
                        <span class="flex items-center gap-md min-w-0">
                            <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">support_agent</span>
                            <span class="font-body text-body-md truncate">Keluhan &amp; Laporan</span>
                        </span>
                        @if($complaintBadge > 0)
                            <span class="px-sm py-0.5 rounded-full text-micro font-mono tabular-nums {{ request()->routeIs('aslap.keluhan.*') ? 'bg-surface text-ink' : 'bg-danger-soft text-on-danger-soft' }}">
                                {{ $complaintBadge }}
                            </span>
                        @endif
                    </a>
                </nav>
            </div>

            <div class="pt-md border-t border-border-soft flex flex-col gap-sm">
                <div class="flex items-center gap-sm px-xs">
                    <div class="w-9 h-9 rounded-full bg-obsidian text-on-obsidian flex items-center justify-center font-display text-label-sm shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-label-sm text-ink truncate">{{ $user?->name ?? 'Bima Santoso' }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">Asisten Lapangan</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="min-h-[44px] w-full flex items-center justify-center gap-sm rounded-full bg-surface-subtle hover:bg-surface-muted text-ink font-body text-label-sm cursor-pointer transition-colors">
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">logout</span>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 lg:h-full lg:overflow-hidden pb-20 lg:pb-0">
            <header class="bg-surface lg:rounded-xl border-b lg:border border-border-soft px-lg py-md flex items-center justify-between gap-md shrink-0 print:hidden">
                <div class="flex items-center gap-md min-w-0">
                    <button type="button" @click="mobileSidebarOpen = true" aria-label="Buka menu" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full bg-surface-subtle text-ink cursor-pointer">
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">menu</span>
                    </button>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-micro text-ink-muted truncate">{{ $breadcrumb }}</span>
                        <h1 class="font-display text-section-title lg:text-page-title text-ink truncate">{{ $title }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-sm shrink-0">
                    <span class="hidden sm:inline-block px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink-muted">
                        {{ $todayLabel }}
                    </span>
                    <x-theme-toggle />
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-md lg:p-lg flex flex-col gap-lg">
                @if (session('success'))
                    <div role="status" class="p-md rounded-lg bg-success-soft text-on-success-soft border border-success-soft font-body text-body-md">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div role="alert" class="p-md rounded-lg bg-danger-soft text-on-danger-soft border border-danger-soft font-body text-body-md">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="p-md rounded-lg bg-danger-soft text-on-danger-soft border border-danger-soft font-body text-body-md">
                        <ul class="list-disc pl-lg space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <nav aria-label="Navigasi Cepat Asisten Lapangan" class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-surface border-t border-border-soft grid grid-cols-4 px-xs py-xs print:hidden">
            <a href="{{ route('aslap.dashboard') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('aslap.dashboard') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">grid_view</span>
                <span class="text-[11px]">Dashboard</span>
            </a>
            <a href="{{ route('aslap.pengadaan.index') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('aslap.pengadaan.*') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">inventory_2</span>
                <span class="text-[11px]">Pengadaan</span>
            </a>
            <a href="{{ route('aslap.distribusi.index') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('aslap.distribusi.*') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">local_shipping</span>
                <span class="text-[11px]">Jadwal</span>
            </a>
            <a href="{{ route('aslap.keluhan.index') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('aslap.keluhan.*') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">support_agent</span>
                <span class="text-[11px]">Keluhan</span>
            </a>
        </nav>
    </div>
</body>
</html>
