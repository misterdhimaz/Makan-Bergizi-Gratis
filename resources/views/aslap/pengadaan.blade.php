<x-layouts.aslap title="Pengadaan & Penerimaan Bahan Baku" :complaintBadge="$complaintBadge">
    {{-- Pemilih Tanggal & Filter Tab --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-md">
            <div>
                <h2 class="font-display text-section-title text-ink">
                    Kebutuhan Menu {{ $selectedMenu?->operational_date?->translatedFormat('j M Y') ?? '-' }}
                </h2>
                <p class="font-body text-micro text-ink-muted">
                    Diajukan oleh {{ $selectedMenu?->submittedBy?->name ?? 'Nadia Putri' }} · {{ number_format($selectedMenu?->target_portions ?? 0, 0, ',', '.') }} porsi
                </p>
            </div>
            <form method="GET" action="{{ route('aslap.pengadaan.index') }}" class="flex items-center gap-sm">
                <select name="tanggal" onchange="this.form.submit()" class="min-h-[44px] rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                    @foreach($menusWithNeeds as $m)
                        <option value="{{ $m->operational_date?->toDateString() }}" @selected($selectedMenu?->id === $m->id)>
                            Menu {{ $m->operational_date?->translatedFormat('j M Y') }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="p-md rounded-lg bg-alert-soft/40 border border-alert-soft font-body text-body-md text-ink">
            Selisih realisasi dapur hari ini (hanya baca): <strong>{{ $varianceSummary }}</strong>
        </div>

        <div class="flex flex-wrap gap-sm">
            @php $baseParams = ['tanggal' => $selectedMenu?->operational_date?->toDateString()]; @endphp
            <a href="{{ route('aslap.pengadaan.index', array_merge($baseParams, ['status' => 'semua'])) }}" class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $filter === 'semua' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface-subtle text-ink' }}">
                Semua ({{ $transactions->count() }})
            </a>
            <a href="{{ route('aslap.pengadaan.index', array_merge($baseParams, ['status' => 'menunggu'])) }}" class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $filter === 'menunggu' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface-subtle text-ink' }}">
                Menunggu ({{ $transactions->where('procurement_status', 'menunggu_terima')->count() }})
            </a>
            <a href="{{ route('aslap.pengadaan.index', array_merge($baseParams, ['status' => 'lengkap'])) }}" class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $filter === 'lengkap' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface-subtle text-ink' }}">
                Lengkap ({{ $transactions->where('procurement_status', 'diterima_lengkap')->count() }})
            </a>
        </div>
    </section>

    {{-- Daftar Bahan (Desktop & Mobile Setara) --}}
    <section class="flex flex-col gap-md">
        @foreach($filteredTransactions as $tx)
            @php
                $isLocked = $tx->verification_status !== 'menunggu';
                $statusLabel = match($tx->procurement_status) {
                    'diterima_lengkap' => 'Diterima Lengkap',
                    'diterima_sebagian' => 'Diterima Sebagian',
                    default => 'Menunggu Terima',
                };
            @endphp
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                    <div>
                        <h3 class="font-display text-section-title text-ink">{{ $tx->ingredient?->name }}</h3>
                        <p class="font-body text-micro text-ink-muted">
                            Kebutuhan: <strong class="font-mono tabular-nums">{{ rtrim(rtrim(number_format((float) $tx->planned_qty, 2, ',', '.'), '0'), ',') }} {{ $tx->ingredient?->unit }}</strong>
                            · Aktual: <strong class="font-mono tabular-nums">{{ rtrim(rtrim(number_format((float) $tx->quantity, 2, ',', '.'), '0'), ',') }} {{ $tx->ingredient?->unit }}</strong>
                        </p>
                        <p class="font-body text-micro text-ink-muted">
                            Pemasok &amp; Waktu: {{ $tx->supplier_name ?? 'Pemasok belum ditautkan' }}
                            · {{ $tx->received_at ? $tx->received_at->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i').' WIB' : 'Belum diterima' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-xs">
                        <span class="px-sm py-0.5 rounded-full text-micro {{ $tx->procurement_status === 'diterima_lengkap' ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="px-sm py-0.5 rounded-full text-micro bg-surface-subtle border border-border-soft text-ink-muted">
                            Verifikasi: {{ ucfirst(str_replace('_', ' ', $tx->verification_status)) }}
                        </span>
                    </div>
                </div>

                @if(! $isLocked)
                    <form method="POST" action="{{ route('aslap.pengadaan.update', $tx) }}" class="border-t border-border-soft pt-md grid grid-cols-1 sm:grid-cols-4 gap-sm items-end">
                        @csrf
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Jumlah aktual ({{ $tx->ingredient?->unit }})</label>
                            <input type="number" step="0.01" min="0" name="quantity" value="{{ (float) $tx->quantity }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                        </div>
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Pemasok</label>
                            <select name="supplier_name" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                                <option value="Pemasok belum ditautkan">Pemasok belum ditautkan</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->name }}" @selected($tx->supplier_name === $sup->name)>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Waktu penerimaan</label>
                            <input type="datetime-local" name="received_at" value="{{ ($tx->received_at ?? now('Asia/Jakarta'))->timezone('Asia/Jakarta')->format('Y-m-d\TH:i') }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                        </div>
                        <div>
                            <button type="submit" class="min-h-[44px] w-full px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                Catat / Ubah
                            </button>
                        </div>
                    </form>
                @else
                    <p class="font-body text-micro text-ink-muted">Baris ini telah diverifikasi Ahli Gizi dan terkunci.</p>
                @endif
            </article>
        @endforeach
    </section>
</x-layouts.aslap>
