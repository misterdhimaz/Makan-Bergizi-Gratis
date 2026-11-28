@php
    $now = now('Asia/Jakarta');
    $schoolName = $location?->name ?? 'Pesantren Al-Falah Palembang';
    $schoolShort = str_replace(' Palembang', '', $schoolName);
    $openCreate = request('aksi') === 'buat' || $errors->any();
@endphp

<x-layouts.guru-pic
    title="Tiket Keluhan &amp; Tindak Lanjut"
    mobile-title="Keluhan &amp; Tindak Lanjut"
    :mobile-chip="$activeCount.' Aktif'"
    :mobile-chip-tone="$activeCount > 0 ? 'alert' : 'neutral'"
    :subtitle="$now->translatedFormat('l, d M Y').' · '.$schoolShort"
    :location="$location"
>
    <x-slot:actions>
        <button
            id="open-keluhan-btn"
            type="button"
            @click="$dispatch('open-keluhan-dialog')"
            class="min-h-[44px] inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 transition-opacity whitespace-nowrap cursor-pointer"
        >
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add_a_photo</span>
            Buat Tiket Keluhan
        </button>
    </x-slot:actions>

    <div
        x-data="{
            keluhanOpen: {{ $openCreate ? 'true' : 'false' }},
            fileError: '',
            validateFile(event) {
                this.fileError = '';
                const file = event.target.files?.[0];
                if (!file) return;
                const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!allowed.includes(file.type) && !/\.(jpe?g|png|pdf)$/i.test(file.name)) {
                    this.fileError = 'Format berkas harus JPG, JPEG, PNG, atau PDF.';
                    event.target.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    this.fileError = 'Ukuran berkas melebihi batas maksimal 2 MB.';
                    event.target.value = '';
                }
            }
        }"
        @open-keluhan-dialog.window="keluhanOpen = true"
        class="flex flex-col gap-lg"
    >
        {{-- Mobile Lime Focal Card (1:1 Stitch Mobile) --}}
        <article class="lg:hidden bg-lime-accent rounded-xl p-lg flex flex-col gap-sm">
            <div class="flex items-center justify-between">
                <span class="font-body text-card-title text-on-lime">Tiket Aktif</span>
                <span class="font-mono text-micro text-on-lime">{{ $resolvedThisMonth }} Selesai bln ini</span>
            </div>
            <span class="font-display text-kpi text-on-lime tabular-nums">{{ $activeCount }} Tiket Terbuka</span>
            <button
                type="button"
                @click="keluhanOpen = true"
                class="min-h-[44px] w-full inline-flex items-center justify-center gap-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer"
            >
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">add_a_photo</span>
                Buat Tiket Keluhan
            </button>
        </article>

        {{-- Desktop 3 Summary Cards (1:1 Stitch Desktop) --}}
        <section aria-label="Ringkasan Tiket Keluhan" class="hidden lg:grid grid-cols-1 md:grid-cols-3 gap-lg items-stretch">
            <article class="bg-lime-accent rounded-xl p-xl flex flex-col justify-between gap-sm">
                <span class="font-body text-card-title text-on-lime">Tiket Aktif Saat Ini</span>
                <span class="font-display text-kpi text-on-lime tabular-nums">{{ $activeCount }} Tiket</span>
                <span class="font-body text-micro text-on-lime">Menunggu penyelesaian akhir dari SPPG</span>
            </article>
            <article class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col justify-between gap-sm">
                <span class="font-body text-card-title text-ink">Selesai Bulan Ini</span>
                <span class="font-display text-kpi text-ink tabular-nums">{{ $resolvedThisMonth }} Tiket</span>
                <span class="font-body text-micro text-ink-muted">Seluruh tindak lanjut terdokumentasi</span>
            </article>
            <article class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col justify-between gap-sm">
                <span class="font-body text-card-title text-ink">Rata-rata Respons</span>
                <span class="font-display text-kpi text-ink tabular-nums">{{ $avgResponseMinutes }} mnt</span>
                <span class="font-body text-micro text-ink-muted">Sejak tiket dibuat hingga ditindaklanjuti</span>
            </article>
        </section>

        {{-- Desktop Daftar Tiket Keluhan --}}
        <section class="hidden lg:flex bg-surface rounded-xl p-xl border border-border-soft flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Daftar Tiket Keluhan {{ $schoolShort }}</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted">No. Tiket</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Tanggal &amp; Jam</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Kategori &amp; Uraian</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Tindak Lanjut SPPG</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @forelse($tickets as $ticket)
                            @php
                                $isClosed = $ticket->status === 'resolved';
                                $isPriority = $ticket->severity === 'kritis';
                            @endphp
                            <tr class="{{ ! $isClosed ? 'bg-alert-soft/30' : '' }}">
                                <td class="py-md pr-md font-mono text-label-sm text-ink whitespace-nowrap">{{ $ticket->ticket_number }}</td>
                                <td class="py-md px-md font-mono text-label-sm text-ink-muted whitespace-nowrap">
                                    {{ $ticket->reported_at?->copy()->timezone('Asia/Jakarta')->translatedFormat('d M Y · H:i') }}
                                </td>
                                <td class="py-md px-md">
                                    <div class="flex flex-col gap-xs">
                                        <div class="flex flex-wrap items-center gap-sm">
                                            <span class="font-body text-card-title text-ink">{{ $ticket->categoryLabel() }}</span>
                                            @if($isPriority)
                                                <span class="px-sm py-xs rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Prioritas</span>
                                            @endif
                                            @if($ticket->photo_path)
                                                <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink">
                                                    <span class="material-symbols-outlined text-label-sm" aria-hidden="true">photo_camera</span>
                                                    1 foto bukti terlampir
                                                </span>
                                            @endif
                                        </div>
                                        <span class="font-body text-body-md text-ink">{{ $ticket->description }}</span>
                                    </div>
                                </td>
                                <td class="py-md px-md font-body text-body-md text-ink-muted">
                                    {{ $ticket->resolution_notes ?: 'Menunggu tindak lanjut Asisten Lapangan / Kepala Dapur' }}
                                </td>
                                <td class="py-md pl-md">
                                    @if($isClosed)
                                        <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro whitespace-nowrap">Selesai</span>
                                    @else
                                        <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro whitespace-nowrap">Dalam Tindak Lanjut</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-lg text-center font-body text-body-md text-ink-muted">Belum ada tiket keluhan untuk sekolah ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Mobile Ticket Cards (1:1 Stitch Mobile) --}}
        <section class="lg:hidden flex flex-col gap-sm">
            @forelse($tickets as $ticket)
                @php
                    $isClosed = $ticket->status === 'resolved';
                    $isPriority = $ticket->severity === 'kritis';
                @endphp
                <article class="bg-surface rounded-xl p-lg border {{ ! $isClosed ? 'border-alert-soft' : 'border-border-soft' }} flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-mono text-micro text-ink">{{ $ticket->ticket_number }}</span>
                        <span class="px-sm py-xs rounded-full {{ $isClosed ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }} font-body text-micro">
                            {{ $isClosed ? 'Selesai' : 'Dalam Tindak Lanjut' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-xs">
                        <span class="font-body text-card-title text-ink">{{ $ticket->categoryLabel() }}</span>
                        @if($isPriority)
                            <span class="px-sm py-xs rounded-full bg-danger-soft text-on-danger-soft font-body text-micro">Prioritas</span>
                        @endif
                    </div>
                    <p class="font-body text-body-md text-ink text-pretty">{{ $ticket->description }}</p>
                    <div class="flex items-center justify-between gap-sm pt-xs">
                        <span class="font-body text-micro text-ink-muted">
                            {{ $ticket->reported_at?->copy()->timezone('Asia/Jakarta')->translatedFormat('d M Y · H:i') }} WIB · {{ $ticket->resolution_notes ?: 'Menunggu SPPG' }}
                        </span>
                        @if($ticket->photo_path)
                            <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink shrink-0">
                                <span class="material-symbols-outlined text-label-sm" aria-hidden="true">photo_camera</span>
                                1 foto bukti
                            </span>
                        @endif
                    </div>
                </article>
            @empty
                <p class="font-body text-body-md text-ink-muted">Belum ada tiket keluhan untuk sekolah ini.</p>
            @endforelse
        </section>

        {{-- Centered Modal (Desktop) / Bottom Sheet (Mobile) Buat Tiket Keluhan (1:1 Stitch) --}}
        <template x-teleport="body">
            <div
                x-show="keluhanOpen"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-[9999] flex items-end md:items-center justify-center md:p-lg bg-obsidian/40 backdrop-blur-sm"
            >
                <div
                    @click.outside="keluhanOpen = false"
                    @keydown.escape.window="keluhanOpen = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="keluhan-title"
                    class="bg-surface rounded-t-xl md:rounded-xl border-t md:border border-border-soft p-lg md:p-xl w-full md:max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl flex flex-col gap-md"
                >
                    <div class="flex items-start justify-between gap-sm">
                        <div class="flex flex-col gap-xs">
                            <h3 id="keluhan-title" class="font-display text-section-title text-ink">Buat Tiket Keluhan</h3>
                            <span class="font-mono text-micro text-ink-muted">Nomor Tiket Otomatis: {{ $nextTicketNumber }}</span>
                        </div>
                        <button type="button" @click="keluhanOpen = false" aria-label="Tutup dialog" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full text-ink-muted hover:text-ink cursor-pointer">
                            <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('guru-pic.complaints.store') }}" enctype="multipart/form-data" class="flex flex-col gap-md">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
                            <label class="flex flex-col gap-xs">
                                <span class="font-body text-label-sm text-ink">Kategori keluhan</span>
                                <select name="category" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                                    <option value="kemasan_tumpah" @selected(old('category') === 'kemasan_tumpah')>Kemasan / Ompreng Tumpah</option>
                                    <option value="selisih_porsi" @selected(old('category') === 'selisih_porsi')>Selisih Jumlah Porsi</option>
                                    <option value="keterlambatan" @selected(old('category') === 'keterlambatan')>Keterlambatan Pengiriman</option>
                                    <option value="kualitas_organoleptik" @selected(old('category') === 'kualitas_organoleptik')>Kualitas / Uji Organoleptik</option>
                                </select>
                            </label>

                            <label class="flex flex-col gap-xs">
                                <span class="font-body text-label-sm text-ink">Prioritas</span>
                                <select name="priority" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                                    <option value="normal" @selected(old('priority') === 'normal')>Normal</option>
                                    <option value="serius" @selected(old('priority') === 'serius')>Serius (Notifikasi Prioritas)</option>
                                </select>
                            </label>
                        </div>

                        <label class="flex flex-col gap-xs">
                            <span class="font-body text-label-sm text-ink">Uraian keluhan <span class="text-on-danger-soft">*</span></span>
                            <textarea
                                name="description"
                                rows="3"
                                required
                                minlength="5"
                                placeholder="Jelaskan kendala makanan atau pengiriman yang ditemukan..."
                                class="w-full rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink"
                            >{{ old('description') }}</textarea>
                        </label>

                        <label class="flex flex-col gap-xs">
                            <span class="font-body text-label-sm text-ink">Foto bukti (opsional)</span>
                            <input
                                type="file"
                                name="photo"
                                accept=".jpg,.jpeg,.png,.pdf"
                                @change="validateFile($event)"
                                class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-label-sm text-ink"
                            >
                            <span class="font-body text-micro text-ink-muted">Maksimal 2 MB · Format JPG, PNG, atau PDF</span>
                            <span x-show="fileError" x-text="fileError" class="font-body text-micro text-on-danger-soft"></span>
                        </label>

                        <div class="flex items-center justify-end gap-sm pt-xs">
                            <button type="button" @click="keluhanOpen = false" class="min-h-[44px] px-lg py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="min-h-[44px] px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">
                                Kirim Tiket Keluhan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-layouts.guru-pic>
