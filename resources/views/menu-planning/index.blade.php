@extends('layouts.app')

@section('title', 'Perencanaan & Validasi Menu')
@section('page_title', 'Perencanaan & Validasi Multi-Tahap')
@section('page_subtitle', 'Alur validasi resmi: Ahli Gizi (Draf) → Akuntan (PAGU) → Kepala Dapur (Approval)')

@section('content')
<div x-data="{
    openCreateModal: false,
    openPaguModal: false,
    openApproveModal: false,
    activeMenuId: null,
    activeMenuCode: '',
    activeMenuName: '',
    activeCost: 0,
    operationalDate: '{{ $defaultOperationalDate ?? now()->format('Y-m-d') }}',
    menuCode: '{{ $nextMenuCode ?? 'MNU-SS1-' . now()->format('Ymd') . '-01' }}',
    isGeneratingCode: false,
    async updateMenuCode() {
        if (!this.operationalDate) return;
        this.isGeneratingCode = true;
        try {
            const res = await fetch(`{{ route('menu-planning.generate-code') }}?date=${encodeURIComponent(this.operationalDate)}`);
            const data = await res.json();
            if (data.menu_code) {
                this.menuCode = data.menu_code;
            }
        } catch (e) {
            const clean = this.operationalDate.replace(/-/g, '');
            this.menuCode = `MNU-SS1-${clean}-01`;
        } finally {
            this.isGeneratingCode = false;
        }
    },
    items: [{ ingredient_id: '', qty_per_portion: '', preparation_notes: '' }],
    addItem() {
        this.items.push({ ingredient_id: '', qty_per_portion: '', preparation_notes: '' });
    },
    removeItem(idx) {
        if (this.items.length > 1) {
            this.items.splice(idx, 1);
        }
    },
    openPagu(id, code, name, cost) {
        this.activeMenuId = id;
        this.activeMenuCode = code;
        this.activeMenuName = name;
        this.activeCost = cost;
        this.openPaguModal = true;
    },
    openApprove(id, code, name) {
        this.activeMenuId = id;
        this.activeMenuCode = code;
        this.activeMenuName = name;
        this.openApproveModal = true;
    }
}">

    <!-- Banner Ringkasan SOP Validasi Multi-Tahap -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="gsap-reveal surface-card rounded-3xl p-5 border-l-4 border-emerald-500">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 block">Tahap 1 &bull; Ahli Gizi</span>
            <h4 class="font-bold text-sm text-slate-900 mt-1">Penyusunan Menu & Gizi</h4>
            <p class="text-[11px] text-slate-500 mt-1">Echa Urbhita, S.Gz menyusun menu harian & kuota target 1.414 porsi.</p>
            <div class="mt-3 flex items-center justify-between text-[11px] font-mono">
                <span class="text-slate-400">Status Draf:</span>
                <span class="font-bold text-slate-800">{{ $statusCounts['draft'] ?? 0 }} Menu</span>
            </div>
        </div>

        <div class="gsap-reveal surface-card rounded-3xl p-5 border-l-4 border-sky-500">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-sky-600 block">Tahap 2 &bull; Akuntan SPPG</span>
            <h4 class="font-bold text-sm text-slate-900 mt-1">Verifikasi PAGU Anggaran</h4>
            <p class="text-[11px] text-slate-500 mt-1">Batas PAGU resmi BGN: <strong>Rp 15.000 / porsi</strong>.</p>
            <div class="mt-3 flex items-center justify-between text-[11px] font-mono">
                <span class="text-slate-400">Menunggu PAGU:</span>
                <span class="font-bold text-sky-700">{{ $statusCounts['pending_budget_review'] ?? 0 }} Menu</span>
            </div>
        </div>

        <div class="gsap-reveal surface-card rounded-3xl p-5 border-l-4 border-amber-500">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-600 block">Tahap 3 &bull; Kepala Dapur</span>
            <h4 class="font-bold text-sm text-slate-900 mt-1">Persetujuan & Komando</h4>
            <p class="text-[11px] text-slate-500 mt-1">Chef Hendra Wijaya mengesahkan menu untuk siklus 35 relawan dapur.</p>
            <div class="mt-3 flex items-center justify-between text-[11px] font-mono">
                <span class="text-slate-400">Menunggu Approval:</span>
                <span class="font-bold text-amber-700">{{ $statusCounts['pending_approval'] ?? 0 }} Menu</span>
            </div>
        </div>

        <div class="gsap-reveal surface-card rounded-3xl p-5 border-l-4 border-emerald-600 flex flex-col justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 block">Siap Produksi</span>
                <h4 class="font-bold text-sm text-slate-900 mt-1">Menu Telah Disetujui</h4>
                <p class="text-[11px] text-slate-500 mt-1">{{ $statusCounts['approved'] ?? 0 }} Menu aktif disahkan.</p>
            </div>
            @if(in_array(auth()->user()->role->value, ['admin', 'ahli_gizi']))
                <button @click="openCreateModal = true"
                        class="mt-3 w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md flex items-center justify-center gap-2 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    + Ajukan Menu Baru
                </button>
            @endif
        </div>
    </div>

    <!-- 2 Grafik Analitik Menu -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <div class="gsap-reveal lg:col-span-8 surface-card rounded-3xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">Kalkulasi AKG & Estimasi Biaya</span>
                    <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Tren Energi Kalori (kkal) & Protein (g) per Menu</h3>
                </div>
                <span class="text-xs font-semibold text-slate-500">Pagu Acuan: Rp 15.000 / porsi</span>
            </div>
            <div class="h-64">
                <canvas id="menuNutritionChart"></canvas>
            </div>
        </div>

        <div class="gsap-reveal lg:col-span-4 surface-card rounded-3xl p-6">
            <div class="mb-4">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">Distribusi Status Siklus</span>
                <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Status Alur Persetujuan</h3>
            </div>
            <div class="h-64">
                <canvas id="menuStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar Lengkap -->
    <div class="gsap-reveal surface-card rounded-3xl p-5 mb-6">
        <form method="GET" action="{{ route('menu-planning.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Menu / Kode</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau MNU-..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold focus:bg-white focus:border-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Alur</label>
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending_budget_review" {{ $status === 'pending_budget_review' ? 'selected' : '' }}>Review PAGU (Akuntan)</option>
                    <option value="pending_approval" {{ $status === 'pending_approval' ? 'selected' : '' }}>Menunggu Approval (Kepala Dapur)</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="in_production" {{ $status === 'in_production' ? 'selected' : '' }}>Sedang Diproduksi</option>
                    <option value="revision_needed" {{ $status === 'revision_needed' ? 'selected' : '' }}>Perlu Revisi</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status PAGU</label>
                <select name="pagu_status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
                    <option value="all" {{ $paguStatus === 'all' ? 'selected' : '' }}>Semua PAGU</option>
                    <option value="sesuai_pagu" {{ $paguStatus === 'sesuai_pagu' ? 'selected' : '' }}>Sesuai PAGU (&le; Rp15.000)</option>
                    <option value="melebihi_pagu" {{ $paguStatus === 'melebihi_pagu' ? 'selected' : '' }}>Melebihi PAGU (&gt; Rp15.000)</option>
                    <option value="pending" {{ $paguStatus === 'pending' ? 'selected' : '' }}>Belum Diverifikasi</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori Penerima</label>
                <select name="recipient_category" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
                    <option value="all" {{ $recipientCategory === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="siswa" {{ $recipientCategory === 'siswa' ? 'selected' : '' }}>Siswa (Sekolah & Pesantren)</option>
                    <option value="balita_bumil" {{ $recipientCategory === 'balita_bumil' ? 'selected' : '' }}>Ibu Hamil & Balita</option>
                    <option value="balita_busui" {{ $recipientCategory === 'balita_busui' ? 'selected' : '' }}>Ibu Menyusui & Balita</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
            </div>

            <div class="lg:col-span-1">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Siklus Menu Harian -->
    @if($menus->isEmpty())
        <div class="surface-card rounded-3xl p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="font-display font-extrabold text-lg text-slate-900">Belum Ada Jadwal Menu yang Cocok</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-5">
                Silakan ubah filter pencarian atau susun jadwal menu gizi baru untuk SPPG Sungai Selayur 1.
            </p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($menus as $menu)
                <div class="gsap-reveal surface-card rounded-3xl p-6 border border-slate-200/80 shadow-soft">
                    
                    <!-- Header Kartu Menu & Pipeline Status -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5 mb-2">
                                <span class="font-mono font-extrabold text-xs px-3 py-1 rounded-xl bg-teal-50 text-teal-900 border border-teal-300 dark:bg-teal-950/40 dark:text-teal-200 dark:border-teal-700/50">{{ $menu->menu_code }}</span>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                                    {{ $menu->operational_date->translatedFormat('l, d F Y') }}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ strtoupper($menu->recipient_category ?? 'siswa') }}
                                </span>
                                
                                <!-- PAGU Badge -->
                                @if($menu->pagu_status === 'sesuai_pagu')
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Sesuai PAGU
                                    </span>
                                @elseif($menu->pagu_status === 'melebihi_pagu')
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Melebihi PAGU
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        PAGU Menunggu Review
                                    </span>
                                @endif

                                <!-- Approval Status Badge -->
                                @php
                                    $statusBadge = match($menu->status) {
                                        'approved' => 'bg-emerald-500 text-white',
                                        'in_production' => 'bg-indigo-600 text-white',
                                        'completed' => 'bg-slate-800 text-white',
                                        'revision_needed' => 'bg-rose-600 text-white',
                                        'pending_approval' => 'bg-amber-500 text-slate-950 font-black',
                                        default => 'bg-slate-200 text-slate-800',
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase {{ $statusBadge }}">
                                    {{ $menu->status_label }}
                                </span>
                            </div>

                            <h3 class="font-display font-extrabold text-xl text-slate-900 leading-snug">{{ $menu->menu_name }}</h3>
                            
                            <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span>Disusun oleh: <strong class="text-slate-700">{{ $menu->nutritionist?->name ?? 'Echa Urbhita, S.Gz' }}</strong></span>
                                @if($menu->accountant)
                                    <span>&bull; PAGU Diverifikasi: <strong class="text-sky-700">{{ $menu->accountant->name }}</strong></span>
                                @endif
                                @if($menu->headOfKitchen)
                                    <span>&bull; Approved: <strong class="text-emerald-700">{{ $menu->headOfKitchen->name }}</strong></span>
                                @endif
                            </p>
                        </div>

                        <!-- Target Porsi & Biaya -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-right">
                                <p class="text-[10px] font-bold uppercase text-slate-400">Target Pemorsian</p>
                                <p class="font-display font-extrabold text-base text-slate-900">{{ number_format($menu->target_portions) }} Porsi</p>
                            </div>
                            <div class="px-4 py-2.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-right">
                                <p class="text-[10px] font-bold uppercase text-emerald-600">Estimasi Biaya/Porsi</p>
                                <p class="font-display font-extrabold text-base text-emerald-800">Rp {{ number_format($menu->estimated_cost_per_portion, 0, ',', '.') }}</p>
                            </div>

                            @if(in_array(auth()->user()->role->value, ['admin', 'ahli_gizi']))
                                <form method="POST" action="{{ route('menu-planning.destroy', $menu) }}" onsubmit="return confirm('Hapus jadwal menu {{ $menu->menu_code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition cursor-pointer" title="Hapus Menu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- 3-STAGE PIPELINE STEPPER -->
                    <div class="my-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/70">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2.5">Progress Siklus Validasi Multi-Tahap (SOP BGN):</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                            <!-- Stage 1 -->
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-[10px] shrink-0">1</div>
                                <div class="truncate">
                                    <span class="font-bold text-slate-800 block">Draf Menu Ahli Gizi</span>
                                    <span class="text-[10px] text-slate-500">Tersusun lengkap</span>
                                </div>
                            </div>

                            <!-- Stage 2 -->
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-6 h-6 rounded-full {{ $menu->pagu_status === 'sesuai_pagu' ? 'bg-emerald-500 text-white' : ($menu->pagu_status === 'melebihi_pagu' ? 'bg-rose-500 text-white' : 'bg-amber-400 text-slate-900') }} flex items-center justify-center font-bold text-[10px] shrink-0">2</div>
                                    <div class="truncate">
                                        <span class="font-bold text-slate-800 block">Validasi PAGU Akuntan</span>
                                        <span class="text-[10px] text-slate-500 truncate">{{ $menu->pagu_notes ?? 'Menunggu review' }}</span>
                                    </div>
                                </div>
                                @if(in_array(auth()->user()->role->value, ['admin', 'akuntan']))
                                    <button
                                        type="button"
                                        @click="openPagu({{ $menu->id }}, '{{ $menu->menu_code }}', '{{ addslashes($menu->menu_name) }}', {{ $menu->estimated_cost_per_portion }})"
                                        class="px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white font-bold text-[10px] shrink-0 cursor-pointer"
                                    >
                                        Validasi
                                    </button>
                                @endif
                            </div>

                            <!-- Stage 3 -->
                            <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-6 h-6 rounded-full {{ in_array($menu->status, ['approved', 'in_production', 'completed']) ? 'bg-emerald-500 text-white' : ($menu->status === 'revision_needed' ? 'bg-rose-500 text-white' : 'bg-slate-300 text-slate-700') }} flex items-center justify-center font-bold text-[10px] shrink-0">3</div>
                                    <div class="truncate">
                                        <span class="font-bold text-slate-800 block">Persetujuan Kepala Dapur</span>
                                        <span class="text-[10px] text-slate-500 truncate">{{ $menu->approval_notes ?? 'Menunggu PAGU selesai' }}</span>
                                    </div>
                                </div>
                                @if(in_array(auth()->user()->role->value, ['admin', 'kepala_dapur']))
                                    <button
                                        type="button"
                                        @click="openApprove({{ $menu->id }}, '{{ $menu->menu_code }}', '{{ addslashes($menu->menu_name) }}')"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-sm transition shrink-0 cursor-pointer"
                                    >
                                        Keputusan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Makronutrisi Strip -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 my-4">
                        <div class="p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                            <span class="text-[10px] font-bold uppercase text-emerald-600">Energi Total</span>
                            <p class="font-display font-extrabold text-base text-slate-900 mt-0.5">{{ $menu->calories_kcal }} <span class="text-xs font-normal">kkal</span></p>
                        </div>
                        <div class="p-3 rounded-2xl bg-teal-50/50 border border-teal-100">
                            <span class="text-[10px] font-bold uppercase text-teal-600">Protein</span>
                            <p class="font-display font-extrabold text-base text-slate-900 mt-0.5">{{ $menu->protein_gram }} <span class="text-xs font-normal">gram</span></p>
                        </div>
                        <div class="p-3 rounded-2xl bg-amber-50/50 border border-amber-100">
                            <span class="text-[10px] font-bold uppercase text-amber-600">Karbohidrat</span>
                            <p class="font-display font-extrabold text-base text-slate-900 mt-0.5">{{ $menu->carbs_gram }} <span class="text-xs font-normal">gram</span></p>
                        </div>
                        <div class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                            <span class="text-[10px] font-bold uppercase text-indigo-600">Lemak Total</span>
                            <p class="font-display font-extrabold text-base text-slate-900 mt-0.5">{{ $menu->fat_gram }} <span class="text-xs font-normal">gram</span></p>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase text-slate-500">Serat Pangan</span>
                            <p class="font-display font-extrabold text-base text-slate-900 mt-0.5">{{ $menu->fiber_gram }} <span class="text-xs font-normal">gram</span></p>
                        </div>
                    </div>

                    <!-- Komposisi Gramasi Bahan Baku -->
                    @if($menu->items->isNotEmpty())
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Komposisi Bahan Baku (Gramasi Standar Porsi MBG):</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($menu->items as $item)
                                    <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700">
                                        {{ $item->ingredient?->name ?? 'Bahan' }}:
                                        <strong class="text-emerald-700">{{ $item->total_planned_qty }} {{ $item->ingredient?->unit }}</strong>
                                        <span class="text-[10px] text-slate-400">({{ $item->qty_per_portion }} {{ $item->ingredient?->unit }}/porsi)</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL 1: SUSUN JADWAL MENU BARU (AHLI GIZI) -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openCreateModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-auto max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Ahli Gizi (Echa Urbhita, S.Gz)</span>
                        <h3 class="font-display font-extrabold text-lg text-slate-900">Susun Rencana Menu Harian Baru</h3>
                    </div>
                    <button @click="openCreateModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('menu-planning.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Operasional</label>
                            <input type="date" name="operational_date" required x-model="operationalDate" @change="updateMenuCode()"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Kode Menu</span>
                                <span class="text-[10px] font-extrabold uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Otomatis</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="menu_code" :value="menuCode" readonly
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100/90 font-mono font-extrabold text-xs text-emerald-800 cursor-not-allowed select-all focus:outline-none">
                                <div x-show="isGeneratingCode" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Dibuat otomatis oleh sistem SPPG</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Target Porsi (Total)</label>
                            <input type="number" name="target_portions" required min="10" value="1414"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Penerima Manfaat</label>
                            <select name="recipient_category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                                <option value="siswa">Siswa (5 Sekolah & Pesantren)</option>
                                <option value="balita_bumil">Ibu Hamil & Balita (Posyandu Melati)</option>
                                <option value="balita_busui">Ibu Menyusui & Balita (Posyandu Mawar)</option>
                                <option value="gabungan">Gabungan (Seluruh 1.414 Penerima)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Biaya Bahan per Porsi (Rp)</label>
                            <input type="number" name="estimated_cost_per_portion" required value="14850"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            <span class="text-[10px] text-slate-400">Batas PAGU resmi: Rp 15.000 / porsi</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Paket Menu Lengkap</label>
                        <input type="text" name="menu_name" required placeholder="Cth: Paket Gizi Nusantara: Nasi Pulen, Ayam Madu, Tempe Bacem, Sayur Buncis & Susu"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Energi (kkal)</label>
                            <input type="number" step="0.1" name="calories_kcal" required value="685"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Protein (g)</label>
                            <input type="number" step="0.1" name="protein_gram" required value="28.5"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Karbohidrat (g)</label>
                            <input type="number" step="0.1" name="carbs_gram" required value="84.0"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Lemak (g)</label>
                            <input type="number" step="0.1" name="fat_gram" required value="21.0"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Serat (g)</label>
                            <input type="number" step="0.1" name="fiber_gram" value="8.2"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>

                    <!-- Komposisi Bahan Baku -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700">Komposisi Bahan Baku (Gramasi Porsi)</label>
                            <button type="button" @click="addItem()" class="text-xs font-bold text-emerald-600 hover:underline cursor-pointer">+ Tambah Bahan</button>
                        </div>
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="grid grid-cols-12 gap-2 mb-2">
                                <div class="col-span-6">
                                    <select :name="'items['+idx+'][ingredient_id]'" x-model="item.ingredient_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-4">
                                    <input type="number" step="0.001" :name="'items['+idx+'][qty_per_portion]'" x-model="item.qty_per_portion" placeholder="Qty/Porsi"
                                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                </div>
                                <div class="col-span-2 flex items-center justify-center">
                                    <button type="button" @click="removeItem(idx)" class="text-xs text-rose-500 font-bold hover:underline cursor-pointer">Hapus</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Ajukan Menu ke Akuntan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: VERIFIKASI PAGU ANGGARAN (AKUNTAN SPPG) -->
    <template x-teleport="body">
        <div x-show="openPaguModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md">
            <div @click.away="openPaguModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-sky-600">Akuntan SPPG (Rina Wulandari, S.E.)</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Verifikasi Kesesuaian PAGU Anggaran</h3>
                    </div>
                    <button @click="openPaguModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <div class="p-3.5 rounded-2xl bg-sky-50 border border-sky-200 mb-4 text-xs">
                    <p class="font-bold text-sky-950" x-text="activeMenuCode + ' • ' + activeMenuName"></p>
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-sky-800">Estimasi Biaya per Porsi:</span>
                        <span class="font-mono font-extrabold text-sky-950" x-text="'Rp ' + Number(activeCost).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[11px]">
                        <span class="text-sky-800">Batas PAGU Resmi BGN:</span>
                        <span class="font-mono font-bold text-sky-950">Rp 15.000 / Porsi</span>
                    </div>
                </div>

                <form method="POST" :action="'{{ url('menu-planning') }}/' + activeMenuId + '/verify-pagu'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Hasil Pemeriksaan PAGU</label>
                        <select name="pagu_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            <option value="sesuai_pagu">SESUAI PAGU (Biaya &le; Rp15.000 - Diteruskan ke Kepala Dapur)</option>
                            <option value="melebihi_pagu">MELEBIHI PAGU (Biaya &gt; Rp15.000 - Kembalikan untuk Revisi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Akuntansi & Rekomendasi</label>
                        <textarea name="pagu_notes" rows="3" required placeholder="Tuliskan catatan hasil audit biaya bahan baku terhadap pagu anggaran..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openPaguModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Simpan Hasil Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 3: KEPUTUSAN APPROVAL KEPALA DAPUR -->
    <template x-teleport="body">
        <div x-show="openApproveModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md">
            <div @click.away="openApproveModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Kepala Dapur (Chef Hendra Wijaya)</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Persetujuan (Approval) Rencana Menu</h3>
                    </div>
                    <button @click="openApproveModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <p class="text-xs font-bold text-slate-800 mb-3" x-text="activeMenuCode + ' — ' + activeMenuName"></p>

                <form method="POST" :action="'{{ url('menu-planning') }}/' + activeMenuId + '/approve'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keputusan Kepala Dapur</label>
                        <select name="decision" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            <option value="approved">SETUJUI (Buka Sesi Masak & Jadwalkan Produksi 1.414 Porsi)</option>
                            <option value="in_production">SETUJUI & LANGSUNG EKSEKUSI DAPUR</option>
                            <option value="revision_needed">MINTA REVISI (Kembalikan ke Ahli Gizi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Instruksi Operasional Dapur</label>
                        <textarea name="approval_notes" rows="3" placeholder="Instruksi khusus pemorsian ompreng atau persiapan bumbu..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">Disetujui sesuai standar operasional dapur SPPG Sungai Selayur 1.</textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openApproveModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Sahkan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Chart 1: Gizi
        const ctxGizi = document.getElementById('menuNutritionChart');
        if (ctxGizi) {
            new Chart(ctxGizi, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        {
                            label: 'Energi Kalori (kkal)',
                            data: {!! json_encode($chartCalories) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                            borderColor: '#10B981',
                            borderWidth: 1.5,
                            borderRadius: 8,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Protein (g)',
                            data: {!! json_encode($chartProteins) !!},
                            backgroundColor: 'rgba(14, 165, 233, 0.75)',
                            borderColor: '#0EA5E9',
                            borderWidth: 1.5,
                            borderRadius: 8,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { type: 'linear', position: 'left', grid: { color: 'rgba(0,0,0,0.05)' } },
                        y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } }
                    }
                }
            });
        }

        // Chart 2: Status
        const ctxStatus = document.getElementById('menuStatusChart');
        if (ctxStatus) {
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Review PAGU', 'Approval Dapur', 'Disetujui', 'Produksi', 'Revisi'],
                    datasets: [{
                        data: [
                            {{ $statusCounts['pending_budget_review'] ?? 0 }},
                            {{ $statusCounts['pending_approval'] ?? 0 }},
                            {{ $statusCounts['approved'] ?? 0 }},
                            {{ $statusCounts['in_production'] ?? 0 }},
                            {{ $statusCounts['revision_needed'] ?? 0 }}
                        ],
                        backgroundColor: ['#0EA5E9', '#F59E0B', '#10B981', '#6366F1', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
</script>
@endpush
@endsection
