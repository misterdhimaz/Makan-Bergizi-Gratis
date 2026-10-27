@extends('layouts.app')

@section('title', 'Manajemen Pusat: Mitra, SOP BGN & 7 Role RBAC')
@section('page_title', 'Manajemen Mitra, SOP & 7 Role RBAC')
@section('page_subtitle', 'Kendali otoritas 1.414 penerima manfaat, 7 peran RBAC, SOP BGN, dan audit trail')

@section('content')
<div x-data="{
    activeSection: '{{ request()->has('search_inst') || request()->has('inst_type') ? 'institutions' : 'all' }}',
    openUserModal: false,
    openEditUserModal: false,
    openInstModal: false,
    openEditInstModal: false,
    openSopModal: false,
    editSop: { id: '', param_key: '', param_name: '', param_value: '', unit: '', description: '' },
    editUser: { id: '', name: '', email: '', phone: '', role: 'ahli_gizi', beneficiary_institution_id: '' },
    editInst: { id: '', code: '', name: '', type: 'sd', recipient_category: 'siswa', default_quota: 250, address: '', contact_phone: '', pic_name: '', pic_phone: '' },
    openEditUser(user) {
        this.editUser = {
            id: user.id,
            name: user.name,
            email: user.email,
            phone: user.phone || '',
            role: user.role,
            beneficiary_institution_id: user.beneficiary_institution_id || ''
        };
        this.openEditUserModal = true;
    },
    openEditInstitution(inst) {
        this.editInst = {
            id: inst.id,
            code: inst.code,
            name: inst.name,
            type: inst.type,
            recipient_category: inst.recipient_category || 'siswa',
            default_quota: inst.default_quota,
            address: inst.address,
            contact_phone: inst.contact_phone || '',
            pic_name: inst.pic_name || '',
            pic_phone: inst.pic_phone || ''
        };
        this.openEditInstModal = true;
    },
    openEditSop(sop) {
        this.editSop = {
            id: sop.id,
            param_key: sop.param_key,
            param_name: sop.param_name,
            param_value: sop.param_value,
            unit: sop.unit || '',
            description: sop.description || ''
        };
        this.openSopModal = true;
    }
}">

    <!-- Hero Banner Otoritas Administrator Pusat -->
    <div class="gsap-reveal relative rounded-3xl overflow-hidden bg-gradient-to-r from-[#071E49] via-[#0A2A63] to-[#04122E] text-white p-6 lg:p-8 mb-6 shadow-xl border border-emerald-500/20">
        <div class="ambient-orb w-72 h-72 bg-emerald-500/20 -top-20 -right-10"></div>
        <div class="ambient-orb w-56 h-56 bg-amber-400/15 bottom-0 left-1/3" style="animation-delay: -5s;"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold uppercase tracking-widest mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Pusat Komando Administrator • Full Access Kendali SPPG
                </div>
                <h2 class="font-display font-extrabold text-2xl lg:text-3xl tracking-tight text-white">
                    Manajemen Mitra, 7 Role RBAC & Parameter SOP BGN
                </h2>
                <p class="text-xs sm:text-sm text-emerald-100/75 mt-1.5 max-w-2xl leading-relaxed">
                    Pusat kendali master data <strong class="text-white">1.414 Penerima Manfaat</strong> (5 Sekolah: 1 Pesantren, 4 SD & 2 Posyandu di Kecamatan Kalidoni), otorisasi <strong class="text-white">7 Peran Pengguna</strong>, parameter operasional standar BGN, serta jejak audit real-time.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button @click="openInstModal = true"
                        class="px-4 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/25 flex items-center gap-2 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    + Tambah Mitra Penerima
                </button>
                <button @click="openUserModal = true"
                        class="px-4 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    + Registrasi Personel (7 Role)
                </button>
            </div>
        </div>

        <!-- 4 KPI Strip -->
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3.5 mt-6 pt-6 border-t border-white/10">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-300/80">Total Personel Aktif</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $allUsers->where('is_active', true)->count() }} <span class="text-xs font-normal text-slate-300">/ {{ $allUsers->count() }} Akun</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-300/80">Mitra SPPG Kalidoni</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $allInstitutions->count() }} <span class="text-xs font-normal text-slate-300">Titik (5 Sek + 2 Pos)</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-300/80">Total Kuota Penerima</p>
                <p class="font-display font-extrabold text-2xl text-emerald-300 mt-1">{{ number_format($totalAllocatedQuota) }} <span class="text-xs font-normal text-slate-300">Porsi/Hari</span></p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-300/80">Parameter SOP BGN</p>
                <p class="font-display font-extrabold text-2xl text-white mt-1">{{ $sopConfigs->count() }} <span class="text-xs font-normal text-slate-300">Konfigurasi</span></p>
            </div>
        </div>
    </div>

    <!-- 2 Grafik Visualisasi Pusat (Personel per Role & Kuota Mitra) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <div class="gsap-reveal lg:col-span-6 surface-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">Grafik Struktur Organisasi</span>
                    <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Distribusi Personel per 7 Role RBAC</h3>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $allUsers->count() }} Personel</span>
            </div>
            <div class="h-60">
                <canvas id="rbacRoleChart"></canvas>
            </div>
        </div>

        <div class="gsap-reveal lg:col-span-6 surface-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">Grafik Kuota 1.414 Penerima Manfaat</span>
                    <h3 class="font-display font-extrabold text-base text-slate-900 mt-1.5">Alokasi Porsi per Titik Distribusi (Kecamatan Kalidoni)</h3>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $institutions->count() }} Titik</span>
            </div>
            <div class="h-60">
                <canvas id="instQuotaChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Section Switcher -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="inline-flex flex-wrap p-1.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs gap-1">
            <button @click="activeSection = 'all'"
                    :class="activeSection === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Tampilkan Semua Panel
            </button>
            <button @click="activeSection = 'institutions'"
                    :class="activeSection === 'institutions' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Mitra Penerima ({{ $institutions->count() }})
            </button>
            <button @click="activeSection = 'users'"
                    :class="activeSection === 'users' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Personel 7 Role RBAC ({{ $users->count() }})
            </button>
            <button @click="activeSection = 'sop'"
                    :class="activeSection === 'sop' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Konfigurasi SOP BGN & PAGU ({{ $sopConfigs->count() }})
            </button>
            <button @click="activeSection = 'audit'"
                    :class="activeSection === 'audit' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                Audit Trail & Log Aktivitas ({{ $auditLogs->count() }})
            </button>
        </div>

        @if($searchUser !== '' || $roleFilter !== 'all' || $userStatus !== 'all' || $searchInst !== '' || $instType !== 'all')
            <a href="{{ route('users-rbac.index') }}" class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold hover:bg-rose-100 transition">
                Reset Semua Filter Aktif
            </a>
        @endif
    </div>

    <!-- BAGIAN 1: MANAJEMEN MITRA PENERIMA MANFAAT (1.414 KUOTA) -->
    <div x-show="activeSection === 'all' || activeSection === 'institutions'" x-transition class="surface-card rounded-3xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 mb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-600">Master Data 1.414 Penerima Manfaat</span>
                </div>
                <h3 class="font-display font-extrabold text-lg text-slate-900 mt-1">Daftar Titik Distribusi Sekolah & Posyandu (SPPG Sungai Selayur 1)</h3>
                <p class="text-xs text-slate-500">5 Sekolah (1 Pesantren, 4 SD) dan 2 Posyandu di Kecamatan Kalidoni Kota Palembang dengan total alokasi 1.414 porsi/hari.</p>
            </div>

            <button @click="openInstModal = true"
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-sm flex items-center gap-2 self-start lg:self-auto transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Titik Distribusi Baru
            </button>
        </div>

        <!-- Filter & Search Bar Mitra -->
        <form method="GET" action="{{ route('users-rbac.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-6 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/70">
            <input type="hidden" name="search_user" value="{{ $searchUser }}">
            <input type="hidden" name="role" value="{{ $roleFilter }}">
            <input type="hidden" name="user_status" value="{{ $userStatus }}">

            <div class="md:col-span-6 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search_inst" value="{{ $searchInst }}" placeholder="Cari nama sekolah/posyandu, kode mitra, PIC, atau alamat..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium focus:border-emerald-500 focus:outline-none">
            </div>

            <div class="md:col-span-4">
                <select name="inst_type" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
                    <option value="all" {{ $instType === 'all' ? 'selected' : '' }}>Semua Jenjang Mitra (Pesantren, SD, SMP, SMA, Posyandu)</option>
                    <option value="pesantren" {{ $instType === 'pesantren' ? 'selected' : '' }}>Pondok Pesantren (Ponpes)</option>
                    <option value="sd" {{ $instType === 'sd' ? 'selected' : '' }}>Sekolah Dasar (SD)</option>
                    <option value="smp" {{ $instType === 'smp' ? 'selected' : '' }}>Sekolah Menengah Pertama (SMP)</option>
                    <option value="sma" {{ $instType === 'sma' ? 'selected' : '' }}>Sekolah Menengah Atas/Kejuruan (SMA/SMK)</option>
                    <option value="paud_tk" {{ $instType === 'paud_tk' ? 'selected' : '' }}>PAUD / Taman Kanak-Kanak (TK)</option>
                    <option value="posyandu" {{ $instType === 'posyandu' ? 'selected' : '' }}>Posyandu Balita & Ibu Hamil</option>
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filter Mitra
                </button>
            </div>
        </form>

        <!-- Tabel Mitra Sekolah & Posyandu -->
        @if($institutions->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h4 class="font-display font-bold text-base text-slate-800">Tidak Ada Mitra yang Sesuai Filter</h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-4">
                    Silakan ubah kata kunci pencarian atau reset filter untuk menampilkan kembali data mitra.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-4">Kode & Nama Titik Distribusi</th>
                            <th class="py-3.5 px-4">Jenjang & Sasaran Penerima</th>
                            <th class="py-3.5 px-4">Kuota Porsi</th>
                            <th class="py-3.5 px-4">PIC Lapangan</th>
                            <th class="py-3.5 px-4">Alamat Wilayah Kalidoni</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($institutions as $inst)
                            <tr class="hover:bg-emerald-50/30 transition">
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $inst->code }}</span>
                                    <p class="font-bold text-slate-900 text-sm mt-1">{{ $inst->name }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        @if($inst->type === 'pesantren')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">PESANTREN</span>
                                        @elseif($inst->type === 'posyandu')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">POSYANDU</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">{{ strtoupper($inst->type) }}</span>
                                        @endif
                                        <span class="text-[11px] font-semibold text-slate-600">{{ $inst->recipientCategoryLabel() }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-display font-extrabold text-sm text-emerald-700">{{ number_format($inst->default_quota) }}</span>
                                    <span class="text-slate-400">porsi/hari</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800 text-xs">{{ $inst->pic_name ?? 'Belum ditentukan' }}</p>
                                    <p class="text-[11px] font-mono text-slate-500">{{ $inst->pic_phone ?? $inst->contact_phone ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-slate-700 truncate text-xs" title="{{ $inst->address }}">{{ $inst->address }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($inst->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                @click="openEditInstitution({{ Js::from($inst) }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold text-[11px] transition cursor-pointer">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('users-rbac.toggle-institution', $inst) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-[11px] transition cursor-pointer">
                                                {{ $inst->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users-rbac.destroy-institution', $inst) }}" onsubmit="return confirm('Hapus mitra {{ $inst->name }} dari database?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- BAGIAN 2: MANAJEMEN PERSONEL 7 ROLE RBAC -->
    <div x-show="activeSection === 'all' || activeSection === 'users'" x-transition class="surface-card rounded-3xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 mb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-600">Direktori Akun & Otorisasi RBAC</span>
                </div>
                <h3 class="font-display font-extrabold text-lg text-slate-900 mt-1">Daftar Personel Operasional (7 Role Pengguna SPPG Sungai Selayur 1)</h3>
                <p class="text-xs text-slate-500">Hak akses terisolasi untuk Administrator, Ahli Gizi, Akuntan, Kepala Dapur, Asisten Lapangan, Tim Distribusi, dan Guru PIC.</p>
            </div>

            <button @click="openUserModal = true"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm flex items-center gap-2 self-start lg:self-auto transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Registrasi Personel Baru
            </button>
        </div>

        <!-- Filter & Search Bar Personel -->
        <form method="GET" action="{{ route('users-rbac.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-6 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/70">
            <input type="hidden" name="search_inst" value="{{ $searchInst }}">
            <input type="hidden" name="inst_type" value="{{ $instType }}">

            <div class="md:col-span-5 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search_user" value="{{ $searchUser }}" placeholder="Cari nama personel, email, atau nomor telepon..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium focus:border-emerald-500 focus:outline-none">
            </div>

            <div class="md:col-span-3">
                <select name="role" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
                    <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua 7 Role RBAC</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" {{ $roleFilter === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <select name="user_status" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none">
                    <option value="all" {{ $userStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ $userStatus === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $userStatus === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </form>

        <!-- Tabel Personel 7 Role -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="py-3.5 px-4">Personel & Kredensial</th>
                        <th class="py-3.5 px-4">Role RBAC</th>
                        <th class="py-3.5 px-4">Mitra Terkait (Guru PIC)</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4 text-right">Aksi Admin Pusat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($users as $u)
                        <tr class="hover:bg-emerald-50/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white font-display font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $u->name }}</p>
                                        <p class="text-[11px] text-slate-500 font-mono">{{ $u->email }} {{ $u->phone ? '• ' . $u->phone : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $u->role->badgeColor() }}">
                                    {{ $u->role->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->institution)
                                    <span class="font-semibold text-slate-800">{{ $u->institution->name }}</span>
                                @else
                                    <span class="text-slate-400">Internal SPPG Sungai Selayur 1</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                            @click="openEditUser({
                                                id: {{ $u->id }},
                                                name: {{ Js::from($u->name) }},
                                                email: {{ Js::from($u->email) }},
                                                phone: {{ Js::from($u->phone) }},
                                                role: '{{ $u->role->value }}',
                                                beneficiary_institution_id: '{{ $u->beneficiary_institution_id }}'
                                            })"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold text-[11px] transition cursor-pointer">
                                        Edit / Reset Sandi
                                    </button>

                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('users-rbac.toggle-user', $u) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-[11px] transition cursor-pointer">
                                                {{ $u->is_active ? 'Suspend' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users-rbac.destroy-user', $u) }}" onsubmit="return confirm('Hapus akun personel {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] font-bold text-emerald-600 px-2">Sesi Anda</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- BAGIAN 3: KONFIGURASI PARAMETER SOP BGN & PAGU ANGGARAN -->
    <div x-show="activeSection === 'all' || activeSection === 'sop'" x-transition class="surface-card rounded-3xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 mb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-indigo-600">Parameter SOP BGN & Regulasi Porsi</span>
                </div>
                <h3 class="font-display font-extrabold text-lg text-slate-900 mt-1">Konfigurasi Parameter Standar Operasional SPPG Sungai Selayur 1</h3>
                <p class="text-xs text-slate-500">Nilai ambang batas suhu HACCP, pagu anggaran per porsi, jumlah relawan dapur per shift, retensi food bank, dan batas waktu distribusi.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                {{ $sopConfigs->count() }} Parameter Terkonfigurasi
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($sopConfigs as $sop)
                <div class="p-5 rounded-2xl border border-slate-200/80 bg-white hover:border-indigo-400 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-bold uppercase tracking-wider">
                                {{ $sop->param_key }}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sop->category === 'pagu' ? 'bg-amber-100 text-amber-800' : ($sop->category === 'haccp_temp' ? 'bg-rose-100 text-rose-800' : ($sop->category === 'volunteer' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ strtoupper($sop->category) }}
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm mb-1.5">{{ $sop->param_name }}</h4>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $sop->description }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nilai Ambang SOP</span>
                            <span class="font-display font-extrabold text-lg text-indigo-900">
                                @if($sop->category === 'pagu')
                                    Rp {{ number_format((float)$sop->param_value, 0, ',', '.') }}
                                @else
                                    {{ $sop->param_value }}
                                @endif
                                <span class="text-xs font-normal text-slate-500">{{ $sop->unit }}</span>
                            </span>
                        </div>
                        <button type="button"
                                @click="openEditSop({{ Js::from($sop) }})"
                                class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition cursor-pointer">
                            Perbarui SOP
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- BAGIAN 4: AUDIT TRAIL / LOG AKTIVITAS SISTEM REAL-TIME (FITUR 2 DOKUMEN REKAGIZI) -->
    <div x-show="activeSection === 'all' || activeSection === 'audit'" x-transition class="surface-card rounded-3xl p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 mb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-teal-600">Audit Trail & Log Aktivitas (Fitur 2 Dokumen)</span>
                </div>
                <h3 class="font-display font-extrabold text-lg text-slate-900 mt-1">Rekam Jejak Aktivitas Operasional Terakhir (40 Transaksi Terkini)</h3>
                <p class="text-xs text-slate-500">Mencatat mutasi status menu, verifikasi PAGU, logging produksi ompreng, QC APD, penerimaan organoleptik, dan penanganan tiket keluhan.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $auditLogs->count() }} Entri Audit Tersedia</span>
        </div>

        @if($auditLogs->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                <p class="text-xs text-slate-500">Belum ada aktivitas tercatat pada log audit.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-4">Waktu (WIB)</th>
                            <th class="py-3.5 px-4">Personel & Role</th>
                            <th class="py-3.5 px-4">Modul & Aksi</th>
                            <th class="py-3.5 px-4">Deskripsi Aktivitas</th>
                            <th class="py-3.5 px-4 text-right">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-900">{{ $log->user_name }}</p>
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase">{{ str_replace('_', ' ', $log->role) }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-col gap-0.5 items-start">
                                        <span class="font-mono text-[10px] font-extrabold px-2 py-0.5 rounded bg-slate-100 text-slate-700 uppercase">
                                            {{ $log->action }}
                                        </span>
                                        <span class="text-[10px] text-slate-500">{{ $log->module }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-700 max-w-md">
                                    <p class="text-xs leading-relaxed">{{ $log->description }}</p>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-[11px] text-slate-400">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- MODAL 1: TAMBAH MITRA SEKOLAH / POSYANDU -->
    <template x-teleport="body">
        <div x-show="openInstModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openInstModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-auto max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-600">Otoritas Administrator Pusat</span>
                        <h3 class="font-display font-extrabold text-lg text-slate-900">Tambah Titik Penerima Manfaat Baru</h3>
                    </div>
                    <button @click="openInstModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" action="{{ route('users-rbac.store-institution') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Titik Distribusi</label>
                            <input type="text" name="code" required placeholder="Cth: SCH-SS1-006"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori / Jenjang</label>
                            <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                                <option value="pesantren">Pondok Pesantren (Ponpes)</option>
                                <option value="sd" selected>Sekolah Dasar (SD)</option>
                                <option value="smp">SMP / MTs</option>
                                <option value="sma">SMA / SMK / MA</option>
                                <option value="paud_tk">PAUD / TK</option>
                                <option value="posyandu">Posyandu</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sasaran Kategori Penerima</label>
                            <select name="recipient_category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                                <option value="siswa" selected>Siswa / Santri Sekolah</option>
                                <option value="balita_bumil">Balita & Ibu Hamil</option>
                                <option value="balita_busui">Balita & Ibu Menyusui</option>
                                <option value="umum">Penerima Manfaat Umum</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuota Porsi Harian</label>
                            <input type="number" name="default_quota" required min="10" value="250"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Resmi Sekolah / Posyandu</label>
                        <input type="text" name="name" required placeholder="Cth: SD Negeri 132 Palembang"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC Sekolah / Bidan</label>
                            <input type="text" name="pic_name" placeholder="Cth: Dra. Nurhayati"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Kontak WhatsApp PIC</label>
                            <input type="text" name="contact_phone" placeholder="0812xxxxxxx"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap Titik Distribusi</label>
                        <textarea name="address" rows="2" required placeholder="Jl. Mayor Zen, Kel. Sungai Selayur, Kec. Kalidoni, Palembang"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openInstModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold shadow-md cursor-pointer">Simpan Mitra ke Database</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: EDIT MITRA SEKOLAH / POSYANDU -->
    <template x-teleport="body">
        <div x-show="openEditInstModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openEditInstModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-auto max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Perbarui Data Titik Distribusi</span>
                        <h3 class="font-display font-extrabold text-lg text-slate-900" x-text="'Edit: ' + editInst.name"></h3>
                    </div>
                    <button @click="openEditInstModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" :action="'{{ url('/users-rbac/institution') }}/' + editInst.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Titik Distribusi</label>
                            <input type="text" name="code" x-model="editInst.code" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori / Jenjang</label>
                            <select name="type" x-model="editInst.type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                                <option value="pesantren">Pondok Pesantren (Ponpes)</option>
                                <option value="sd">Sekolah Dasar (SD)</option>
                                <option value="smp">SMP / MTs</option>
                                <option value="sma">SMA / SMK / MA</option>
                                <option value="paud_tk">PAUD / TK</option>
                                <option value="posyandu">Posyandu</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sasaran Kategori Penerima</label>
                            <select name="recipient_category" x-model="editInst.recipient_category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                                <option value="siswa">Siswa / Santri Sekolah</option>
                                <option value="balita_bumil">Balita & Ibu Hamil</option>
                                <option value="balita_busui">Balita & Ibu Menyusui</option>
                                <option value="umum">Penerima Manfaat Umum</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuota Porsi Harian</label>
                            <input type="number" name="default_quota" x-model="editInst.default_quota" required min="10"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Resmi Sekolah / Posyandu</label>
                        <input type="text" name="name" x-model="editInst.name" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC Sekolah / Bidan</label>
                            <input type="text" name="pic_name" x-model="editInst.pic_name" placeholder="Nama PIC"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Kontak WhatsApp PIC</label>
                            <input type="text" name="contact_phone" x-model="editInst.contact_phone"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea name="address" x-model="editInst.address" rows="2" required
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openEditInstModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 3: REGISTRASI PERSONEL BARU -->
    <template x-teleport="body">
        <div x-show="openUserModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openUserModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-auto max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Manajemen RBAC</span>
                        <h3 class="font-display font-extrabold text-lg text-slate-900">Registrasi Personel Baru (7 Role)</h3>
                    </div>
                    <button @click="openUserModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" action="{{ route('users-rbac.store-user') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" required placeholder="Cth: Siti Rahmawati, S.Gz"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi (Login)</label>
                            <input type="email" name="email" required placeholder="nama@sppg-selayur1.id"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone" placeholder="0812xxxxxxx"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Role Otorisasi (7 Role)</label>
                            <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password Awal (Min. 8 Karakter)</label>
                            <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Afiliasi Mitra Sekolah / Posyandu (Khusus Guru PIC)</label>
                        <select name="beneficiary_institution_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                            <option value="">-- Personel Internal Dapur SPPG Sungai Selayur 1 --</option>
                            @foreach($allInstitutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->code }} — {{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openUserModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md cursor-pointer">Daftarkan Personel</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 4: EDIT PERSONEL & RESET PASSWORD -->
    <template x-teleport="body">
        <div x-show="openEditUserModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openEditUserModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-auto max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Otoritas Administrator Pusat</span>
                        <h3 class="font-display font-extrabold text-lg text-slate-900" x-text="'Edit Personel: ' + editUser.name"></h3>
                    </div>
                    <button @click="openEditUserModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" :action="'{{ url('/users-rbac/user') }}/' + editUser.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="editUser.name" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Login</label>
                            <input type="email" name="email" x-model="editUser.email" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon</label>
                            <input type="text" name="phone" x-model="editUser.phone"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Role RBAC</label>
                            <select name="role" x-model="editUser.role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru (Opsional)</label>
                            <input type="password" name="password" minlength="8" placeholder="Kosongkan jika tetap"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Afiliasi Mitra Sekolah / Posyandu</label>
                        <select name="beneficiary_institution_id" x-model="editUser.beneficiary_institution_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                            <option value="">-- Personel Internal SPPG Sungai Selayur 1 --</option>
                            @foreach($allInstitutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->code }} — {{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openEditUserModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 5: PERBARUI PARAMETER SOP BGN -->
    <template x-teleport="body">
        <div x-show="openSopModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto">
            <div @click.away="openSopModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 my-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600">Konfigurasi SOP BGN</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900" x-text="editSop.param_name"></h3>
                    </div>
                    <button @click="openSopModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" :action="'{{ url('/users-rbac/sop-config') }}/' + editSop.id" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nilai Parameter Baru (<span x-text="editSop.unit"></span>)
                        </label>
                        <input type="text" name="param_value" x-model="editSop.param_value" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Dasar Regulasi SOP</label>
                        <textarea name="description" x-model="editSop.description" rows="3"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="openSopModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md cursor-pointer">Simpan Konfigurasi</button>
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
    const roleLabels = @json($chartRoleLabels);
    const roleCounts = @json($chartRoleCounts);

    const ctxRole = document.getElementById('rbacRoleChart');
    if (ctxRole) {
        new Chart(ctxRole, {
            type: 'bar',
            data: {
                labels: roleLabels,
                datasets: [{
                    label: 'Jumlah Personel',
                    data: roleCounts,
                    backgroundColor: [
                        'rgba(97, 137, 211, 0.9)',
                        'rgba(146, 208, 93, 0.85)',
                        'rgba(196, 154, 76, 0.85)',
                        'rgba(222, 193, 131, 0.85)',
                        'rgba(181, 224, 234, 0.85)',
                        'rgba(138, 168, 222, 0.85)',
                        'rgba(225, 29, 72, 0.85)'
                    ],
                    borderRadius: 10,
                    barThickness: 22
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(181, 224, 234, 0.07)' } },
                    y: { ticks: { font: { size: 10, weight: '600' } }, grid: { display: false } }
                }
            }
        });
    }

    const instLabels = @json($chartInstLabels);
    const instQuotas = @json($chartInstQuotas);
    const ctxInst = document.getElementById('instQuotaChart');
    if (ctxInst) {
        new Chart(ctxInst, {
            type: 'bar',
            data: {
                labels: instLabels.length ? instLabels : ['Belum Ada Mitra Terdaftar'],
                datasets: [{
                    label: 'Kuota Porsi / Hari',
                    data: instQuotas.length ? instQuotas : [0],
                    backgroundColor: 'rgba(209, 176, 108, 0.85)',
                    borderColor: '#C49A4C',
                    borderWidth: 1,
                    borderRadius: 10,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(181, 224, 234, 0.07)' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } }
                }
            }
        });
    }
});
</script>
@endpush
