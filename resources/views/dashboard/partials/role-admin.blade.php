@php
    $schoolInstitutionCount = $schoolInstitutionCount ?? $allInstitutions
        ->whereIn('type', ['pesantren', 'sd', 'smp', 'sma', 'paud_tk'])
        ->count();
    $posyanduInstitutionCount = $posyanduInstitutionCount ?? $allInstitutions
        ->where('type', 'posyandu')
        ->count();
@endphp

<!-- RINGKASAN ADMINISTRATOR SISTEM -->
<div class="role-summary-admin surface-card rounded-3xl p-6 sm:p-7 border-l-4 border-l-indigo-600 shadow-soft">
    <div class="role-summary-admin__header flex flex-col gap-5 pb-6 border-b border-slate-100 mb-6">
        <div class="role-summary-admin__identity flex items-start gap-2.5">
            <div class="min-w-0">
                <div class="role-summary-admin__title-row">
                    <div class="role-summary-icon flex shrink-0 items-center justify-center" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 5 6v5c0 4.5 3 7.5 7 10 4-2.5 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="font-display font-extrabold text-xl text-slate-900">
                        Administrator Sistem
                    </h3>
                </div>
                <p class="role-summary-admin__description text-sm text-slate-600 mt-1" title="Mengelola akun dan data master, serta menjaga konfigurasi dan audit sistem.">
                    Mengelola akun dan data master, serta menjaga konfigurasi dan audit sistem.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Administrator -->
        <div class="role-summary-admin__actions flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('users-rbac.index') }}" class="role-summary-admin__action inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Kelola Mitra & RBAC
            </a>
            <a href="{{ route('reports.index', ['type' => 'kinerja_role']) }}" class="role-summary-admin__action inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan Kinerja 7 Role
            </a>
            <a href="{{ route('settings.appearance') }}" class="role-summary-admin__action inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2M5.636 5.636l1.414 1.414m9.9 9.9 1.414 1.414M3 12h2m14 0h2M5.636 18.364l1.414-1.414m9.9-9.9 1.414-1.414M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Branding & Tampilan
            </a>
        </div>
    </div>

    <!-- 4 KARTU BENTO KHUSUS ADMIN -->
    <div class="role-summary-admin__stats grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="role-summary-admin__stat min-w-0 rounded-xl bg-slate-50 p-4">
            <span class="role-summary-admin__label" title="Total kuota sasaran">Total kuota sasaran</span>
            <p class="role-summary-admin__value tabular-nums">{{ number_format($grandTotalQuota, 0, ',', '.') }} <span class="role-summary-admin__unit">porsi/hari</span></p>
            <p class="role-summary-admin__detail" title="{{ number_format($schoolInstitutionCount, 0, ',', '.') }} sekolah · {{ number_format($posyanduInstitutionCount, 0, ',', '.') }} posyandu aktif">{{ number_format($schoolInstitutionCount, 0, ',', '.') }} sekolah · {{ number_format($posyanduInstitutionCount, 0, ',', '.') }} posyandu aktif</p>
        </div>

        <div class="role-summary-admin__stat min-w-0 rounded-xl bg-slate-50 p-4">
            <span class="role-summary-admin__label" title="Personel & relawan aktif">Personel & relawan aktif</span>
            <p class="role-summary-admin__value tabular-nums">{{ number_format($volunteersCount, 0, ',', '.') }} <span class="role-summary-admin__unit">relawan</span></p>
            <p class="role-summary-admin__detail" title="7 role aktif · RBAC terisolasi sesuai SOP BGN">7 role aktif</p>
        </div>

        <div class="role-summary-admin__stat min-w-0 rounded-xl bg-slate-50 p-4">
            <span class="role-summary-admin__label" title="Valuasi gudang & PAGU">Valuasi gudang & PAGU</span>
            <p class="role-summary-admin__value tabular-nums"><span class="role-summary-admin__unit">Rp</span> {{ number_format($totalInventoryValue, 0, ',', '.') }}</p>
            <p class="role-summary-admin__detail" title="Rp {{ number_format($paguLimit, 0, ',', '.') }}/porsi · {{ number_format($ingredients->count(), 0, ',', '.') }} komoditas">Rp {{ number_format($paguLimit, 0, ',', '.') }}/porsi · {{ number_format($ingredients->count(), 0, ',', '.') }} komoditas</p>
        </div>

        <div class="role-summary-admin__stat min-w-0 rounded-xl bg-slate-50 p-4">
            <span class="role-summary-admin__label" title="Status distribusi">Status distribusi</span>
            <p class="role-summary-admin__value tabular-nums">{{ number_format($confirmedCount, 0, ',', '.') }}<span class="role-summary-admin__ratio"> / {{ number_format($totalDeliveries, 0, ',', '.') }}</span> <span class="role-summary-admin__unit">terkonfirmasi</span></p>
            <p class="role-summary-admin__detail" title="Tingkat verifikasi: {{ $deliveryRate }}%">Tingkat verifikasi: {{ $deliveryRate }}%</p>
        </div>
    </div>
</div>
