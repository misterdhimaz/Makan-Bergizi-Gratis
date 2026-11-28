@props([
    'title' => 'Dashboard Ahli Gizi',
    'subtitle' => null,
    'breadcrumb' => 'Ahli Gizi · SPPG Sungai Selayur 1',
])
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim((string) ($user?->name ?? 'NP'))) ?: ['NP'])
        ->filter()
        ->take(2)
        ->map(static fn (string $p): string => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
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
        class="min-h-screen lg:h-screen flex p-md lg:p-lg gap-lg bg-ground lg:overflow-hidden"
    >
        {{-- Backdrop Menu Geser Mobile --}}
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            x-cloak
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-30 bg-obsidian/50 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Floating White Bento Sidebar (Desktop & Mobile Drawer) --}}
        <aside
            id="sidebar"
            :class="{'!flex fixed inset-y-0 left-0 z-40 w-72 rounded-none sm:rounded-r-xl shadow-2xl h-screen overflow-y-auto': mobileSidebarOpen}"
            class="hidden lg:flex lg:flex-col lg:w-64 bg-surface rounded-xl p-lg justify-between shrink-0 border border-border-soft lg:h-full lg:overflow-y-auto"
        >
            <div class="flex flex-col gap-lg">
                <div class="flex items-center justify-between px-xs">
                    <a href="{{ route('ahli-gizi.dashboard') }}" class="flex items-center gap-sm min-w-0">
                        <img src="{{ asset('images/logo-bgn.png') }}" alt="Logo Badan Gizi Nasional" class="w-9 h-9 rounded-full object-contain shrink-0">
                        <span class="font-display text-section-title text-ink truncate">REKAGIZI MBG</span>
                    </a>
                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        aria-label="Tutup menu samping"
                        class="lg:hidden p-xs rounded-sm text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                    </button>
                </div>

                {{-- Fixed Single-SPPG Location Label (Tanpa Pemilih Unit) --}}
                <div class="w-full flex items-center gap-sm p-md rounded-md bg-surface-subtle border border-border-soft">
                    <div class="w-8 h-8 rounded-sm bg-surface border border-border-soft flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-card-title text-ink" aria-hidden="true">storefront</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">SPPG Sungai Selayur 1</span>
                        <span class="font-body text-micro text-ink-muted">7 titik penerima</span>
                    </div>
                </div>

                {{-- Navigasi Utama Ahli Gizi --}}
                <nav aria-label="Navigasi Ahli Gizi" class="flex flex-col gap-xs">
                    <a
                        href="{{ route('ahli-gizi.dashboard') }}"
                        @if(request()->routeIs('ahli-gizi.dashboard')) aria-current="page" @endif
                        class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('ahli-gizi.dashboard') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">grid_view</span>
                        <span class="font-body text-body-md">Dashboard</span>
                    </a>

                    <a
                        href="{{ route('ahli-gizi.menu.index') }}"
                        @if(request()->routeIs('ahli-gizi.menu.*')) aria-current="page" @endif
                        class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('ahli-gizi.menu.*') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">restaurant_menu</span>
                        <span class="font-body text-body-md">Perencanaan Menu</span>
                    </a>

                    <a
                        href="{{ route('ahli-gizi.inventory.index') }}"
                        @if(request()->routeIs('ahli-gizi.inventory.*')) aria-current="page" @endif
                        class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('ahli-gizi.inventory.*') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">inventory_2</span>
                        <span class="font-body text-body-md">Persediaan &amp; Pemorsian</span>
                    </a>

                    <a
                        href="{{ route('ahli-gizi.safety.index') }}"
                        @if(request()->routeIs('ahli-gizi.safety.*')) aria-current="page" @endif
                        class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('ahli-gizi.safety.*') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">verified_user</span>
                        <span class="font-body text-body-md">Keamanan Pangan &amp; APD</span>
                    </a>

                    {{-- Grup kecil Hanya lihat --}}
                    <div class="pt-md mt-sm border-t border-border-soft flex flex-col gap-xs">
                        <span class="px-md pb-xs font-body text-micro text-ink-muted uppercase tracking-wider">Hanya lihat</span>

                        @foreach([
                            ['route' => 'ahli-gizi.readonly.distribusi', 'icon' => 'local_shipping', 'label' => 'Distribusi'],
                            ['route' => 'ahli-gizi.readonly.keluhan', 'icon' => 'chat_bubble_outline', 'label' => 'Keluhan'],
                            ['route' => 'ahli-gizi.readonly.laporan', 'icon' => 'description', 'label' => 'Laporan'],
                            ['route' => 'ahli-gizi.readonly.data-master', 'icon' => 'database', 'label' => 'Data Master'],
                        ] as $ro)
                            <a
                                href="{{ route($ro['route']) }}"
                                @if(request()->routeIs($ro['route'])) aria-current="page" @endif
                                class="flex items-center justify-between gap-sm px-md py-sm rounded-full transition-colors {{ request()->routeIs($ro['route']) ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}"
                            >
                                <span class="flex items-center gap-md min-w-0">
                                    <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">{{ $ro['icon'] }}</span>
                                    <span class="font-body text-body-md truncate">{{ $ro['label'] }}</span>
                                </span>
                                <span class="inline-flex items-center gap-xs shrink-0">
                                    <span class="px-xs py-0.5 rounded bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted whitespace-nowrap">Hanya lihat</span>
                                    <span class="material-symbols-outlined text-label-sm text-ink-muted" aria-label="Hanya lihat">visibility</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </nav>
            </div>

            {{-- Footer Profil Pengguna & Keluar --}}
            <div class="flex flex-col gap-md pt-lg border-t border-border-soft">
                <div class="flex items-center justify-between gap-sm px-xs">
                    <div class="flex items-center gap-sm min-w-0">
                        <div class="w-9 h-9 rounded-full bg-surface-muted text-ink flex items-center justify-center shrink-0">
                            <span class="font-body text-label-sm font-semibold">{{ $initials }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-body text-label-sm font-semibold text-ink truncate">{{ $user?->name ?? 'Nadia Putri' }}</span>
                            <span class="font-body text-micro text-ink-muted truncate">Ahli Gizi</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" aria-label="Keluar dari sistem" title="Keluar" class="p-xs rounded-full text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer">
                            <span class="material-symbols-outlined text-card-title" aria-hidden="true">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Area Konten Utama --}}
        <div class="flex-1 flex flex-col gap-lg min-w-0 lg:h-full lg:overflow-y-auto lg:overflow-x-hidden lg:pr-xs">
            <header class="flex flex-wrap items-center justify-between gap-md pt-xs">
                <div class="flex items-center gap-md min-w-0">
                    <button
                        type="button"
                        @click="mobileSidebarOpen = true"
                        aria-label="Buka menu samping"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-full bg-surface border border-border-soft text-ink cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">menu</span>
                    </button>
                    <div class="flex flex-col min-w-0">
                        @if($breadcrumb)
                            <p class="font-body text-micro text-ink-muted">{{ $breadcrumb }}</p>
                        @endif
                        <h1 class="font-display text-page-title text-ink truncate">{{ $title }}</h1>
                        @if($subtitle)
                            <p class="font-body text-micro text-ink-muted">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-sm flex-wrap">
                    {{ $actions ?? '' }}
                    <x-theme-toggle />
                </div>
            </header>

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" role="status" class="flex items-center justify-between gap-md px-lg py-md rounded-lg bg-success-soft border border-border-soft text-on-success-soft">
                    <div class="flex items-center gap-sm min-w-0">
                        <span class="material-symbols-outlined text-section-title text-success shrink-0" aria-hidden="true">check_circle</span>
                        <p class="font-body text-body-md">{{ session('success') }}</p>
                    </div>
                    <button type="button" @click="show = false" aria-label="Tutup pemberitahuan" class="p-xs rounded-full hover:bg-surface cursor-pointer">
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">close</span>
                    </button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div x-data="{ show: true }" x-show="show" role="alert" class="flex items-start justify-between gap-md px-lg py-md rounded-lg bg-danger-soft border border-border-soft text-on-danger-soft">
                    <div class="font-body text-body-md">
                        @if(session('error'))
                            <p>{{ session('error') }}</p>
                        @endif
                        @if($errors->any())
                            <ul class="list-disc list-inside text-micro mt-xs">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <button type="button" @click="show = false" aria-label="Tutup peringatan" class="p-xs rounded-full hover:bg-surface cursor-pointer">
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">close</span>
                    </button>
                </div>
            @endif

            <main class="flex-1 flex flex-col gap-lg min-w-0">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
