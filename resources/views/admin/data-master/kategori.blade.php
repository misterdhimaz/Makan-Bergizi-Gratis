@php
    $itemsJson = $categories->getCollection()->map(static fn ($cat) => [
        'id' => $cat->id,
        'code' => $cat->code,
        'name' => $cat->name,
        'description' => $cat->description,
        'update_url' => route('admin.data-master.kategori.update', $cat),
    ])->values();
@endphp

<x-layouts.admin
    title="Kategori Penerima"
    subtitle="Kelola kelompok penerima manfaat program Makan Bergizi Gratis"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button type="button" x-data @click="$dispatch('open-category-create')" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Tambah Kategori
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.kategori.store') }}',
            form: { code: '', name: '', description: '', reason: '' },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.kategori.store') }}';
                this.form = { code: '', name: '', description: '', reason: '' };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                this.form = { code: item.code, name: item.name, description: item.description || '', reason: '' };
                this.panelOpen = true;
            }
        }"
        @open-category-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.kategori.index')">
            <div class="lg:col-span-6 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari Kategori</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kode atau nama kategori" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
            </div>
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-status" class="font-body text-micro text-ink-muted">Status</label>
                <select id="filter-status" name="status" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </x-filter-bar>

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            @if($categories->isEmpty())
                <x-empty-state icon="category" title="Belum ada kategori penerima" description="Tambahkan kategori penerima manfaat." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Kode', 'Nama Kategori', 'Keterangan', 'Status', 'Aksi']">
                        @foreach($categories as $idx => $cat)
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm font-mono text-micro text-ink-muted">{{ $cat->code }}</td>
                                <td class="px-md py-sm font-body text-label-sm font-semibold text-ink">{{ $cat->name }}</td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">{{ $cat->description ?? '-' }}</td>
                                <td class="px-md py-sm"><x-badge-status :variant="$cat->is_active ? 'success' : 'danger'" :label="$cat->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $cat->is_active ? route('admin.data-master.kategori.deactivate', $cat) : route('admin.data-master.kategori.activate', $cat) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($categories as $idx => $cat)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-mono text-micro text-ink-muted">{{ $cat->code }}</p>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $cat->name }}</p>
                                </div>
                                <x-badge-status :variant="$cat->is_active ? 'success' : 'danger'" :label="$cat->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $cat->is_active ? route('admin.data-master.kategori.deactivate', $cat) : route('admin.data-master.kategori.activate', $cat) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$categories" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Kategori Penerima" subtitle="Kelompok penerima manfaat SPPG">
            <form method="POST" :action="actionUrl" class="flex flex-col gap-md">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Kode Kategori</label>
                    <input type="text" name="code" x-model="form.code" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Nama Kategori</label>
                    <input type="text" name="name" x-model="form.name" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Keterangan</label>
                    <textarea name="description" x-model="form.description" rows="3" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink"></textarea>
                </div>
                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Perubahan</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>
                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Simpan Kategori</button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
