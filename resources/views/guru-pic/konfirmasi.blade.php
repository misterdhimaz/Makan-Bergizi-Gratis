@php
    $now = now('Asia/Jakarta');
    $schoolName = $location?->name ?? 'Pesantren Al-Falah Palembang';
    $schoolShort = str_replace(' Palembang', '', $schoolName);
    $dispatched = (int) ($route?->dispatched_portions ?? $location?->target_portions ?? 250);
    $confirmed = (int) old('confirmed_portions', $route?->confirmed_portions ?? $dispatched);
    $scheduled = $route?->scheduled_arrival_time ?: '08:00';
    $arrivedTime = old('arrival_time', $route?->arrived_at ? $route->arrived_at->copy()->timezone('Asia/Jakarta')->format('H:i') : '08:12');
    $packaging = old('packaging_condition', $route?->packaging_condition ?? 'sebagian-tumpah');
    $notes = old('notes', $route?->notes ?? '');
    $taste = old('organoleptic_taste', $route?->organoleptic_taste ?? 'normal');
    $aroma = old('organoleptic_aroma', $route?->organoleptic_aroma ?? 'normal');
    $texture = old('organoleptic_texture', $route?->organoleptic_texture ?? 'normal');
    $color = old('organoleptic_color', $route?->organoleptic_color ?? 'normal');
    $courierName = $route?->courier_name ?? $route?->courier?->name ?? 'Dedi Pratama';
    $vehiclePlate = $route?->vehicle_plate ?? 'B 9142 KDA';
    $waybill = $route?->waybill_number ?? '#DST-'.$now->format('Ymd').'-07';

    $components = is_array($todayMenu?->components_json) ? $todayMenu->components_json : [];
    $menuList = [
        ['name' => $components['pokok'] ?? 'Nasi Putih', 'qty' => "{$dispatched} porsi"],
        ['name' => $components['hewani'] ?? 'Ayam Goreng Lengkuas', 'qty' => "{$dispatched} ptg"],
        ['name' => $components['nabati'] ?? 'Tahu Goreng', 'qty' => "{$dispatched} ptg"],
        ['name' => $components['sayur'] ?? 'Tumis Buncis & Wortel', 'qty' => "{$dispatched} porsi"],
        ['name' => $components['minuman'] ?? 'Susu UHT 200 ml', 'qty' => "{$dispatched} ktk"],
    ];
@endphp

<x-layouts.guru-pic
    title="Konfirmasi Penerimaan &amp; Uji Organoleptik"
    mobile-title="Konfirmasi &amp; Organoleptik"
    :subtitle="$now->translatedFormat('l, d M Y').' · Pengiriman '.$waybill.' · '.$schoolShort"
    :location="$location"
