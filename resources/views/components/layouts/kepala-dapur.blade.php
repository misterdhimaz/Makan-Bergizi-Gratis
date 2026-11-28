@props([
    'title' => 'Dashboard Kepala Dapur',
    'subtitle' => null,
    'breadcrumb' => 'Kepala Dapur · SPPG Sungai Selayur 1',
    'badges' => ['persetujuan_badge' => 0, 'tindak_lanjut_badge' => 0],
])
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim((string) ($user?->name ?? 'HW'))) ?: ['HW'])
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
    @stack('styles')
</head>
<body class="bg-ground text-ink font-body antialiased">
    <div
        x-data="{ mobileSidebarOpen: false }"
        @keydown.escape.window="mobileSidebarOpen = false"
        class="min-h-screen lg:h-screen flex flex-col lg:flex-row lg:p-lg lg:gap-lg bg-ground lg:overflow-hidden"
    >
        {{-- Backdrop Drawer Mobile --}}
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            x-cloak
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-obsidian/50 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Sidebar Desktop & Drawer Mobile --}}
        <aside
            id="sidebar"
            :class="{'!flex fixed inset-y-0 left-0 z-50 w-72 rounded-none sm:rounded-r-xl shadow-2xl h-screen overflow-y-auto': mobileSidebarOpen}"
            class="hidden lg:flex lg:flex-col lg:w-64 bg-surface rounded-xl p-lg justify-between shrink-0 border border-border-soft lg:h-full lg:overflow-y-auto print:hidden"
        >
            <div class="flex flex-col gap-lg">
                <div class="flex items-center justify-between px-xs">
                    <a href="{{ route('kepala-dapur.dashboard') }}" class="flex items-center gap-sm min-w-0">
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

                <nav aria-label="Menu Kepala Dapur" class="flex flex-col gap-xs">
                    <a
                        href="{{ route('kepala-dapur.dashboard') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('kepala-dapur.dashboard'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('kepala-dapur.dashboard'),
                        ])
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">grid_view</span>
                        <span class="font-body text-body-md">Dashboard</span>
                    </a>

                    <a
                        href="{{ route('kepala-dapur.persetujuan.index') }}"
                        @class([
                            'min-h-[44px] flex items-center justify-between gap-sm px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('kepala-dapur.persetujuan.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('kepala-dapur.persetujuan.*'),
                        ])
                    >
                        <span class="flex items-center gap-md min-w-0">
                            <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">verified</span>
                            <span class="font-body text-body-md truncate">Persetujuan &amp; Koreksi</span>
                        </span>
                        @if(($badges['persetujuan_badge'] ?? 0) > 0)
                            <span class="px-sm py-0.5 rounded-full text-micro font-mono tabular-nums {{ request()->routeIs('kepala-dapur.persetujuan.*') ? 'bg-surface text-ink' : 'bg-alert-soft text-on-alert-soft' }}">
                                {{ $badges['persetujuan_badge'] }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('kepala-dapur.tindak-lanjut.index') }}"
                        @class([
                            'min-h-[44px] flex items-center justify-between gap-sm px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('kepala-dapur.tindak-lanjut.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('kepala-dapur.tindak-lanjut.*'),
                        ])
                    >
                        <span class="flex items-center gap-md min-w-0">
                            <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">assignment_late</span>
                            <span class="font-body text-body-md truncate">Tindak Lanjut</span>
                        </span>
                        @if(($badges['tindak_lanjut_badge'] ?? 0) > 0)
                            <span class="px-sm py-0.5 rounded-full text-micro font-mono tabular-nums {{ request()->routeIs('kepala-dapur.tindak-lanjut.*') ? 'bg-surface text-ink' : 'bg-danger-soft text-on-danger-soft' }}">
                                {{ $badges['tindak_lanjut_badge'] }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('kepala-dapur.laporan.index') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian font-semibold' => request()->routeIs('kepala-dapur.laporan.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('kepala-dapur.laporan.*'),
                        ])
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">summarize</span>
                        <span class="font-body text-body-md">Laporan &amp; Ekspor</span>
                    </a>
                </nav>
            </div>

            <div class="pt-md border-t border-border-soft flex flex-col gap-sm">
                <div class="flex items-center gap-sm px-xs">
                    <div class="w-9 h-9 rounded-full bg-obsidian text-on-obsidian flex items-center justify-center font-display text-label-sm shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-label-sm text-ink truncate">{{ $user?->name ?? 'Hendra Wijaya' }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">Kepala Dapur</span>
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

        {{-- Konten Utama --}}
        <div class="flex-1 flex flex-col min-w-0 lg:h-full lg:overflow-hidden pb-20 lg:pb-0">
            {{-- Header --}}
            <header class="bg-surface lg:rounded-xl border-b lg:border border-border-soft px-lg py-md flex items-center justify-between gap-md shrink-0 print:hidden">
                <div class="flex items-center gap-md min-w-0">
                    <button
                        type="button"
                        @click="mobileSidebarOpen = true"
                        aria-label="Buka menu"
                        class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full bg-surface-subtle text-ink cursor-pointer"
                    >
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

        {{-- Bottom Nav Mobile --}}
        <nav aria-label="Navigasi Cepat Kepala Dapur" class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-surface border-t border-border-soft grid grid-cols-4 px-xs py-xs print:hidden">
            <a href="{{ route('kepala-dapur.dashboard') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('kepala-dapur.dashboard') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">grid_view</span>
                <span class="text-[11px]">Dashboard</span>
            </a>
            <a href="{{ route('kepala-dapur.persetujuan.index') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('kepala-dapur.persetujuan.*') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">verified</span>
                <span class="text-[11px]">Persetujuan</span>
            </a>
            <a href="{{ route('kepala-dapur.tindak-lanjut.index') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('kepala-dapur.tindak-lanjut.*') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">assignment_late</span>
                <span class="text-[11px]">Tindak Lanjut</span>
            </a>
            <a href="{{ route('kepala-dapur.laporan.index') }}" class="min-h-[44px] flex flex-col items-center justify-center gap-0.5 rounded-md {{ request()->routeIs('kepala-dapur.laporan.*') ? 'text-ink font-semibold' : 'text-ink-muted' }}">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">summarize</span>
                <span class="text-[11px]">Laporan</span>
            </a>
        </nav>
    </div>
</body>
</html>
