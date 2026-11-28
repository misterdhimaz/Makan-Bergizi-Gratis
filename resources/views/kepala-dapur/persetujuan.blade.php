<x-layouts.kepala-dapur title="Persetujuan & Koreksi" :badges="$badges">
    {{-- Tab Navigasi --}}
    <div class="flex flex-wrap gap-sm">
        <a href="{{ route('kepala-dapur.persetujuan.index', ['tab' => 'menunggu']) }}"
           class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $tab === 'menunggu' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface border border-border-soft text-ink' }}">
            Menunggu Persetujuan ({{ $menusPending->count() }})
        </a>
        <a href="{{ route('kepala-dapur.persetujuan.index', ['tab' => 'koreksi']) }}"
           class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $tab === 'koreksi' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface border border-border-soft text-ink' }}">
            Koreksi Data Terkunci ({{ $revisionsPending->count() }})
        </a>
        <a href="{{ route('kepala-dapur.persetujuan.index', ['tab' => 'disetujui']) }}"
           class="min-h-[44px] px-lg inline-flex items-center rounded-full font-body text-label-sm {{ $tab === 'disetujui' ? 'bg-obsidian text-on-obsidian font-semibold' : 'bg-surface border border-border-soft text-ink' }}">
            Sudah Disetujui ({{ $menusApproved->count() + $revisionsDecided->count() }})
        </a>
    </div>

    @if($tab === 'menunggu')
        <section class="flex flex-col gap-md">
            @if($menusPending->isEmpty())
                <div class="bg-surface rounded-xl p-xl border border-border-soft text-center font-body text-body-md text-ink-muted">
                    Tidak ada menu berstatus Lolos PAGU yang menunggu persetujuan Kepala Dapur saat ini.
                </div>
            @else
                @foreach($menusPending as $menu)
                    @php $check = $menu->latestPaguCheck; @endphp
                    <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                            <div>
                                <span class="font-mono text-label-sm text-ink-muted">{{ $menu->operational_date?->translatedFormat('l, j M Y') }}</span>
                                <h2 class="font-display text-section-title text-ink">{{ $menu->name }}</h2>
                            </div>
                            <span class="self-start px-md py-xs rounded-full bg-success-soft text-on-success-soft font-body text-label-sm">Lolos PAGU</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-sm">
                            @foreach($menu->categoryPortions as $cp)
                                <div class="p-sm rounded-md bg-surface-subtle border border-border-soft">
                                    <span class="font-body text-micro text-ink-muted">{{ $cp->recipientCategory?->name }}</span>
                                    <div class="font-mono text-card-title text-ink tabular-nums">{{ number_format($cp->target_portions, 0, ',', '.') }} porsi</div>
                                </div>
                            @endforeach
                        </div>

                        @if($check)
                            <div class="p-md rounded-lg bg-success-soft/40 border border-success-soft font-body text-body-md">
                                <span class="font-semibold text-ink">Lolos PAGU oleh {{ $check->checker?->name ?? 'Akuntan' }} · {{ $check->checked_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }} WIB</span>
                                <p class="text-ink-muted mt-xs">Catatan Akuntan: {{ $check->catatan }}</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('kepala-dapur.persetujuan.menu.decide', $menu) }}" class="border-t border-border-soft pt-md flex flex-col gap-md">
                            @csrf
                            <h3 class="font-display text-card-title text-ink">Keputusan Kepala Dapur</h3>
                            <div class="flex flex-wrap gap-md">
                                <label class="min-h-[44px] px-md inline-flex items-center gap-sm rounded-lg border border-border-soft cursor-pointer">
                                    <input type="radio" name="decision" value="setujui" checked class="accent-obsidian">
                                    <span class="font-body text-body-md text-ink">Setujui &amp; Kunci Menu</span>
                                </label>
                                <label class="min-h-[44px] px-md inline-flex items-center gap-sm rounded-lg border border-border-soft cursor-pointer">
                                    <input type="radio" name="decision" value="minta_revisi" class="accent-obsidian">
                                    <span class="font-body text-body-md text-ink">Minta Revisi</span>
                                </label>
                            </div>
                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">Catatan operasional / alasan revisi (wajib min. 5 karakter bila Minta Revisi)</label>
                                <textarea name="catatan" rows="2" class="w-full rounded-lg border border-border-soft bg-surface-subtle p-sm font-body text-body-md text-ink" placeholder="Tuliskan catatan untuk tim produksi atau Ahli Gizi..."></textarea>
                            </div>
                            <p class="font-body text-micro text-ink-muted">Menu yang disetujui akan terkunci dan hanya dapat dikoreksi melalui Audit Trail.</p>
                            <div class="flex flex-wrap gap-sm">
                                <button type="submit" class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                    Simpan Keputusan
                                </button>
                            </div>
                        </form>
                    </article>
                @endforeach
            @endif
        </section>
    @elseif($tab === 'koreksi')
        <section class="flex flex-col gap-md">
            @if($revisionsPending->isEmpty())
                <div class="bg-surface rounded-xl p-xl border border-border-soft text-center font-body text-body-md text-ink-muted">
                    Tidak ada permintaan koreksi data terkunci yang menunggu keputusan.
                </div>
            @else
                @foreach($revisionsPending as $rev)
                    <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                            <div>
                                <span class="font-body text-micro text-ink-muted">
                                    Pemohon: {{ $rev->requester?->name ?? '-' }} · {{ $rev->created_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }} WIB
                                </span>
                                <h2 class="font-display text-section-title text-ink">{{ $rev->field ?? 'Data Terkunci' }}</h2>
                            </div>
                            <span class="self-start px-md py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-label-sm">Menunggu Keputusan</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                            <div class="p-md rounded-lg bg-surface-subtle border border-border-soft">
                                <span class="font-body text-micro text-ink-muted">Nilai Lama (Terkunci)</span>
                                <p class="font-mono text-body-md text-ink mt-xs">{{ collect($rev->old_values ?? [])->map(fn ($v, $k) => "$k: $v")->implode(', ') }}</p>
                            </div>
                            <div class="p-md rounded-lg bg-success-soft/40 border border-success-soft">
                                <span class="font-body text-micro text-on-success-soft">Nilai Usulan Baru</span>
                                <p class="font-mono text-body-md text-ink mt-xs">{{ collect($rev->new_values ?? [])->map(fn ($v, $k) => "$k: $v")->implode(', ') }}</p>
                            </div>
                        </div>

                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft">
                            <span class="font-body text-micro text-ink-muted">Alasan Pemohon</span>
                            <p class="font-body text-body-md text-ink mt-xs">{{ $rev->reason }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-md pt-sm border-t border-border-soft">
                            <form method="POST" action="{{ route('kepala-dapur.persetujuan.koreksi.approve', $rev) }}">
                                @csrf
                                <button type="submit" class="min-h-[44px] w-full sm:w-auto px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                    Setujui Koreksi &amp; Catat Audit
                                </button>
                            </form>

                            <form method="POST" action="{{ route('kepala-dapur.persetujuan.koreksi.reject', $rev) }}" class="flex-1 flex flex-col sm:flex-row gap-sm">
                                @csrf
                                <input type="text" name="decision_notes" required placeholder="Alasan penolakan (min. 5 karakter)..." class="min-h-[44px] flex-1 rounded-full border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                                <button type="submit" class="min-h-[44px] px-lg rounded-full bg-danger-soft text-on-danger-soft font-body text-label-sm font-semibold cursor-pointer">
                                    Tolak Koreksi
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            @endif
        </section>
    @else
        <section class="flex flex-col gap-lg">
            <div class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <h2 class="font-display text-section-title text-ink">Menu Sudah Disetujui ({{ $menusApproved->count() }})</h2>
                <div class="flex flex-col gap-sm">
                    @foreach($menusApproved as $menu)
                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                            <div>
                                <span class="font-mono text-label-sm text-ink">{{ $menu->operational_date?->translatedFormat('j M Y') }}</span>
                                <p class="font-body text-body-md text-ink font-medium">{{ $menu->name }}</p>
                                <p class="font-body text-micro text-ink-muted">{{ $menu->last_note ?? $menu->approval_notes }}</p>
                            </div>
                            <span class="self-start px-md py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro">
                                Disetujui {{ $menu->approved_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <h2 class="font-display text-section-title text-ink">Riwayat Keputusan Koreksi ({{ $revisionsDecided->count() }})</h2>
                <div class="flex flex-col gap-sm">
                    @foreach($revisionsDecided as $rev)
                        <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-display text-card-title text-ink">{{ $rev->field ?? 'Koreksi Data' }}</span>
                                <span class="px-sm py-0.5 rounded-full text-micro {{ $rev->status === 'ditolak' ? 'bg-danger-soft text-on-danger-soft' : 'bg-success-soft text-on-success-soft' }}">
                                    {{ ucfirst($rev->status) }}
                                </span>
                            </div>
                            <p class="font-body text-body-md text-ink">{{ $rev->reason }}</p>
                            <span class="font-body text-micro text-ink-muted">
                                Pemohon: {{ $rev->requester?->name ?? '-' }} · Diputuskan oleh: {{ $rev->approver?->name ?? '-' }} ({{ $rev->approved_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }})
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.kepala-dapur>
