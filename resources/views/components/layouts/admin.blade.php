@props([
    'title' => 'Panel Administrator Sistem',
    'breadcrumb' => null,
    'subtitle' => null,
])
@php
    $user = auth()->user();
    $initials = $user
        ? strtoupper(collect(explode(' ', trim($user->name)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''))
        : 'AM';
    $dataMasterActive = request()->routeIs('admin.data-master.*') || request()->is('admin/data-master*');
    $settingsActive = request()->routeIs('admin.settings.*') || request()->is('admin/pengaturan*');
@endphp
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
<body
    class="bg-ground text-ink font-body antialiased min-h-screen"
    x-data="{
        mobileSidebarOpen: false,
        dataMasterOpen: {{ $dataMasterActive ? 'true' : 'false' }},
        settingsOpen: {{ $settingsActive ? 'true' : 'false' }},
    }"
>
    {{-- Backdrop Menu Geser Mobile --}}
    <div
        x-show="mobileSidebarOpen"
        x-transition.opacity
        @click="mobileSidebarOpen = false"
        class="fixed inset-0 z-30 bg-obsidian/50 lg:hidden"
        x-cloak
    ></div>

    <div class="min-h-screen lg:h-screen lg:overflow-hidden flex p-lg gap-lg bg-ground">
        {{-- Sidebar Mengambang Putih Bento --}}
        <aside
            id="admin-sidebar"
            class="hidden lg:flex lg:flex-col lg:w-64 lg:h-full lg:overflow-y-auto bg-surface rounded-xl p-lg justify-between shrink-0 border border-border-soft"
            :class="{'!flex fixed inset-y-0 left-0 z-40 w-72 rounded-none sm:rounded-r-xl shadow-2xl h-screen overflow-y-auto': mobileSidebarOpen}"
        >
            <div class="flex flex-col gap-lg">
                {{-- Header Brand + Logo BGN --}}
                <div class="flex items-center justify-between px-xs">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-sm min-w-0">
                        <img src="{{ asset('images/logo-bgn.png') }}" alt="Logo Badan Gizi Nasional" class="w-9 h-9 rounded-full object-contain shrink-0">
                        <span class="font-display text-section-title text-ink truncate">REKAGIZI MBG</span>
                    </a>
                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        aria-label="Tutup menu"
                        class="lg:hidden p-xs rounded-sm text-ink-muted hover:text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                    </button>
                </div>

                {{-- Kartu Unit SPPG --}}
                <div class="w-full flex items-center gap-sm p-md rounded-md bg-surface-subtle border border-border-soft">
                    <div class="w-8 h-8 rounded-sm bg-surface border border-border-soft flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-card-title text-ink" aria-hidden="true">storefront</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-body text-card-title text-ink truncate">SPPG Sungai Selayur 1</span>
                        <span class="font-body text-micro text-ink-muted">7 titik penerima</span>
                    </div>
                </div>

                {{-- Navigasi Utama Admin --}}
                <nav aria-label="Navigasi Administrator" class="flex flex-col gap-xs">
                    <ul class="flex flex-col gap-xs">
                        <li>
                            <a
                                href="{{ route('admin.dashboard') }}"
                                @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                                class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                            >
                                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">grid_view</span>
                                <span class="font-body text-body-md">Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('admin.users.index') }}"
                                @if(request()->routeIs('admin.users.*')) aria-current="page" @endif
                                class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                            >
                                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">manage_accounts</span>
                                <span class="font-body text-body-md">Pengguna &amp; Peran</span>
                            </a>
                        </li>

                        {{-- Submenu Data Master --}}
                        <li>
                            <button
                                type="button"
                                @click="dataMasterOpen = !dataMasterOpen"
                                :aria-expanded="dataMasterOpen.toString()"
                                class="w-full flex items-center justify-between gap-sm px-md py-sm rounded-full transition-colors cursor-pointer {{ $dataMasterActive ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                            >
                                <span class="flex items-center gap-md min-w-0">
                                    <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">database</span>
                                    <span class="font-body text-body-md truncate">Data Master</span>
                                </span>
                                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true" x-text="dataMasterOpen ? 'expand_less' : 'expand_more'">expand_more</span>
                            </button>

                            <ul x-show="dataMasterOpen" x-collapse x-cloak class="mt-xs pl-lg flex flex-col gap-xs">
                                <li>
                                    <a href="{{ route('admin.data-master.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.index') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Ringkasan Data Master</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.lokasi.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.lokasi.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Lokasi Penerima</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.kategori.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.kategori.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Kategori Penerima</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.standar-porsi.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.standar-porsi.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Standar Porsi</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.bahan.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.bahan.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Daftar Bahan</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.pemasok.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.pemasok.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Pemasok</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.pagu.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.pagu.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Parameter PAGU</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.data-master.checklist-sop.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.data-master.checklist-sop.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Checklist SOP BGN</a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a
                                href="{{ route('admin.audit-logs.index') }}"
                                @if(request()->routeIs('admin.audit-logs.*')) aria-current="page" @endif
                                class="flex items-center gap-md px-md py-sm rounded-full transition-colors {{ request()->routeIs('admin.audit-logs.*') ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                            >
                                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">history</span>
                                <span class="font-body text-body-md">Log Aktivitas</span>
                            </a>
                        </li>

                        {{-- Submenu Pengaturan Sistem --}}
                        <li>
                            <button
                                type="button"
                                @click="settingsOpen = !settingsOpen"
                                :aria-expanded="settingsOpen.toString()"
                                class="w-full flex items-center justify-between gap-sm px-md py-sm rounded-full transition-colors cursor-pointer {{ $settingsActive ? 'bg-obsidian text-on-obsidian font-semibold' : 'text-ink hover:bg-surface-subtle' }}"
                            >
                                <span class="flex items-center gap-md min-w-0">
                                    <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">settings</span>
                                    <span class="font-body text-body-md truncate">Pengaturan Sistem</span>
                                </span>
                                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true" x-text="settingsOpen ? 'expand_less' : 'expand_more'">expand_more</span>
                            </button>

                            <ul x-show="settingsOpen" x-collapse x-cloak class="mt-xs pl-lg flex flex-col gap-xs">
                                <li>
                                    <a href="{{ route('admin.settings.keamanan-sesi') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.settings.keamanan-sesi') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Keamanan Sesi</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.settings.backup.index') }}" class="block px-md py-xs rounded-md font-body text-label-sm {{ request()->routeIs('admin.settings.backup.*') ? 'bg-surface-muted text-ink font-semibold' : 'text-ink-muted hover:text-ink hover:bg-surface-subtle' }}">Backup &amp; Restore</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
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
                            <span class="font-body text-label-sm font-semibold text-ink truncate">{{ $user?->name ?? 'Arief Mahendra' }}</span>
                            <span class="font-body text-micro text-ink-muted truncate">{{ $user?->role?->label() ?? 'Administrator Sistem' }}</span>
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
