@php
    $hour = (int) now('Asia/Jakarta')->format('G');
    $salam = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $nameParts = preg_split('/\s+/', trim((string) auth()->user()->name)) ?: [];
    $firstName = collect($nameParts)->first(
        static fn (string $part): bool => mb_strlen($part) > 1 && ! str_ends_with($part, '.')
    ) ?? ($nameParts[0] ?? 'Admin');
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $friendlyAction = static fn (string $action): string => match ($action) {
        'login' => 'Masuk ke sistem',
        'login_gagal' => 'Gagal masuk',
        'logout' => 'Keluar dari sistem',
        'tambah' => 'Tambah data',
        'ubah' => 'Ubah data',
        'aktifkan' => 'Aktifkan',
        'nonaktifkan' => 'Nonaktifkan',
        'reset_sandi' => 'Reset kata sandi',
        'ubah_pengaturan' => 'Ubah pengaturan',
        'revisi_data', 'revisi' => 'Revisi data',
        'backup_manual' => 'Backup manual',
        'backup_terjadwal' => 'Backup terjadwal',
        'backup_pre_restore' => 'Backup pengaman',
        'unduh_backup' => 'Unduh backup',
        'restore', 'restore_gagal' => $action === 'restore' ? 'Pulihkan basis data' : 'Pemulihan gagal',
        'lupa_sandi' => 'Permintaan reset sandi',
        default => ucfirst(str_replace('_', ' ', $action)),
    };
@endphp

<x-layouts.admin
    :title="$salam.', '.$firstName"
    breadcrumb="Administrator Sistem · SPPG Sungai Selayur 1"
