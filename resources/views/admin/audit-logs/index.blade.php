@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $actionBadge = static function (string $action): array {
        return match ($action) {
            'login', 'aktifkan', 'backup' => ['variant' => 'success', 'label' => strtoupper(str_replace('_', ' ', $action))],
            'login_gagal', 'nonaktifkan', 'restore' => ['variant' => 'danger', 'label' => strtoupper(str_replace('_', ' ', $action))],
            'reset_sandi', 'ubah_pengaturan', 'revisi', 'lupa_sandi' => ['variant' => 'warning', 'label' => strtoupper(str_replace('_', ' ', $action))],
            default => ['variant' => 'neutral', 'label' => strtoupper(str_replace('_', ' ', $action))],
        };
    };
@endphp

<x-layouts.admin
    title="Log Aktivitas"
    subtitle="Jejak audit immutable seluruh aktivitas pengguna dan sistem SPPG Sungai Selayur 1"
    breadcrumb="Administrator Sistem"
>
    <div
        x-data="adminAuditLogsPage()"
        class="flex flex-col gap-lg"
    >
        {{-- Statistik Ringkas --}}
        <section aria-label="Ringkasan log aktivitas" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
            <x-stat-card label="Total entri log" :value="$fmt($stats['total'])" badge="Immutable" badge-variant="neutral" highlight />
            <x-stat-card label="Aktivitas hari ini" :value="$fmt($stats['hari_ini'])" icon="today" />
            <x-stat-card label="Login gagal 24 jam" :value="$fmt($stats['login_gagal_24_jam'])" :badge="$stats['login_gagal_24_jam'] > 0 ? 'Perlu pantau' : 'Aman'" :badge-variant="$stats['login_gagal_24_jam'] > 0 ? 'warning' : 'success'" icon="gpp_maybe" />
            <x-stat-card label="Perubahan data & sistem" :value="$fmt($stats['perubahan_sistem'])" icon="edit_note" />
        </section>

        {{-- Filter --}}
        <x-filter-bar :action="route('admin.audit-logs.index')">
            <div class="lg:col-span-3 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari</label>
                <input
                    id="filter-q"
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Pengguna, target, alasan, IP"
                    class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0"
                >
            </div>
            <div class="lg:col-span-2 flex flex-col gap-xs">
                <label for="filter-peran" class="font-body text-micro text-ink-muted">Peran</label>
                <select id="filter-peran" name="peran" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected($filters['peran'] === 'all')>Semua peran</option>
                    @foreach($roleOptions as $roleOption)
                        <option value="{{ $roleOption }}" @selected($filters['peran'] === $roleOption)>{{ $roleOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2 flex flex-col gap-xs">
                <label for="filter-modul" class="font-body text-micro text-ink-muted">Modul</label>
                <select id="filter-modul" name="modul" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected($filters['modul'] === 'all')>Semua modul</option>
                    @foreach($moduleOptions as $moduleOption)
                        <option value="{{ $moduleOption }}" @selected($filters['modul'] === $moduleOption)>{{ $moduleOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2 flex flex-col gap-xs">
                <label for="filter-aksi" class="font-body text-micro text-ink-muted">Jenis Aksi</label>
                <select id="filter-aksi" name="aksi" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected($filters['aksi'] === 'all')>Semua aksi</option>
                    @foreach($actionOptions as $actionOption)
                        <option value="{{ $actionOption }}" @selected($filters['aksi'] === $actionOption)>{{ $actionOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2 flex flex-col gap-xs">
                <label for="filter-rentang" class="font-body text-micro text-ink-muted">Rentang Waktu</label>
                <select id="filter-rentang" name="rentang" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="semua" @selected($filters['rentang'] === 'semua')>Semua waktu</option>
                    <option value="hari-ini" @selected($filters['rentang'] === 'hari-ini')>Hari ini</option>
                    <option value="7-hari" @selected($filters['rentang'] === '7-hari')>7 hari terakhir</option>
                    <option value="30-hari" @selected($filters['rentang'] === '30-hari')>30 hari terakhir</option>
                </select>
            </div>
        </x-filter-bar>

        {{-- Daftar Log Aktivitas --}}
        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            <div class="flex flex-wrap items-center justify-between gap-sm">
                <div>
                    <h2 class="font-display text-section-title text-ink">Riwayat Aktivitas (Append-Only)</h2>
                    <p class="font-body text-micro text-ink-muted">Entri log tidak dapat diubah atau dihapus demi kepatuhan audit SPPG.</p>
                </div>
                <span class="font-mono text-micro text-ink-muted">{{ $fmt($logs->total()) }} entri ditemukan</span>
            </div>

            @if($logs->isEmpty())
                <x-empty-state
                    icon="history"
                    title="Belum ada entri log yang sesuai"
                    description="Ubah kata kunci pencarian atau filter rentang waktu untuk melihat riwayat aktivitas lainnya."
                />
            @else
                {{-- Tabel Desktop --}}
                <div class="hidden md:block">
                    <x-data-table :headers="['Waktu', 'Pengguna & Peran', 'Aksi', 'Modul & Target', 'IP Address', 'Detail']">
                        @foreach($logs as $log)
                            @php($badge = $actionBadge($log->action))
                            <tr class="hover:bg-surface-subtle/60 transition-colors">
                                <td class="px-md py-sm whitespace-nowrap font-mono text-micro text-ink-muted">
                                    {{ $log->occurred_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}
                                </td>
                                <td class="px-md py-sm">
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $log->user_name_snapshot }}</p>
                                    <p class="font-body text-micro text-ink-muted">{{ $log->role }}</p>
                                </td>
                                <td class="px-md py-sm whitespace-nowrap">
                                    <x-badge-status :variant="$badge['variant']" :label="$badge['label']" />
                                </td>
                                <td class="px-md py-sm">
                                    <p class="font-body text-label-sm text-ink">{{ $log->module }}</p>
                                    <p class="font-body text-micro text-ink-muted truncate max-w-xs">{{ $log->target ?? '-' }}</p>
                                </td>
                                <td class="px-md py-sm whitespace-nowrap font-mono text-micro text-ink-muted">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openDetail('{{ route('admin.audit-logs.show', $log) }}')"
                                        class="inline-flex items-center gap-xs px-md py-xs rounded-full border border-border-soft bg-surface hover:bg-surface-subtle font-body text-micro font-semibold text-ink cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-body-md" aria-hidden="true">visibility</span>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                {{-- Daftar Kartu Mobile --}}
                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($logs as $log)
                        @php($badge = $actionBadge($log->action))
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div class="min-w-0">
                                    <p class="font-body text-label-sm font-semibold text-ink truncate">{{ $log->user_name_snapshot }}</p>
                                    <p class="font-body text-micro text-ink-muted">{{ $log->role }}</p>
                                </div>
                                <x-badge-status :variant="$badge['variant']" :label="$badge['label']" />
                            </div>
                            <div class="text-micro font-body text-ink-muted flex flex-col gap-xs">
                                <p><span class="text-ink font-semibold">Modul:</span> {{ $log->module }} — {{ $log->target ?? '-' }}</p>
                                <p class="font-mono">{{ $log->occurred_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} &middot; {{ $log->ip_address ?? '-' }}</p>
                            </div>
                            <button
                                type="button"
                                @click="openDetail('{{ route('admin.audit-logs.show', $log) }}')"
                                class="w-full inline-flex items-center justify-center gap-xs px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-body-md" aria-hidden="true">visibility</span>
                                Lihat Detail Log
                            </button>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$logs" />
            @endif
        </section>

        {{-- Drawer Detail Log Aktivitas --}}
        <x-slide-panel open-var="detailOpen" title="Detail Entri Log Aktivitas" subtitle="Rekaman audit read-only">
            <template x-if="loading">
                <p class="font-body text-body-md text-ink-muted">Memuat detail log aktivitas...</p>
            </template>

            <template x-if="errorMessage">
                <div class="rounded-lg bg-danger-soft text-on-danger-soft p-md font-body text-label-sm" x-text="errorMessage"></div>
            </template>

            <template x-if="!loading && !errorMessage && detail">
                <div class="flex flex-col gap-md">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-md rounded-lg bg-surface-subtle border border-border-soft p-md">
                        <div>
                            <p class="font-body text-micro text-ink-muted">Waktu Kejadian</p>
                            <p class="font-mono text-label-sm text-ink" x-text="detail.occurred_at"></p>
                        </div>
                        <div>
                            <p class="font-body text-micro text-ink-muted">Alamat IP</p>
                            <p class="font-mono text-label-sm text-ink" x-text="detail.ip_address"></p>
                        </div>
                        <div>
                            <p class="font-body text-micro text-ink-muted">Pengguna</p>
                            <p class="font-body text-label-sm font-semibold text-ink" x-text="detail.user_name_snapshot"></p>
                            <p class="font-body text-micro text-ink-muted" x-text="detail.role"></p>
                        </div>
                        <div>
                            <p class="font-body text-micro text-ink-muted">Modul &amp; Aksi</p>
                            <p class="font-body text-label-sm font-semibold text-ink" x-text="detail.module + ' · ' + detail.action"></p>
                            <p class="font-body text-micro text-ink-muted break-all" x-text="detail.target"></p>
                        </div>
                    </div>

                    <template x-if="detail.reason">
                        <div class="rounded-lg bg-surface-subtle border border-border-soft p-md">
                            <p class="font-body text-micro text-ink-muted mb-xs">Alasan / Catatan</p>
                            <p class="font-body text-body-md text-ink" x-text="detail.reason"></p>
                        </div>
                    </template>

                    <template x-if="detail.approver_name">
                        <div class="rounded-lg bg-surface-subtle border border-border-soft p-md">
                            <p class="font-body text-micro text-ink-muted mb-xs">Penyetuju</p>
                            <p class="font-body text-label-sm font-semibold text-ink" x-text="detail.approver_name"></p>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 gap-md">
                        <div class="rounded-lg border border-border-soft p-md">
                            <p class="font-body text-micro font-semibold text-ink-muted mb-xs">Nilai Sebelumnya (old_values)</p>
                            <pre class="font-mono text-micro text-ink whitespace-pre-wrap break-words bg-surface-subtle p-sm rounded" x-text="detail.old_values ? JSON.stringify(detail.old_values, null, 2) : 'Tidak ada data sebelumnya'"></pre>
                        </div>
                        <div class="rounded-lg border border-border-soft p-md">
                            <p class="font-body text-micro font-semibold text-ink-muted mb-xs">Nilai Baru (new_values)</p>
                            <pre class="font-mono text-micro text-ink whitespace-pre-wrap break-words bg-surface-subtle p-sm rounded" x-text="detail.new_values ? JSON.stringify(detail.new_values, null, 2) : 'Tidak ada data baru'"></pre>
                        </div>
                    </div>
                </div>
            </template>
        </x-slide-panel>
    </div>

    @push('scripts')
        <script>
            function adminAuditLogsPage() {
                return {
                    detailOpen: false,
                    loading: false,
                    errorMessage: null,
                    detail: null,
                    async openDetail(url) {
                        this.detailOpen = true;
                        this.loading = true;
                        this.errorMessage = null;
                        this.detail = null;
                        try {
                            const res = await fetch(url, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!res.ok) {
                                throw new Error('Gagal memuat detail log aktivitas.');
                            }
                            this.detail = await res.json();
                        } catch (e) {
                            this.errorMessage = e.message || 'Terjadi kesalahan saat mengambil detail log.';
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            }
        </script>
    @endpush
</x-layouts.admin>