>
    <x-slot:actions>
        <a href="{{ route('guru-pic.dashboard') }}" class="min-h-[44px] inline-flex items-center gap-sm px-lg py-sm rounded-full bg-surface border border-border-soft font-body text-label-sm text-ink hover:bg-surface-subtle whitespace-nowrap">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">arrow_back</span>
            <span>Kembali ke Dashboard</span>
        </a>
    </x-slot:actions>

    <div
        x-data="{
            targetPorsi: {{ $dispatched }},
            porsi: {{ $confirmed }},
            jamTiba: '{{ $arrivedTime }}',
            jadwal: '{{ $scheduled }}',
            get selisih() {
                const v = parseInt(this.porsi, 10);
                return (Number.isNaN(v) ? 0 : v) - this.targetPorsi;
            },
            get telatMenit() {
                if (!this.jamTiba || !this.jadwal) return 0;
                const [ah, am] = this.jamTiba.split(':').map(Number);
                const [sh, sm] = this.jadwal.split(':').map(Number);
                return Math.max(0, (ah * 60 + am) - (sh * 60 + sm));
            },
            get semuaSesuai() {
                return this.selisih === 0 && this.telatMenit === 0;
            }
        }"
        class="grid grid-cols-1 xl:grid-cols-3 gap-lg items-start"
    >
        {{-- Mobile Lime Summary Card (1:1 Stitch Mobile) --}}
        <article class="xl:hidden bg-lime-accent rounded-xl p-lg flex flex-col gap-xs">
            <div class="flex items-center justify-between">
                <span class="font-body text-card-title text-on-lime">Target Kirim Hari Ini</span>
                <span class="font-mono text-micro text-on-lime">{{ $now->translatedFormat('d M Y') }}</span>
            </div>
            <span class="font-display text-kpi text-on-lime tabular-nums">{{ $dispatched }} porsi</span>
            <span class="font-body text-micro text-on-lime">Jadwal tiba {{ $scheduled }} WIB · Kurir {{ $courierName }}</span>
        </article>

        {{-- Form Konfirmasi & Uji Organoleptik --}}
        <form
            id="konfirmasi-form"
            method="POST"
            action="{{ route('guru-pic.confirmation.store') }}"
            class="xl:col-span-2 bg-surface rounded-xl p-lg lg:p-xl border border-border-soft flex flex-col gap-xl"
        >
            @csrf

            {{-- Section 1: Konfirmasi Penerimaan --}}
            <div class="flex flex-col gap-md">
                <div class="flex flex-wrap items-center justify-between gap-sm pb-sm border-b border-border-soft">
                    <h2 class="font-display text-section-title text-ink">1. Konfirmasi Jumlah &amp; Waktu Tiba</h2>
                    <span class="font-mono text-label-sm text-ink-muted tabular-nums">Dikirim: {{ $dispatched }} porsi · Jadwal: {{ $scheduled }} WIB</span>
                </div>

                <div class="grid grid-cols-2 gap-sm md:gap-md">
                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Jumlah porsi diterima</span>
                        <input
                            id="porsi-input"
                            name="confirmed_portions"
                            type="number"
                            min="0"
                            max="{{ $dispatched }}"
                            x-model.number="porsi"
                            required
                            class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-mono text-body-md text-ink tabular-nums"
                        >
                    </label>

                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Jam tiba aktual (WIB)</span>
                        <input
                            id="jam-input"
                            name="arrival_time"
                            type="time"
                            x-model="jamTiba"
                            required
                            class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-mono text-body-md text-ink tabular-nums"
                        >
                    </label>
                </div>

                {{-- Automatic Discrepancy & Delay Alerts --}}
                <div id="auto-status-banner" x-show="semuaSesuai" x-cloak class="p-md rounded-md bg-success-soft text-on-success-soft flex items-center gap-sm">
                    <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">check_circle</span>
                    <span class="font-body text-label-sm">Sesuai pengiriman &amp; tepat waktu ({{ $dispatched }} porsi · {{ $scheduled }} WIB).</span>
                </div>

                <div id="auto-warning-box" class="grid grid-cols-1 md:grid-cols-2 gap-sm">
                    <div
                        id="selisih-alert"
                        :class="selisih < 0 ? 'bg-alert-soft text-on-alert-soft' : 'bg-success-soft text-on-success-soft'"
                        class="p-md rounded-md flex items-center gap-sm"
                    >
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true" x-text="selisih < 0 ? 'warning' : 'check_circle'">warning</span>
                        <span class="font-body text-label-sm" x-text="selisih < 0 ? ('Selisih otomatis: diterima ' + porsi + ' dari ' + targetPorsi + ' porsi (' + selisih + ' porsi).') : ('Jumlah porsi sesuai pengiriman (' + porsi + ' dari ' + targetPorsi + ' porsi).')"></span>
                    </div>
                    <div
                        id="telat-alert"
                        :class="telatMenit > 0 ? 'bg-alert-soft text-on-alert-soft' : 'bg-success-soft text-on-success-soft'"
                        class="p-md rounded-md flex items-center gap-sm"
                    >
                        <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true" x-text="telatMenit > 0 ? 'schedule' : 'verified'">schedule</span>
                        <span class="font-body text-label-sm" x-text="telatMenit > 0 ? ('Terlambat ' + telatMenit + ' menit dari jadwal tiba ' + jadwal + ' WIB.') : ('Tiba tepat waktu sesuai jadwal ' + jadwal + ' WIB.')"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Kondisi kemasan &amp; makanan</span>
                        <select name="packaging_condition" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                            <option value="sebagian-tumpah" @selected($packaging === 'sebagian-tumpah')>Sebagian ompreng tumpah / rusak</option>
                            <option value="baik" @selected($packaging === 'baik')>Baik &amp; utuh seluruhnya</option>
                            <option value="tidak-layak" @selected($packaging === 'tidak-layak')>Tidak layak konsumsi</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Catatan penerimaan</span>
                        <input
                            type="text"
                            name="notes"
                            value="{{ $notes }}"
                            placeholder="Catatan kondisi pengiriman atau jumlah porsi..."
                            class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink"
                        >
                    </label>
                </div>
            </div>

            {{-- Section 2: Formulir Uji Organoleptik Digital --}}
            <div class="flex flex-col gap-md">
                <div class="flex flex-wrap items-center justify-between gap-sm pb-sm border-b border-border-soft">
                    <h2 class="font-display text-section-title text-ink">2. Formulir Uji Organoleptik Digital</h2>
                    <span class="font-body text-micro text-ink-muted">Wajib diisi setiap penerimaan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <label class="flex flex-col gap-xs p-md rounded-md bg-surface-subtle border border-border-soft">
                        <span class="font-body text-card-title text-ink">Rasa</span>
                        <select name="organoleptic_taste" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink">
                            <option value="normal" @selected($taste === 'normal')>Normal / Sesuai standar</option>
                            <option value="kurang" @selected($taste === 'kurang')>Kurang sesuai (hambar / terlalu asin)</option>
                            <option value="tidak-layak" @selected($taste === 'tidak-layak')>Tidak layak (basi / asam)</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-xs p-md rounded-md bg-surface-subtle border border-border-soft">
                        <span class="font-body text-card-title text-ink">Aroma</span>
                        <select name="organoleptic_aroma" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink">
                            <option value="normal" @selected($aroma === 'normal')>Normal / Segar</option>
                            <option value="kurang" @selected($aroma === 'kurang')>Kurang segar</option>
                            <option value="tidak-layak" @selected($aroma === 'tidak-layak')>Berbau menyengat / basi</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-xs p-md rounded-md bg-surface-subtle border border-border-soft">
                        <span class="font-body text-card-title text-ink">Tekstur</span>
                        <select name="organoleptic_texture" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink">
                            <option value="normal" @selected($texture === 'normal')>Normal / Matang merata</option>
                            <option value="kurang" @selected($texture === 'kurang')>Terlalu lembek / keras</option>
                            <option value="tidak-layak" @selected($texture === 'tidak-layak')>Berlendir / tidak layak</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-xs p-md rounded-md bg-surface-subtle border border-border-soft">
                        <span class="font-body text-card-title text-ink">Warna</span>
                        <select name="organoleptic_color" required class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink">
                            <option value="normal" @selected($color === 'normal')>Normal / Cerah alami</option>
                            <option value="kurang" @selected($color === 'kurang')>Agak pucat / berubah warna</option>
                            <option value="tidak-layak" @selected($color === 'tidak-layak')>Berjamur / warna mencurigakan</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-sm pt-sm border-t border-border-soft">
                <a href="{{ route('guru-pic.complaints.index', ['aksi' => 'buat']) }}"
                   class="min-h-[44px] inline-flex items-center justify-center px-lg py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink hover:bg-surface-muted">
                    Laporkan Keluhan
                </a>
                <button id="submit-konfirmasi-btn" type="submit"
                        class="min-h-[44px] inline-flex items-center justify-center px-xl py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
                    Simpan Konfirmasi &amp; Organoleptik
                </button>
            </div>
        </form>

        {{-- Right Info Panel (Desktop) --}}
        <aside class="hidden xl:flex flex-col gap-lg">
            <div class="bg-lime-accent rounded-xl p-xl flex flex-col gap-sm">
                <span class="font-body text-card-title text-on-lime">Ringkasan Pengiriman</span>
                <span class="font-display text-kpi text-on-lime tabular-nums">{{ $dispatched }} porsi</span>
                <span class="font-body text-micro text-on-lime">{{ $waybill }} · Pengantar: {{ $courierName }} · Armada {{ $vehiclePlate }}</span>
            </div>

            <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                <h2 class="font-display text-section-title text-ink">Menu yang Diterima</h2>
                <ul class="flex flex-col gap-sm font-body text-body-md text-ink">
                    @foreach($menuList as $mItem)
                        <li class="flex justify-between gap-sm">
                            <span>{{ $mItem['name'] }}</span>
                            <span class="font-mono text-micro text-ink-muted tabular-nums">{{ $mItem['qty'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</x-layouts.guru-pic>
