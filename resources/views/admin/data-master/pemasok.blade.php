@php
    $itemsJson = $suppliers->getCollection()->map(static fn ($sup) => [
        'id' => $sup->id,
        'code' => $sup->code,
        'name' => $sup->name,
        'contact_person' => $sup->contact_person,
        'phone' => $sup->phone,
        'address' => $sup->address,
        'category_focus' => $sup->category_focus,
        'update_url' => route('admin.data-master.pemasok.update', $sup),
    ])->values();
@endphp

<x-layouts.admin
    title="Pemasok"
    subtitle="Mitra pemasok bahan pangan SPPG — lengkapi narahubung dan telepon untuk status siap"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button type="button" x-data @click="$dispatch('open-supplier-create')" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Tambah Pemasok
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.pemasok.store') }}',
            form: { code: '', name: '', contact_person: '', phone: '', address: '', category_focus: '', reason: '' },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.pemasok.store') }}';
                this.form = { code: '', name: '', contact_person: '', phone: '', address: '', category_focus: '', reason: '' };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                this.form = {
                    code: item.code,
                    name: item.name,
                    contact_person: item.contact_person || '',
                    phone: item.phone || '',
                    address: item.address || '',
                    category_focus: item.category_focus || '',
                    reason: '',
                };
                this.panelOpen = true;
            }
        }"
        @open-supplier-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.pemasok.index')">
            <div class="lg:col-span-6 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari Pemasok</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kode, nama, narahubung, atau fokus komoditas" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
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
            @if($suppliers->isEmpty())
                <x-empty-state icon="local_shipping" title="Belum ada pemasok" description="Tambahkan mitra pemasok bahan pangan." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Kode & Nama', 'Narahubung & Telepon', 'Fokus Komoditas', 'Status', 'Aksi']">
                        @foreach($suppliers as $idx => $sup)
                            @php($hasContact = !empty(trim((string) $sup->contact_person)) && !empty(trim((string) $sup->phone)))
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm">
                                    <p class="font-mono text-micro text-ink-muted">{{ $sup->code }}</p>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $sup->name }}</p>
                                </td>
                                <td class="px-md py-sm">
                                    @if($hasContact)
                                        <p class="font-body text-label-sm text-ink">{{ $sup->contact_person }}</p>
                                        <p class="font-mono text-micro text-ink-muted">{{ $sup->phone }}</p>
                                    @else
                                        <x-badge-status variant="warning" label="Kontak belum lengkap" />
                                    @endif
                                </td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">{{ $sup->category_focus ?? '-' }}</td>
                                <td class="px-md py-sm"><x-badge-status :variant="$sup->is_active ? 'success' : 'danger'" :label="$sup->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $sup->is_active ? route('admin.data-master.pemasok.deactivate', $sup) : route('admin.data-master.pemasok.activate', $sup) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $sup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($suppliers as $idx => $sup)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-mono text-micro text-ink-muted">{{ $sup->code }}</p>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $sup->name }}</p>
                                </div>
                                <x-badge-status :variant="$sup->is_active ? 'success' : 'danger'" :label="$sup->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <p class="font-body text-micro text-ink-muted">Kontak: {{ $sup->contact_person ?: 'Belum diisi' }} ({{ $sup->phone ?: '-' }})</p>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $sup->is_active ? route('admin.data-master.pemasok.deactivate', $sup) : route('admin.data-master.pemasok.activate', $sup) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $sup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$suppliers" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Pemasok" subtitle="Isi narahubung dan telepon agar memenuhi kelengkapan Data Master">
            <form method="POST" :action="actionUrl" class="flex flex-col gap-md">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Kode Pemasok</label>
                    <input type="text" name="code" x-model="form.code" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Nama Pemasok</label>
                    <input type="text" name="name" x-model="form.name" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Narahubung</label>
                        <input type="text" name="contact_person" x-model="form.contact_person" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Nomor Telepon</label>
                        <input type="text" name="phone" x-model="form.phone" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Fokus Komoditas</label>
                    <input type="text" name="category_focus" x-model="form.category_focus" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Alamat</label>
                    <textarea name="address" x-model="form.address" rows="2" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink"></textarea>
                </div>
                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Perubahan</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>
                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Simpan Pemasok</button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
