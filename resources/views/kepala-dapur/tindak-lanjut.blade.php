<x-layouts.kepala-dapur title="Tindak Lanjut" :badges="$badges">
    <div class="flex flex-wrap gap-sm">
        <a href="{{ route('kepala-dapur.tindak-lanjut.index', ['tab' => 'temuan']) }}"
           class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $tab === 'temuan' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface border border-border-soft text-ink' }}">
            Temuan Keamanan Pangan &amp; APD ({{ $findings->where('status', 'terbuka')->count() }})
        </a>
        <a href="{{ route('kepala-dapur.tindak-lanjut.index', ['tab' => 'keluhan']) }}"
           class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $tab === 'keluhan' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface border border-border-soft text-ink' }}">
            Keluhan ({{ $complaints->whereNotIn('status', ['resolved', 'selesai'])->count() }})
        </a>
        <a href="{{ route('kepala-dapur.tindak-lanjut.index', ['tab' => 'kendala']) }}"
           class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $tab === 'kendala' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface border border-border-soft text-ink' }}">
            Kendala Produksi ({{ $obstacles->count() }})
        </a>
    </div>

    @if($tab === 'temuan')
        @if($latestSafetyCheck)
            <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-sm">
                <h2 class="font-display text-card-title text-ink">Konteks Checklist Terakhir ({{ $latestSafetyCheck->check_date?->translatedFormat('j M Y') }} · Shift {{ ucfirst($latestSafetyCheck->shift) }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-sm font-body text-body-md">
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                        <span class="text-micro text-ink-muted block">Kepatuhan APD</span>
                        <span class="font-mono font-semibold text-ink tabular-nums">{{ $latestSafetyCheck->compliant_volunteers }}/{{ $latestSafetyCheck->total_volunteers }} ({{ $latestSafetyCheck->compliancePercent() }})</span>
                    </div>
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                        <span class="text-micro text-ink-muted block">Pemeriksaan Suhu</span>
                        <span class="text-ink">{{ count($latestSafetyCheck->temperatures_json ?? []) }} titik dicatat</span>
                    </div>
                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                        <span class="text-micro text-ink-muted block">Kebersihan Dapur</span>
                        <span class="text-ink">{{ count($latestSafetyCheck->hygiene_json ?? []) > 0 ? count($latestSafetyCheck->hygiene_json).' item' : 'Belum diisi' }}</span>
                    </div>
                </div>
            </section>
        @endif

        <section class="flex flex-col gap-md">
            @foreach($findings as $finding)
                <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                        <div>
                            <span class="font-mono text-micro text-ink-muted">{{ $finding->finding_date?->translatedFormat('j M Y') }} · {{ $finding->type }}</span>
                            <h3 class="font-display text-section-title text-ink">{{ $finding->description }}</h3>
                        </div>
                        <span class="self-start px-md py-xs rounded-full text-micro {{ $finding->status === 'terbuka' ? 'bg-danger-soft text-on-danger-soft' : 'bg-success-soft text-on-success-soft' }}">
                            {{ $finding->status === 'terbuka' ? 'Terbuka' : 'Ditindaklanjuti' }}
                        </span>
                    </div>

                    @if($finding->status === 'terbuka')
                        <form method="POST" action="{{ route('kepala-dapur.tindak-lanjut.temuan.resolve', $finding) }}" class="border-t border-border-soft pt-md flex flex-col gap-sm">
                            @csrf
                            <label class="font-body text-label-sm font-semibold text-ink">Catat Tindakan Korektif (wajib min. 10 karakter)</label>
                            <textarea name="handler_note" rows="2" required class="w-full rounded-lg border border-border-soft bg-surface-subtle p-sm font-body text-body-md text-ink" placeholder="Tuliskan tindakan korektif yang telah dilakukan..."></textarea>
                            <div>
                                <button type="submit" class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                    Tandai Ditindaklanjuti
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-md rounded-lg bg-success-soft/40 border border-success-soft font-body text-body-md">
                            <span class="font-semibold text-ink">Tindakan ({{ $finding->handler?->name ?? 'Kepala Dapur' }}):</span>
                            <p class="text-ink mt-xs">{{ $finding->handler_note }}</p>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    @elseif($tab === 'keluhan')
        <section class="flex flex-col gap-md">
            @foreach($complaints as $ticket)
                @php
                    $isSerious = in_array($ticket->severity, ['kritis', 'serius'], true);
                    $isResolved = in_array($ticket->status, ['resolved', 'selesai'], true);
                @endphp
                <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                        <div>
                            <span class="font-mono text-label-sm text-ink-muted">{{ $ticket->ticket_number }} · {{ $ticket->location?->name }} · {{ $ticket->reported_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }} WIB</span>
                            <h3 class="font-display text-section-title text-ink">{{ $ticket->categoryLabel() }}</h3>
                        </div>
                        <div class="flex items-center gap-xs">
                            <span class="px-sm py-0.5 rounded-full text-micro {{ $isSerious ? 'bg-danger-soft text-on-danger-soft' : 'bg-surface-subtle border border-border-soft text-ink-muted' }}">
                                {{ $isSerious ? 'Serius' : 'Normal' }}
                            </span>
                            <span class="px-sm py-0.5 rounded-full text-micro {{ $isResolved ? 'bg-success-soft text-on-success-soft' : 'bg-alert-soft text-on-alert-soft' }}">
                                {{ $isResolved ? 'Selesai' : 'Dalam Tindak Lanjut' }}
                            </span>
                        </div>
                    </div>

                    <p class="font-body text-body-md text-ink">{{ $ticket->description }}</p>

                    @if($ticket->resolution_notes)
                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft font-body text-body-md">
                            <span class="text-micro text-ink-muted block">Tindak lanjut terakhir ({{ $ticket->resolver?->name ?? '-' }}):</span>
                            <span class="text-ink">{{ $ticket->resolution_notes }}</span>
                        </div>
                    @endif

                    @if($isSerious && ! $isResolved)
                        <form method="POST" action="{{ route('kepala-dapur.tindak-lanjut.keluhan.handle', $ticket) }}" class="border-t border-border-soft pt-md flex flex-col gap-sm">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
                                <div>
                                    <label class="block font-body text-label-sm text-ink mb-xs">Status Penanganan</label>
                                    <select name="status" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                                        <option value="in_progress">Dalam Tindak Lanjut</option>
                                        <option value="resolved">Selesai</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">Tindakan Penyelesaian (wajib min. 10 karakter untuk Selesai)</label>
                                <textarea name="resolution_notes" rows="2" required class="w-full rounded-lg border border-border-soft bg-surface-subtle p-sm font-body text-body-md text-ink" placeholder="Catat tindakan penyelesaian keluhan serius..."></textarea>
                            </div>
                            <div>
                                <button type="submit" class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                    Simpan Tindak Lanjut
                                </button>
                            </div>
                        </form>
                    @elseif(! $isSerious)
                        <p class="font-body text-micro text-ink-muted">Keluhan prioritas normal bersifat hanya baca untuk Kepala Dapur (ditangani oleh Asisten Lapangan / Tim Distribusi).</p>
                    @endif
                </article>
            @endforeach
        </section>
    @else
        <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
            <h2 class="font-display text-section-title text-ink">Kendala Produksi (Hanya Baca)</h2>
            <div class="flex flex-col gap-sm">
                @foreach($obstacles as $obs)
                    <article class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                        <div>
                            <span class="font-mono text-micro text-ink-muted">{{ $obs->obstacle_date?->translatedFormat('j M Y') }} · Dicatat {{ $obs->recorder?->name ?? '-' }}</span>
                            <p class="font-body text-body-md text-ink mt-xs">{{ $obs->description }}</p>
                        </div>
                        <span class="self-start px-sm py-0.5 rounded-full text-micro {{ $obs->status === 'terbuka' ? 'bg-alert-soft text-on-alert-soft' : 'bg-success-soft text-on-success-soft' }}">
                            {{ ucfirst($obs->status) }}
                        </span>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.kepala-dapur>
