@php
    $itemsJson = $standards->getCollection()->map(static fn ($std) => [
        'id' => $std->id,
        'recipient_category_id' => (string) $std->recipient_category_id,
        'meal_component' => $std->meal_component->value,
        'quantity' => (float) $std->quantity,
        'unit' => $std->unit,
        'notes' => $std->notes,
        'update_url' => route('admin.data-master.standar-porsi.update', $std),
    ])->values();
@endphp

<x-layouts.admin
    title="Standar Porsi"
    subtitle="Acuan gramasi/volume per komponen makanan untuk setiap kategori penerima"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button type="button" x-data @click="$dispatch('open-portion-create')" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Tambah Standar Porsi
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.standar-porsi.store') }}',
            form: { recipient_category_id: '{{ $categories->first()?->id }}', meal_component: 'Pokok', quantity: '', unit: 'g', notes: '', reason: '' },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.standar-porsi.store') }}';
                this.form = { recipient_category_id: '{{ $categories->first()?->id }}', meal_component: 'Pokok', quantity: '', unit: 'g', notes: '', reason: '' };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                this.form = { ...item, notes: item.notes || '', reason: '' };
                this.panelOpen = true;
            }
        }"
        @open-portion-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.standar-porsi.index')">
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari Komponen</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Komponen atau catatan" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
            </div>
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-cat" class="font-body text-micro text-ink-muted">Kategori Penerima</label>
                <select id="filter-cat" name="category_id" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['category_id'] === 'all')>Semua kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) $filters['category_id'] === (string) $cat->id)>{{ $cat->name }}</option>
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
            @if($standards->isEmpty())
                <x-empty-state icon="scale" title="Belum ada standar porsi" description="Tambahkan standar porsi berdasarkan kategori penerima." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Kategori', 'Komponen', 'Takaran', 'Catatan', 'Status', 'Aksi']">
                        @foreach($standards as $idx => $std)
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm font-body text-label-sm font-semibold text-ink">{{ $std->recipientCategory?->name ?? '-' }}</td>
                                <td class="px-md py-sm font-body text-label-sm text-ink">{{ $std->meal_component->value }}</td>
                                <td class="px-md py-sm font-mono text-label-sm font-semibold text-ink">{{ number_format((float) $std->quantity, 0, ',', '.') }} {{ $std->unit }}</td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">{{ $std->notes ?? '-' }}</td>
                                <td class="px-md py-sm"><x-badge-status :variant="$std->is_active ? 'success' : 'danger'" :label="$std->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $std->is_active ? route('admin.data-master.standar-porsi.deactivate', $std) : route('admin.data-master.standar-porsi.activate', $std) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $std->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($standards as $idx => $std)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $std->recipientCategory?->name ?? '-' }}</p>
                                    <p class="font-body text-micro text-ink-muted">{{ $std->meal_component->value }} &middot; {{ number_format((float) $std->quantity, 0, ',', '.') }} {{ $std->unit }}</p>
                                </div>
                                <x-badge-status :variant="$std->is_active ? 'success' : 'danger'" :label="$std->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $std->is_active ? route('admin.data-master.standar-porsi.deactivate', $std) : route('admin.data-master.standar-porsi.activate', $std) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $std->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$standards" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Standar Porsi" subtitle="Takaran gizi per kategori penerima">
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
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Komponen</label>
                    <select name="meal_component" x-model="form.meal_component" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                        @foreach($components as $comp)
                            <option value="{{ $comp->value }}">{{ $comp->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Jumlah</label>
                        <input type="number" step="0.01" min="0.01" name="quantity" x-model="form.quantity" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Satuan</label>
                        <select name="unit" x-model="form.unit" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                            <option value="g">g (gram)</option>
                            <option value="ml">ml (mililiter)</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Catatan</label>
                    <input type="text" name="notes" x-model="form.notes" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Perubahan</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>
                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Simpan Standar Porsi</button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
