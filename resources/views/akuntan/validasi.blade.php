@php
    $initialOpenSheet = $errors->any() || request()->boolean('open');
@endphp

<x-layouts.akuntan
    title="Validasi Anggaran Menu"
    subtitle="Catat hasil pemeriksaan kesesuaian anggaran menu terhadap batas PAGU."
    breadcrumb="Akuntan · Validasi Anggaran Menu"
>
    <div
        x-data="{
            activeTab: @js($tab),
            selectedId: @js($selectedMenu?->id),
            mobileSheetOpen: @js($initialOpenSheet),
            selectMenu(id, openSheet = true) {
                this.selectedId = id;
                if (openSheet && window.innerWidth < 1280) {
                    this.mobileSheetOpen = true;
                }
            }
        }"
        @keydown.escape.window="mobileSheetOpen = false"
        class="flex flex-col gap-lg"
    >
        {{-- Tabs --}}
        <div class="flex gap-xs overflow-x-auto pb-xs" role="tablist" aria-label="Filter status validasi menu">
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === 'menunggu'"
                @click="activeTab = 'menunggu'; @if($waitingMenus->first()) selectedId = {{ $waitingMenus->first()->id }}; @endif"
                :class="activeTab === 'menunggu' ? 'bg-obsidian text-on-obsidian' : 'bg-surface border border-border-soft text-ink hover:bg-surface-subtle'"
                class="min-h-[44px] px-lg rounded-full font-body text-label-sm whitespace-nowrap shrink-0 cursor-pointer transition-colors"
            >
                Menunggu ({{ $waitingMenus->count() }})
            </button>

            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === 'sudah'"
                @click="activeTab = 'sudah'; @if($checkedMenus->first()) selectedId = {{ $checkedMenus->first()->id }}; @endif"
                :class="activeTab === 'sudah' ? 'bg-obsidian text-on-obsidian' : 'bg-surface border border-border-soft text-ink hover:bg-surface-subtle'"
                class="min-h-[44px] px-lg rounded-full font-body text-label-sm whitespace-nowrap shrink-0 cursor-pointer transition-colors"
            >
                Sudah diperiksa ({{ $checkedMenus->count() }})
            </button>

            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === 'semua'"
                @click="activeTab = 'semua'; @if($allMenus->first()) selectedId = {{ $allMenus->first()->id }}; @endif"
                :class="activeTab === 'semua' ? 'bg-obsidian text-on-obsidian' : 'bg-surface border border-border-soft text-ink hover:bg-surface-subtle'"
                class="min-h-[44px] px-lg rounded-full font-body text-label-sm whitespace-nowrap shrink-0 cursor-pointer transition-colors"
            >
                Semua menu · Hanya lihat ({{ $allMenus->count() }})
            </button>
        </div>

        {{-- Main Split Layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-lg items-start">
            {{-- Left: Menu List --}}
            <div class="xl:col-span-7 flex flex-col gap-md">
                {{-- Tab: Menunggu --}}
                <section x-show="activeTab === 'menunggu'" x-cloak class="flex flex-col gap-md">
                    @forelse ($waitingMenus as $menu)
                        <article
                            @click="selectMenu({{ $menu->id }}, true)"
                            :class="selectedId === {{ $menu->id }} ? 'border-2 border-obsidian' : 'border border-border-soft'"
                            class="bg-surface rounded-xl p-lg flex flex-col gap-sm cursor-pointer transition-colors"
                        >
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro text-ink">
                                    {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('l, j M Y') }}
                                </span>
                                <span class="px-sm py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro">
                                    Menunggu Pemeriksaan Anggaran
                                </span>
                            </div>
                            <h2 class="font-display text-section-title text-ink">{{ $menu->nama_menu }}</h2>
                            <p class="font-body text-micro text-ink-muted">
                                {{ $menu->categorySummaryLabel() }} · Total {{ number_format($menu->target_portions, 0, ',', '.') }} porsi
                            </p>
                            <p class="font-body text-micro text-ink-muted">
                                Diajukan {{ $menu->submitter?->name ?? 'Nadia Putri' }}
                                @if ($menu->submitted_at)
                                    · {{ $menu->submitted_at->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }} WIB
                                @endif
                            </p>
                            <div class="pt-xs flex justify-end">
                                <button
                                    type="button"
                                    @click.stop="selectMenu({{ $menu->id }}, true)"
                                    class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer"
                                >
                                    Periksa Menu {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M') }}
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="bg-surface rounded-xl p-xl border border-border-soft text-center font-body text-body-md text-ink-muted">
                            Tidak ada menu yang menunggu pemeriksaan anggaran.
                        </div>
                    @endforelse
                </section>

                {{-- Tab: Sudah diperiksa --}}
                <section x-show="activeTab === 'sudah'" x-cloak class="flex flex-col gap-sm">
                    @forelse ($checkedMenus as $menu)
                        @php
                            $status = $menu->menuStatus();
                            $latestCheck = $menu->paguChecks->first();
                        @endphp
                        <article
                            @click="selectMenu({{ $menu->id }}, true)"
                            :class="selectedId === {{ $menu->id }} ? 'border-2 border-obsidian' : 'border border-border-soft'"
                            class="bg-surface rounded-xl p-lg flex flex-col gap-xs cursor-pointer transition-colors"
                        >
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro text-ink">
                                    {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('l, j M Y') }}
                                </span>
                                <span class="px-sm py-xs rounded-full font-body text-micro {{ $status->badgeClasses() }}">
                                    {{ $status->displayLabel() }}
                                </span>
                            </div>
                            <h2 class="font-body text-card-title text-ink">{{ $menu->nama_menu }}</h2>
                            <span class="font-body text-micro text-ink-muted">
                                {{ $menu->categorySummaryLabel() }} · {{ number_format($menu->target_portions, 0, ',', '.') }} porsi
                            </span>
                            @if ($latestCheck)
                                <span class="font-mono text-micro text-ink-muted">
                                    {{ $latestCheck->checked_at?->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }} · "{{ $latestCheck->catatan }}"
                                </span>
                            @endif
                        </article>
                    @empty
                        <div class="bg-surface rounded-xl p-xl border border-border-soft text-center font-body text-body-md text-ink-muted">
                            Belum ada menu yang selesai diperiksa.
                        </div>
                    @endforelse
                </section>

                {{-- Tab: Semua menu · Hanya lihat --}}
                <section x-show="activeTab === 'semua'" x-cloak class="flex flex-col gap-sm">
                    <div class="p-md rounded-lg bg-surface border border-border-soft flex items-center justify-between gap-sm">
                        <span class="font-body text-micro text-ink-muted">
                            Menampilkan seluruh rencana menu periode ini (termasuk draf &amp; disetujui).
                        </span>
                        <span class="px-xs py-0.5 rounded bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted shrink-0">
                            Hanya lihat
                        </span>
                    </div>

                    @foreach ($allMenus as $menu)
                        @php $status = $menu->menuStatus(); @endphp
                        <article
                            @click="selectMenu({{ $menu->id }}, true)"
                            :class="selectedId === {{ $menu->id }} ? 'border-2 border-obsidian' : 'border border-border-soft'"
                            class="bg-surface rounded-xl p-lg flex flex-col gap-xs cursor-pointer transition-colors"
                        >
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro text-ink">
                                    {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('l, j M Y') }}
                                </span>
                                <span class="px-sm py-xs rounded-full font-body text-micro {{ $status->badgeClasses() }}">
                                    {{ $status->displayLabel() }}
                                </span>
                            </div>
                            <h2 class="font-body text-card-title text-ink">{{ $menu->nama_menu }}</h2>
                            <span class="font-body text-micro text-ink-muted">
                                {{ $menu->categorySummaryLabel() }} · {{ number_format($menu->target_portions, 0, ',', '.') }} porsi
                            </span>
                            @if ($menu->last_note)
                                <span class="font-body text-micro text-ink-muted">{{ $menu->last_note }}</span>
                            @endif
                        </article>
                    @endforeach
                </section>
            </div>

            {{-- Right: Inspection Side Panel (Desktop) & Bottom Sheet (Mobile/Tablet) --}}
            <div
                x-show="mobileSheetOpen"
                x-transition.opacity
                x-cloak
                @click="mobileSheetOpen = false"
                class="fixed inset-0 z-40 bg-obsidian/50 xl:hidden"
                aria-hidden="true"
            ></div>

            <aside
                :class="mobileSheetOpen ? 'fixed inset-x-0 bottom-0 z-50 max-h-[90vh] rounded-t-2xl shadow-2xl overflow-y-auto xl:static xl:z-auto xl:max-h-none xl:rounded-xl xl:shadow-none' : 'hidden xl:block'"
                class="xl:col-span-5 bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md"
                aria-label="Panel pemeriksaan menu"
            >
                <div class="xl:hidden flex items-center justify-between pb-sm border-b border-border-soft">
                    <span class="font-display text-section-title text-ink">Detail Pemeriksaan Menu</span>
                    <button
                        type="button"
                        @click="mobileSheetOpen = false"
                        aria-label="Tutup panel"
                        class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                    </button>
                </div>

                @foreach ($allMenus as $menu)
                    @php
                        $status = $menu->menuStatus();
                        $canVerify = $status === \App\Enums\MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN;
                        $latestCheck = $menu->paguChecks->first();
                        $menuPortions = $menu->categoryPortions;
                        $oldEstimates = old('estimates', []);
                    @endphp
                    <div x-show="selectedId === {{ $menu->id }}" x-cloak class="flex flex-col gap-md">
                        <div class="flex flex-col gap-xs pb-md border-b border-border-soft">
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro text-ink-muted">
                                    {{ $menu->operational_date?->timezone('Asia/Jakarta')->translatedFormat('l, j M Y') }}
                                </span>
                                <span class="px-sm py-xs rounded-full font-body text-micro {{ $status->badgeClasses() }}">
                                    {{ $status->displayLabel() }}
                                </span>
                            </div>
                            <h2 class="font-display text-section-title text-ink">{{ $menu->nama_menu }}</h2>
                            <span class="font-body text-micro text-ink-muted">
                                {{ $menu->categorySummaryLabel() }} · Total {{ number_format($menu->target_portions, 0, ',', '.') }} porsi
                            </span>
                        </div>

                        {{-- Kategori & Batas PAGU --}}
                        <div class="flex flex-col gap-xs">
                            <span class="font-body text-label-sm text-ink">Rincian Porsi &amp; Batas PAGU</span>
                            <div class="flex flex-col gap-xs">
                                @foreach ($menuPortions as $cp)
                                    @php
                                        $paguNominal = (int) ($activePagu->get($cp->recipient_category_id)?->max_budget_per_portion ?? 0);
                                    @endphp
                                    <div class="p-sm rounded-md bg-surface-subtle border border-border-soft flex items-center justify-between gap-sm">
                                        <div class="flex flex-col">
                                            <span class="font-body text-card-title text-ink">{{ $cp->recipientCategory?->name }}</span>
                                            <span class="font-body text-micro text-ink-muted">{{ number_format($cp->target_porsi, 0, ',', '.') }} porsi</span>
                                        </div>
                                        <span class="font-mono text-label-sm text-ink tabular-nums">
                                            PAGU Rp {{ number_format($paguNominal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Komponen Bahan (Read-Only) --}}
                        @if ($menu->components->isNotEmpty())
                            <div class="flex flex-col gap-xs">
                                <span class="font-body text-label-sm text-ink">Komponen Bahan Menu</span>
                                <div class="p-sm rounded-md bg-surface-subtle border border-border-soft flex flex-wrap gap-xs">
                                    @foreach ($menu->components as $comp)
                                        <span class="px-sm py-xs rounded bg-surface border border-border-soft font-body text-micro text-ink">
                                            {{ $comp->ingredient?->name ?? $comp->dish_name }}
                                            @if ($comp->planned_quantity > 0)
                                                ({{ rtrim(rtrim(number_format((float) $comp->planned_quantity, 1, ',', '.'), '0'), ',') }} {{ $comp->unit }})
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Form Pemeriksaan PAGU (Hanya jika status Menunggu Pemeriksaan Anggaran) --}}
                        @if ($canVerify)
                            @php
                                $initialEstimates = [];
                                foreach ($menuPortions as $idx => $cp) {
                                    $oldVal = $oldEstimates[$idx]['estimasi_per_porsi'] ?? null;
                                    $initialEstimates[$cp->recipient_category_id] = $oldVal !== null && $oldVal !== '' ? (string) $oldVal : '';
                                }
                                $paguMap = [];
                                foreach ($menuPortions as $cp) {
                                    $paguMap[$cp->recipient_category_id] = (int) ($activePagu->get($cp->recipient_category_id)?->max_budget_per_portion ?? 0);
                                }
                            @endphp
                            <form
                                method="POST"
                                action="{{ route('akuntan.validasi.store', $menu) }}"
                                x-data="{
                                    hasil: @js(old('hasil', 'lolos')),
                                    catatan: @js(old('catatan', 'Sesuai batas PAGU.')),
                                    estimates: @js($initialEstimates),
                                    pagu: @js($paguMap),
                                    setHasil(val) {
                                        this.hasil = val;
                                        if (val === 'lolos' && this.catatan.trim() === '') {
                                            this.catatan = 'Sesuai batas PAGU.';
                                        } else if (val === 'perlu_revisi' && this.catatan.trim() === 'Sesuai batas PAGU.') {
                                            this.catatan = '';
                                        }
                                    },
                                    hasOverPagu() {
                                        return Object.keys(this.estimates).some((catId) => {
                                            const raw = this.estimates[catId];
                                            if (raw === '' || raw === null || raw === undefined) return false;
                                            const num = Number(raw);
                                            return !Number.isNaN(num) && num > Number(this.pagu[catId] || 0);
                                        });
                                    },
                                    formatRp(num) {
                                        return 'Rp ' + Number(num).toLocaleString('id-ID');
                                    }
                                }"
                                class="flex flex-col gap-md pt-md border-t border-border-soft"
                            >
                                @csrf

                                {{-- Optional Estimasi Biaya per Porsi --}}
                                <div class="flex flex-col gap-sm">
                                    <div class="flex flex-col">
                                        <span class="font-body text-label-sm text-ink">Estimasi biaya per porsi (opsional)</span>
                                        <span class="font-body text-micro text-ink-muted">Isi manual sebagai alat bantu banding terhadap batas PAGU.</span>
                                    </div>

                                    @foreach ($menuPortions as $idx => $cp)
                                        @php
                                            $catId = $cp->recipient_category_id;
                                            $paguNominal = (int) ($activePagu->get($catId)?->max_budget_per_portion ?? 0);
                                        @endphp
                                        <div class="flex flex-col gap-xs">
                                            <input type="hidden" name="estimates[{{ $idx }}][recipient_category_id]" value="{{ $catId }}">
                                            <div class="flex items-center justify-between gap-sm">
                                                <label for="est-{{ $menu->id }}-{{ $catId }}" class="font-body text-micro text-ink">
                                                    {{ $cp->recipientCategory?->name }} (PAGU Rp {{ number_format($paguNominal, 0, ',', '.') }})
                                                </label>
                                                <template x-if="estimates[{{ $catId }}] !== '' && estimates[{{ $catId }}] !== null && !Number.isNaN(Number(estimates[{{ $catId }}]))">
                                                    <span
                                                        :class="Number(estimates[{{ $catId }}]) <= {{ $paguNominal }} ? 'bg-success-soft text-on-success-soft' : 'bg-danger-soft text-on-danger-soft'"
                                                        class="px-xs py-0.5 rounded font-body text-micro"
                                                        x-text="Number(estimates[{{ $catId }}]) <= {{ $paguNominal }} ? 'Di bawah PAGU' : ('Melebihi PAGU ' + formatRp(Number(estimates[{{ $catId }}]) - {{ $paguNominal }}))"
                                                    ></span>
                                                </template>
                                            </div>
                                            <input
                                                id="est-{{ $menu->id }}-{{ $catId }}"
                                                type="number"
                                                min="0"
                                                step="1"
                                                name="estimates[{{ $idx }}][estimasi_per_porsi]"
                                                x-model="estimates[{{ $catId }}]"
                                                placeholder="Kosongkan jika tidak diisi"
                                                class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink"
                                            >
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Pilihan Hasil Pemeriksaan --}}
                                <div class="flex flex-col gap-xs">
                                    <span class="font-body text-label-sm text-ink">Hasil pemeriksaan PAGU</span>
                                    <div class="grid grid-cols-2 gap-sm">
                                        <label
                                            :class="hasil === 'lolos' ? 'border-2 border-obsidian bg-lime-accent/30' : 'border border-border-soft bg-surface'"
                                            class="min-h-[44px] px-md py-sm rounded-lg flex items-center gap-xs cursor-pointer"
                                        >
                                            <input
                                                type="radio"
                                                name="hasil"
                                                value="lolos"
                                                :checked="hasil === 'lolos'"
                                                @change="setHasil('lolos')"
                                                class="sr-only"
                                            >
                                            <span class="material-symbols-outlined text-card-title text-on-success-soft" aria-hidden="true">check_circle</span>
                                            <span class="font-body text-label-sm text-ink">Lolos PAGU</span>
                                        </label>

                                        <label
                                            :class="hasil === 'perlu_revisi' ? 'border-2 border-obsidian bg-danger-soft/50' : 'border border-border-soft bg-surface'"
                                            class="min-h-[44px] px-md py-sm rounded-lg flex items-center gap-xs cursor-pointer"
                                        >
                                            <input
                                                type="radio"
                                                name="hasil"
                                                value="perlu_revisi"
                                                :checked="hasil === 'perlu_revisi'"
                                                @change="setHasil('perlu_revisi')"
                                                class="sr-only"
                                            >
                                            <span class="material-symbols-outlined text-card-title text-on-danger-soft" aria-hidden="true">error</span>
                                            <span class="font-body text-label-sm text-ink">Perlu Revisi Anggaran</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Warning Informasional Jika Lolos tapi Estimasi > PAGU --}}
                                <div
                                    x-show="hasil === 'lolos' && hasOverPagu()"
                                    x-cloak
                                    class="p-sm rounded-md bg-alert-soft text-on-alert-soft font-body text-micro"
                                >
                                    Estimasi biaya melebihi batas PAGU pada salah satu kategori. Pastikan alasan tercatat pada kolom catatan.
                                </div>

                                {{-- Catatan --}}
                                <div class="flex flex-col gap-xs">
                                    <label for="catatan-{{ $menu->id }}" class="font-body text-label-sm text-ink">
                                        Catatan pemeriksaan <span x-show="hasil === 'perlu_revisi'" class="text-on-danger-soft">(wajib min. 5 karakter)</span>
                                    </label>
                                    <textarea
                                        id="catatan-{{ $menu->id }}"
                                        name="catatan"
                                        rows="3"
                                        x-model="catatan"
                                        placeholder="Tuliskan catatan hasil pemeriksaan PAGU..."
                                        class="w-full rounded-md border border-border-soft bg-surface-subtle p-md font-body text-body-md text-ink"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="min-h-[44px] w-full rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer"
                                >
                                    Simpan Hasil Pemeriksaan
                                </button>
                            </form>
                        @else
                            {{-- Read-only Hasil Pemeriksaan Sebelumnya --}}
                            @if ($latestCheck)
                                <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                                    <div class="flex items-center justify-between gap-sm">
                                        <span class="font-body text-label-sm text-ink">Hasil Pemeriksaan PAGU (Final)</span>
                                        <span class="font-mono text-micro text-ink-muted">
                                            {{ $latestCheck->checked_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }} WIB
                                        </span>
                                    </div>
                                    <span class="font-body text-body-md text-ink">
                                        {{ $latestCheck->hasil->label() }} · {{ $latestCheck->accountant?->name ?? 'Rizky Pratama' }}
                                    </span>
                                    <p class="font-body text-micro text-ink-muted">"{{ $latestCheck->catatan }}"</p>

                                    @if ($latestCheck->estimates->isNotEmpty())
                                        <div class="pt-xs mt-xs border-t border-border-soft flex flex-col gap-xs">
                                            @foreach ($latestCheck->estimates as $est)
                                                <div class="flex items-center justify-between font-mono text-micro text-ink-muted">
                                                    <span>{{ $est->recipientCategory?->name }}</span>
                                                    <span>
                                                        Est: {{ $est->estimasi_per_porsi !== null ? 'Rp ' . number_format($est->estimasi_per_porsi, 0, ',', '.') : '-' }}
                                                        / PAGU Rp {{ number_format($est->pagu_saat_itu, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif

                        {{-- Riwayat Status Menu --}}
                        @if ($menu->histories->isNotEmpty())
                            <div class="flex flex-col gap-xs pt-md border-t border-border-soft">
                                <span class="font-body text-label-sm text-ink">Riwayat Status</span>
                                <div class="flex flex-col gap-xs">
                                    @foreach ($menu->histories->sortByDesc('created_at') as $history)
                                        <div class="p-sm rounded bg-surface-subtle border border-border-soft flex flex-col gap-0.5">
                                            <div class="flex items-center justify-between gap-sm">
                                                <span class="font-body text-micro text-ink font-medium">{{ str_replace('_', ' ', ucfirst($history->aksi)) }}</span>
                                                <span class="font-mono text-micro text-ink-muted">
                                                    {{ $history->created_at?->timezone('Asia/Jakarta')->translatedFormat('j M H:i') }}
                                                </span>
                                            </div>
                                            <span class="font-body text-micro text-ink-muted">
                                                {{ $history->user?->name ?? 'Sistem' }}
                                                @if ($history->catatan)
                                                    · {{ $history->catatan }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </aside>
        </div>
    </div>
</x-layouts.akuntan>
