@props([
    'title' => 'Dashboard Guru PIC',
    'mobileTitle' => null,
    'mobileChip' => null,
    'mobileChipTone' => 'neutral',
    'subtitle' => null,
    'location' => null,
])
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim((string) ($user?->name ?? 'RM'))) ?: ['RM'])
        ->filter()
        ->take(2)
        ->map(static fn (string $p): string => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $schoolName = $location?->name ?? 'Pesantren Al-Falah Palembang';
    $schoolShort = str_replace(' Palembang', '', $schoolName);
    $targetPortions = (int) ($location?->target_portions ?? 250);
    $shortDate = now('Asia/Jakarta')->translatedFormat('d M');
    $resolvedMobileTitle = $mobileTitle ?? 'REKAGIZI MBG';
    $resolvedMobileChip = $mobileChip ?? $shortDate;
    $chipClass = $mobileChipTone === 'alert'
        ? 'bg-alert-soft text-on-alert-soft'
        : 'bg-surface-subtle border border-border-soft text-ink-muted';
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
        {{-- Backdrop Menu Geser Mobile --}}
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            x-cloak
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-obsidian/40 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Floating White Bento Sidebar (Desktop & Mobile Drawer) --}}
        <aside
            id="sidebar"
            :class="{'!flex fixed inset-y-0 left-0 z-50 w-72 max-w-full rounded-none shadow-2xl h-screen overflow-y-auto': mobileSidebarOpen}"
            class="hidden lg:flex lg:flex-col lg:w-64 bg-surface rounded-xl p-lg justify-between shrink-0 border border-border-soft lg:h-full lg:overflow-y-auto"
        >
            <div class="flex flex-col gap-lg">
                <div class="flex items-center justify-between px-xs">
                    <a href="{{ route('guru-pic.dashboard') }}" class="flex items-center gap-sm min-w-0">
                        <img src="{{ asset('images/logo-bgn.png') }}" alt="Logo Badan Gizi Nasional" class="w-9 h-9 rounded-full object-contain shrink-0">
                        <span class="font-display text-section-title text-ink truncate">REKAGIZI MBG</span>
                    </a>
                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        aria-label="Tutup menu samping"
                        class="lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center rounded-sm text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                    </button>
                </div>

                {{-- Fixed School Location Label (Terikat 1 Lokasi Sekolah) --}}
                <div class="w-full flex items-center gap-sm p-md rounded-md bg-surface-subtle border border-border-soft">
                    <div class="w-8 h-8 rounded-sm bg-surface border border-border-soft flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-card-title text-ink" aria-hidden="true">school</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">{{ $schoolShort }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">{{ $targetPortions }} porsi · SPPG Selayur 1</span>
                    </div>
                </div>

                {{-- 3 Menu Utama Guru PIC --}}
                <nav aria-label="Navigasi Guru PIC" class="flex flex-col gap-xs">
                    @php
                        $isDash = request()->routeIs('guru-pic.dashboard');
                        $isKonf = request()->routeIs('guru-pic.confirmation.*');
                        $isKel = request()->routeIs('guru-pic.complaints.*');
                    @endphp
                    <a
                        href="{{ route('guru-pic.dashboard') }}"
                        @if($isDash) aria-current="page" @endif
                        class="min-h-[44px] flex items-center gap-md transition-colors {{ $isDash ? 'px-lg py-md rounded-full bg-obsidian text-on-obsidian' : 'px-md py-sm rounded text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined shrink-0 {{ $isDash ? 'text-section-title text-on-obsidian' : 'text-card-title text-ink-muted' }}" @if($isDash) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">grid_view</span>
                        <span class="font-body {{ $isDash ? 'text-card-title' : 'text-body-md' }}">Dashboard</span>
                    </a>

                    <a
                        href="{{ route('guru-pic.confirmation.index') }}"
                        @if($isKonf) aria-current="page" @endif
                        class="min-h-[44px] flex items-center gap-md transition-colors {{ $isKonf ? 'px-lg py-md rounded-full bg-obsidian text-on-obsidian' : 'px-md py-sm rounded text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined shrink-0 {{ $isKonf ? 'text-section-title text-on-obsidian' : 'text-card-title text-ink-muted' }}" @if($isKonf) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">fact_check</span>
                        <span class="font-body {{ $isKonf ? 'text-card-title' : 'text-body-md' }}">Konfirmasi &amp; Organoleptik</span>
                    </a>

                    <a
                        href="{{ route('guru-pic.complaints.index') }}"
                        @if($isKel) aria-current="page" @endif
                        class="min-h-[44px] flex items-center gap-md transition-colors {{ $isKel ? 'px-lg py-md rounded-full bg-obsidian text-on-obsidian' : 'px-md py-sm rounded text-ink hover:bg-surface-subtle' }}"
                    >
                        <span class="material-symbols-outlined shrink-0 {{ $isKel ? 'text-section-title text-on-obsidian' : 'text-card-title text-ink-muted' }}" @if($isKel) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">report_problem</span>
                        <span class="font-body {{ $isKel ? 'text-card-title' : 'text-body-md' }}">Keluhan &amp; Tindak Lanjut</span>
                    </a>
                </nav>
            </div>

            {{-- User Profile Card --}}
            <div class="pt-md border-t border-border-soft flex items-center justify-between gap-sm">
                <div class="flex items-center gap-sm min-w-0">
                    <div class="w-10 h-10 rounded-full bg-surface-muted flex items-center justify-center shrink-0">
                        <span class="font-body text-label-sm text-ink">{{ $initials ?: 'RM' }}</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">{{ $user?->name ?? 'Rina Marlina' }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">Guru PIC Sekolah</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button
                        type="submit"
                        aria-label="Keluar"
                        title="Keluar"
                        class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content Column --}}
        <div class="flex-1 flex flex-col min-w-0 lg:overflow-y-auto">
            {{-- Sticky Mobile Header (Tanpa Lonceng Notifikasi) --}}
            <header class="lg:hidden sticky top-0 z-30 bg-surface border-b border-border-soft px-lg py-md flex items-center justify-between gap-sm">
                <div class="flex items-center gap-sm min-w-0">
                    <button
                        id="open-drawer-btn"
                        type="button"
                        @click="mobileSidebarOpen = true"
                        aria-label="Buka menu navigasi"
                        class="min-w-[44px] min-h-[44px] -ml-sm flex items-center justify-center rounded-full text-ink hover:bg-surface-subtle cursor-pointer shrink-0"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">menu</span>
                    </button>
                    <div class="flex flex-col min-w-0">
                        <span class="font-display text-card-title text-ink truncate">{{ $resolvedMobileTitle }}</span>
                        <span class="font-body text-micro text-ink-muted truncate">{{ $schoolShort }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-xs shrink-0">
                    <span class="px-sm py-xs rounded-full {{ $chipClass }} font-mono text-micro whitespace-nowrap">
                        {{ $resolvedMobileChip }}
                    </span>
                    <x-theme-toggle />
                </div>
            </header>

            <main class="flex-1 p-lg lg:p-0 flex flex-col gap-lg">
                {{-- Desktop Open Top Header (Tanpa Kotak Pembungkus, Tanpa Lonceng, Tanpa Badge Koneksi Stabil) --}}
                <header class="hidden lg:flex flex-wrap items-center justify-between gap-md pt-xs">
                    <div class="flex flex-col gap-xs min-w-0">
                        <h1 class="font-display text-page-title text-ink truncate">{{ $title }}</h1>
                        @if($subtitle)
                            <p class="font-body text-body-md text-ink-muted">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-sm flex-wrap shrink-0">
                        @isset($actions)
                            {{ $actions }}
                        @endisset
                        <x-theme-toggle />
                    </div>
                </header>

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div role="status" class="p-md rounded-lg bg-success-soft text-on-success-soft border border-success/20 flex items-center gap-sm font-body text-label-sm">
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div role="alert" class="p-md rounded-lg bg-danger-soft text-on-danger-soft border border-on-danger-soft/20 flex items-center gap-sm font-body text-label-sm">
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div role="alert" class="p-md rounded-lg bg-danger-soft text-on-danger-soft border border-on-danger-soft/20 flex flex-col gap-xs font-body text-label-sm">
                        @foreach($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
