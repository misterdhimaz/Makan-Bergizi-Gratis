@php
    $firstCategory = $categories->first();
    $firstParam = $firstCategory?->paguParameters->first();
    $totalTargetPorsi = $categories->sum(function ($cat) {
        return (int) \App\Models\LocationCategoryQuota::query()
            ->where('recipient_category_id', $cat->id)
            ->sum('portion_count');
    });
@endphp

<x-layouts.akuntan
    title="Parameter PAGU"
    subtitle="Batas anggaran per porsi sebagai acuan pemeriksaan menu SPPG Sungai Selayur 1."
    breadcrumb="Akuntan · Parameter PAGU"
>
    <div
        x-data="{
            selectedCatId: @js(old('kategori_id', $firstCategory?->id)),
            selectedCatName: @js($firstCategory?->name ?? 'Siswa'),
            nominal: @js(old('nominal_per_porsi', $firstParam?->max_budget_per_portion ?? 15000)),
            catatan: @js(old('catatan', '')),
            sheetOpen: @js($errors->any()),
            openEdit(id, name, nom) {
                this.selectedCatId = id;
                this.selectedCatName = name;
                this.nominal = nom;
                this.catatan = '';
                this.sheetOpen = true;
            }
        }"
        @keydown.escape.window="sheetOpen = false"
        class="flex flex-col gap-lg"
    >
        {{-- Info Banners --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            <div class="p-md rounded-lg bg-surface border border-border-soft font-body text-micro text-ink">
                Anda hanya dapat mengelola Parameter PAGU. Data master lain dikelola Administrator Sistem.
            </div>
            <div class="p-md rounded-lg bg-alert-soft/50 border border-alert-soft font-body text-micro text-on-alert-soft">
                PAGU hanya acuan pemeriksaan menu. Sistem tidak menghitung anggaran otomatis.
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-lg items-start">
            {{-- Left: Category List + History --}}
            <div class="xl:col-span-8 flex flex-col gap-lg">
                <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                    <div class="flex items-center justify-between gap-sm">
                        <div>
                            <h2 class="font-display text-section-title text-ink">Daftar Batas PAGU per Kategori</h2>
                            <p class="font-body text-micro text-ink-muted">
                                Total target porsi {{ number_format($totalTargetPorsi, 0, ',', '.') }} porsi per hari
                            </p>
                        </div>
                    </div>

                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                                    <th class="py-sm pr-md">Kategori</th>
                                    <th class="py-sm pr-md text-right">Target Porsi</th>
                                    <th class="py-sm pr-md text-right">Batas PAGU / Porsi</th>
                                    <th class="py-sm pr-md">Berlaku Sejak</th>
                                    <th class="py-sm pr-md">Diubah Oleh</th>
                                    <th class="py-sm text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-soft font-body text-body-md">
                                @foreach ($categories as $category)
                                    @php
                                        $param = $category->paguParameters->first();
                                        $nominal = (int) ($param?->max_budget_per_portion ?? 0);
                                        $target = (int) \App\Models\LocationCategoryQuota::query()
                                            ->where('recipient_category_id', $category->id)
                                            ->sum('portion_count');
                                    @endphp
                                    <tr :class="selectedCatId === {{ $category->id }} ? 'bg-surface-subtle' : ''">
                                        <td class="py-md pr-md text-ink font-medium">{{ $category->name }}</td>
                                        <td class="py-md pr-md text-right font-mono tabular-nums text-ink">
                                            {{ number_format($target, 0, ',', '.') }}
                                        </td>
                                        <td class="py-md pr-md text-right font-mono tabular-nums text-ink font-semibold">
                                            Rp {{ number_format($nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="py-md pr-md text-ink-muted whitespace-nowrap">
                                            {{ $param?->effective_date?->translatedFormat('j M Y') ?? '-' }}
                                        </td>
                                        <td class="py-md pr-md text-ink-muted">
                                            {{ $param?->updatedBy?->name ?? 'Sistem' }}
                                        </td>
                                        <td class="py-md text-right">
                                            <button
                                                type="button"
                                                @click="openEdit({{ $category->id }}, @js($category->name), {{ $nominal }})"
                                                class="min-h-[44px] px-md rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink hover:bg-obsidian hover:text-on-obsidian cursor-pointer transition-colors"
                                            >
                                                Ubah
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Stacked Cards --}}
                    <div class="md:hidden flex flex-col gap-sm">
                        @foreach ($categories as $category)
                            @php
                                $param = $category->paguParameters->first();
                                $nominal = (int) ($param?->max_budget_per_portion ?? 0);
                                $target = (int) \App\Models\LocationCategoryQuota::query()
                                    ->where('recipient_category_id', $category->id)
                                    ->sum('portion_count');
                            @endphp
                            <article
                                :class="selectedCatId === {{ $category->id }} ? 'border-2 border-obsidian' : 'border border-border-soft'"
                                class="bg-surface rounded-xl p-lg flex flex-col gap-xs"
                            >
                                <div class="flex items-center justify-between gap-sm">
                                    <span class="font-body text-card-title text-ink">{{ $category->name }}</span>
                                    <span class="font-mono text-card-title text-ink tabular-nums">
                                        Rp {{ number_format($nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                                <span class="font-body text-micro text-ink-muted">
                                    Target porsi: {{ number_format($target, 0, ',', '.') }}
                                    · Berlaku sejak {{ $param?->effective_date?->translatedFormat('j M Y') ?? '-' }}
                                    · {{ $param?->updatedBy?->name ?? 'Sistem' }}
                                </span>
                                <button
                                    type="button"
                                    @click="openEdit({{ $category->id }}, @js($category->name), {{ $nominal }})"
                                    class="min-h-[44px] mt-xs w-full rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer"
                                >
                                    Ubah {{ $category->name }}
                                </button>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- Riwayat Perubahan --}}
                <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                    <h2 class="font-display text-section-title text-ink">Riwayat Perubahan Parameter PAGU</h2>
                    <div class="flex flex-col divide-y divide-border-soft">
                        @forelse ($histories as $history)
                            <div class="py-sm flex flex-col sm:flex-row sm:items-center justify-between gap-xs">
                                <div class="flex flex-col">
                                    <span class="font-body text-body-md text-ink">
                                        <strong>{{ $history->recipientCategory?->name }}</strong>:
                                        @if ($history->nilai_lama !== null)
                                            Rp {{ number_format($history->nilai_lama, 0, ',', '.') }} →
                                        @endif
                                        Rp {{ number_format($history->nilai_baru, 0, ',', '.') }}
                                    </span>
                                    <span class="font-body text-micro text-ink-muted">
                                        {{ $history->user?->name ?? 'Sistem' }}
                                        @if ($history->catatan)
                                            · {{ $history->catatan }}
                                        @endif
                                    </span>
                                </div>
                                <span class="font-mono text-micro text-ink-muted whitespace-nowrap">
                                    {{ $history->created_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }}
                                </span>
                            </div>
                        @empty
                            <p class="font-body text-body-md text-ink-muted">Belum ada riwayat perubahan.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Right: Edit Form Panel (Desktop) & Bottom Sheet (Mobile) --}}
            <div
                x-show="sheetOpen"
                x-transition.opacity
                x-cloak
                @click="sheetOpen = false"
                class="fixed inset-0 z-40 bg-obsidian/50 xl:hidden"
                aria-hidden="true"
            ></div>

            <aside
                :class="sheetOpen ? 'fixed inset-x-0 bottom-0 z-50 max-h-[85vh] rounded-t-2xl shadow-2xl overflow-y-auto xl:static xl:z-auto xl:max-h-none xl:rounded-xl xl:shadow-none' : 'hidden xl:block'"
                class="xl:col-span-4 bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md"
                aria-label="Form ubah Parameter PAGU"
            >
                <div class="flex items-center justify-between gap-sm pb-sm border-b border-border-soft">
                    <div class="flex flex-col">
                        <span class="font-body text-micro text-ink-muted">Ubah Batas PAGU</span>
                        <h2 class="font-display text-section-title text-ink" x-text="selectedCatName"></h2>
                    </div>
                    <button
                        type="button"
                        @click="sheetOpen = false"
                        aria-label="Tutup form"
                        class="xl:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full text-ink hover:bg-surface-subtle cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">close</span>
                    </button>
                </div>

                <form
                    method="POST"
                    :action="`{{ url('/akuntan/parameter-pagu') }}/${selectedCatId}`"
                    class="flex flex-col gap-md"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="kategori_id" x-model="selectedCatId">

                    <div class="flex flex-col gap-xs">
                        <label for="nominal_per_porsi" class="font-body text-label-sm text-ink">
                            Nominal batas PAGU per porsi (Rp)
                        </label>
                        <input
                            id="nominal_per_porsi"
                            type="number"
                            name="nominal_per_porsi"
                            x-model="nominal"
                            min="1"
                            step="1"
                            required
                            class="w-full min-h-[44px] rounded-md border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink"
                        >
                        <span class="font-body text-micro text-ink-muted">
                            Harus bilangan bulat lebih dari 0. Perubahan berlaku untuk pemeriksaan baru dan tidak mengubah riwayat pemeriksaan lama.
                        </span>
                    </div>

                    <div class="flex flex-col gap-xs">
                        <label for="pagu_catatan" class="font-body text-label-sm text-ink">
                            Catatan perubahan (opsional)
                        </label>
                        <textarea
                            id="pagu_catatan"
                            name="catatan"
                            rows="3"
                            x-model="catatan"
                            placeholder="Contoh: Penyesuaian acuan harga bahan pokok..."
                            class="w-full rounded-md border border-border-soft bg-surface-subtle p-md font-body text-body-md text-ink"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="min-h-[44px] w-full rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer"
                    >
                        Simpan Parameter PAGU
                    </button>
                </form>
            </aside>
        </div>
    </div>
</x-layouts.akuntan>
