<!DOCTYPE html>
<html lang="id" data-theme="light-only" data-theme-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIM-MBG SPPG Sungai Selayur 1</title>

    @include('partials.theme-head')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        if (window.Chart) {
            Chart.defaults.color = '#64748B';
            Chart.defaults.borderColor = 'rgba(6, 31, 71, 0.08)';
            Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body
    class="h-screen w-screen overflow-hidden flex antialiased selection:bg-brand-gold selection:text-brand-navy"
    x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        sidebarCollapsed: false,
        isLoading: true,
        notifOpen: false,
        notificationStorageKey: 'sim-mbg-notifications-read-{{ auth()->id() }}',
        notificationReadIds: [],
        profileDropdownOpen: false,
        securityModalOpen: false,
        passwordModalTrigger: null,
        toastVisible: true,
        globalQuery: '',
        globalResults: [],
        globalSearching: false,
        globalDropdownOpen: false,
        openPasswordModal(trigger) {
            this.passwordModalTrigger = trigger;
            this.profileDropdownOpen = false;
            this.securityModalOpen = true;
            this.$nextTick(() => setTimeout(() => this.$refs.currentPassword.focus(), 50));
        },
        closePasswordModal(restoreFocus = true) {
            this.securityModalOpen = false;
            const trigger = this.passwordModalTrigger;
            this.$nextTick(() => {
                if (restoreFocus && trigger) trigger.focus();
            });
        },
        trapPasswordModalFocus(event) {
            if (event.key !== 'Tab') return;
            const focusable = [...this.$refs.passwordModal.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])'
            )].filter(element => element.getClientRects().length > 0);
            if (focusable.length === 0) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
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
        },
        currentNotificationIds() {
            return [...document.querySelectorAll('[data-notification-id]')]
                .map(element => element.dataset.notificationId);
        },
        loadNotificationReadState() {
            const currentIds = this.currentNotificationIds();
            let storedIds = [];

            try {
                storedIds = JSON.parse(localStorage.getItem(this.notificationStorageKey) || '[]');
            } catch (error) {
                console.warn('Status baca notifikasi tidak dapat dimuat dari penyimpanan browser.', error);
            }

            this.notificationReadIds = Array.isArray(storedIds)
                ? storedIds.filter(id => currentIds.includes(id))
                : [];
        },
        unreadNotificationCount() {
            return this.currentNotificationIds()
                .filter(id => !this.notificationReadIds.includes(id))
                .length;
        },
        markNotificationsRead() {
            this.notificationReadIds = this.currentNotificationIds();

            try {
                localStorage.setItem(this.notificationStorageKey, JSON.stringify(this.notificationReadIds));
            } catch (error) {
                console.warn('Status baca notifikasi tidak dapat disimpan di penyimpanan browser.', error);
            }
        }
    }"
    @keydown.window.ctrl.k.prevent="$refs.globalSearchInput && $refs.globalSearchInput.focus()"
    x-init="loadNotificationReadState(); setTimeout(() => { isLoading = false }, 120)"
