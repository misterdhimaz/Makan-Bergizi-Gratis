@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $fmtQty = static fn (int|float|null $n): string => $n === null ? '0' : (fmod((float) $n, 1.0) === 0.0 ? (string) (int) $n : number_format((float) $n, 1, ',', '.'));
@endphp

<x-layouts.ahli-gizi
    title="Persediaan & Pemorsian"
    subtitle="Kebutuhan bahan, verifikasi penerimaan, realisasi pemorsian, dan kendala"
    breadcrumb="Ahli Gizi · SPPG Sungai Selayur 1"
>
    <x-slot:actions>
        <form method="GET" action="{{ route('ahli-gizi.inventory.index') }}" class="flex items-center gap-sm">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <label class="inline-flex items-center gap-xs rounded-full bg-surface border border-border-soft px-md py-xs">
                <span class="material-symbols-outlined text-card-title text-ink-muted" aria-hidden="true">calendar_today</span>
                <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" aria-label="Pilih tanggal operasional" class="border-0 bg-transparent p-0 font-mono text-label-sm text-ink focus:ring-0">
            </label>
        </form>
    </x-slot:actions>

    <div
        x-data="{
            tab: '{{ $activeTab }}',
            needsOpen: false,
            verifOpen: false,
            verifUrl: '#',
            verifLabel: '',
            verifStatus: 'sesuai',
            verifNotes: '',
            realisasiOpen: false,
            omprengOpen: false,
            kendalaOpen: false,
            openVerif(url, label, status, notes) {
                this.verifUrl = url;
                this.verifLabel = label;
                this.verifStatus = status === 'tidak_sesuai' ? 'tidak_sesuai' : 'sesuai';
                this.verifNotes = notes || '';
                this.verifOpen = true;
            }
        }"
        class="flex flex-col gap-lg"
    >
        {{-- 4 Tabs --}}
        <div role="tablist" aria-label="Bagian Persediaan dan Pemorsian" class="flex flex-wrap items-center gap-xs bg-surface rounded-full p-xs border border-border-soft w-fit">
            @foreach([
                'kebutuhan' => 'Kebutuhan Bahan',
                'penerimaan' => 'Penerimaan Bahan',
                'realisasi' => 'Realisasi & Pemorsian',
                'kendala' => 'Kendala',
            ] as $key => $label)
                <button
                    type="button"
                    role="tab"
                    :aria-selected="(tab === '{{ $key }}').toString()"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-obsidian text-on-obsidian' : 'text-ink-muted hover:text-ink'"
                    class="px-lg py-sm rounded-full font-body text-label-sm whitespace-nowrap cursor-pointer transition-colors"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- TAB 1: Kebutuhan Bahan --}}
        <section x-show="tab === 'kebutuhan'" role="tabpanel" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <div class="flex flex-wrap items-center justify-between gap-sm">
                <div class="flex flex-col gap-xs">
                    <h2 class="font-display text-section-title text-ink">Rencana kebutuhan bahan untuk target {{ $fmt($targetPortions) }} porsi</h2>
                    <span class="font-body text-micro text-ink-muted">{{ $items->count() }} bahan · {{ $menu?->menu_name ?? 'Belum ada menu' }}</span>
                </div>
                @if($menu)
                    <button type="button" @click="needsOpen = true" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">edit_note</span>
                        Catat kebutuhan
                    </button>
                @endif
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Bahan</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Kategori</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Rencana kebutuhan</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @foreach($items as $item)
                            <tr>
                                <td class="py-sm pr-md font-body text-card-title text-ink">{{ $item->ingredient?->name }}</td>
                                <td class="py-sm px-md font-body text-body-md text-ink-muted">{{ $item->ingredient?->category }}</td>
                                <td class="py-sm px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmtQty($item->total_planned_qty) }}</td>
                                <td class="py-sm pl-md font-body text-body-md text-ink-muted">{{ $item->ingredient?->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden flex flex-col gap-sm">
                @foreach($items as $item)
                    <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex items-center justify-between gap-sm">
                        <div>
                            <p class="font-body text-card-title text-ink">{{ $item->ingredient?->name }}</p>
                            <p class="font-body text-micro text-ink-muted">{{ $item->ingredient?->category }}</p>
                        </div>
                        <span class="font-mono text-label-sm text-ink tabular-nums">{{ $fmtQty($item->total_planned_qty) }} {{ $item->ingredient?->unit }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- TAB 2: Penerimaan Bahan --}}
        <section x-show="tab === 'penerimaan'" x-cloak role="tabpanel" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <div class="flex flex-col gap-xs">
                <h2 class="font-display text-section-title text-ink">Penerimaan Bahan · Verifikasi Ahli Gizi</h2>
                <span class="font-body text-micro text-ink-muted">Dicatat oleh Asisten Lapangan (Bima Santoso) dan diverifikasi oleh Ahli Gizi (Nadia Putri)</span>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Bahan</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Pemasok asal</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Rencana</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Diterima</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Status</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @foreach($receipts as $trx)
                            @php
                                $unit = $trx->ingredient?->unit ?? 'kg';
                                $vLabel = match ($trx->verification_status) {
                                    'sesuai' => 'Sesuai',
                                    'tidak_sesuai' => 'Tidak sesuai',
                                    default => 'Menunggu verifikasi',
                                };
                                $vVariant = match ($trx->verification_status) {
                                    'sesuai' => 'success',
                                    'tidak_sesuai' => 'danger',
                                    default => 'warning',
                                };
                            @endphp
                            <tr>
                                <td class="py-md pr-md font-body text-card-title text-ink">{{ $trx->ingredient?->name }}</td>
                                <td class="py-md px-md font-body text-body-md text-ink">{{ $trx->supplier_name ?? 'Pemasok belum ditautkan' }}</td>
                                <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmtQty($trx->planned_qty) }} {{ $unit }}</td>
                                <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmtQty($trx->quantity) }} {{ $unit }}</td>
                                <td class="py-md px-md"><x-badge-status :label="$vLabel" :variant="$vVariant" /></td>
                                <td class="py-md pl-md text-right">
                                    @if($trx->verification_status === 'sesuai')
                                        <span class="font-body text-micro text-ink-muted">Terverifikasi</span>
                                    @else
                                        <button
                                            type="button"
                                            @click="openVerif('{{ route('ahli-gizi.inventory.verify', $trx) }}', '{{ $trx->ingredient?->name }} · Rencana {{ $fmtQty($trx->planned_qty) }} {{ $unit }} · Diterima {{ $fmtQty($trx->quantity) }} {{ $unit }}', '{{ $trx->verification_status }}', @js($trx->verification_notes ?? ''))"
                                            class="px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro cursor-pointer"
                                        >
                                            Verifikasi
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden flex flex-col gap-sm">
                @foreach($receipts as $trx)
                    @php
                        $unit = $trx->ingredient?->unit ?? 'kg';
                        $vLabel = $trx->verification_status === 'sesuai' ? 'Sesuai' : ($trx->verification_status === 'tidak_sesuai' ? 'Tidak sesuai' : 'Menunggu verifikasi');
                    @endphp
                    <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-body text-card-title text-ink">{{ $trx->ingredient?->name }}</span>
                            <span class="font-body text-micro text-ink-muted">{{ $vLabel }}</span>
                        </div>
                        <p class="font-mono text-micro text-ink-muted">Rencana {{ $fmtQty($trx->planned_qty) }} {{ $unit }} · Diterima {{ $fmtQty($trx->quantity) }} {{ $unit }}</p>
                        @if($trx->verification_status !== 'sesuai')
                            <button
                                type="button"
                                @click="openVerif('{{ route('ahli-gizi.inventory.verify', $trx) }}', '{{ $trx->ingredient?->name }}', '{{ $trx->verification_status }}', @js($trx->verification_notes ?? ''))"
                                class="mt-xs w-full px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro cursor-pointer"
                            >
                                Verifikasi
                            </button>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        {{-- TAB 3: Realisasi & Pemorsian --}}
        <section x-show="tab === 'realisasi'" x-cloak role="tabpanel" class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start">
            <div class="lg:col-span-2 bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                <div class="flex flex-wrap items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-ink">Realisasi Penggunaan Bahan</h2>
                    @if($items->isNotEmpty())
                        <button type="button" @click="realisasiOpen = true" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">
                            <span class="material-symbols-outlined text-card-title" aria-hidden="true">edit_note</span>
                            Catat realisasi
                        </button>
                    @endif
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-soft">
                                <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Bahan</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Rencana</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Diserahkan ke persiapan</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right">Digunakan</th>
                                <th class="py-sm pl-md font-body text-label-sm text-ink-muted text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft">
                            @foreach($items as $item)
                                @php
                                    $unit = $item->ingredient?->unit ?? 'kg';
                                    $diff = $item->actual_used_qty !== null ? ((float) $item->actual_used_qty - (float) $item->total_planned_qty) : 0;
                                @endphp
                                <tr class="{{ $diff != 0 ? 'bg-alert-soft/40' : '' }}">
                                    <td class="py-sm pr-md font-body text-card-title text-ink">{{ $item->ingredient?->name }}</td>
                                    <td class="py-sm px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmtQty($item->total_planned_qty) }} {{ $unit }}</td>
                                    <td class="py-sm px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmtQty($item->dispatched_qty) }} {{ $unit }}</td>
                                    <td class="py-sm px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmtQty($item->actual_used_qty) }} {{ $unit }}</td>
                                    <td class="py-sm pl-md text-right">
                                        @if($diff != 0)
                                            <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-mono text-micro tabular-nums">{{ $diff > 0 ? '+' : '' }}{{ $fmtQty($diff) }} {{ $unit }}</span>
                                        @else
                                            <span class="font-mono text-micro text-ink-muted tabular-nums">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($items as $item)
                        @php($unit = $item->ingredient?->unit ?? 'kg')
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                            <span class="font-body text-card-title text-ink">{{ $item->ingredient?->name }}</span>
                            <span class="font-mono text-micro text-ink-muted">Rencana {{ $fmtQty($item->total_planned_qty) }} · Diserahkan {{ $fmtQty($item->dispatched_qty) }} · Digunakan {{ $fmtQty($item->actual_used_qty) }} {{ $unit }}</span>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="bg-lime-accent rounded-xl p-xl flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-on-lime">Jumlah ompreng terisi</h2>
                    <span class="px-sm py-xs rounded-full bg-success-soft text-on-success-soft font-mono text-micro">{{ $omprengPct }}%</span>
                </div>
                <div class="flex items-baseline gap-sm">
                    <span class="font-display text-kpi text-on-lime tabular-nums">{{ $fmt($omprengFilled) }}</span>
                    <span class="font-mono text-body-md text-on-lime tabular-nums">dari {{ $fmt($targetPortions) }} porsi</span>
                </div>
                <div class="w-full h-2 rounded-full bg-obsidian/15 overflow-hidden">
                    <div class="h-full rounded-full bg-obsidian" style="width: {{ $omprengPct }}%;"></div>
                </div>
                <p class="font-body text-micro text-on-lime">Seluruh porsi untuk 7 titik penerima dicatat dalam ompreng.</p>
                @if($menu)
                    <button type="button" @click="omprengOpen = true" class="inline-flex items-center justify-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true">inventory_2</span>
                        Catat ompreng terisi
                    </button>
                @endif
            </aside>
        </section>

        {{-- TAB 4: Kendala --}}
        <section x-show="tab === 'kendala'" x-cloak role="tabpanel" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <div class="flex flex-wrap items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink">Catatan Kendala Bahan &amp; Pemorsian</h2>
                <button type="button" @click="kendalaOpen = true" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
                    Tambah catatan kendala
                </button>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Tanggal</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Uraian kendala</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Status tindak lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @forelse($obstacles as $obs)
                            <tr>
                                <td class="py-md pr-md font-mono text-label-sm text-ink whitespace-nowrap">{{ $obs->operational_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-md px-md font-body text-body-md text-ink">{{ $obs->description }}</td>
                                <td class="py-md pl-md">
                                    <x-badge-status :label="$obs->status === 'ditindaklanjuti' ? 'Ditindaklanjuti' : 'Terbuka'" :variant="$obs->status === 'ditindaklanjuti' ? 'success' : 'warning'" />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-md"><x-empty-state icon="info" title="Belum ada kendala" description="Catatan kendala bahan & pemorsian akan tampil di sini." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden flex flex-col gap-sm">
                @foreach($obstacles as $obs)
                    <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                        <div class="flex items-center justify-between gap-sm">
                            <span class="font-mono text-micro text-ink-muted">{{ $obs->operational_date?->translatedFormat('d M Y') }}</span>
                            <x-badge-status :label="$obs->status === 'ditindaklanjuti' ? 'Ditindaklanjuti' : 'Terbuka'" :variant="$obs->status === 'ditindaklanjuti' ? 'success' : 'warning'" />
                        </div>
                        <p class="font-body text-body-md text-ink">{{ $obs->description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Dialog/Sheet Catat Kebutuhan --}}
        @if($menu)
            <x-slide-panel open-var="needsOpen" title="Catat kebutuhan bahan" subtitle="Menu {{ $menu->operational_date?->translatedFormat('d M Y') }}">
                <form method="POST" action="{{ route('ahli-gizi.inventory.needs.store') }}" class="flex flex-col gap-md">
                    @csrf
                    <input type="hidden" name="daily_menu_id" value="{{ $menu->id }}">
                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Bahan</span>
                        <select name="ingredient_id" required class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Total rencana kebutuhan</span>
                        <input type="number" name="total_planned_qty" step="0.1" min="0.1" required class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </label>
                    <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                        <button type="button" @click="needsOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                        <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Simpan Kebutuhan</button>
                    </div>
                </form>
            </x-slide-panel>
        @endif

        {{-- Dialog/Sheet Verifikasi Penerimaan Bahan --}}
        <x-slide-panel open-var="verifOpen" title="Verifikasi penerimaan bahan" subtitle="Pemeriksaan kesesuaian bahan oleh Ahli Gizi">
            <form method="POST" :action="verifUrl" class="flex flex-col gap-md">
                @csrf
                <p class="font-body text-micro text-ink-muted" x-text="verifLabel"></p>
                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Hasil pemeriksaan</span>
                    <select name="verification_status" x-model="verifStatus" class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                        <option value="sesuai">Sesuai</option>
                        <option value="tidak_sesuai">Tidak sesuai</option>
                    </select>
                </label>
                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Catatan verifikasi</span>
                    <textarea name="verification_notes" x-model="verifNotes" rows="3" placeholder="Tambahkan catatan kondisi bahan..." class="w-full rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink"></textarea>
                </label>
                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="verifOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Simpan Verifikasi</button>
                </div>
            </form>
        </x-slide-panel>

        {{-- Dialog/Sheet Catat Realisasi --}}
        <x-slide-panel open-var="realisasiOpen" title="Catat realisasi" subtitle="Isi jumlah diserahkan ke persiapan & digunakan">
            <form method="POST" action="{{ route('ahli-gizi.inventory.realization.store') }}" class="flex flex-col gap-md">
                @csrf
                <div class="flex flex-col gap-sm max-h-96 overflow-y-auto pr-xs">
                    @foreach($items as $idx => $item)
                        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}">
                        <div class="grid grid-cols-12 items-center gap-sm">
                            <span class="col-span-4 font-body text-body-md text-ink">{{ $item->ingredient?->name }} ({{ $item->ingredient?->unit }})</span>
                            <input type="number" step="0.1" min="0" name="items[{{ $idx }}][dispatched_qty]" value="{{ $item->dispatched_qty ?? $item->total_planned_qty }}" required class="col-span-4 rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-mono text-body-md text-ink">
                            <input type="number" step="0.1" min="0" name="items[{{ $idx }}][actual_used_qty]" value="{{ $item->actual_used_qty ?? $item->dispatched_qty ?? $item->total_planned_qty }}" required class="col-span-4 rounded-md border border-border-soft bg-surface px-sm py-xs font-mono text-body-md text-ink">
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="realisasiOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Simpan Realisasi</button>
                </div>
            </form>
        </x-slide-panel>

        {{-- Dialog/Sheet Catat Ompreng Terisi --}}
        @if($menu)
            <x-slide-panel open-var="omprengOpen" title="Catat ompreng terisi" subtitle="Target {{ $fmt($targetPortions) }} porsi">
                <form method="POST" action="{{ route('ahli-gizi.inventory.ompreng.store') }}" class="flex flex-col gap-md">
                    @csrf
                    <input type="hidden" name="daily_menu_id" value="{{ $menu->id }}">
                    <label class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink">Ompreng terisi</span>
                        <input type="number" name="actual_good_portions" min="0" max="{{ $targetPortions }}" value="{{ $omprengFilled }}" required class="w-full rounded-md border border-border-soft bg-surface px-md py-sm font-mono text-body-md text-ink">
                        <span class="font-body text-micro text-ink-muted">Jumlah ompreng tidak boleh melebihi target porsi {{ $fmt($targetPortions) }}.</span>
                    </label>
                    <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                        <button type="button" @click="omprengOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                        <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Simpan Ompreng</button>
                    </div>
                </form>
            </x-slide-panel>
        @endif

        {{-- Dialog/Sheet Tambah Catatan Kendala --}}
        <x-slide-panel open-var="kendalaOpen" title="Tambah catatan kendala" subtitle="Kendala bahan & pemorsian">
            <form method="POST" action="{{ route('ahli-gizi.inventory.obstacles.store') }}" class="flex flex-col gap-md">
                @csrf
                <input type="hidden" name="operational_date" value="{{ $selectedDate }}">
                @if($menu)
                    <input type="hidden" name="daily_menu_id" value="{{ $menu->id }}">
                @endif
                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Uraian kendala <span class="text-on-danger-soft">*</span></span>
                    <textarea name="description" rows="3" required minlength="5" placeholder="Tuliskan kendala bahan atau pemorsian..." class="w-full rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink"></textarea>
                </label>
                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="kendalaOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Simpan Kendala</button>
                </div>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.ahli-gizi>
