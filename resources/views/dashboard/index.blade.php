@extends('layouts.app')

@section('title', 'Pusat Komando & Analytics')
@section('page_title', 'Pusat Komando & Analytics')
@section('page_subtitle', 'Monitoring real-time dapur, gudang, QC & distribusi')

@section('content')
    @php
        $role = auth()->user()->role;
        $isAdmin = $role->value === 'admin';
        $plannedPortions = $todayProduction?->planned_portions ?? ($todayMenu?->target_portions ?? 0);
        $actualPortions = $todayProduction?->actual_good_portions ?? 0;
        $rejectedPortions = $todayProduction?->rejected_damaged_portions ?? 0;
        $variance = $todayProduction?->portion_variance ?? 0;
        $apdScore = $latestQc?->apd_compliance_score ?? 0;
        $coreTemp = $latestQc?->cooking_core_temp_c ?? 0;
        $holdingTemp = $latestQc?->holding_temp_c ?? 0;
        $confirmedCount = $deliveries->whereIn('status', ['confirmed_clean', 'confirmed_with_issue'])->count();
        $inTransitCount = $deliveries->where('status', 'in_transit')->count();
        $totalDeliveries = $deliveries->count();
        $deliveryRate = $totalDeliveries > 0 ? round(($confirmedCount / $totalDeliveries) * 100) : 0;
    @endphp

    <div x-data="{
        activeDataTab: 'mitra',
        localTableSearch: ''
    }" class="space-y-6">

        <!-- ==================== NOTIFIKASI PRIORITAS KRITIS (FITUR 7) ==================== -->
        @if(isset($criticalComplaintsCount) && $criticalComplaintsCount > 0)
            <div class="p-4 rounded-3xl bg-gradient-to-r from-rose-600 via-rose-700 to-red-800 text-white shadow-xl border border-rose-400 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 animate-pulse">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-black uppercase tracking-wider">Perhatian Khusus: Terdapat {{ $criticalComplaintsCount }} Tiket Keluhan Kritis Sekolah!</h4>
                        <p class="text-[11px] text-rose-100">SOP BGN: Tiket keluhan berprioritas kritis wajib segera ditindaklanjuti oleh Asisten Lapangan & Kepala Dapur.</p>
                    </div>
                </div>
                <a href="{{ route('distribution.index') }}" class="px-4 py-2.5 rounded-2xl bg-white text-rose-950 font-black text-xs shadow-lg hover:bg-rose-50 transition shrink-0">
                    Buka Modul Keluhan &rarr;
                </a>
            </div>
        @endif

        <!-- ==================== HERO COMMAND BANNER & QUICK ACTIONS ==================== -->
        <div class="gsap-stagger-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#071739] via-[#0B2559] to-[#05112B] text-white p-6 sm:p-7 shadow-xl border border-slate-700/60">
            <!-- Subtle ambient glows -->
            <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 left-1/3 w-72 h-72 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-6">
                <!-- Top Row: Welcome & Status + Cohesive Action Buttons -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    <div class="space-y-2 max-w-2xl">
                        <!-- Badges -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                {{ $role->label() }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/10 text-slate-200 text-xs font-semibold">
                                SPPG Sungai Selayur 1
                            </span>
                            @if($todayMenu)
                                <span class="px-2.5 py-1 rounded-full bg-amber-400/20 border border-amber-400/30 font-mono text-xs font-bold text-amber-300">
                                    {{ $todayMenu->menu_code }} &bull; {{ $todayMenu->operational_date->translatedFormat('d F Y') }}
                                </span>
                            @endif
                        </div>

                        <!-- Clean Heading -->
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white leading-tight">
                            {{ $todayMenu?->menu_name ?? 'Pusat Operasional & Database Gizi MBG' }}
                        </h2>

                        <!-- Subtitle -->
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            @if($isAdmin)
                                Kendali terpadu database gizi: kelola mitra penerima, perencanaan AKG, stok komoditas gudang, sanitasi QC dapur, hingga verifikasi distribusi.
                            @else
                                {{ $role->shortDesc() }}
                            @endif
                        </p>
                    </div>

                    <!-- Quick Actions (Pusat Dokumen Laporan BGN) -->
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 shrink-0">
                        <!-- Cetak / PDF ke Halaman Laporan -->
                        <a
                            href="{{ route('reports.index') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer"
                            title="Buka Pusat Laporan & Cetak PDF Resmi"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Cetak PDF</span>
                        </a>

                        <!-- Opsi Excel -->
                        <a
                            href="{{ route('reports.export-excel', ['type' => 'operasional']) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-teal-500/20 hover:bg-teal-500/30 text-teal-200 border border-teal-400/30 font-bold text-xs transition-all duration-200 hover:-translate-y-0.5"
                            title="Unduh Lembar Kerja Excel (.xls)"
                        >
                            <svg class="w-4 h-4 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Excel (.xls)</span>
                        </a>

                        <!-- Opsi CSV -->
                        <a
                            href="{{ route('reports.export-csv', ['type' => 'operasional']) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs border border-white/15 backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5"
                            title="Unduh Berkas CSV UTF-8"
                        >
                            <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>CSV</span>
                        </a>

                        <!-- Buka Halaman Laporan Lengkap -->
                        <a
                            href="{{ route('reports.index') }}"
                            class="p-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-slate-300 hover:text-white border border-white/15 transition cursor-pointer"
                            title="Buka Pusat Laporan Lengkap"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </a>
                        @if($isAdmin)
                            <a href="{{ route('users-rbac.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-md transition-all duration-200 hover:-translate-y-0.5">
                                <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Mitra (1.414)</span>
                            </a>
                        @endif
                        @if(in_array($role->value, ['admin', 'ahli_gizi', 'kepala_dapur']))
                            <a href="{{ route('menu-planning.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs border border-white/15 backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5">
                                <span>Menu</span>
                            </a>
                        @endif
                        @if(in_array($role->value, ['admin', 'kepala_dapur', 'asisten_lapangan', 'akuntan']))
                            <a href="{{ route('production-inventory.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs border border-white/15 backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5">
                                <span>Gudang</span>
                            </a>
                        @endif
                        @if(in_array($role->value, ['admin', 'tim_distribusi', 'guru_pic']))
                            <a href="{{ route('distribution.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs border border-white/15 backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5">
                                <span>Distribusi</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 6-Stage Operational Pipeline Stepper -->
                <div class="pt-5 border-t border-white/10">
                    <div class="flex items-center justify-between mb-3 text-xs">
                        <span class="font-bold text-slate-300 flex items-center gap-2 uppercase tracking-wider text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Alur Siklus Operasional MBG
                        </span>
                        <span class="text-slate-400 text-[11px] font-mono">6 Tahap Terintegrasi</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <a href="{{ route('users-rbac.index') }}" class="p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/40 transition group block">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1.5">
                                <span class="text-slate-400 group-hover:text-emerald-300 font-bold">01 • MITRA</span>
                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-bold">{{ $institutionsCount }} AKTIF</span>
                            </div>
                            <p class="text-xs font-bold text-white group-hover:text-emerald-300 transition">Sekolah & Posyandu</p>
                            <p class="text-[11px] text-slate-300/80 mt-1 font-mono">Kuota: <span class="text-white font-semibold">{{ number_format($totalAllocatedQuota) }}</span></p>
                        </a>

                        <a href="{{ route('menu-planning.index') }}" class="p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/40 transition group block">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1.5">
                                <span class="text-slate-400 group-hover:text-emerald-300 font-bold">02 • MENU</span>
                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-bold">{{ $todayMenu ? strtoupper($todayMenu->status) : 'KOSONG' }}</span>
                            </div>
                            <p class="text-xs font-bold text-white group-hover:text-emerald-300 transition">Perencanaan AKG</p>
                            <p class="text-[11px] text-slate-300/80 mt-1 font-mono"><span class="text-white font-semibold">{{ $todayMenu?->calories_kcal ?? 0 }}</span> kkal &bull; <span class="text-white font-semibold">{{ $todayMenu?->protein_gram ?? 0 }}g</span></p>
                        </a>

                        <a href="{{ route('production-inventory.index') }}" class="p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/40 transition group block">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1.5">
                                <span class="text-slate-400 group-hover:text-emerald-300 font-bold">03 • GUDANG</span>
                                <span class="px-1.5 py-0.5 rounded-md {{ $lowStockIngredients->count() > 0 ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300' }} font-bold">
                                    {{ $ingredients->count() }} ITEM
                                </span>
                            </div>
                            <p class="text-xs font-bold text-white group-hover:text-emerald-300 transition">Stok Bahan Baku</p>
                            <p class="text-[11px] text-slate-300/80 mt-1 font-mono"><span class="text-white font-semibold">{{ $lowStockIngredients->count() }}</span> Perlu Restock</p>
                        </a>

                        <a href="{{ route('production-inventory.index') }}" class="p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/40 transition group block">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1.5">
                                <span class="text-slate-400 group-hover:text-emerald-300 font-bold">04 • PRODUKSI</span>
                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-bold">{{ $variance >= 0 ? '+' . $variance : $variance }}</span>
                            </div>
                            <p class="text-xs font-bold text-white group-hover:text-emerald-300 transition">Realisasi Dapur</p>
                            <p class="text-[11px] text-slate-300/80 mt-1 font-mono"><span class="text-white font-semibold">{{ number_format($actualPortions) }}</span> Porsi Baik</p>
                        </a>

                        <a href="{{ route('quality-control.index') }}" class="p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/40 transition group block">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1.5">
                                <span class="text-slate-400 group-hover:text-emerald-300 font-bold">05 • SUHU QC</span>
                                <span class="px-1.5 py-0.5 rounded-md bg-teal-500/20 text-teal-300 font-bold">{{ $coreTemp }}°C</span>
                            </div>
                            <p class="text-xs font-bold text-white group-hover:text-emerald-300 transition">Keamanan Pangan</p>
                            <p class="text-[11px] text-slate-300/80 mt-1 font-mono">Standar: <span class="text-white font-semibold">≥75°C</span></p>
                        </a>

                        <a href="{{ route('distribution.index') }}" class="p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/40 transition group block">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1.5">
                                <span class="text-slate-400 group-hover:text-emerald-300 font-bold">06 • DISTRIBUSI</span>
                                <span class="px-1.5 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-bold">{{ $confirmedCount }}/{{ $totalDeliveries }}</span>
                            </div>
                            <p class="text-xs font-bold text-white group-hover:text-emerald-300 transition">Uji Organoleptik</p>
                            <p class="text-[11px] text-slate-300/80 mt-1 font-mono">PIC: <span class="text-white font-semibold">{{ $deliveryRate }}%</span> Verif</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== WORKSPACE PERSONALISASI SESUAI ROLE (SPESIFIK PERAN) ==================== -->
        @if($role->value === 'ahli_gizi')
            @include('dashboard.partials.role-ahli-gizi')
        @elseif($role->value === 'akuntan')
            @include('dashboard.partials.role-akuntan')
        @elseif($role->value === 'kepala_dapur')
            @include('dashboard.partials.role-kepala-dapur')
        @elseif($role->value === 'asisten_lapangan')
            @include('dashboard.partials.role-asisten-lapangan')
        @elseif($role->value === 'tim_distribusi')
            @include('dashboard.partials.role-tim-distribusi')
        @elseif($role->value === 'guru_pic')
            @include('dashboard.partials.role-guru-pic')
        @else
            @include('dashboard.partials.role-admin')
        @endif

        <!-- ==================== 4 KARTU METRIK UTAMA (BENTO KPI CARDS) ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

            <!-- KPI 1 -->
            <div class="gsap-stagger-card gsap-hover-card bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Realisasi & Selisih Porsi</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold font-mono text-slate-900 gsap-counter" data-target="{{ $actualPortions }}">0</span>
                            <span class="text-xs font-mono font-semibold text-slate-400">/ {{ number_format($plannedPortions) }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center animate-float-subtle shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Selisih Produksi:</span>
                    <span class="px-2.5 py-0.5 rounded-lg font-mono font-extrabold {{ $variance >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $variance >= 0 ? '+' : '' }}{{ $variance }} Porsi
                    </span>
                </div>
            </div>

            <!-- KPI 2 -->
            <div class="gsap-stagger-card gsap-hover-card bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Valuasi Gudang SPPG</span>
                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span class="text-xs font-mono font-bold text-slate-400">Rp</span>
                            <span class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 gsap-counter" data-target="{{ round($totalInventoryValue) }}">0</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center animate-float-subtle shrink-0" style="animation-delay: -1s;">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ $ingredients->count() }} Komoditas:</span>
                    <span class="px-2.5 py-0.5 rounded-lg font-extrabold {{ $lowStockIngredients->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $lowStockIngredients->count() > 0 ? $lowStockIngredients->count() . ' Perlu Restock' : 'Stok Aman' }}
                    </span>
                </div>
            </div>

            <!-- KPI 3 -->
            <div class="gsap-stagger-card gsap-hover-card bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Pemeriksaan Suhu HACCP</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold font-mono text-teal-700 gsap-counter" data-target="{{ $coreTemp }}" data-decimals="1">0</span>
                            <span class="text-sm font-mono font-bold text-teal-700">°C</span>
                            <span class="text-xs font-semibold text-slate-400">(Hold: {{ $holdingTemp }}°C)</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center animate-float-subtle shrink-0" style="animation-delay: -2s;">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Cek APD Relawan:</span>
                    <a href="{{ route('apd-inspection.index') }}" class="px-2.5 py-0.5 rounded-lg font-mono font-extrabold bg-emerald-100 text-emerald-800 hover:underline">{{ $apdScore }}% APD &rarr;</a>
                </div>
            </div>

            <!-- KPI 4 -->
            <div class="gsap-stagger-card gsap-hover-card bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Mitra & Organoleptik</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold font-mono text-indigo-600">{{ $institutionsCount }}</span>
                            <span class="text-xs font-semibold text-slate-500">Mitra ({{ number_format($totalAllocatedQuota) }} Porsi)</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center animate-float-subtle shrink-0" style="animation-delay: -3s;">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Terkonfirmasi PIC:</span>
                    <span class="px-2.5 py-0.5 rounded-lg font-extrabold bg-indigo-100 text-indigo-800">{{ $confirmedCount }}/{{ $totalDeliveries }} Pengiriman</span>
                </div>
            </div>
        </div>

        <!-- ==================== BARIS FILTER KOMPREHENSIF & PENCARIAN TELEMETRI ==================== -->
        <div class="gsap-stagger-card bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-soft">
            <form action="{{ route('dashboard') }}" method="GET" class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">Filter & Pencarian Telemetri Operasional</h2>
                            <p class="text-[11px] text-slate-500">Saring grafik, tabel, dan metrik berdasarkan rentang tanggal, kategori mitra, status siklus, atau kata kunci</p>
                        </div>
                    </div>

                    @if($search !== '' || $dateFrom || $dateTo || $institutionType !== 'all' || $menuStatus !== 'all')
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition flex items-center gap-1">
                            <span>&times; Reset Filter Aktif</span>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kata Kunci Pencarian</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Menu, bahan baku, surat jalan, sekolah..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs font-semibold focus:outline-none focus:border-emerald-500"
                        >
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs font-medium">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs font-medium">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Mitra (7 Titik)</label>
                        <select name="institution_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs font-bold truncate">
                            <option value="all" @selected($institutionId === 'all')>Semua Mitra (1.414 Porsi)</option>
                            @foreach($allInstitutions as $inst)
                                <option value="{{ $inst->id }}" @selected($institutionId == $inst->id)>{{ $inst->name }} ({{ $inst->default_quota }} Porsi)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kategori Penerima</label>
                        <select name="recipient_category" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs font-bold">
                            <option value="all" @selected($recipientCategory === 'all')>Semua Kategori</option>
                            <option value="siswa" @selected($recipientCategory === 'siswa')>Siswa (Sekolah & Ponpes)</option>
                            <option value="balita_bumil" @selected($recipientCategory === 'balita_bumil')>Ibu Hamil & Balita</option>
                            <option value="balita_busui" @selected($recipientCategory === 'balita_busui')>Ibu Menyusui & Balita</option>
                        </select>
                    </div>

                    <div class="lg:col-span-1">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-glow-emerald transition cursor-pointer">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ==================== BARIS 1 GRAFIK ANALYTICS (3 GRAFIK UTAMA) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Grafik 1: Tren Realisasi Produksi vs Target Kuota -->
            <div class="lg:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft gsap-scroll-reveal flex flex-col justify-between">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">1. Grafik Target vs Realisasi Porsi & Afkir</h3>
                        <p class="text-xs text-slate-500">Data historis produksi dapur dari tabel <code class="font-mono">production_runs</code></p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-bold">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-emerald-600"></span> Porsi Baik</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-xs bg-amber-400"></span> Target</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="chartProductionTrend"></canvas>
                </div>
            </div>

            <!-- Grafik 2: Distribusi Kuota per Kategori Mitra -->
            <div class="lg:col-span-3 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft gsap-scroll-reveal flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">2. Kuota per Jenjang Mitra</h3>
                    <p class="text-xs text-slate-500 mb-3">Proporsi SD, SMP, SMA, PAUD & Posyandu</p>
                    <div class="h-52">
                        <canvas id="chartCategoryQuota"></canvas>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Total Kuota Mitra:</span>
                    <strong class="font-mono text-slate-900">{{ number_format($totalAllocatedQuota) }} Porsi</strong>
                </div>
            </div>

            <!-- Grafik 3: Profil Gizi Makro Menu Aktif -->
            <div class="lg:col-span-3 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft gsap-scroll-reveal flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">3. Profil Gizi Makro (AKG)</h3>
                    <p class="text-xs text-slate-500 mb-3">Energi: <strong class="font-mono text-emerald-700">{{ $todayMenu?->calories_kcal ?? 0 }} kkal</strong></p>
                    <div class="h-52">
                        <canvas id="chartNutritionRadar"></canvas>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 grid grid-cols-4 gap-1 text-center font-mono text-[10px]">
                    <div class="bg-emerald-50 rounded-lg py-1 text-emerald-800 font-bold">P:{{ $todayMenu?->protein_gram ?? 0 }}g</div>
                    <div class="bg-amber-50 rounded-lg py-1 text-amber-800 font-bold">K:{{ $todayMenu?->carbs_gram ?? 0 }}g</div>
                    <div class="bg-indigo-50 rounded-lg py-1 text-indigo-800 font-bold">L:{{ $todayMenu?->fat_gram ?? 0 }}g</div>
                    <div class="bg-teal-50 rounded-lg py-1 text-teal-800 font-bold">S:{{ $todayMenu?->fiber_gram ?? 0 }}g</div>
                </div>
            </div>
        </div>

        <!-- ==================== BARIS 2 GRAFIK ANALYTICS (SUHU HACCP & VALUASI GUDANG) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Grafik 4: Tren Suhu Masak HACCP & Holding -->
            <div class="lg:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft gsap-scroll-reveal">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">4. Grafik Tren Suhu Keamanan Pangan (HACCP) & APD</h3>
                        <p class="text-xs text-slate-500">Pemantauan suhu inti masak (&ge;75°C) dan suhu holding (&ge;60°C) dari tabel <code class="font-mono">qc_inspections</code></p>
                    </div>
                    <a href="{{ route('quality-control.index') }}" class="text-xs font-bold text-teal-700 hover:underline">Buka Modul QC &rarr;</a>
                </div>
                <div class="h-60">
                    <canvas id="chartQcTempTrend"></canvas>
                </div>
            </div>

            <!-- Grafik 5: Valuasi Stok Bahan Baku per Kategori -->
            <div class="lg:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft gsap-scroll-reveal">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">5. Grafik Valuasi Persediaan Gudang per Kategori (Rp)</h3>
                        <p class="text-xs text-slate-500">Nilai aset stok aktif (<code class="font-mono">current_stock &times; unit_cost</code>) di tabel <code class="font-mono">ingredients</code></p>
                    </div>
                    <a href="{{ route('production-inventory.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Kelola Gudang &rarr;</a>
                </div>
                <div class="h-60">
                    <canvas id="chartStockValuation"></canvas>
                </div>
            </div>
        </div>

        <!-- ==================== PANEL EKSPLORASI DATA TERPADU (TABS + INSTANT SEARCH) ==================== -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft gsap-scroll-reveal space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex flex-wrap items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                    <button
                        type="button"
                        @click="activeDataTab = 'mitra'"
                        :class="activeDataTab === 'mitra' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition"
                    >
                        Mitra Sekolah & Posyandu ({{ $activeInstitutions->count() }})
                    </button>
                    <button
                        type="button"
                        @click="activeDataTab = 'gudang'"
                        :class="activeDataTab === 'gudang' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition"
                    >
                        Stok Bahan Baku ({{ $ingredients->count() }})
                    </button>
                    <button
                        type="button"
                        @click="activeDataTab = 'distribusi'"
                        :class="activeDataTab === 'distribusi' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition"
                    >
                        Pengiriman & Organoleptik ({{ $deliveries->count() }})
                    </button>
                </div>

                <!-- Instant Client-Side Filter -->
                <div class="relative w-full md:w-72">
                    <input
                        type="text"
                        x-model="localTableSearch"
                        placeholder="Ketik untuk memfilter baris tabel..."
                        class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs font-semibold focus:outline-none focus:border-emerald-500"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- TAB 1: MITRA SEKOLAH & POSYANDU -->
            <div x-show="activeDataTab === 'mitra'" x-transition>
                @if($activeInstitutions->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-4">Kode Mitra</th>
                                    <th class="py-3 px-4">Nama Sekolah / Posyandu</th>
                                    <th class="py-3 px-4">Jenjang</th>
                                    <th class="py-3 px-4">Alamat</th>
                                    <th class="py-3 px-4 text-right">Kuota Harian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($activeInstitutions as $inst)
                                    <tr
                                        x-show="localTableSearch === '' || '{{ strtolower($inst->code . ' ' . $inst->name . ' ' . $inst->type . ' ' . $inst->address) }}'.includes(localTableSearch.toLowerCase())"
                                        class="hover:bg-emerald-50/40 transition"
                                    >
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $inst->code }}</td>
                                        <td class="py-3.5 px-4 font-extrabold text-slate-900">{{ $inst->name }}</td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800">{{ $inst->type }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500">{{ $inst->address }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-700">{{ number_format($inst->default_quota) }} Porsi</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-10 text-center space-y-3 bg-slate-50/70 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm font-extrabold text-slate-800">Belum Ada Data Mitra Sekolah / Posyandu di Database</p>
                        <p class="text-xs text-slate-500">Administrator Pusat dapat menambahkan mitra penerima manfaat melalui menu Manajemen Mitra & RBAC.</p>
                        @if($isAdmin)
                            <a href="{{ route('users-rbac.index') }}" class="inline-flex px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold">+ Tambah Mitra Sekarang</a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- TAB 2: STOK BAHAN BAKU GUDANG -->
            <div x-show="activeDataTab === 'gudang'" x-transition x-cloak>
                @if($ingredients->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-4">SKU</th>
                                    <th class="py-3 px-4">Nama Bahan Baku</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4 text-right">Stok Aktual</th>
                                    <th class="py-3 px-4 text-right">Harga Satuan</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($ingredients as $ing)
                                    @php $low = $ing->isLowStock(); @endphp
                                    <tr
                                        x-show="localTableSearch === '' || '{{ strtolower($ing->sku . ' ' . $ing->name . ' ' . $ing->category) }}'.includes(localTableSearch.toLowerCase())"
                                        class="hover:bg-emerald-50/40 transition"
                                    >
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-600">{{ $ing->sku }}</td>
                                        <td class="py-3.5 px-4 font-extrabold text-slate-900">{{ $ing->name }}</td>
                                        <td class="py-3.5 px-4 uppercase text-[11px] font-semibold text-slate-600">{{ str_replace('_', ' ', $ing->category) }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono font-extrabold {{ $low ? 'text-amber-700' : 'text-slate-900' }}">{{ number_format($ing->current_stock, 1) }} {{ $ing->unit }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-600">Rp {{ number_format($ing->unit_cost, 0, ',', '.') }}</td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $low ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $low ? 'Restock' : 'Aman' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-10 text-center space-y-3 bg-slate-50/70 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm font-extrabold text-slate-800">Belum Ada Master Bahan Baku di Database</p>
                        <a href="{{ route('production-inventory.index') }}" class="inline-flex px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">+ Tambah Master Bahan Baku</a>
                    </div>
                @endif
            </div>

            <!-- TAB 3: PENGIRIMAN & UJI ORGANOLEPTIK -->
            <div x-show="activeDataTab === 'distribusi'" x-transition x-cloak>
                @if($deliveries->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-4">No. Surat Jalan</th>
                                    <th class="py-3 px-4">Mitra Tujuan</th>
                                    <th class="py-3 px-4">Armada</th>
                                    <th class="py-3 px-4 text-right">Porsi Dikirim</th>
                                    <th class="py-3 px-4">Status & Uji Organoleptik</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($deliveries as $deliv)
                                    <tr
                                        x-show="localTableSearch === '' || '{{ strtolower($deliv->waybill_number . ' ' . $deliv->institution->name . ' ' . $deliv->vehicle_plate_number) }}'.includes(localTableSearch.toLowerCase())"
                                        class="hover:bg-emerald-50/40 transition"
                                    >
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $deliv->waybill_number }}</td>
                                        <td class="py-3.5 px-4 font-extrabold text-slate-900">{{ $deliv->institution->name }}</td>
                                        <td class="py-3.5 px-4 font-mono text-slate-600">{{ $deliv->vehicle_plate_number }}</td>
                                        <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-700">{{ number_format($deliv->dispatched_portions) }}</td>
                                        <td class="py-3.5 px-4">
                                            @if($deliv->confirmation)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                                    Lulus Organoleptik ({{ $deliv->confirmation->arrival_temperature_c }}°C)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                                    {{ strtoupper($deliv->status) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-10 text-center space-y-3 bg-slate-50/70 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm font-extrabold text-slate-800">Belum Ada Surat Jalan Pengiriman di Database</p>
                        <a href="{{ route('distribution.index') }}" class="inline-flex px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold">+ Buka Modul Distribusi</a>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const prodLabels = @json($chartProductionLabels);
        const plannedData = @json($chartPlannedData);
        const actualData = @json($chartActualData);

        // 1. Chart Production Trend
        const ctxProd = document.getElementById('chartProductionTrend');
        if (ctxProd) {
            new Chart(ctxProd, {
                type: 'bar',
                data: {
                    labels: prodLabels.length ? prodLabels : ['Belum Ada Data Produksi'],
                    datasets: [
                        {
                            label: 'Porsi Aktual Baik',
                            data: actualData.length ? actualData : [0],
                            backgroundColor: 'rgba(146, 208, 93, 0.85)',
                            borderRadius: 10,
                            barPercentage: 0.5
                        },
                        {
                            label: 'Target Kuota Rencana',
                            data: plannedData.length ? plannedData : [0],
                            type: 'line',
                            borderColor: '#D1B06C',
                            backgroundColor: 'rgba(245, 158, 11, 0.15)',
                            borderWidth: 3,
                            pointBackgroundColor: '#D1B06C',
                            pointRadius: 4,
                            tension: 0.35
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Chart Category Quota
        const catLabels = @json($chartCategoryLabels);
        const catQuotas = @json($chartCategoryQuotas);
        const ctxCat = document.getElementById('chartCategoryQuota');
        if (ctxCat) {
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: catLabels.length ? catLabels : ['Belum Ada Mitra'],
                    datasets: [{
                        data: catQuotas.length ? catQuotas : [1],
                        backgroundColor: catQuotas.length ? ['#92D05D', '#B5E0EA', '#6189D3', '#D1B06C', '#9333ea'] : ['#1E3266'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                    }
                }
            });
        }

        // 3. Chart Nutrition Polar
        const ctxNutr = document.getElementById('chartNutritionRadar');
        if (ctxNutr) {
            new Chart(ctxNutr, {
                type: 'polarArea',
                data: {
                    labels: ['Protein (g)', 'Karbohidrat (g)', 'Lemak (g)', 'Serat (g)'],
                    datasets: [{
                        data: @json($nutritionChartData),
                        backgroundColor: [
                            'rgba(5, 150, 105, 0.78)',
                            'rgba(245, 158, 11, 0.78)',
                            'rgba(79, 70, 229, 0.78)',
                            'rgba(13, 148, 136, 0.78)'
                        ],
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                    },
                    scales: { r: { ticks: { display: false } } }
                }
            });
        }

        // 4. Chart QC Temperature Trend
        const qcLabels = @json($chartQcLabels);
        const qcCore = @json($chartQcCoreTemps ?? $chartQcCoreTemp);
        const qcHold = @json($chartQcHoldingTemp);
        const ctxQc = document.getElementById('chartQcTempTrend');
        if (ctxQc) {
            new Chart(ctxQc, {
                type: 'line',
                data: {
                    labels: qcLabels.length ? qcLabels : ['Standar HACCP'],
                    datasets: [
                        {
                            label: 'Suhu Inti Masak (°C)',
                            data: qcCore.length ? qcCore : [75],
                            borderColor: '#92D05D',
                            backgroundColor: 'rgba(5, 150, 105, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5
                        },
                        {
                            label: 'Suhu Holding (°C)',
                            data: qcHold.length ? qcHold : [60],
                            borderColor: '#B5E0EA',
                            backgroundColor: 'rgba(13, 148, 136, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } } },
                    scales: {
                        y: { suggestedMin: 40, suggestedMax: 95, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 5. Chart Stock Valuation
        const stockLabels = @json($chartStockCatLabels);
        const stockVals = @json($chartStockCatValues);
        const ctxStock = document.getElementById('chartStockValuation');
        if (ctxStock) {
            new Chart(ctxStock, {
                type: 'bar',
                data: {
                    labels: stockLabels.length ? stockLabels : ['Gudang Kosong'],
                    datasets: [{
                        label: 'Valuasi Stok (Rp)',
                        data: stockVals.length ? stockVals : [0],
                        backgroundColor: ['#92D05D', '#B5E0EA', '#D1B06C', '#6189D3', '#0284c7', '#9333ea'],
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
