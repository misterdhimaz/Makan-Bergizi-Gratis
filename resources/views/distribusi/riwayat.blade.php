<x-layouts.tim-distribusi title="Riwayat & Kendala">
    {{-- Kendala Pengiriman (Hanya Baca) --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Kendala Pengiriman</h2>
        <div class="flex flex-col gap-sm">
            @forelse($obstacles as $obs)
                <article class="p-md rounded-lg bg-alert-soft/40 border border-alert-soft flex flex-col gap-xs">
                    <div class="flex flex-wrap items-center justify-between gap-sm">
                        <span class="font-mono text-label-sm text-ink">{{ $obs->ticket_number }} · {{ $obs->location?->name }}</span>
                        <span class="px-sm py-0.5 rounded-full text-micro bg-surface border border-border-soft text-ink">{{ $obs->categoryLabel() }}</span>
                    </div>
                    <p class="font-body text-body-md text-ink">{{ $obs->description }}</p>
                    @if($obs->resolution_notes)
                        <p class="font-body text-micro text-ink-muted">Instruksi / Tindak Lanjut: <strong>{{ $obs->resolution_notes }}</strong></p>
                    @endif
                </article>
            @empty
                <p class="font-body text-body-md text-ink-muted">Tidak ada kendala pengiriman tercatat.</p>
            @endforelse
        </div>
    </section>

    {{-- Log Serah Terima 5 Hari Terakhir (Desktop & Mobile Setara) --}}
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
        <h2 class="font-display text-section-title text-ink">Log Serah Terima (5 Hari Terakhir)</h2>

        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted uppercase">
                        <th class="py-sm pr-md">Tanggal &amp; Waktu</th>
                        <th class="py-sm pr-md">Tujuan</th>
                        <th class="py-sm pr-md">Penerima</th>
                        <th class="py-sm pr-md">Jumlah / Target</th>
                        <th class="py-sm">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft font-body text-body-md">
                    @foreach($logs as $log)
                        @php
                            $actual = $log->confirmed_portions ?? $log->jumlah_diserahkan;
                            $diff = $actual !== null ? ($actual - $log->dispatched_portions) : null;
                        @endphp
                        <tr>
                            <td class="py-md pr-md font-mono text-label-sm">{{ $log->operational_date?->translatedFormat('j M Y') }} · {{ $log->arrived_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-' }}</td>
                            <td class="py-md pr-md text-ink">{{ $log->location?->name }}</td>
                            <td class="py-md pr-md text-ink-muted">{{ $log->nama_penerima_serah ?? $log->confirmer?->name ?? '-' }}</td>
                            <td class="py-md pr-md font-mono tabular-nums">{{ $actual ?? '-' }} / {{ $log->dispatched_portions }}</td>
                            <td class="py-md">
                                @if($diff === null)
                                    <span class="px-sm py-0.5 rounded-full bg-surface-subtle text-micro">{{ $log->status->label() }}</span>
                                @elseif($diff === 0)
                                    <span class="px-sm py-0.5 rounded-full bg-success-soft text-on-success-soft text-micro">Lengkap</span>
                                @else
                                    <span class="px-sm py-0.5 rounded-full bg-danger-soft text-on-danger-soft text-micro">Kurang {{ abs($diff) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="lg:hidden flex flex-col gap-sm">
            @foreach($logs as $log)
                @php
                    $actual = $log->confirmed_portions ?? $log->jumlah_diserahkan;
                    $diff = $actual !== null ? ($actual - $log->dispatched_portions) : null;
                @endphp
                <article class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-mono text-micro text-ink-muted">Waktu: {{ $log->operational_date?->translatedFormat('j M Y') }} · {{ $log->arrived_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-' }}</span>
                        <span class="px-sm py-0.5 rounded-full text-micro {{ $diff === 0 ? 'bg-success-soft text-on-success-soft' : ($diff !== null ? 'bg-danger-soft text-on-danger-soft' : 'bg-surface border border-border-soft') }}">
                            {{ $diff === null ? $log->status->label() : ($diff === 0 ? 'Lengkap' : 'Kurang '.abs($diff)) }}
                        </span>
                    </div>
                    <p class="font-display text-card-title text-ink">Tujuan: {{ $log->location?->name }}</p>
                    <p class="font-body text-micro text-ink-muted">Penerima: {{ $log->nama_penerima_serah ?? $log->confirmer?->name ?? '-' }}</p>
                    <p class="font-mono text-micro text-ink tabular-nums">Jumlah / Target: {{ $actual ?? '-' }} / {{ $log->dispatched_portions }}</p>
                </article>
            @endforeach
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </section>
</x-layouts.tim-distribusi>
