@props([
    'title' => 'Dashboard Akuntan',
    'subtitle' => null,
    'breadcrumb' => 'Akuntan · SPPG Sungai Selayur 1',
])
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim((string) ($user?->name ?? 'RP'))) ?: ['RP'])
        ->filter()
        ->take(2)
        ->map(static fn (string $p): string => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $todayLabel = now()->timezone('Asia/Jakarta')->translatedFormat('l, j M Y');
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
        {{-- Backdrop Drawer Mobile --}}
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            x-cloak
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-obsidian/50 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Floating White Bento Sidebar (Desktop & Mobile Drawer) --}}
        <aside
            id="sidebar"
            :class="{'!flex fixed inset-y-0 left-0 z-50 w-72 rounded-none sm:rounded-r-xl shadow-2xl h-screen overflow-y-auto': mobileSidebarOpen}"
            class="hidden lg:flex lg:flex-col lg:w-64 bg-surface rounded-xl p-lg justify-between shrink-0 border border-border-soft lg:h-full lg:overflow-y-auto"
        >
            <div class="flex flex-col gap-lg">
                <div class="flex items-center justify-between px-xs">
                    <a href="{{ route('akuntan.dashboard') }}" class="flex items-center gap-sm min-w-0">
                        <img src="{{ asset('images/logo-bgn.png') }}" alt="Logo Badan Gizi Nasional" class="w-9 h-9 rounded-full object-contain shrink-0">
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

                {{-- Fixed Single-SPPG Location Label --}}
                <div class="w-full flex items-center gap-sm p-md rounded-md bg-surface-subtle border border-border-soft">
                    <div class="w-8 h-8 rounded-sm bg-surface border border-border-soft flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-card-title text-ink" aria-hidden="true">storefront</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">SPPG Sungai Selayur 1</span>
                        <span class="font-body text-micro text-ink-muted">Palembang · 1.414 porsi</span>
                    </div>
                </div>

                {{-- Primary Navigation --}}
                <nav aria-label="Menu Akuntan" class="flex flex-col gap-xs">
                    <a
                        href="{{ route('akuntan.dashboard') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.dashboard'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('akuntan.dashboard'),
                        ])
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">grid_view</span>
                        <span class="font-body text-body-md">Dashboard</span>
                    </a>

                    <a
                        href="{{ route('akuntan.validasi.index') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.validasi.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('akuntan.validasi.*'),
                        ])
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">fact_check</span>
                        <span class="font-body text-body-md">Validasi Anggaran Menu</span>
                    </a>

                    <a
                        href="{{ route('akuntan.parameter-pagu.index') }}"
                        @class([
                            'min-h-[44px] flex items-center gap-md px-md rounded-full transition-colors',
                            'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.parameter-pagu.*'),
                            'text-ink hover:bg-surface-subtle' => ! request()->routeIs('akuntan.parameter-pagu.*'),
                        ])
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">tune</span>
                        <span class="font-body text-body-md">Parameter PAGU</span>
                    </a>

                    <div class="pt-md mt-sm border-t border-border-soft flex flex-col gap-xs">
                        <span class="px-md pb-xs font-body text-micro text-ink-muted uppercase tracking-wider">Hanya lihat</span>
                        <a
                            href="{{ route('akuntan.laporan.index') }}"
                            @class([
                                'min-h-[44px] flex items-center justify-between gap-sm px-md rounded-full transition-colors',
                                'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.laporan.*'),
                                'text-ink-muted hover:text-ink hover:bg-surface-subtle' => ! request()->routeIs('akuntan.laporan.*'),
                            ])
                        >
                            <span class="flex items-center gap-md min-w-0">
                                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">description</span>
                                <span class="font-body text-body-md truncate">Laporan</span>
                            </span>
                            <span @class([
                                'px-xs py-0.5 rounded font-body text-micro shrink-0',
                                'bg-surface/20 text-on-obsidian' => request()->routeIs('akuntan.laporan.*'),
                                'bg-surface-subtle border border-border-soft text-ink-muted' => ! request()->routeIs('akuntan.laporan.*'),
                            ])>Hanya lihat</span>
                        </a>
                    </div>
                </nav>
            </div>

            {{-- Profile Footer --}}
            <div class="pt-md border-t border-border-soft flex items-center justify-between gap-sm">
                <div class="flex items-center gap-sm min-w-0">
                    <div class="w-10 h-10 rounded-full bg-surface-muted flex items-center justify-center shrink-0">
                        <span class="font-body text-label-sm text-ink">{{ $initials }}</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">{{ $user?->name ?? 'Rizky Pratama' }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">Akuntan</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        aria-label="Keluar"
                        class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content Shell --}}
        <div class="flex-1 flex flex-col min-w-0 lg:h-full lg:overflow-y-auto">
            {{-- Mobile Sticky Header --}}
            <header class="lg:hidden sticky top-0 z-30 bg-surface border-b border-border-soft px-lg py-md flex items-center justify-between gap-sm">
                <div class="flex items-center gap-sm min-w-0">
                    <button
                        type="button"
                        @click="mobileSidebarOpen = true"
                        aria-label="Buka menu"
                        class="min-w-[44px] min-h-[44px] -ml-sm flex items-center justify-center rounded-full text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">menu</span>
                    </button>
                    <div class="flex flex-col min-w-0">
                        <span class="font-display text-card-title text-ink truncate">{{ $title }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">{{ $todayLabel }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-xs shrink-0">
                    <span class="px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted">
                        Akuntan
                    </span>
                    <x-theme-toggle />
                </div>
            </header>

            {{-- Desktop Top Header --}}
            <header class="hidden lg:flex items-center justify-between gap-md pb-lg shrink-0">
                <div class="flex flex-col">
                    <span class="font-body text-micro text-ink-muted">{{ $breadcrumb }}</span>
                    <h1 class="font-display text-page-title text-ink">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="font-body text-body-md text-ink-muted">{{ $subtitle }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-sm shrink-0">
                    <div class="flex items-center gap-xs px-md py-sm rounded-full bg-surface border border-border-soft">
                        <span class="material-symbols-outlined text-card-title text-ink-muted" aria-hidden="true">calendar_today</span>
                        <span class="font-body text-label-sm text-ink">{{ $todayLabel }}</span>
                    </div>
                    <x-theme-toggle />
                </div>
            </header>

            {{-- Flash Messages & Validation Summary --}}
            <div class="px-lg pt-md lg:px-0 lg:pt-0">
                @if (session('success'))
                    <div role="status" class="mb-md p-md rounded-lg bg-success-soft text-on-success-soft border border-success/20 flex items-center gap-sm font-body text-body-md">
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-md p-md rounded-lg bg-danger-soft text-on-danger-soft border border-on-danger-soft/20 flex flex-col gap-xs font-body text-body-md">
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Page Content --}}
            <main class="flex-1 p-lg lg:p-0 flex flex-col gap-lg">
                {{ $slot }}
            </main>

            {{-- Mobile Sticky Bottom Navigation --}}
            <nav aria-label="Navigasi bawah Akuntan" class="lg:hidden sticky bottom-0 z-30 bg-surface border-t border-border-soft grid grid-cols-4 px-xs py-xs mt-auto">
                <a
                    href="{{ route('akuntan.dashboard') }}"
                    @class([
                        'min-h-[44px] flex flex-col items-center justify-center gap-xs rounded-md',
                        'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.dashboard'),
                        'text-ink-muted' => ! request()->routeIs('akuntan.dashboard'),
                    ])
                >
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">grid_view</span>
                    <span class="font-body text-micro">Dashboard</span>
                </a>
                <a
                    href="{{ route('akuntan.validasi.index') }}"
                    @class([
                        'min-h-[44px] flex flex-col items-center justify-center gap-xs rounded-md',
                        'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.validasi.*'),
                        'text-ink-muted' => ! request()->routeIs('akuntan.validasi.*'),
                    ])
                >
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">fact_check</span>
                    <span class="font-body text-micro">Validasi</span>
                </a>
                <a
                    href="{{ route('akuntan.parameter-pagu.index') }}"
                    @class([
                        'min-h-[44px] flex flex-col items-center justify-center gap-xs rounded-md',
                        'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.parameter-pagu.*'),
                        'text-ink-muted' => ! request()->routeIs('akuntan.parameter-pagu.*'),
                    ])
                >
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">tune</span>
                    <span class="font-body text-micro">PAGU</span>
                </a>
                <a
                    href="{{ route('akuntan.laporan.index') }}"
                    @class([
                        'min-h-[44px] flex flex-col items-center justify-center gap-xs rounded-md',
                        'bg-obsidian text-on-obsidian' => request()->routeIs('akuntan.laporan.*'),
                        'text-ink-muted' => ! request()->routeIs('akuntan.laporan.*'),
                    ])
                >
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">description</span>
                    <span class="font-body text-micro">Laporan</span>
                </a>
            </nav>
        </div>
    </div>
</body>
</html>
