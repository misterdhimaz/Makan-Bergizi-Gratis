<!DOCTYPE html>
@php
    $currentAppTheme = $activeTheme ?? App\Models\AppSetting::get('theme_preset', 'sppg-navy');
    $isLightServer = in_array($currentAppTheme, ['light-clean', 'minimalist']);
    if ($currentAppTheme === 'custom') {
        $cCanvas = App\Models\AppSetting::get('custom_bg_canvas', '#030D22');
        $hexC = ltrim($cCanvas, '#');
        if (strlen($hexC) === 6) {
            $rC = hexdec(substr($hexC, 0, 2)) / 255;
            $gC = hexdec(substr($hexC, 2, 2)) / 255;
            $bC = hexdec(substr($hexC, 4, 2)) / 255;
            $isLightServer = ($rC * 0.2126 + $gC * 0.7152 + $bC * 0.0722) > 0.45;
        }
    }
@endphp
<html lang="id" data-theme="{{ $currentAppTheme }}" data-theme-mode="{{ $isLightServer ? 'light' : 'dark' }}" class="{{ $isLightServer ? '' : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Komando') — SIM-MBG SPPG Sungai Selayur 1</title>

    @include('partials.theme-head')

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- GSAP Core & ScrollTrigger -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

    <!-- Chart.js dengan Default Tema SPPG -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        if (window.Chart) {
            Chart.defaults.color = '#9FB3CF';
            Chart.defaults.borderColor = 'rgba(181, 224, 234, 0.08)';
            Chart.defaults.font.family = 'Urbanist, sans-serif';
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .ambient-orb { position: absolute; border-radius: 9999px; filter: blur(55px); pointer-events: none; }
        .gsap-page-wrapper { opacity: 0; transform: translateY(10px); }
    </style>

    @stack('styles')
</head>
<!-- h-screen overflow-hidden memastikan Sidebar 100% FIXED (tidak ikut scroll) dan hanya konten yang di-scroll -->
<body
    class="h-screen w-screen overflow-hidden flex selection:bg-emerald-500 selection:text-slate-950 antialiased"
    x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        sidebarCollapsed: false,
        isLoading: true,
        notifOpen: false,
        profileDropdownOpen: false,
        securityModalOpen: false,
        themeMenuOpen: false,
        currentTheme: document.documentElement.getAttribute('data-theme') || '{{ $activeTheme }}',
        saveAsSystemDefault: false,
        toastVisible: true,
        globalQuery: '',
        globalResults: [],
        globalSearching: false,
        globalDropdownOpen: false,
        switchTheme(themeId) {
            this.currentTheme = themeId;
            window.setMbgTheme(themeId, this.saveAsSystemDefault);
        },
        async performGlobalSearch() {
            if (this.globalQuery.trim().length < 1) {
                this.globalResults = [];
                this.globalDropdownOpen = false;
                return;
            }
            this.globalSearching = true;
            this.globalDropdownOpen = true;
            try {
                const res = await fetch(`{{ route('global.search') }}?q=${encodeURIComponent(this.globalQuery)}`);
                const data = await res.json();
                this.globalResults = data.results || [];
            } catch (e) {
                this.globalResults = [];
            } finally {
                this.globalSearching = false;
            }
        }
    }"
    @keydown.window.ctrl.k.prevent="$refs.globalSearchInput.focus()"
    x-init="setTimeout(() => { isLoading = false; setTimeout(() => window.initMbgAnimations(), 40); }, 180)"
