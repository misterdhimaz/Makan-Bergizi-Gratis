@php
    $fmt = static fn (int|float|null $n): string => number_format((float) $n, 0, ',', '.');
    $itemsJson = $ingredients->getCollection()->map(static fn ($ing) => [
        'id' => $ing->id,
        'name' => $ing->name,
        'category' => $ing->category,
        'unit' => $ing->unit,
        'current_stock' => (float) $ing->current_stock,
        'unit_cost' => (float) $ing->unit_cost,
        'supplier_id' => $ing->supplier_id,
        'update_url' => route('admin.data-master.bahan.update', $ing),
    ])->values();
@endphp

<x-layouts.admin
    title="Daftar Bahan"
    subtitle="Referensi bahan pangan dapur SPPG (tanpa SKU & tanpa batas stok minimum)"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button type="button" x-data @click="$dispatch('open-ingredient-create')" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Tambah Bahan
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.bahan.store') }}',
            form: { name: '', category: 'Pokok', unit: 'kg', current_stock: 0, unit_cost: 0, supplier_id: '', reason: '' },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.bahan.store') }}';
                this.form = { name: '', category: 'Pokok', unit: 'kg', current_stock: 0, unit_cost: 0, supplier_id: '', reason: '' };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                this.form = { ...item, supplier_id: item.supplier_id || '', reason: '' };
                this.panelOpen = true;
            }
        }"
        @open-ingredient-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.bahan.index')">
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari Bahan</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Nama bahan" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
            </div>
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-category" class="font-body text-micro text-ink-muted">Kategori</label>
                <select id="filter-category" name="category" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['category'] === 'all')>Semua kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->value }}" @selected($filters['category'] === $cat->value)>{{ $cat->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-3 flex flex-col gap-xs">
                <label for="filter-status" class="font-body text-micro text-ink-muted">Status</label>
                <select id="filter-status" name="status" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </x-filter-bar>

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            @if($ingredients->isEmpty())
                <x-empty-state icon="inventory_2" title="Belum ada daftar bahan" description="Tambahkan bahan pangan untuk referensi menu & pengadaan." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Nama Bahan', 'Kategori', 'Satuan', 'Pemasok', 'Harga Satuan', 'Status', 'Aksi']">
                        @foreach($ingredients as $idx => $ing)
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm font-body text-label-sm font-semibold text-ink">{{ $ing->name }}</td>
                                <td class="px-md py-sm font-body text-label-sm text-ink">{{ $ing->category }}</td>
                                <td class="px-md py-sm font-mono text-micro text-ink-muted">{{ $ing->unit }}</td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">{{ $ing->supplier?->name ?? '-' }}</td>
                                <td class="px-md py-sm font-mono text-label-sm text-ink">Rp {{ $fmt($ing->unit_cost) }}</td>
                                <td class="px-md py-sm"><x-badge-status :variant="$ing->is_active ? 'success' : 'danger'" :label="$ing->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $ing->is_active ? route('admin.data-master.bahan.deactivate', $ing) : route('admin.data-master.bahan.activate', $ing) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $ing->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($ingredients as $idx => $ing)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $ing->name }}</p>
                                    <p class="font-body text-micro text-ink-muted">{{ $ing->category }} &middot; {{ $ing->unit }} &middot; Rp {{ $fmt($ing->unit_cost) }}</p>
                                </div>
                                <x-badge-status :variant="$ing->is_active ? 'success' : 'danger'" :label="$ing->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $ing->is_active ? route('admin.data-master.bahan.deactivate', $ing) : route('admin.data-master.bahan.activate', $ing) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $ing->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$ingredients" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Daftar Bahan" subtitle="Referensi bahan pangan SPPG">
            <form method="POST" :action="actionUrl" class="flex flex-col gap-md">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Nama Bahan</label>
                    <input type="text" name="name" x-model="form.name" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Kategori</label>
                        <select name="category" x-model="form.category" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}">{{ $cat->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Satuan</label>
                        <input type="text" name="unit" x-model="form.unit" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Stok Awal</label>
                        <input type="number" step="0.01" min="0" name="current_stock" x-model="form.current_stock" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Harga Satuan (Rp)</label>
                        <input type="number" step="1" min="0" name="unit_cost" x-model="form.unit_cost" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Pemasok Utama (opsional)</label>
                    <select name="supplier_id" x-model="form.supplier_id" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                        <option value="">— Tanpa pemasok —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Perubahan</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>
                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Simpan Bahan</button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
