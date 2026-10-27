@extends('layouts.app')

@section('title', 'Distribusi, Konfirmasi & Tiket Keluhan')
@section('page_title', 'Distribusi Armada & Tiket Keluhan')
@section('page_subtitle', 'Monitoring pengiriman 1.414 porsi, uji organoleptik sekolah & penanganan keluhan')

@section('content')
<div x-data="{
    activeTab: 'deliveries',
    openWaybillModal: false,
    openComplaintModal: false,
    openResolveModal: false,
    resolveTicketId: null,
    resolveTicketNumber: '',
    resolveInstitutionName: '',
    openResolve(id, num, inst) {
        this.resolveTicketId = id;
        this.resolveTicketNumber = num;
        this.resolveInstitutionName = inst;
        this.openResolveModal = true;
    }
}">

    <!-- PERINGATAN PRIORITAS TIKET KELUHAN KRITIS (FITUR 7) -->
    @if(isset($criticalTickets) && $criticalTickets->count() > 0)
        <div class="mb-6 p-4 rounded-3xl bg-gradient-to-r from-rose-600 via-rose-700 to-red-800 text-white shadow-xl border border-rose-400 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 animate-pulse">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase tracking-wider">Perhatian Kritis: Terdapat {{ $criticalTickets->count() }} Keluhan Berstatus Kritis dari Sekolah!</h4>
                    <p class="text-[11px] text-rose-100">SOP BGN: Asisten Lapangan & Kepala Dapur wajib menindaklanjuti dan mengunggah bukti penyelesaian hari ini.</p>
                </div>
            </div>
            <button @click="activeTab = 'complaints'" class="px-4 py-2 rounded-2xl bg-white text-rose-950 font-black text-xs shadow-lg hover:bg-rose-50 transition shrink-0 cursor-pointer">
                Lihat Daftar Tiket Keluhan &rarr;
            </button>
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div class="gsap-reveal grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-emerald-500">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Total Surat Jalan</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl text-slate-900">{{ $deliveries->count() }}</h3>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                    {{ $deliveries->whereIn('status', ['confirmed_clean', 'confirmed_with_issue'])->count() }} Dikonfirmasi
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">7 Titik Tujuan Wilayah Kalidoni</p>
        </div>

        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-teal-500">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Total Porsi Dimuat</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl text-teal-700">{{ number_format($deliveries->sum('dispatched_portions')) }}</h3>
                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full">Target 1.414</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Berangkat dari Dapur SPPG</p>
        </div>

        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-amber-500">
            @php
                $totalReceived = $deliveries->sum(fn($d) => $d->confirmation?->received_good_portions ?? 0);
            @endphp
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Diterima Layak (PIC)</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl text-slate-900">{{ number_format($totalReceived) }}</h3>
                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">Uji Sensorik</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Konfirmasi Guru PIC & Posyandu</p>
        </div>

        <div class="surface-card rounded-3xl p-5 border-l-4 border-l-rose-500 flex flex-col justify-between">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Tiket Keluhan Digital</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="font-display font-extrabold text-3xl text-rose-600">{{ $complaintTickets->count() }}</h3>
                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full">
                    {{ $complaintTickets->where('status', '!=', 'resolved')->count() }} Terbuka
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Laporan kendala sekolah</p>
        </div>
    </div>

    <!-- TAB SWITCHER: MANIFES PENGIRIMAN VS TIKET KELUHAN -->
    <div class="flex items-center justify-between border-b border-slate-200 mb-6">
        <div class="flex gap-2">
            <button
                type="button"
                @click="activeTab = 'deliveries'"
                :class="activeTab === 'deliveries' ? 'border-emerald-600 text-emerald-700 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="py-3 px-4 border-b-2 text-xs uppercase tracking-wider transition cursor-pointer flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <span>Manifes Surat Jalan & Konfirmasi Sekolah ({{ $deliveries->count() }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'complaints'"
                :class="activeTab === 'complaints' ? 'border-rose-600 text-rose-700 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="py-3 px-4 border-b-2 text-xs uppercase tracking-wider transition cursor-pointer flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Tiket Keluhan Digital ({{ $complaintTickets->count() }})</span>
                @if($complaintTickets->where('status', '!=', 'resolved')->count() > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                @endif
            </button>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array(auth()->user()->role->value, ['admin', 'tim_distribusi', 'asisten_lapangan']))
                <button @click="openWaybillModal = true" class="py-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Terbitkan Surat Jalan</span>
                </button>
            @endif
            <button @click="openComplaintModal = true" class="py-2 px-3.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow transition cursor-pointer flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>+ Buat Tiket Keluhan</span>
            </button>
        </div>
    </div>

    <!-- ==================== TAB 1: MANIFES SURAT JALAN & KONFIRMASI SEKOLAH ==================== -->
    <div x-show="activeTab === 'deliveries'" class="space-y-6">

        <!-- 2 Grafik Distribusi -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="gsap-reveal lg:col-span-8 surface-card rounded-3xl p-6">
                <div class="mb-4">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">Grafik Rekonsiliasi Porsi</span>
                    <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Perbandingan Porsi Dikirim vs Diterima Baik per Mitra</h3>
                </div>
                <div class="h-64">
                    <canvas id="delivPortionChart"></canvas>
                </div>
            </div>

            <div class="gsap-reveal lg:col-span-4 surface-card rounded-3xl p-6">
                <div class="mb-4">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">Status Armada</span>
                    <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Proporsi Status Pengiriman</h3>
                </div>
                <div class="h-64">
                    <canvas id="delivStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="gsap-reveal surface-card rounded-3xl p-5">
            <form method="GET" action="{{ route('distribution.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Surat Jalan / Plat / Sekolah</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nomor SJ/... atau nama sekolah..."
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold focus:bg-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mitra Penerima</label>
                    <select name="institution_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700">
                        <option value="all" {{ $institutionId === 'all' ? 'selected' : '' }}>Semua Titik (5 Sekolah + 2 Posyandu)</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}" {{ (string)$institutionId === (string)$inst->id ? 'selected' : '' }}>{{ $inst->code }} — {{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pengiriman</label>
                    <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>Persiapan Muat</option>
                        <option value="in_transit" {{ $status === 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan</option>
                        <option value="arrived" {{ $status === 'arrived' ? 'selected' : '' }}>Tiba di Lokasi</option>
                        <option value="confirmed_clean" {{ $status === 'confirmed_clean' ? 'selected' : '' }}>Dikonfirmasi Bersih</option>
                        <option value="confirmed_with_issue" {{ $status === 'confirmed_with_issue' ? 'selected' : '' }}>Ada Catatan / Isu</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filter
                    </button>
                    @if($search !== '' || $status !== 'all' || $institutionId !== 'all')
                        <a href="{{ route('distribution.index') }}" class="py-2.5 px-3 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 font-bold text-xs">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Daftar Surat Jalan -->
        @if($deliveries->isEmpty())
            <div class="surface-card rounded-3xl p-12 text-center">
                <h3 class="font-display font-extrabold text-lg text-slate-900">Tidak Ada Surat Jalan Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Gunakan tombol "Terbitkan Surat Jalan" untuk membuat jadwal rute distribusi baru.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($deliveries as $delivery)
                    <div class="gsap-reveal surface-card rounded-3xl p-6 border border-slate-200/80 shadow-soft">
                        
                        <!-- Header Surat Jalan -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 mb-5 border-b border-slate-100">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="font-mono font-extrabold text-xs px-3 py-1 rounded-xl bg-teal-50 text-teal-900 border border-teal-300 dark:bg-teal-950/40 dark:text-teal-200 dark:border-teal-700/50">{{ $delivery->waybill_number }}</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold font-mono">
                                        Armada: {{ $delivery->vehicle_plate_number }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold uppercase">
                                        {{ $delivery->institution->recipient_category ?? 'siswa' }}
                                    </span>
                                    @php
                                        $dBadge = match($delivery->status) {
                                            'confirmed_clean' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'confirmed_with_issue' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'in_transit' => 'bg-sky-100 text-sky-800 border-sky-200 animate-pulse',
                                            'arrived' => 'bg-teal-100 text-teal-800 border-teal-200',
                                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase border {{ $dBadge }}">
                                        {{ str_replace('_', ' ', $delivery->status) }}
                                    </span>

                                    <!-- Indikator Keterlambatan Waktu Tiba (> 10:30 WIB) -->
                                    @if($delivery->arrived_at && $delivery->arrived_at->format('H:i') > $maxDeliveryTimeStr)
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 border border-rose-300 text-xs font-bold">
                                            Terlambat Tiba (&gt; {{ $maxDeliveryTimeStr }} WIB)
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-display font-extrabold text-xl text-slate-900">
                                    {{ $delivery->institution?->name }} ({{ $delivery->institution?->code }})
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Menu: <strong>{{ $delivery->productionRun?->dailyMenu?->menu_name }}</strong> &bull; Kurir: {{ $delivery->courier?->name ?? 'Tim Distribusi SPPG' }}
                                    @if($delivery->departed_at)
                                        &bull; Berangkat: <strong>{{ $delivery->departed_at->format('H:i') }} WIB</strong>
                                    @endif
                                    @if($delivery->arrived_at)
                                        &bull; Tiba: <strong>{{ $delivery->arrived_at->format('H:i') }} WIB</strong>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-right">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Porsi Dikirim</span>
                                    <span class="font-display font-extrabold text-base text-slate-900">{{ number_format($delivery->dispatched_portions) }} Porsi</span>
                                </div>
                                @if(auth()->user()->role->value === 'admin')
                                    <form method="POST" action="{{ route('distribution.destroy', $delivery) }}" onsubmit="return confirm('Hapus surat jalan {{ $delivery->waybill_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 cursor-pointer">✕</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- 2 Kolom: Kolom Kiri Tim Distribusi & Kolom Kanan Guru PIC -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            
                            <!-- Kolom Kiri: Form Tim Distribusi -->
                            <div class="lg:col-span-5 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center justify-between">
                                    <span>1. Update Status Tim Distribusi</span>
                                    <span class="text-[10px] font-normal text-slate-400">Armada</span>
                                </p>

                                @if(in_array(auth()->user()->role->value, ['admin', 'tim_distribusi', 'asisten_lapangan']))
                                    <form method="POST" action="{{ route('distribution.dispatch', $delivery) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Porsi Aktual Kirim</label>
                                                <input type="number" name="dispatched_portions" value="{{ $delivery->dispatched_portions }}" required min="1"
                                                       class="w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">No. Polisi Armada</label>
                                                <input type="text" name="vehicle_plate_number" value="{{ $delivery->vehicle_plate_number }}" required
                                                       class="w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Status Pengiriman</label>
                                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold">
                                                <option value="preparing" {{ $delivery->status === 'preparing' ? 'selected' : '' }}>Persiapan Muat Ompreng</option>
                                                <option value="in_transit" {{ $delivery->status === 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan Armada</option>
                                                <option value="arrived" {{ $delivery->status === 'arrived' ? 'selected' : '' }}>Armada Tiba di Sekolah/Posyandu</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Foto Bukti Serah Terima / Surat Jalan (Maks 2MB)</label>
                                            <input type="file" name="dispatch_photo" accept="image/*,.pdf" class="w-full text-xs text-slate-500">
                                            @if($delivery->dispatch_photo_path)
                                                <a href="{{ Storage::url($delivery->dispatch_photo_path) }}" class="text-[11px] text-emerald-600 font-bold hover:underline mt-1 inline-block">
                                                    → Lihat Bukti Foto Terunggah
                                                </a>
                                            @endif
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Catatan Perjalanan Kurir</label>
                                            <input type="text" name="courier_notes" value="{{ $delivery->courier_notes }}" placeholder="Kondisi lalu lintas atau jalur..."
                                                   class="w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs">
                                        </div>

                                        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition cursor-pointer shadow-sm">
                                            Perbarui Status Pengiriman
                                        </button>
                                    </form>
                                @else
                                    <div class="text-xs text-slate-600 space-y-1.5">
                                        <p>Armada: <strong>{{ $delivery->vehicle_plate_number }}</strong></p>
                                        <p>Porsi Kirim: <strong>{{ $delivery->dispatched_portions }} Porsi</strong></p>
                                        <p>Status: <span class="font-bold uppercase">{{ str_replace('_', ' ', $delivery->status) }}</span></p>
                                        @if($delivery->dispatch_photo_path)
                                            <a href="{{ Storage::url($delivery->dispatch_photo_path) }}" class="text-emerald-700 font-bold hover:underline block">
                                                → Lihat Berkas / Foto Surat Jalan
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Kolom Kanan: Form Konfirmasi Penerimaan & Uji Organoleptik Guru PIC -->
                            <div class="lg:col-span-7 p-4 rounded-2xl bg-white border border-slate-200/90">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-800 mb-3 flex items-center justify-between">
                                    <span>2. Konfirmasi Penerimaan & Uji Organoleptik Digital (Guru PIC)</span>
                                    <span class="text-[10px] font-normal text-slate-400">Sensorik Pangan</span>
                                </p>

                                @if(in_array(auth()->user()->role->value, ['admin', 'guru_pic', 'asisten_lapangan']))
                                    <form method="POST" action="{{ route('distribution.confirm', $delivery) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Porsi Baik Diterima</label>
                                                <input type="number" name="received_good_portions" value="{{ $delivery->confirmation?->received_good_portions ?? $delivery->dispatched_portions }}" required min="0"
                                                       class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-emerald-700">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Porsi Rusak / Kurang</label>
                                                <input type="number" name="received_damaged_or_missing_portions" value="{{ $delivery->confirmation?->received_damaged_or_missing_portions ?? 0 }}" required min="0"
                                                       class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-rose-600">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Suhu Tiba (°C)</label>
                                                <input type="number" step="0.1" name="arrival_temperature_c" value="{{ $delivery->confirmation?->arrival_temperature_c ?? 58.5 }}" required
                                                       class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-teal-700">
                                            </div>
                                        </div>

                                        <!-- 4 Parameter Uji Sensorik / Organoleptik -->
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Uji Aroma</label>
                                                <select name="aroma_test" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-bold">
                                                    <option value="normal_segar" {{ ($delivery->confirmation?->aroma_test ?? 'normal_segar') === 'normal_segar' ? 'selected' : '' }}>Normal Segar</option>
                                                    <option value="kurang_sedap" {{ ($delivery->confirmation?->aroma_test ?? '') === 'kurang_sedap' ? 'selected' : '' }}>Kurang Sedap</option>
                                                    <option value="basi_asam" {{ ($delivery->confirmation?->aroma_test ?? '') === 'basi_asam' ? 'selected' : '' }}>Basi / Asam</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Uji Rasa</label>
                                                <select name="taste_test" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-bold">
                                                    <option value="normal_lezat" {{ ($delivery->confirmation?->taste_test ?? 'normal_lezat') === 'normal_lezat' ? 'selected' : '' }}>Normal Lezat</option>
                                                    <option value="hambar" {{ ($delivery->confirmation?->taste_test ?? '') === 'hambar' ? 'selected' : '' }}>Hambar</option>
                                                    <option value="tidak_layak" {{ ($delivery->confirmation?->taste_test ?? '') === 'tidak_layak' ? 'selected' : '' }}>Tidak Layak</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Uji Visual/Warna</label>
                                                <select name="color_visual_test" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-bold">
                                                    <option value="cerah_normal" {{ ($delivery->confirmation?->color_visual_test ?? 'cerah_normal') === 'cerah_normal' ? 'selected' : '' }}>Cerah Normal</option>
                                                    <option value="pucat" {{ ($delivery->confirmation?->color_visual_test ?? '') === 'pucat' ? 'selected' : '' }}>Pucat</option>
                                                    <option value="berubah_warna" {{ ($delivery->confirmation?->color_visual_test ?? '') === 'berubah_warna' ? 'selected' : '' }}>Berubah Warna</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Uji Tekstur</label>
                                                <select name="texture_test" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-bold">
                                                    <option value="baik_sesuai" {{ ($delivery->confirmation?->texture_test ?? 'baik_sesuai') === 'baik_sesuai' ? 'selected' : '' }}>Baik & Sesuai</option>
                                                    <option value="terlalu_keras_lembek" {{ ($delivery->confirmation?->texture_test ?? '') === 'terlalu_keras_lembek' ? 'selected' : '' }}>Keras / Lembek</option>
                                                    <option value="berlendir" {{ ($delivery->confirmation?->texture_test ?? '') === 'berlendir' ? 'selected' : '' }}>Berlendir</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 items-center">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tingkat Isu</label>
                                                <select name="complaint_severity" class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold">
                                                    <option value="none" {{ ($delivery->confirmation?->complaint_severity ?? 'none') === 'none' ? 'selected' : '' }}>Tanpa Keluhan (Lulus)</option>
                                                    <option value="low" {{ ($delivery->confirmation?->complaint_severity ?? '') === 'low' ? 'selected' : '' }}>Keluhan Ringan</option>
                                                    <option value="medium" {{ ($delivery->confirmation?->complaint_severity ?? '') === 'medium' ? 'selected' : '' }}>Keluhan Sedang</option>
                                                    <option value="critical" {{ ($delivery->confirmation?->complaint_severity ?? '') === 'critical' ? 'selected' : '' }}>Keluhan Kritis</option>
                                                </select>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Catatan Konfirmasi Penerima</label>
                                                <input type="text" name="feedback_or_complaint" value="{{ $delivery->confirmation?->feedback_or_complaint }}" placeholder="Makanan diterima dalam kondisi hangat & baik..."
                                                       class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition cursor-pointer">
                                            Simpan Konfirmasi Penerimaan & Uji Organoleptik
                                        </button>
                                    </form>
                                @else
                                    @if($delivery->confirmation)
                                        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 text-xs">
                                            <p class="font-bold text-emerald-950">
                                                Diterima Baik: {{ $delivery->confirmation->received_good_portions }} Porsi (Selisih: {{ $delivery->confirmation->reception_variance }}) &bull; Suhu: {{ $delivery->confirmation->arrival_temperature_c }}°C
                                            </p>
                                            <p class="text-[11px] text-slate-600 mt-1">Uji Sensorik: Aroma ({{ $delivery->confirmation->aroma_test }}), Rasa ({{ $delivery->confirmation->taste_test }}), Tekstur ({{ $delivery->confirmation->texture_test }}).</p>
                                            <p class="text-[11px] text-slate-700 font-medium mt-1">Catatan: {{ $delivery->confirmation->feedback_or_complaint }}</p>
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-500 italic p-3 border border-slate-200 rounded-xl">Menunggu konfirmasi kedatangan & uji organoleptik dari Guru PIC {{ $delivery->institution->name }}.</p>
                                    @endif
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ==================== TAB 2: TIKET KELUHAN DIGITAL (FITUR 7) ==================== -->
    <div x-show="activeTab === 'complaints'" class="space-y-6">
        <div class="surface-card rounded-3xl p-6 border border-slate-200/80 shadow-soft">
            <div class="flex flex-wrap items-center justify-between gap-4 pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-display font-extrabold text-lg text-slate-900">Manajemen Tiket Keluhan & Pelaporan Masalah Sekolah</h3>
                    <p class="text-xs text-slate-500">Penanganan keluhan berstandar SOP BGN: Nomor tiket otomatis, bukti foto & audit penyelesaian</p>
                </div>
                <button @click="openComplaintModal = true" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Buat Tiket Keluhan Baru</span>
                </button>
            </div>

            @if($complaintTickets->isEmpty())
                <div class="text-center py-12 text-slate-500 text-xs">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="font-bold text-slate-800 text-sm">Nihil Tiket Keluhan Aktif</p>
                    <p class="text-slate-500 mt-0.5">Seluruh distribusi makanan di wilayah Kalidoni berjalan lancar dan diterima dengan baik oleh mitra sekolah.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-[10px] font-extrabold uppercase text-slate-400">
                                <th class="py-3 px-3">Nomor Tiket</th>
                                <th class="py-3 px-3">Sekolah / Mitra</th>
                                <th class="py-3 px-3">Kategori Masalah</th>
                                <th class="py-3 px-3">Keparahan</th>
                                <th class="py-3 px-3">Deskripsi Keluhan</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Penanganan Lapangan</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($complaintTickets as $tkt)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-3.5 px-3 font-mono font-bold text-slate-900">{{ $tkt->ticket_number }}</td>
                                    <td class="py-3.5 px-3">
                                        <p class="font-bold text-slate-900">{{ $tkt->institution->name }}</p>
                                        <span class="text-[10px] text-slate-400">Pelapor: {{ $tkt->reporter->name ?? 'Guru PIC' }}</span>
                                    </td>
                                    <td class="py-3.5 px-3 font-semibold text-slate-800">{{ $tkt->categoryLabel() }}</td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $tkt->severityBadge() }}">
                                            {{ $tkt->severity }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 max-w-xs text-slate-600">
                                        <p class="line-clamp-2">{{ $tkt->description }}</p>
                                        @if($tkt->photo_path)
                                            <a href="{{ Storage::url($tkt->photo_path) }}" class="text-[10px] text-rose-600 font-bold hover:underline mt-0.5 block">
                                                → Foto Bukti Keluhan
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3">
                                        @php
                                            $stClass = match($tkt->status) {
                                                'resolved' => 'bg-emerald-100 text-emerald-800',
                                                'in_progress' => 'bg-amber-100 text-amber-800',
                                                default => 'bg-rose-100 text-rose-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $stClass }}">
                                            {{ str_replace('_', ' ', $tkt->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 max-w-xs">
                                        @if($tkt->resolution_notes)
                                            <p class="text-[11px] text-slate-800 font-medium">{{ $tkt->resolution_notes }}</p>
                                            <span class="text-[10px] text-slate-400">Oleh: {{ $tkt->resolver->name ?? 'Aslap' }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Belum ditangani</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        @if(in_array(auth()->user()->role->value, ['admin', 'asisten_lapangan', 'kepala_dapur']))
                                            <button
                                                type="button"
                                                @click="openResolve({{ $tkt->id }}, '{{ $tkt->ticket_number }}', '{{ addslashes($tkt->institution->name) }}')"
                                                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs cursor-pointer shadow-sm transition"
                                            >
                                                Tindak Lanjuti
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL 1: TERBITKAN SURAT JALAN BARU -->
    <template x-teleport="body">
        <div x-show="openWaybillModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openWaybillModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-display font-extrabold text-base text-slate-900">Terbitkan Surat Jalan Baru</h3>
                    <button @click="openWaybillModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('distribution.store') }}" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Surat Jalan</label>
                            <input type="text" name="waybill_number" required value="SJ/MBG-SS1/{{ now()->format('Ymd') }}/{{ rand(100,999) }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Polisi Armada</label>
                            <input type="text" name="vehicle_plate_number" required value="BG 8122 KL"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Sesi Produksi Dapur</label>
                        <select name="production_run_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                            @foreach($productionRuns as $run)
                                <option value="{{ $run->id }}">{{ $run->dailyMenu?->menu_code }} — {{ $run->dailyMenu?->menu_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tujuan Mitra (7 Titik Wilayah Kalidoni)</label>
                        <select name="beneficiary_institution_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->code }} — {{ $inst->name }} (Kuota: {{ $inst->default_quota }} Porsi)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuota Alokasi Porsi</label>
                            <input type="number" name="allocated_portions" required value="260"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Porsi Aktual Dimuat</label>
                            <input type="number" name="dispatched_portions" required value="260"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Khusus Pengantaran</label>
                        <input type="text" name="courier_notes" placeholder="Contoh: Rute Mayor Zen, container bersegel..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="openWaybillModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold cursor-pointer shadow">Terbitkan Surat Jalan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: BUAT TIKET KELUHAN BARU (GURU PIC / SEKOLAH) -->
    <template x-teleport="body">
        <div x-show="openComplaintModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openComplaintModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-rose-600">Pelaporan Resmi Sekolah</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Buat Tiket Keluhan MBG Baru</h3>
                    </div>
                    <button @click="openComplaintModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sekolah / Posyandu Pelapor</label>
                        <select name="beneficiary_institution_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Keluhan</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                                <option value="porsi_kurang">Kekurangan Porsi / Tidak Cukup</option>
                                <option value="ompreng_rusak">Ompreng Rusak / Tutup Pecah</option>
                                <option value="keterlambatan">Keterlambatan Jam Tiba (&gt;10:30)</option>
                                <option value="basi_asam">Makanan Basi / Berbau Asam</option>
                                <option value="benda_asing">Temuan Benda Asing</option>
                                <option value="lainnya">Keluhan Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Keparahan</label>
                            <select name="severity" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                                <option value="rendah">Rendah (Pemberitahuan)</option>
                                <option value="sedang">Sedang (Perlu Pergantian)</option>
                                <option value="kritis">KRITIS (Pemicu Notifikasi Darurat)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Rinci Kendala Lapangan</label>
                        <textarea name="description" rows="3" required placeholder="Jelaskan secara spesifik temuan, jumlah porsi terdampak, dsb..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bukti Keluhan (Maks 2MB)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="openComplaintModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold cursor-pointer shadow">Kirim Tiket Keluhan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 3: TINDAK LANJUTI TIKET KELUHAN (ASLAP / KEPALA DAPUR) -->
    <template x-teleport="body">
        <div x-show="openResolveModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openResolveModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl my-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Tindak Lanjut Aslap & Kepala Dapur</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Penyelesaian Tiket Keluhan</h3>
                    </div>
                    <button @click="openResolveModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 mb-4 text-xs">
                    <p class="font-mono font-bold text-slate-900" x-text="'Nomor Tiket: ' + resolveTicketNumber"></p>
                    <p class="text-slate-600 mt-0.5" x-text="'Mitra: ' + resolveInstitutionName"></p>
                </div>

                <form method="POST" :action="'{{ url('complaints') }}/' + resolveTicketId + '/resolve'" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Penanganan</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            <option value="in_progress">Dalam Proses Tindak Lanjut</option>
                            <option value="resolved">SELESAI / RESOLVED (Masalah Tuntas)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tindakan Penyelesaian yang Dilakukan</label>
                        <textarea name="resolution_notes" rows="3" required placeholder="Contoh: Mengirimkan ompreng cadangan steril, mengecek driver rute..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bukti Penyelesaian (Opsional, Maks 2MB)</label>
                        <input type="file" name="resolution_photo" accept="image/*" class="w-full text-xs text-slate-500">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="openResolveModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer shadow-sm transition">Simpan Penyelesaian</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const labels = @json($chartDelivLabels);
    const dispatched = @json($chartDispatched);
    const received = @json($chartReceivedGood);

    const ctxPortion = document.getElementById('delivPortionChart');
    if (ctxPortion) {
        new Chart(ctxPortion, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Belum Ada Pengiriman'],
                datasets: [
                    {
                        label: 'Porsi Dikirim',
                        data: dispatched.length ? dispatched : [0],
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderRadius: 8
                    },
                    {
                        label: 'Diterima Baik (PIC)',
                        data: received.length ? received : [0],
                        backgroundColor: 'rgba(14, 165, 233, 0.75)',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const statusDist = @json(array_values($statusDistribution));
    const totalSt = statusDist.reduce((a, b) => a + b, 0);
    const ctxSt = document.getElementById('delivStatusChart');
    if (ctxSt) {
        new Chart(ctxSt, {
            type: 'doughnut',
            data: {
                labels: totalSt > 0 ? ['Persiapan', 'Perjalanan', 'Tiba', 'Bersih', 'Ada Keluhan'] : ['Belum Ada Data'],
                datasets: [{
                    data: totalSt > 0 ? statusDist : [1],
                    backgroundColor: totalSt > 0 ? ['#5D7090', '#6189D3', '#D1B06C', '#10B981', '#f43f5e'] : ['#1E3266'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
@endpush
