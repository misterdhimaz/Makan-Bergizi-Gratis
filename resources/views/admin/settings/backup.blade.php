@php
    $formatBytes = static function (int $bytes): string {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.').' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.').' KB';
        }
        return $bytes.' B';
    };
    $triggerMeta = static fn (string $type): array => match ($type) {
        'manual' => ['label' => 'Manual', 'variant' => 'neutral'],
        'pre_restore' => ['label' => 'Pengaman (sebelum restore)', 'variant' => 'warning'],
        default => ['label' => 'Terjadwal', 'variant' => 'info'],
    };
@endphp

<x-layouts.admin
    title="Backup & Restore"
    subtitle="Arsip basis data terjadwal dan manual — tabel audit_logs dikecualikan agar jejak audit tetap utuh"
    breadcrumb="Administrator Sistem · Pengaturan Sistem"
>
    <x-slot:actions>
        <form method="POST" action="{{ route('admin.settings.backup.store') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">backup</span>
                Buat Backup Manual
            </button>
        </form>
    </x-slot:actions>

    <div
        x-data="{
            confirmOpen: false,
            restoreUrl: '',
            restoreFilename: '',
            openRestore(url, filename) {
                this.restoreUrl = url;
                this.restoreFilename = filename;
                this.confirmOpen = true;
            }
        }"
        class="flex flex-col gap-lg"
    >
        @php
            $showRetensi = (bool) config('mbg.fitur_opsional.retensi_arsip', false);
        @endphp
        <section class="grid grid-cols-1 {{ $showRetensi ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }} gap-md">
            <x-stat-card label="Jadwal Backup Harian" :value="$scheduleTime.' WIB'" badge="Otomatis" badge-variant="success" highlight />
            @if($showRetensi)
                <x-stat-card label="Retensi Arsip" :value="$retentionDays.' hari'" icon="inventory" />
            @endif
            <x-stat-card label="Perlindungan Audit Log" value="Dikecualikan" hint="--ignore-table audit_logs" icon="shield" />
        </section>

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            <div>
                <h2 class="font-display text-section-title text-ink">Riwayat Arsip Backup</h2>
                <p class="font-body text-micro text-ink-muted">Pemulihan basis data tidak pernah menimpa atau menghapus tabel `audit_logs`.</p>
            </div>

            @if($backups->isEmpty())
                <x-empty-state icon="cloud_off" title="Belum ada arsip backup" description="Klik Buat Backup Manual untuk membuat salinan basis data sekarang." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Berkas', 'Jenis', 'Ukuran', 'Status', 'Dibuat Oleh', 'Aksi']">
                        @foreach($backups as $backup)
                            @php
                                $meta = $triggerMeta($backup->trigger_type);
                                $fileExists = $backup->disk === 'local'
                                    && str_starts_with($backup->path, 'backups/')
                                    && ! str_contains($backup->path, '..')
                                    && \Illuminate\Support\Facades\Storage::disk($backup->disk)->exists($backup->path);
                            @endphp
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm">
                                    <p class="font-mono text-label-sm font-semibold text-ink">{{ $backup->filename }}</p>
                                    <p class="font-mono text-micro text-ink-muted">{{ $backup->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</p>
                                </td>
                                <td class="px-md py-sm">
                                    <x-badge-status :variant="$meta['variant']" :label="$meta['label']" />
                                </td>
                                <td class="px-md py-sm font-mono text-micro text-ink">{{ $formatBytes($backup->size_bytes) }}</td>
                                <td class="px-md py-sm">
                                    <x-badge-status :variant="$backup->status === 'success' ? 'success' : 'danger'" :label="$backup->status === 'success' ? 'Berhasil' : 'Gagal'" />
                                </td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">
                                    {{ $backup->trigger_type === 'scheduled' ? 'Sistem' : ($backup->creator?->name ?? 'Sistem') }}
                                    @if($backup->restored_at)
                                        <span class="block text-ink">Dipulihkan: {{ $backup->restored_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}</span>
                                    @endif
                                </td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    @if($fileExists)
                                        <div class="inline-flex items-center gap-xs">
                                            <a href="{{ route('admin.settings.backup.download', $backup) }}" class="px-md py-xs rounded-full border border-border-soft bg-surface hover:bg-surface-subtle font-body text-micro font-semibold text-ink">Unduh</a>
                                            <button
                                                type="button"
                                                @click="openRestore('{{ route('admin.settings.backup.restore', $backup) }}', '{{ $backup->filename }}')"
                                                class="px-md py-xs rounded-full border border-border-soft bg-surface hover:bg-surface-subtle font-body text-micro font-semibold text-ink cursor-pointer"
                                            >
                                                Pulihkan
                                            </button>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-xs">
                                            <button type="button" disabled class="px-md py-xs rounded-full border border-border-soft bg-surface-subtle font-body text-micro text-ink-muted opacity-50 cursor-not-allowed">Unduh</button>
                                            <button type="button" disabled class="px-md py-xs rounded-full border border-border-soft bg-surface-subtle font-body text-micro text-ink-muted opacity-50 cursor-not-allowed">Pulihkan</button>
                                        </div>
                                        <p class="font-body text-micro text-ink-muted mt-xs">Berkas tidak tersedia</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($backups as $backup)
                        @php
                            $meta = $triggerMeta($backup->trigger_type);
                            $fileExists = $backup->disk === 'local'
                                && str_starts_with($backup->path, 'backups/')
                                && ! str_contains($backup->path, '..')
                                && \Illuminate\Support\Facades\Storage::disk($backup->disk)->exists($backup->path);
                        @endphp
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div class="min-w-0">
                                    <p class="font-mono text-label-sm font-semibold text-ink truncate">{{ $backup->filename }}</p>
                                    <p class="font-mono text-micro text-ink-muted">{{ $formatBytes($backup->size_bytes) }} &middot; {{ $meta['label'] }} &middot; {{ $backup->trigger_type === 'scheduled' ? 'Sistem' : ($backup->creator?->name ?? 'Sistem') }}</p>
                                </div>
                                <x-badge-status :variant="$backup->status === 'success' ? 'success' : 'danger'" :label="$backup->status === 'success' ? 'Berhasil' : 'Gagal'" />
                            </div>
                            @if($fileExists)
                                <div class="flex gap-xs">
                                    <a href="{{ route('admin.settings.backup.download', $backup) }}" class="flex-1 text-center px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink">Unduh</a>
                                    <button
                                        type="button"
                                        @click="openRestore('{{ route('admin.settings.backup.restore', $backup) }}', '{{ $backup->filename }}')"
                                        class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer"
                                    >
                                        Pulihkan
                                    </button>
                                </div>
                            @else
                                <p class="font-body text-micro text-ink-muted">Berkas tidak tersedia</p>
                            @endif
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$backups" />
            @endif
        </section>

        <x-confirm-dialog
            open-var="confirmOpen"
            title="Konfirmasi Pemulihan Basis Data"
            message="Masukkan kata sandi Administrator untuk mengonfirmasi pemulihan basis data. Sistem akan membuat backup pengaman otomatis terlebih dahulu dan mempertahankan seluruh Log Aktivitas."
            variant="danger"
        >
            <p class="font-body text-body-md text-ink-muted mb-md">
                Berkas sumber: <span class="font-mono font-semibold text-ink" x-text="restoreFilename"></span>
            </p>
            <form method="POST" :action="restoreUrl" id="restore-backup-form" class="flex flex-col gap-md">
                @csrf
                <div class="flex flex-col gap-xs">
                    <label for="restore-password" class="font-body text-label-sm text-ink">Kata Sandi Administrator (Re-autentikasi)</label>
                    <input id="restore-password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi Anda" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="flex justify-end gap-sm">
                    <button type="button" @click="confirmOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-danger-soft text-on-danger-soft font-body text-label-sm font-semibold cursor-pointer">Ya, Pulihkan Sekarang</button>
                </div>
            </form>
        </x-confirm-dialog>
    </div>
</x-layouts.admin>