>
    @php
        $currentUser = auth()->user();
        $userRole = $currentUser->role->value;
        $userRoleLabel = $currentUser->role->label();
        $dbVariance = $navActiveProduction?->portion_variance ?? 0;
        $dbTargetPortions = $navActiveMenu?->target_portions ?? ($navActiveProduction?->planned_portions ?? 0);
        $dbActualPortions = $navActiveProduction?->actual_good_portions ?? 0;
        $dbCoreTemp = $navLatestQc?->cooking_core_temp_c ?? 0;
        $dbApdScore = $navLatestApd?->compliance_percentage ?? ($navLatestQc?->apd_compliance_score ?? 100);
    @endphp

    <!-- Mobile Backdrop Overlay -->
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-950/75 backdrop-blur-md lg:hidden"
        x-cloak
    ></div>

    <!-- ==================== SIDEBAR FIXED (DIAM & TIDAK IKUT TER-SCROLL) ==================== -->
    <aside
        :class="{
            'translate-x-0': sidebarOpen,
            '-translate-x-full': !sidebarOpen,
            'w-72': !sidebarCollapsed,
            'w-20': sidebarCollapsed
        }"
        class="fixed inset-y-0 left-0 z-40 h-screen flex flex-col sidebar-obsidian shadow-soft transition-all duration-300 ease-out lg:static lg:translate-x-0 shrink-0 select-none"
    >
        <!-- Brand Header SPPG Sungai Selayur 1 -->
        <div class="h-20 px-5 flex items-center justify-between border-b border-emerald-500/15 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 group">
                @if($brandLogoUrl)
                <img src="{{ $brandLogoUrl }}" alt="Logo SPPG" class="w-11 h-11 rounded-2xl object-contain p-1 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs shrink-0">
                @else
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sppg-green to-emerald-700 flex items-center justify-center text-sppg-navy shadow-glow-emerald group-hover:scale-105 transition-transform duration-200 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                @endif
                <div x-show="!sidebarCollapsed" x-transition.opacity class="overflow-hidden">
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-base tracking-tight text-white">SIM-MBG</span>
                        <span class="px-1.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-md">PUSAT</span>
                    </div>
                    <p class="text-xs font-semibold text-emerald-400/80 truncate">SPPG Sungai Selayur 1</p>
                </div>
            </a>

            <button
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:flex p-1.5 rounded-xl text-slate-400 hover:text-emerald-300 hover:bg-emerald-500/10 transition cursor-pointer"
                title="Perkecil / Perlebar Sidebar"
            >
                <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Menu Navigasi Berbasis Role (7 Role RBAC) -->
        <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
            <div x-show="!sidebarCollapsed" class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                Modul Operasional MBG
            </div>

            <!-- 1. Dashboard Analytics -->
            <a href="{{ route('dashboard') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard Analytics</span>
            </a>

            <!-- 2. Perencanaan & Validasi Menu -->
            @if(in_array($userRole, ['admin', 'ahli_gizi', 'kepala_dapur', 'akuntan']))
            <a href="{{ route('menu-planning.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('menu-planning.*') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Perencanaan & Menu</span>
            </a>
            @endif

            <!-- 3. Monitoring Produksi & Bahan Baku -->
            @if(in_array($userRole, ['admin', 'kepala_dapur', 'asisten_lapangan', 'akuntan']))
            <a href="{{ route('production-inventory.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('production-inventory.*') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Produksi & Bahan Baku</span>
            </a>
            @endif

            <!-- 4. Modul Cek Suhu (HACCP) -->
            @if(in_array($userRole, ['admin', 'ahli_gizi', 'kepala_dapur', 'asisten_lapangan']))
            <a href="{{ route('quality-control.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('quality-control.*') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Cek Suhu (HACCP)</span>
            </a>
            @endif

            <!-- 5. Modul Cek APD Relawan -->
            @if(in_array($userRole, ['admin', 'ahli_gizi', 'kepala_dapur', 'asisten_lapangan']))
            <a href="{{ route('apd-inspection.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('apd-inspection.*') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Cek APD Relawan</span>
            </a>
            @endif

            <!-- 5. Distribusi & Konfirmasi Organoleptik -->
            @if(in_array($userRole, ['admin', 'tim_distribusi', 'guru_pic', 'asisten_lapangan', 'kepala_dapur']))
            <a href="{{ route('distribution.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('distribution.*') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Distribusi & Keluhan</span>
            </a>
            @endif

            <!-- 6. Laporan Harian Resmi BGN & Cetak (Halaman Tersendiri) -->
            <a href="{{ route('reports.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-bold shadow-glow-emerald' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-green' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Laporan Resmi (Cetak)</span>
            </a>

            <!-- 6. Manajemen Pusat: 7 Role RBAC & Mitra Sekolah/Posyandu -->
            @if($userRole === 'admin')
            <div x-show="!sidebarCollapsed" class="pt-3 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-sppg-gold">
                Pengelola Pusat (Full Access)
            </div>
            <a href="{{ route('users-rbac.index') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('users-rbac.*') ? 'bg-gradient-to-r from-sppg-gold to-amber-500 text-sppg-navy font-bold shadow-glow-gold' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-gold' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Mitra & RBAC</span>
            </a>
            <a href="{{ route('settings.appearance') }}"
               class="gsap-nav-item flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-sppg-gold to-amber-500 text-sppg-navy font-bold shadow-glow-gold' : 'text-slate-300/80 hover:bg-white/5 hover:text-sppg-gold' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tampilan & Foto Login</span>
            </a>
            @endif
        </nav>

        <!-- Footer Profil Pengguna Terautentikasi (Fixed di Bawah Sidebar) -->
        <div class="p-3.5 border-t border-slate-200/80 dark:border-emerald-500/15 bg-slate-50/80 dark:bg-black/25 shrink-0">
            <div class="flex items-center justify-between gap-2">
                <button @click="securityModalOpen = true" class="flex items-center gap-2.5 min-w-0 text-left group cursor-pointer flex-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white font-bold flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
                        {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                    </div>
                    <div x-show="!sidebarCollapsed" class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition">{{ $currentUser->name }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 mt-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30">
                            {{ $userRoleLabel }}
                        </span>
                    </div>
                </button>

                <form x-show="!sidebarCollapsed" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="btn-logout p-2.5 rounded-xl transition cursor-pointer flex items-center justify-center shrink-0"
                        title="Keluar Sesi Aman (Logout)"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ==================== AREA KANAN (TOPBAR TETAP + HANYA ISI KONTEN YANG SCROLL) ==================== -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">

        <!-- TOPBAR GLASSMORPHISM (TETAP DI ATAS) -->
        <header class="h-20 shrink-0 z-20 glass-header px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 lg:gap-6">
            <!-- Sisi Kiri: Judul Halaman & Status Otoritas (Lebar Alami Tanpa Tumpang Tindih) -->
            <div class="flex items-center gap-3 shrink-0">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-xl text-slate-300 hover:bg-emerald-500/15 transition shrink-0"
                    title="Buka / Tutup Navigasi"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg xl:text-xl font-black text-white tracking-tight whitespace-nowrap">
                            @yield('page_title', 'Monitoring Operasional MBG')
                        </h1>
                        @if($userRole === 'admin')
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 whitespace-nowrap shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Admin Pusat
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 hidden sm:block mt-0.5 whitespace-nowrap">
                        @yield('page_subtitle', 'Sistem Informasi Monitoring Program Makan Bergizi Gratis — SPPG')
                    </p>
                </div>
            </div>

            <!-- Bagian Tengah: Pencarian Global Fleksibel (Tidak Saling Bertabrakan) -->
            <div class="relative flex-1 max-w-xs md:max-w-sm lg:max-w-md hidden sm:block mx-2" @click.outside="globalDropdownOpen = false">
                <div class="relative flex items-center">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        x-ref="globalSearchInput"
                        x-model="globalQuery"
                        @input.debounce.250ms="performGlobalSearch()"
                        @focus="if (globalQuery.trim().length > 0) globalDropdownOpen = true"
                        placeholder="Cari data MBG..."
                        class="w-full pl-10 pr-12 py-2 rounded-2xl border border-emerald-500/20 bg-slate-950/60 focus:bg-slate-950 text-xs font-semibold text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 transition-all"
                    >
                    <!-- Shortcut Badge (Otomatis hilang saat mulai mengetik) -->
                    <span
                        x-show="!globalQuery || globalQuery.trim().length === 0"
                        x-transition.opacity.duration.150ms
                        class="absolute right-3 px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700 text-[10px] font-mono text-slate-400 pointer-events-none select-none"
                    >
                        Ctrl+K
                    </span>

                    <!-- Tombol Reset / Hapus Pencarian (Muncul saat ada teks) -->
                    <button
                        type="button"
                        x-show="globalQuery && globalQuery.trim().length > 0"
                        x-transition.opacity.duration.150ms
                        @click="globalQuery = ''; globalResults = []; globalDropdownOpen = false; $refs.globalSearchInput.focus()"
                        class="absolute right-3 p-1 rounded-full text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer"
                        title="Hapus pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Dropdown Hasil Pencarian Global -->
                <div
                    x-show="globalDropdownOpen"
                    x-transition
                    class="absolute left-0 right-0 mt-2 bg-[#061A40] rounded-2xl shadow-2xl border border-emerald-500/25 p-3 z-50 max-h-96 overflow-y-auto"
                    x-cloak
                >
                    <div class="flex items-center justify-between px-2 pb-2 border-b border-slate-800 text-[11px]">
                        <span class="font-extrabold text-slate-300">Hasil Pencarian Database</span>
                        <span x-show="globalSearching" class="text-emerald-400 font-semibold animate-pulse">Mencari...</span>
                        <span x-show="!globalSearching" class="font-mono text-slate-400" x-text="globalResults.length + ' ditemukan'"></span>
                    </div>

                    <div class="divide-y divide-slate-800/80 mt-1">
                        <template x-for="(item, idx) in globalResults" :key="idx">
                            <a :href="item.url" class="block p-2.5 hover:bg-emerald-500/10 rounded-xl transition">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400" x-text="item.category"></span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-900 font-mono text-[10px] font-bold text-slate-300" x-text="item.badge"></span>
                                </div>
                                <p class="text-xs font-extrabold text-white mt-0.5" x-text="item.title"></p>
                                <p class="text-[11px] text-slate-400 truncate" x-text="item.subtitle"></p>
                            </a>
                        </template>

                        <div x-show="!globalSearching && globalResults.length === 0" class="py-6 text-center text-xs text-slate-400">
                            Tidak ada data yang cocok dengan "<span class="font-bold text-white" x-text="globalQuery"></span>".
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Notifikasi Real-Time & Profil Pengguna -->
            <div class="flex items-center gap-3 shrink-0">

                <!-- Notifikasi Real-Time -->
                <div class="relative" @click.outside="notifOpen = false">
                    <button
                        @click="notifOpen = !notifOpen"
                        class="relative p-2.5 rounded-xl bg-slate-900/80 border border-emerald-500/20 text-slate-300 hover:text-emerald-400 hover:border-emerald-400/40 transition cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if($navLiveNotifications->count() > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-amber-500 text-slate-950 text-[10px] font-extrabold flex items-center justify-center">
                                {{ $navLiveNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <div
                        x-show="notifOpen"
                        x-transition
                        class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-[#061A40] shadow-2xl border border-emerald-500/25 p-4 z-50"
                        x-cloak
                    >
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <span class="font-extrabold text-sm text-white">Notifikasi Database Real-Time</span>
                            <span class="text-[11px] font-mono font-bold text-emerald-400">{{ $navLiveNotifications->count() }} Event</span>
                        </div>
                        <div class="divide-y divide-slate-800/80 text-xs max-h-80 overflow-y-auto">
                            @forelse($navLiveNotifications as $notif)
                                <a href="{{ $notif['url'] }}" class="py-3 px-2 hover:bg-emerald-500/10 rounded-xl flex gap-3 transition block">
                                    <span class="w-2.5 h-2.5 mt-1 rounded-full bg-emerald-400 shrink-0"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white truncate">{{ $notif['title'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $notif['subtitle'] }}</p>
                                        <span class="text-[10px] font-mono text-slate-500 mt-1 block">{{ $notif['time'] }}</span>
                                    </div>
                                </a>
                            @empty
                                <p class="py-6 text-center text-slate-500">Belum ada notifikasi operasional aktif.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Dropdown Profil & Keamanan Akun -->
                <div class="relative" @click.outside="profileDropdownOpen = false">
                    <button
                        @click="profileDropdownOpen = !profileDropdownOpen"
                        class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-900/80 hover:bg-slate-900 border border-emerald-500/20 transition cursor-pointer"
                    >
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-emerald-500 to-teal-500 text-white font-bold text-xs flex items-center justify-center">
                            {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-xs font-extrabold text-white leading-none">{{ $currentUser->name }}</p>
                            <p class="text-[10px] font-semibold text-emerald-400 mt-0.5">{{ $userRoleLabel }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="profileDropdownOpen"
                        x-transition
                        class="absolute right-0 mt-2 w-72 rounded-2xl bg-[#061A40] shadow-2xl border border-emerald-500/25 p-3 z-50"
                        x-cloak
                    >
                        <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800 mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ $userRoleLabel }}
                            </span>
                            <p class="text-xs font-bold text-white mt-1.5">{{ $currentUser->name }}</p>
                            <p class="text-[11px] font-mono text-slate-400 truncate">{{ $currentUser->email }}</p>
                            @if($currentUser->institution)
                                <p class="text-[11px] font-semibold text-amber-300 mt-1">Mitra: {{ $currentUser->institution->name }}</p>
                            @endif
                        </div>

                        @if($userRole === 'admin')
                        <a
                            href="{{ route('settings.appearance') }}"
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-slate-200 hover:bg-emerald-500/15 flex items-center gap-2.5 transition mb-1"
                        >
                            <svg class="w-4 h-4 text-sppg-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Tema & Tampilan Web</span>
                        </a>
                        @endif

                        <button
                            type="button"
                            @click="profileDropdownOpen = false; securityModalOpen = true"
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-slate-200 hover:bg-emerald-500/15 flex items-center gap-2.5 transition cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Ubah Kata Sandi Keamanan</span>
                        </button>

                        <form action="{{ route('logout') }}" method="POST" class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                            @csrf
                            <button
                                type="submit"
                                class="btn-logout w-full text-left px-3 py-2.5 rounded-xl text-xs font-extrabold flex items-center gap-2.5 transition cursor-pointer"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar Sesi (Logout)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTAINER SCROLLABLE KHUSUS KONTEN (SIDEBAR & TOPBAR TETAP DIAM) -->
        <div id="main-scroll-container" class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col">

            <!-- FLASH MESSAGES / TOAST FEEDBACK -->
            @if(session('success') || session('error') || (isset($errors) && $errors->any()))
            <div class="px-4 sm:px-8 pt-5" x-show="toastVisible" x-transition>
                @if(session('success'))
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-500/30 text-emerald-900 dark:text-emerald-100 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <p class="text-sm font-bold">{{ session('success') }}</p>
                        </div>
                        <button @click="toastVisible = false" class="text-emerald-700 dark:text-emerald-300 hover:text-emerald-950 dark:hover:text-white p-1 cursor-pointer font-bold text-lg">&times;</button>
                    </div>
                @endif

                @if(session('error') || (isset($errors) && $errors->any()))
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-500/30 text-rose-950 dark:text-rose-100 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div class="text-sm font-bold">
                                {{ session('error') }}
                                @if(isset($errors) && $errors->any())
                                    <ul class="list-disc list-inside text-xs font-semibold mt-1 space-y-0.5">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        <button @click="toastVisible = false" class="text-rose-700 dark:text-rose-300 hover:text-rose-950 dark:hover:text-white p-1 cursor-pointer font-bold text-lg">&times;</button>
                    </div>
                @endif
            </div>
            @endif

            <!-- SKELETON LOADING STATE -->
            <div x-show="isLoading" class="p-6 sm:p-8 space-y-6 animate-pulse">
                <div class="h-28 bg-emerald-950/40 border border-emerald-500/15 rounded-3xl"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="h-32 bg-emerald-950/40 border border-emerald-500/15 rounded-2xl"></div>
                    <div class="h-32 bg-emerald-950/40 border border-emerald-500/15 rounded-2xl"></div>
                    <div class="h-32 bg-emerald-950/40 border border-emerald-500/15 rounded-2xl"></div>
                    <div class="h-32 bg-emerald-950/40 border border-emerald-500/15 rounded-2xl"></div>
                </div>
            </div>

            <!-- KONTEN UTAMA DENGAN GSAP FADE-IN -->
            <main x-show="!isLoading" x-cloak class="gsap-page-wrapper flex-1 p-4 sm:p-6 xl:p-8 space-y-7">
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="px-6 sm:px-8 py-4 border-t border-emerald-500/15 bg-[#030D22]/80 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2 shrink-0">
                <div>
                    <strong class="text-slate-200">SIM-MBG SPPG Sungai Selayur 1</strong> &bull; Pusat Komando & Monitoring Program Makan Bergizi Gratis
                </div>
                <div class="font-mono text-[11px] text-emerald-400/80">
                    Secured Production Mode &bull; RBAC Active
                </div>
            </footer>
        </div>
    </div>

    <!-- ==================== MODAL KEAMANAN PROFIL & UBAH KATA SANDI ==================== -->
    <template x-teleport="body">
        <div
            x-show="securityModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto"
            x-cloak
        >
            <div @click.outside="securityModalOpen = false" class="bg-[#061A40] rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-emerald-500/30 text-slate-100 my-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Keamanan Akun</span>
                        <h3 class="text-lg font-extrabold text-white mt-1">Perbarui Kata Sandi</h3>
                    </div>
                    <button @click="securityModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Kata Sandi Baru (Min. 8 Karakter)</label>
                        <input type="password" name="password" required minlength="8" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" required minlength="8" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-emerald-400 focus:outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" @click="securityModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-glow-emerald transition cursor-pointer">
                            Simpan Kata Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- ==================== INISIALISASI ANIMASI GSAP ==================== -->
    <script>
        gsap.registerPlugin(ScrollTrigger);

        window.initMbgAnimations = function () {
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

            tl.to('.gsap-page-wrapper', {
                opacity: 1,
                y: 0,
                duration: 0.45,
                clearProps: 'transform'
            });

            const revealItems = document.querySelectorAll('.gsap-reveal, .gsap-stagger-card');
            if (revealItems.length > 0) {
                gsap.fromTo(revealItems,
                    { opacity: 0, y: 18, scale: 0.99 },
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.45,
                        stagger: 0.05,
                        ease: 'power2.out',
                        clearProps: 'transform'
                    }
                );
            }

            document.querySelectorAll('.gsap-progress-bar').forEach((bar) => {
                const targetWidth = bar.getAttribute('data-width') || '0%';
                gsap.to(bar, {
                    width: targetWidth,
                    duration: 1.1,
                    ease: 'power3.out',
                    delay: 0.15
                });
            });

            document.querySelectorAll('.gsap-counter').forEach((el) => {
                const target = parseFloat(el.getAttribute('data-target') || '0');
                const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
                const obj = { val: 0 };
                gsap.to(obj, {
                    val: target,
                    duration: 1.25,
                    ease: 'power2.out',
                    onUpdate: () => {
                        el.textContent = decimals > 0
                            ? obj.val.toFixed(decimals)
                            : Math.round(obj.val).toLocaleString('id-ID');
                    }
                });
            });
        };
    </script>

    @stack('scripts')
</body>
</html>
