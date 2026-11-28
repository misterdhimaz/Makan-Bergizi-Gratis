<x-layouts.kepala-dapur title="Laporan & Ekspor" :badges="$badges">
    @php
        $queryParams = [
            'jenis' => $periodType,
            'tanggal' => $selectedDate,
            'lokasi_id' => $selectedLocationId,
        ];
        $summary = $report['summary'];
    @endphp

    {{-- Filter & Tombol Ekspor --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md print:hidden">
        <form method="GET" action="{{ route('kepala-dapur.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-md items-end">
            <div>
                <label class="block font-body text-label-sm text-ink mb-xs">Jenis laporan</label>
                <select name="jenis" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                    <option value="harian" @selected($periodType === 'harian')>Harian</option>
                    <option value="7hari" @selected($periodType === '7hari')>Berkala 7 hari</option>
                    <option value="30hari" @selected($periodType === '30hari')>Berkala 30 hari</option>
                </select>
            </div>
            <div>
                <label class="block font-body text-label-sm text-ink mb-xs">Periode (tanggal akhir)</label>
                <input type="date" name="tanggal" value="{{ $selectedDate }}" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
            </div>
            <div>
                <label class="block font-body text-label-sm text-ink mb-xs">Lokasi Penerima</label>
                <select name="lokasi_id" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                    <option value="">Semua Lokasi (7 titik)</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected($selectedLocationId === $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="min-h-[44px] w-full px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                    Terapkan Filter
                </button>
            </div>
        </form>

        <div class="flex flex-wrap gap-sm pt-sm border-t border-border-soft">
            <a href="{{ route('kepala-dapur.laporan.export.pdf', $queryParams) }}" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-danger-soft text-on-danger-soft font-body text-label-sm font-semibold">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">picture_as_pdf</span>
                <span>Ekspor PDF</span>
            </a>
            <a href="{{ route('kepala-dapur.laporan.export.excel', $queryParams) }}" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-success-soft text-on-success-soft font-body text-label-sm font-semibold">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">table_view</span>
                <span>Ekspor Excel (.xlsx)</span>
            </a>
            <a href="{{ route('kepala-dapur.laporan.export.csv', $queryParams) }}" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-surface-subtle border border-border-soft text-ink font-body text-label-sm font-semibold">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">csv</span>
                <span>Ekspor CSV</span>
            </a>
            <button type="button" onclick="window.print()" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-surface-subtle border border-border-soft text-ink font-body text-label-sm font-semibold cursor-pointer">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">print</span>
                <span>Cetak</span>
            </button>
        </div>
    </section>

    {{-- Pratinjau Laporan Operasional Harian --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
            <div>
                <h2 class="font-display text-section-title text-ink">Laporan Operasional Harian</h2>
                <p class="font-body text-micro text-ink-muted">Periode: {{ $report['period_label'] }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted uppercase">
                        <th class="py-sm pr-md">Modul</th>
                        <th class="py-sm pr-md">Ringkasan Realisasi</th>
                        <th class="py-sm pr-md">Penanggung Jawab</th>
                        <th class="py-sm">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft font-body text-body-md">
                    <tr>
                        <td class="py-md pr-md font-semibold text-ink">Menu &amp; Pemorsian</td>
                        <td class="py-md pr-md tabular-nums">{{ $summary['menus_approved'] }} dari {{ $summary['menus_total'] }} menu disetujui</td>
                        <td class="py-md pr-md text-ink-muted">Ahli Gizi &amp; Kepala Dapur</td>
                        <td class="py-md"><span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft text-micro">Terkendali</span></td>
                    </tr>
                    <tr>
                        <td class="py-md pr-md font-semibold text-ink">Persediaan &amp; Selisih Bahan</td>
                        <td class="py-md pr-md tabular-nums">{{ $report['procurements']->count() }} transaksi penerimaan dicatat</td>
                        <td class="py-md pr-md text-ink-muted">Asisten Lapangan &amp; Ahli Gizi</td>
                        <td class="py-md"><span class="px-sm py-0.5 rounded-full bg-surface-subtle border border-border-soft text-micro">Terpantau</span></td>
                    </tr>
                    <tr>
                        <td class="py-md pr-md font-semibold text-ink">Keamanan Pangan &amp; APD</td>
                        <td class="py-md pr-md tabular-nums">{{ $report['safety_checks']->count() }} pemeriksaan · {{ $summary['safety_findings_open'] }} temuan terbuka</td>
                        <td class="py-md pr-md text-ink-muted">Ahli Gizi &amp; Kepala Dapur</td>
                        <td class="py-md">
                            <span class="px-sm py-0.5 rounded-full text-micro {{ $summary['safety_findings_open'] > 0 ? 'bg-danger-soft text-on-danger-soft' : 'bg-success-soft text-on-success-soft' }}">
                                {{ $summary['safety_findings_open'] > 0 ? 'Perlu Tindak Lanjut' : 'Patuh' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-md pr-md font-semibold text-ink">Distribusi &amp; Penerimaan</td>
                        <td class="py-md pr-md tabular-nums">
                            Dikirim {{ number_format($summary['total_dispatched'], 0, ',', '.') }} · Diterima {{ number_format($summary['total_confirmed'], 0, ',', '.') }} · Selisih {{ $summary['total_variance'] }} ({{ $summary['confirmed_count'] }}/{{ $summary['total_routes'] }} titik)
                        </td>
                        <td class="py-md pr-md text-ink-muted">Tim Distribusi &amp; Guru PIC</td>
                        <td class="py-md"><span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft text-micro">{{ $summary['complaints_total'] }} keluhan</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.kepala-dapur>