>
    <x-slot:actions>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">person_add</span>
            Tambah Pengguna
        </a>
    </x-slot:actions>

    {{-- Kartu KPI ringkas --}}
    <section aria-label="Ringkasan indikator" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
        <x-stat-card
            label="Akun aktif"
            :value="$fmt($metrics['akun_aktif'])"
            :hint="$fmt($metrics['akun_nonaktif']).' dinonaktifkan'"
            badge="Hari ini"
            badge-variant="neutral"
            highlight
        />
        <x-stat-card
            label="Peran terpakai"
            :value="$fmt($metrics['peran_terpakai']).' / '.$fmt($metrics['peran_total'])"
            hint="Lengkap"
            icon="groups"
        />
        <x-stat-card
            label="Data master siap"
            :value="$fmt($metrics['data_master_siap']).' / '.$fmt($metrics['data_master_total'])"
            :hint="$fmt($metrics['data_master_total'] - $metrics['data_master_siap']).' perlu dilengkapi'"
            icon="database"
        />
        <x-stat-card
            label="Login gagal 24 jam"
            :value="$fmt($metrics['login_gagal_24_jam'])"
            hint="Audit keamanan"
            icon="shield"
        />
    </section>

    <section aria-label="Status backup" class="bg-surface rounded-xl border border-border-soft p-lg flex flex-wrap items-center justify-between gap-md">
        <div class="flex items-center gap-md min-w-0">
            <span class="material-symbols-outlined text-section-title text-ink-muted" aria-hidden="true">backup</span>
            <div>
                <p class="font-body text-label-sm text-ink">Backup terakhir</p>
                <p class="font-body text-micro text-ink-muted">
                    @if($metrics['last_backup'])
                        {{ $metrics['last_backup']->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                        · {{ $metrics['last_backup']->trigger_type === 'scheduled' ? 'Otomatis' : 'Manual' }}
                    @else
                        Belum ada cadangan tercatat
                    @endif
                </p>
            </div>
        </div>
        <x-badge-status
            :label="$metrics['last_backup']?->status === 'success' ? 'Berhasil' : 'Belum ada'"
            :variant="$metrics['last_backup']?->status === 'success' ? 'success' : 'neutral'"
            :dot="true"
        />
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-lg">
        {{-- Akun per peran --}}
        <section aria-labelledby="akun-peran-heading" class="xl:col-span-5 bg-surface rounded-xl border border-border-soft p-xl flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 id="akun-peran-heading" class="font-display text-section-title text-ink">Akun per peran</h2>
                <a href="{{ route('admin.users.index') }}" class="font-body text-micro text-ink-muted hover:text-ink">Kelola ›</a>
            </div>
            <ul class="flex flex-col gap-sm">
                @foreach($metrics['akun_per_peran'] as $roleKey => $row)
                    <li class="flex items-center justify-between gap-md py-sm border-b border-border-soft last:border-0">
                        <div class="min-w-0">
                            <p class="font-body text-body-md text-ink truncate">{{ $row['label'] }}</p>
                            <p class="font-body text-micro text-ink-muted">{{ $fmt($row['aktif']) }} aktif · {{ $fmt($row['total']) }} total</p>
                        </div>
                        <span class="font-mono text-card-title tabular-nums text-ink">{{ $fmt($row['aktif']) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Kelengkapan data master --}}
        <section aria-labelledby="master-heading" class="xl:col-span-7 bg-surface rounded-xl border border-border-soft p-xl flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 id="master-heading" class="font-display text-section-title text-ink">Kelengkapan data master</h2>
                <span class="font-body text-micro text-ink-muted">{{ $fmt($metrics['data_master_siap']) }} / {{ $fmt($metrics['data_master_total']) }} siap</span>
            </div>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
                @foreach($metrics['data_master_items'] as $item)
                    <li class="flex items-start gap-sm p-md rounded-lg bg-surface-subtle border border-border-soft">
                        <span class="material-symbols-outlined text-card-title {{ $item['complete'] ? 'text-success' : 'text-on-alert-soft' }}" aria-hidden="true">
                            {{ $item['complete'] ? 'check_circle' : 'pending' }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-body text-label-sm text-ink">{{ $item['label'] }}</p>
                            <p class="font-body text-micro text-ink-muted">{{ $item['detail'] }}</p>
                            @unless($item['complete'])
                                <x-badge-status label="Perlu dilengkapi" variant="alert" class="mt-xs" />
                            @endunless
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    @if($showOperasional)
        <section aria-labelledby="ops-heading" class="bg-surface rounded-xl border border-border-soft p-xl flex flex-col gap-lg">
            <div class="flex flex-wrap items-center justify-between gap-md">
                <div>
                    <h2 id="ops-heading" class="font-display text-section-title text-ink">Ringkasan operasional hari ini</h2>
                    <p class="font-body text-micro text-ink-muted">Hanya lihat · {{ \Carbon\Carbon::parse($metrics['operational_date'])->translatedFormat('d M Y') }} · {{ $fmt($metrics['jumlah_lokasi']) }} lokasi</p>
                </div>
                <x-badge-status label="Hanya lihat" variant="neutral" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-md">
                <x-stat-card label="Target porsi" :value="$fmt($metrics['target_porsi'])" hint="Semua lokasi aktif" />
                <x-stat-card
                    label="Porsi dikirim"
                    :value="$fmt($metrics['dikirim'])"
                    :hint="$fmt($metrics['masih_disiapkan']).' masih disiapkan'"
                    highlight
                />
                <x-stat-card
                    label="Porsi diterima"
                    :value="$fmt($metrics['diterima'])"
                    :hint="$fmt($metrics['titik_terkonfirmasi']).' titik terkonfirmasi'"
                />
                <x-stat-card
                    label="Belum dikonfirmasi"
                    :value="$fmt($metrics['belum_dikonfirmasi'])"
                    :hint="$fmt($metrics['jumlah_titik_belum_dikonfirmasi']).' titik belum dikonfirmasi'"
                />
                <x-stat-card
                    label="Selisih terkonfirmasi"
                    :value="($metrics['selisih_terkonfirmasi'] > 0 ? '+' : '').$fmt($metrics['selisih_terkonfirmasi'])"
                    hint="Σ (terima − kirim)"
                />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                            <th class="px-md py-sm font-medium">Lokasi</th>
                            <th class="px-md py-sm font-medium">Status</th>
                            <th class="px-md py-sm font-medium text-right">Kirim</th>
                            <th class="px-md py-sm font-medium text-right">Terima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft font-body text-body-md">
                        @forelse($metrics['routes'] as $route)
                            <tr>
                                <td class="px-md py-sm text-ink">{{ $route->location?->name ?? '—' }}</td>
                                <td class="px-md py-sm">
                                    <x-badge-status :label="$route->status->label()" :variant="$route->status->badgeVariant()" :dot="true" />
                                </td>
                                <td class="px-md py-sm text-right tabular-nums">{{ $fmt($route->dispatched_portions) }}</td>
                                <td class="px-md py-sm text-right tabular-nums">{{ $route->confirmed_portions === null ? '—' : $fmt($route->confirmed_portions) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-md py-lg">
                                    <x-empty-state title="Belum ada rute hari ini" description="Ringkasan operasional akan muncul setelah ada data pengiriman." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Log aktivitas terbaru --}}
    <section aria-labelledby="log-heading" class="bg-surface rounded-xl border border-border-soft p-xl flex flex-col gap-md">
        <div class="flex items-center justify-between gap-sm">
            <h2 id="log-heading" class="font-display text-section-title text-ink">Log aktivitas terbaru</h2>
            <a href="{{ route('admin.audit-logs.index') }}" class="font-body text-micro text-ink-muted hover:text-ink">Lihat semua log ›</a>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                        <th class="px-md py-sm font-medium">Waktu</th>
                        <th class="px-md py-sm font-medium">Pengguna</th>
                        <th class="px-md py-sm font-medium">Peran</th>
                        <th class="px-md py-sm font-medium">Aksi</th>
                        <th class="px-md py-sm font-medium">Modul</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft font-body text-body-md">
                    @forelse($metrics['recent_logs'] as $log)
                        <tr>
                            <td class="px-md py-sm text-ink-muted tabular-nums whitespace-nowrap">{{ $log->occurred_at?->timezone('Asia/Jakarta')->format('d/m H:i') }}</td>
                            <td class="px-md py-sm text-ink">{{ $log->user_name_snapshot ?? 'Sistem' }}</td>
                            <td class="px-md py-sm text-ink-muted">{{ $log->role ?? '—' }}</td>
                            <td class="px-md py-sm text-ink">{{ $friendlyAction($log->action) }}</td>
                            <td class="px-md py-sm text-ink-muted">{{ $log->module }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-md py-lg">
                                <x-empty-state title="Belum ada log" description="Aktivitas sistem akan tercatat di sini." icon="history" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden flex flex-col gap-sm">
            @forelse($metrics['recent_logs'] as $log)
                <article class="p-md rounded-lg border border-border-soft bg-surface-subtle flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <p class="font-body text-label-sm text-ink">{{ $log->user_name_snapshot ?? 'Sistem' }}</p>
                        <span class="font-mono text-micro text-ink-muted tabular-nums">{{ $log->occurred_at?->timezone('Asia/Jakarta')->format('d/m H:i') }}</span>
                    </div>
                    <p class="font-body text-micro text-ink-muted">{{ $log->role ?? '—' }} · {{ $friendlyAction($log->action) }} · {{ $log->module }}</p>
                </article>
            @empty
                <x-empty-state title="Belum ada log" description="Aktivitas sistem akan tercatat di sini." icon="history" />
            @endforelse
        </div>
    </section>
</x-layouts.admin>
