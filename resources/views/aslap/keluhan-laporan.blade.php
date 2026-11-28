<x-layouts.aslap title="Keluhan & Laporan" :complaintBadge="$complaintBadge">
    {{-- Kartu Keluhan Aktif & Tindak Lanjut --}}
    <section class="flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Daftar Keluhan Distribusi</h2>
        @foreach($complaints as $ticket)
            @php
                $isResolved = in_array($ticket->status, ['resolved', 'selesai'], true);
                $route = $routesToday->firstWhere('location_id', $ticket->location_id);
                $diff = $route && $route->confirmed_portions !== null ? ($route->confirmed_portions - $route->dispatched_portions) : null;
            @endphp
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                    <div>
                        <span class="font-mono text-micro text-ink-muted">{{ $ticket->ticket_number }} · {{ $ticket->location?->name }} · {{ $ticket->reported_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }} WIB</span>
                        <h3 class="font-display text-section-title text-ink">{{ $ticket->categoryLabel() }}</h3>
                    </div>
                    <span class="self-start px-md py-xs rounded-full text-micro {{ $isResolved ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }}">
                        {{ $isResolved ? 'Selesai' : 'Dalam Tindak Lanjut' }}
                    </span>
                </div>

                <p class="font-body text-body-md text-ink">{{ $ticket->description }}</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-sm font-body text-micro">
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                        <span class="text-ink-muted block">Pelapor</span>
                        <span class="text-ink font-semibold">{{ $ticket->reporter?->name ?? '-' }}</span>
                    </div>
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                        <span class="text-ink-muted block">Dikirim / Diterima</span>
                        <span class="font-mono text-ink tabular-nums">
                            {{ $route ? number_format($route->dispatched_portions, 0, ',', '.') : '-' }} / {{ $route?->confirmed_portions !== null ? number_format($route->confirmed_portions, 0, ',', '.') : 'Belum dikonfirmasi' }}
                        </span>
                    </div>
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                        <span class="text-ink-muted block">Selisih</span>
                        <span class="font-mono text-ink tabular-nums">{{ $diff !== null ? $diff : '-' }}</span>
                    </div>
                </div>

                @if(! $isResolved)
                    <form method="POST" action="{{ route('aslap.keluhan.handle', $ticket) }}" class="border-t border-border-soft pt-md flex flex-col gap-sm">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
                            <div>
                                <label class="block font-body text-micro text-ink mb-xs">Status Penanganan</label>
                                <select name="status" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                                    <option value="in_progress">Dalam Tindak Lanjut</option>
                                    <option value="resolved">Selesai</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-body text-micro text-ink mb-xs">Waktu Tindak Lanjut</label>
                                <input type="datetime-local" name="handled_at" value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                            </div>
                        </div>
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Tindakan Penyelesaian (wajib min. 10 karakter)</label>
                            <textarea name="resolution_notes" rows="2" required class="w-full rounded-lg border border-border-soft bg-surface-subtle p-sm font-body text-body-md text-ink" placeholder="Catat tindakan penyelesaian lapangan..."></textarea>
                        </div>
                        <div>
                            <button type="submit" class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                Catat Tindakan Penyelesaian
                            </button>
                        </div>
                    </form>
                @endif
            </article>
        @endforeach
    </section>

    {{-- Konfirmasi Penerimaan Hari Ini (7 Lokasi) --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Konfirmasi Penerimaan Hari Ini (7 Lokasi)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted uppercase">
                        <th class="py-sm pr-md">Lokasi</th>
                        <th class="py-sm pr-md">Guru PIC</th>
                        <th class="py-sm pr-md">Diterima / Dikirim</th>
                        <th class="py-sm">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft font-body text-body-md">
                    @foreach($routesToday as $r)
                        @php $diff = $r->confirmed_portions !== null ? ($r->confirmed_portions - $r->dispatched_portions) : null; @endphp
                        <tr>
                            <td class="py-md pr-md font-medium text-ink">{{ $r->location?->name }}</td>
                            <td class="py-md pr-md text-ink-muted">{{ $r->confirmer?->name ?? '-' }}</td>
                            <td class="py-md pr-md font-mono tabular-nums">
                                {{ $r->confirmed_portions !== null ? number_format($r->confirmed_portions, 0, ',', '.') : '-' }} / {{ number_format($r->dispatched_portions, 0, ',', '.') }}
                            </td>
                            <td class="py-md">
                                @if($r->confirmed_portions === null)
                                    <span class="px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft text-micro">Menunggu Konfirmasi</span>
                                @elseif($diff === 0)
                                    <span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft text-micro">Sesuai</span>
                                @else
                                    <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft text-micro">Kurang {{ abs($diff) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Laporan Operasional Distribusi & Keluhan --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md print:hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
            <div>
                <h2 class="font-display text-section-title text-ink">Laporan Operasional Distribusi &amp; Keluhan</h2>
                <p class="font-body text-micro text-ink-muted">Periode pratinjau: {{ $report['period_label'] }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('aslap.keluhan.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-sm items-end">
            <div>
                <label class="block font-body text-micro text-ink mb-xs">Jenis laporan</label>
                <select name="jenis" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                    <option value="harian" @selected($periodType === 'harian')>Harian</option>
                    <option value="7hari" @selected($periodType === '7hari')>Berkala 7 hari</option>
                    <option value="30hari" @selected($periodType === '30hari')>Berkala 30 hari</option>
                </select>
            </div>
            <div>
                <label class="block font-body text-micro text-ink mb-xs">Periode (tanggal akhir)</label>
                <input type="date" name="tanggal" value="{{ $selectedDate }}" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
            </div>
            <div>
                <button type="submit" class="min-h-[44px] w-full px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                    Terapkan Periode
                </button>
            </div>
        </form>

        @php $queryParams = ['jenis' => $periodType, 'tanggal' => $selectedDate]; @endphp
        <div class="flex flex-wrap gap-sm pt-sm border-t border-border-soft">
            <a href="{{ route('aslap.laporan.export.pdf', $queryParams) }}" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-danger-soft text-on-danger-soft font-body text-label-sm font-semibold">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">picture_as_pdf</span>
                <span>Ekspor PDF</span>
            </a>
            <a href="{{ route('aslap.laporan.export.excel', $queryParams) }}" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-success-soft text-on-success-soft font-body text-label-sm font-semibold">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">table_view</span>
                <span>Ekspor Excel (.xlsx)</span>
            </a>
            <a href="{{ route('aslap.laporan.export.csv', $queryParams) }}" class="min-h-[44px] px-lg inline-flex items-center gap-xs rounded-full bg-surface-subtle border border-border-soft text-ink font-body text-label-sm font-semibold">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">csv</span>
                <span>Ekspor CSV</span>
            </a>
        </div>
    </section>
</x-layouts.aslap>