>
    @php
        $currentUser = auth()->user();
        $userRole = $currentUser->role->value;
        $userRoleLabel = $currentUser->role->label();
    @endphp

    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
        x-cloak
    ></div>

    <aside
        id="app-sidebar"
        :class="{
            'translate-x-0': sidebarOpen,
            '-translate-x-full': !sidebarOpen,
            'w-72': !sidebarCollapsed,
            'w-20': sidebarCollapsed
        }"
        class="fixed inset-y-0 left-0 z-40 h-screen flex flex-col sidebar-obsidian transition-all duration-200 ease-out lg:static lg:translate-x-0 shrink-0 select-none"
    >
        <div class="h-16 px-4 flex items-center justify-between border-b border-white/10 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0 min-h-touch">
                @if(!empty($brandLogoUrl))
                    <img src="{{ $brandLogoUrl }}" alt="Lambang Badan Gizi Nasional Republik Indonesia" class="w-10 h-10 rounded-full object-contain p-0.5 bg-white shrink-0">
                @else
                    <div class="w-10 h-10 rounded-lg bg-brand-gold text-brand-navy flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                @endif
                <div x-show="!sidebarCollapsed" x-transition.opacity class="overflow-hidden min-w-0">
                    <p class="font-extrabold text-sm text-white truncate">SIM-MBG</p>
                    <p class="text-xs text-white/70 truncate">SPPG Sungai Selayur 1</p>
                </div>
            </a>
            <button
                type="button"
                @click="sidebarCollapsed = !sidebarCollapsed"
                :aria-label="sidebarCollapsed ? 'Perlebar sidebar' : 'Perkecil sidebar'"
                :aria-expanded="!sidebarCollapsed"
                aria-controls="app-navigation"
                class="hidden lg:flex min-w-touch min-h-touch items-center justify-center rounded-lg text-white/70 hover:text-white hover:bg-white/10"
                :title="sidebarCollapsed ? 'Perlebar sidebar' : 'Perkecil sidebar'"
            >
                <svg class="w-5 h-5 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
            </button>
        </div>

        <nav id="app-navigation" class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p x-show="!sidebarCollapsed" class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white/75">Modul operasional</p>

            <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span x-show="!sidebarCollapsed">Dashboard</span>
            </a>

            @if(in_array($userRole, ['admin', 'ahli_gizi', 'kepala_dapur', 'akuntan']))
            <a href="{{ route('menu-planning.index') }}" @if(request()->routeIs('menu-planning.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('menu-planning.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-show="!sidebarCollapsed">Perencanaan menu</span>
            </a>
            @endif

            @if(in_array($userRole, ['admin', 'kepala_dapur', 'asisten_lapangan', 'ahli_gizi']))
            <a href="{{ route('production-inventory.index') }}" @if(request()->routeIs('production-inventory.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('production-inventory.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span x-show="!sidebarCollapsed">Produksi & bahan baku</span>
            </a>
            @endif

            @if(in_array($userRole, ['admin', 'ahli_gizi', 'kepala_dapur', 'asisten_lapangan']))
            <a href="{{ route('quality-control.index') }}" @if(request()->routeIs('quality-control.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('quality-control.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span x-show="!sidebarCollapsed">Cek suhu (HACCP)</span>
            </a>
            <a href="{{ route('apd-inspection.index') }}" @if(request()->routeIs('apd-inspection.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('apd-inspection.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span x-show="!sidebarCollapsed">Cek APD</span>
            </a>
            @endif

            @if(in_array($userRole, ['admin', 'tim_distribusi', 'guru_pic', 'asisten_lapangan', 'kepala_dapur']))
            <a href="{{ route('distribution.index') }}" @if(request()->routeIs('distribution.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('distribution.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <span x-show="!sidebarCollapsed">Distribusi & keluhan</span>
            </a>
            @endif

            <a href="{{ route('reports.index') }}" @if(request()->routeIs('reports.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('reports.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span x-show="!sidebarCollapsed">Laporan</span>
            </a>

            @if($userRole === 'admin')
            <p x-show="!sidebarCollapsed" class="pt-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-gold/90">Administrasi</p>
            <a href="{{ route('users-rbac.index') }}" @if(request()->routeIs('users-rbac.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('users-rbac.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span x-show="!sidebarCollapsed">Mitra, akun & log</span>
            </a>
            <a href="{{ route('settings.appearance') }}" @if(request()->routeIs('settings.*')) aria-current="page" @endif class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="!sidebarCollapsed">Branding login</span>
            </a>
            @endif
        </nav>

        <div class="p-3 border-t border-white/10 shrink-0">
            <div class="flex items-center gap-2">
                <button type="button" @click="openPasswordModal($event.currentTarget)" class="flex items-center gap-2.5 min-w-0 flex-1 text-left rounded-lg p-1.5 hover:bg-white/5">
                    <div class="w-9 h-9 rounded-lg bg-brand-gold text-brand-navy font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                    </div>
                    <div x-show="!sidebarCollapsed" class="min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ $currentUser->name }}</p>
                        <p class="text-[11px] text-white/65 truncate">{{ $userRoleLabel }}</p>
                    </div>
                </button>
                <form x-show="!sidebarCollapsed" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" aria-label="Keluar" class="min-w-touch min-h-touch inline-flex items-center justify-center rounded-lg text-white/70 hover:text-white hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0 bg-brand-muted">
        <header class="h-16 px-6 gap-4 shrink-0 z-20 glass-header flex items-center justify-between">
            <div class="flex items-center min-w-0 gap-3">
                <button type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="app-sidebar" aria-label="Menu navigasi" class="lg:hidden min-w-touch min-h-touch inline-flex items-center justify-center rounded-lg text-brand-navy hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-lg font-extrabold text-brand-navy truncate">@yield('page_title', 'Dashboard operasional')</h1>
                    @hasSection('page_subtitle')
                        <p class="text-xs text-slate-500 hidden sm:block truncate">@yield('page_subtitle')</p>
                    @endif
                </div>
            </div>

            <div class="relative hidden md:block w-full max-w-sm min-w-0 flex-1" @click.outside="globalDropdownOpen = false">
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        aria-label="Cari menu, bahan, dan mitra"
                        aria-controls="global-search-results"
                        :aria-expanded="globalDropdownOpen"
                        x-ref="globalSearchInput"
                        x-model="globalQuery"
                        @input.debounce.250ms="performGlobalSearch()"
                        @focus="if (globalQuery.trim().length > 0) globalDropdownOpen = true"
                        placeholder="Cari menu, bahan, mitra"
                        class="form-control pl-10 pr-10 text-sm"
                        style="min-height: 44px; font-size: 16px;"
                    >
                </div>
                <div id="global-search-results" x-show="globalDropdownOpen" x-transition class="absolute left-0 right-0 mt-2 surface-card rounded-xl p-3 z-50 max-h-80 overflow-y-auto" x-cloak>
                    <div class="flex justify-between text-xs mb-2 pb-2 border-b border-slate-200">
                        <span class="font-bold text-slate-600">Hasil pencarian</span>
                        <span x-show="globalSearching" class="text-status-info">Mencari…</span>
                        <span x-show="!globalSearching" class="tabular-nums text-slate-500" x-text="globalResults.length + ' ditemukan'"></span>
                    </div>
                    <template x-for="(item, idx) in globalResults" :key="idx">
                        <a :href="item.url" class="block p-2.5 hover:bg-brand-muted rounded-lg">
                            <span class="text-[10px] font-bold uppercase text-status-info" x-text="item.category"></span>
                            <p class="text-sm font-bold text-brand-navy" x-text="item.title"></p>
                            <p class="text-xs text-slate-500 truncate" x-text="item.subtitle"></p>
                        </a>
                    </template>
                    <div x-show="!globalSearching && globalResults.length === 0" class="py-6 text-center text-sm text-slate-500">Tidak ada hasil.</div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @include('partials.ui.connection-indicator')

                <div class="relative" @click.outside="notifOpen = false">
                    <button type="button" @click="notifOpen = !notifOpen; if (notifOpen) markNotificationsRead()" class="relative min-w-touch min-h-touch inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-brand-navy hover:bg-slate-50" :aria-label="unreadNotificationCount() > 0 ? `Notifikasi, ${unreadNotificationCount()} belum dibaca` : 'Notifikasi'" @if(request()->routeIs('dashboard')) title="Notifikasi operasional dan keluhan prioritas" @endif>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="unreadNotificationCount() > 0" :style="{ display: unreadNotificationCount() > 0 ? 'flex' : 'none' }" x-text="unreadNotificationCount()" x-cloak class="absolute -top-1 -right-1 min-w-[1rem] h-4 px-1 rounded-full bg-status-warning text-white text-[10px] font-bold flex items-center justify-center tabular-nums"></span>
                    </button>
                    <div x-show="notifOpen" x-transition class="absolute right-0 mt-2 w-80 sm:w-96 surface-card rounded-xl p-3 z-50" x-cloak>
                        <div class="flex justify-between pb-2 mb-1 border-b border-slate-200">
                            <span class="font-bold text-sm text-brand-navy">Notifikasi prioritas</span>
                            <span class="text-xs tabular-nums text-slate-500">{{ ($navLiveNotifications ?? collect())->count() }}</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                            @forelse(($navLiveNotifications ?? collect()) as $notif)
                                @php
                                    $notificationId = sha1(implode('|', [
                                        $notif['title'] ?? '',
                                        $notif['subtitle'] ?? '',
                                        $notif['url'] ?? '',
                                    ]));
                                    $notificationIndicatorClass = match($notif['color'] ?? '') {
                                        'rose' => 'bg-rose-600',
                                        'amber' => 'bg-amber-500',
                                        'emerald' => 'bg-emerald-600',
                                        'indigo' => 'bg-indigo-600',
                                        'teal' => 'bg-teal-600',
                                        default => 'bg-status-info',
                                    };
                                @endphp
                                @if(!empty($notif['url']))
                                    <a href="{{ $notif['url'] }}" data-notification-id="{{ $notificationId }}" class="py-3 px-1 flex gap-3 hover:bg-brand-muted rounded-lg">
                                        <span class="w-2 h-2 mt-1.5 rounded-full {{ $notificationIndicatorClass }} shrink-0" aria-hidden="true"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-brand-navy truncate">{{ $notif['title'] }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $notif['subtitle'] }}</p>
                                            <span class="text-[11px] text-slate-600 mt-1 block">{{ $notif['time'] }}</span>
                                        </div>
                                    </a>
                                @else
                                    <div data-notification-id="{{ $notificationId }}" class="py-3 px-1 flex gap-3">
                                        <span class="w-2 h-2 mt-1.5 rounded-full {{ $notificationIndicatorClass }} shrink-0" aria-hidden="true"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-brand-navy truncate">{{ $notif['title'] }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $notif['subtitle'] }}</p>
                                            <span class="text-[11px] text-slate-600 mt-1 block">{{ $notif['time'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                @include('partials.ui.empty-state', ['title' => 'Belum ada notifikasi', 'description' => 'Notifikasi operasional akan muncul di sini.'])
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="relative" @click.outside="profileDropdownOpen = false">
                        <button type="button" x-ref="profileMenuButton" @click="profileDropdownOpen = !profileDropdownOpen; passwordModalTrigger = $event.currentTarget" :aria-expanded="profileDropdownOpen" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5 hover:bg-slate-50">
                        <div class="w-8 h-8 rounded-lg bg-brand-navy text-white text-xs font-bold flex items-center justify-center">{{ strtoupper(substr($currentUser->name, 0, 2)) }}</div>
                        <div class="hidden sm:block text-left">
                            <p class="text-xs font-bold text-brand-navy">{{ $currentUser->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $userRoleLabel }}</p>
                        </div>
                    </button>
                    <div x-show="profileDropdownOpen" x-transition class="profile-dropdown absolute right-0 mt-2 w-72 surface-card rounded-xl p-3 z-50" x-cloak>
                        <div class="p-3 rounded-lg bg-brand-muted mb-2">
                            @include('partials.ui.status-badge', ['tone' => 'info', 'label' => $userRoleLabel])
                            <p class="text-sm font-bold text-brand-navy mt-2">{{ $currentUser->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $currentUser->email }}</p>
                        </div>
                        @if($userRole === 'admin')
                        <a href="{{ route('settings.appearance') }}" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-semibold text-brand-navy hover:bg-brand-muted flex items-center gap-2">Branding login</a>
                        @endif
                        <button type="button" @click="openPasswordModal(passwordModalTrigger || $event.currentTarget)" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-semibold text-brand-navy hover:bg-brand-muted">Ubah kata sandi</button>
                        <form action="{{ route('logout') }}" method="POST" class="mt-1 pt-1 border-t border-slate-200">
                            @csrf
                            <button type="submit" class="btn-logout w-full text-left px-3 py-2.5 rounded-lg text-sm font-bold">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div id="main-scroll-container" class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col">
            @if(session('success') || session('error') || (isset($errors) && $errors->any()))
            <div class="px-4 sm:px-6 pt-4" x-show="toastVisible" x-transition>
                @if(session('success'))
                    <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-green-50 border border-green-200 text-green-900">
                        <p class="text-sm font-semibold">{{ session('success') }}</p>
                        <button type="button" @click="toastVisible = false" class="min-w-touch min-h-touch text-lg font-bold">&times;</button>
                    </div>
                @endif
                @if(session('error') || (isset($errors) && $errors->any()))
                    <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-900 mt-2">
                        <div class="text-sm font-semibold">
                            {{ session('error') }}
                            @if(isset($errors) && $errors->any())
                                <ul class="list-disc list-inside text-xs mt-1">
                                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                                </ul>
                            @endif
                        </div>
                        <button type="button" @click="toastVisible = false" class="min-w-touch min-h-touch text-lg font-bold">&times;</button>
                    </div>
                @endif
            </div>
            @endif

            <div x-show="isLoading" class="p-6 space-y-4 animate-pulse" x-cloak>
                <div class="h-24 bg-white border border-slate-200 rounded-xl"></div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="h-24 bg-white border border-slate-200 rounded-xl"></div>
                    <div class="h-24 bg-white border border-slate-200 rounded-xl"></div>
                    <div class="h-24 bg-white border border-slate-200 rounded-xl"></div>
                    <div class="h-24 bg-white border border-slate-200 rounded-xl"></div>
                </div>
            </div>

            <main x-show="!isLoading" class="flex-1 p-4 sm:p-6 space-y-6">
                @yield('content')
            </main>

            <footer class="px-4 sm:px-6 py-3 border-t border-slate-200 bg-white text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-1 shrink-0">
                <div><strong class="text-brand-navy">SIM-MBG SPPG Sungai Selayur 1</strong> · Monitoring Program Makan Bergizi Gratis</div>
                <div class="tabular-nums">RBAC aktif</div>
            </footer>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="securityModalOpen" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/40" x-cloak>
            <div x-ref="passwordModal" @click.outside="closePasswordModal(false)" @keydown="trapPasswordModalFocus($event)" @keydown.escape.stop.prevent="closePasswordModal()" role="dialog" aria-modal="true" aria-labelledby="password-modal-title" class="surface-card rounded-2xl max-w-md w-full p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 id="password-modal-title" class="text-lg font-extrabold text-brand-navy">Perbarui kata sandi</h3>
                    <button type="button" @click="closePasswordModal()" aria-label="Tutup dialog perbarui kata sandi" class="min-w-touch min-h-touch text-xl font-bold text-slate-400">&times;</button>
                </div>
                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label for="current-password" class="block text-sm font-semibold text-brand-navy mb-1">Kata sandi saat ini</label>
                        <input id="current-password" x-ref="currentPassword" type="password" name="current_password" autocomplete="current-password" required class="form-control">
                    </div>
                    <div>
                        <label for="new-password" class="block text-sm font-semibold text-brand-navy mb-1">Kata sandi baru (min. 8)</label>
                        <input id="new-password" type="password" name="password" autocomplete="new-password" required minlength="8" class="form-control">
                    </div>
                    <div>
                        <label for="password-confirmation" class="block text-sm font-semibold text-brand-navy mb-1">Konfirmasi kata sandi baru</label>
                        <input id="password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8" class="form-control">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closePasswordModal()" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <script>
        window.initMbgAnimations = function () {};
    </script>
    @stack('scripts')
</body>
</html>
