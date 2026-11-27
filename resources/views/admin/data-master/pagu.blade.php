@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $itemsJson = $pagus->getCollection()->map(static fn ($p) => [
        'id' => $p->id,
        'recipient_category_id' => (string) $p->recipient_category_id,
        'max_budget_per_portion' => $p->max_budget_per_portion,
        'effective_date' => $p->effective_date?->format('Y-m-d'),
        'notes' => $p->notes,
        'update_url' => route('admin.data-master.pagu.update', $p),
    ])->values();
@endphp

<x-layouts.admin
    title="Parameter PAGU"
    subtitle="Batas pagu biaya per porsi per kategori penerima sesuai pedoman BGN"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button type="button" x-data @click="$dispatch('open-pagu-create')" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Tambah Parameter PAGU
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.pagu.store') }}',
            form: { recipient_category_id: '{{ $categories->first()?->id }}', max_budget_per_portion: 15000, effective_date: '{{ now()->toDateString() }}', notes: '', reason: '' },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.pagu.store') }}';
                this.form = { recipient_category_id: '{{ $categories->first()?->id }}', max_budget_per_portion: 15000, effective_date: '{{ now()->toDateString() }}', notes: '', reason: '' };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                this.form = { ...item, notes: item.notes || '', reason: '' };
                this.panelOpen = true;
            }
        }"
        @open-pagu-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.pagu.index')">
            <div class="lg:col-span-5 flex flex-col gap-xs">
                <label for="filter-cat" class="font-body text-micro text-ink-muted">Kategori Penerima</label>
                <select id="filter-cat" name="category_id" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['category_id'] === 'all')>Semua kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) $filters['category_id'] === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-5 flex flex-col gap-xs">
                <label for="filter-status" class="font-body text-micro text-ink-muted">Status</label>
                <select id="filter-status" name="status" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </x-filter-bar>

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            @if($pagus->isEmpty())
                <x-empty-state icon="payments" title="Belum ada parameter PAGU" description="Tambahkan batas pagu biaya per porsi." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Kategori', 'PAGU Maks / Porsi', 'Tanggal Berlaku', 'Catatan', 'Status', 'Aksi']">
                        @foreach($pagus as $idx => $p)
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm font-body text-label-sm font-semibold text-ink">{{ $p->recipientCategory?->name ?? '-' }}</td>
                                <td class="px-md py-sm font-mono text-label-sm font-semibold text-ink">Rp {{ $fmt($p->max_budget_per_portion) }}</td>
                                <td class="px-md py-sm font-mono text-micro text-ink-muted">{{ $p->effective_date?->translatedFormat('d M Y') }}</td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">{{ $p->notes ?? '-' }}</td>
                                <td class="px-md py-sm"><x-badge-status :variant="$p->is_active ? 'success' : 'danger'" :label="$p->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $p->is_active ? route('admin.data-master.pagu.deactivate', $p) : route('admin.data-master.pagu.activate', $p) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($pagus as $idx => $p)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $p->recipientCategory?->name ?? '-' }}</p>
                                    <p class="font-mono text-micro text-ink-muted">Rp {{ $fmt($p->max_budget_per_portion) }} &middot; Berlaku {{ $p->effective_date?->translatedFormat('d M Y') }}</p>
                                </div>
                                <x-badge-status :variant="$p->is_active ? 'success' : 'danger'" :label="$p->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $p->is_active ? route('admin.data-master.pagu.deactivate', $p) : route('admin.data-master.pagu.activate', $p) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$pagus" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Parameter PAGU" subtitle="Batas biaya maksimal per porsi">
            <form method="POST" :action="actionUrl" class="flex flex-col gap-md">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Kategori Penerima</label>
                    <select name="recipient_category_id" x-model="form.recipient_category_id" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">PAGU Maksimal (Rp)</label>
                        <input type="number" min="1000" step="100" name="max_budget_per_portion" x-model="form.max_budget_per_portion" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Tanggal Berlaku</label>
                        <input type="date" name="effective_date" x-model="form.effective_date" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Catatan</label>
                    <input type="text" name="notes" x-model="form.notes" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Revisi PAGU</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>
                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Simpan Parameter PAGU</button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
