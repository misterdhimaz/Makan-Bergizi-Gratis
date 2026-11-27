@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $itemsJson = $locations->getCollection()->map(static function ($loc) {
        return [
            'id' => $loc->id,
            'code' => $loc->code,
            'name' => $loc->name,
            'type' => $loc->type,
            'address' => $loc->address,
            'target_portions' => $loc->target_portions,
            'quotas' => $loc->categoryQuotas->pluck('portion_count', 'recipient_category_id')->toArray(),
            'update_url' => route('admin.data-master.lokasi.update', $loc),
        ];
    })->values();
@endphp

<x-layouts.admin
    title="Lokasi Penerima"
    subtitle="Kelola titik sekolah/posyandu, rincian kuota kategori, dan PIC aktif"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button
            type="button"
            x-data
            @click="$dispatch('open-location-create')"
            class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer"
        >
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add_location_alt</span>
            Tambah Lokasi
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.lokasi.store') }}',
            form: { code: '', name: '', type: 'Sekolah', address: '', target_portions: '', reason: '', quotas: {} },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.lokasi.store') }}';
                this.form = { code: '', name: '', type: 'Sekolah', address: '', target_portions: '', reason: '', quotas: {} };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                const typeMap = { sd: 'Sekolah', sekolah: 'Sekolah', pesantren: 'Pesantren', posyandu: 'Posyandu' };
                const normalizedType = typeMap[(item.type || '').toLowerCase()] || item.type || 'Sekolah';
                this.form = {
                    code: item.code,
                    name: item.name,
                    type: normalizedType,
                    address: item.address || '',
                    target_portions: item.target_portions,
                    reason: '',
                    quotas: item.quotas || {},
                };
                this.panelOpen = true;
            }
        }"
        @open-location-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.lokasi.index')">
            <div class="lg:col-span-6 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari Lokasi</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kode, nama, atau tipe lokasi"
                       class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
            </div>
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-status" class="font-body text-micro text-ink-muted">Status</label>
                <select id="filter-status" name="status" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </x-filter-bar>

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            @if($locations->isEmpty())
                <x-empty-state icon="location_off" title="Belum ada lokasi penerima" description="Tambahkan titik lokasi penerima manfaat." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Kode & Nama', 'Tipe', 'Target Porsi', 'Guru PIC Aktif', 'Status', 'Aksi']">
                        @foreach($locations as $idx => $loc)
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm">
                                    <p class="font-mono text-micro text-ink-muted">{{ $loc->code }}</p>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $loc->name }}</p>
                                </td>
                                <td class="px-md py-sm font-body text-label-sm text-ink">{{ $loc->type }}</td>
                                <td class="px-md py-sm">
                                    <p class="font-mono text-label-sm font-semibold text-ink">{{ $fmt($loc->target_portions) }} porsi</p>
                                    <p class="font-body text-micro text-ink-muted">
                                        Rincian: {{ $fmt($loc->categoryQuotas->sum('portion_count')) }} porsi
                                    </p>
                                </td>
                                <td class="px-md py-sm font-body text-label-sm text-ink">
                                    {{ $loc->activeGuruPic?->name ?? 'Belum ditautkan' }}
                                </td>
                                <td class="px-md py-sm">
                                    <x-badge-status :variant="$loc->is_active ? 'success' : 'danger'" :label="$loc->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface hover:bg-surface-subtle font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $loc->is_active ? route('admin.data-master.lokasi.deactivate', $loc) : route('admin.data-master.lokasi.activate', $loc) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface hover:bg-surface-subtle font-body text-micro font-semibold text-ink cursor-pointer">
                                                {{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($locations as $idx => $loc)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-mono text-micro text-ink-muted">{{ $loc->code }}</p>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $loc->name }}</p>
                                </div>
                                <x-badge-status :variant="$loc->is_active ? 'success' : 'danger'" :label="$loc->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <p class="font-body text-micro text-ink-muted">{{ $loc->type }} &middot; Target {{ $fmt($loc->target_portions) }} porsi &middot; PIC: {{ $loc->activeGuruPic?->name ?? '-' }}</p>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $loc->is_active ? route('admin.data-master.lokasi.deactivate', $loc) : route('admin.data-master.lokasi.activate', $loc) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$locations" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Lokasi Penerima" subtitle="Rincian kuota kategori harus sama dengan target porsi">
            <form method="POST" :action="actionUrl" class="flex flex-col gap-md">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Kode Lokasi</label>
                    <input type="text" name="code" x-model="form.code" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Nama Lokasi</label>
                    <input type="text" name="name" x-model="form.name" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Tipe</label>
                        <select name="type" x-model="form.type" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                            <option value="Sekolah">Sekolah</option>
                            <option value="Pesantren">Pesantren</option>
                            <option value="Posyandu">Posyandu</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Target Porsi</label>
                        <input type="number" name="target_portions" x-model="form.target_portions" min="1" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Alamat</label>
                    <textarea name="address" x-model="form.address" rows="2" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink"></textarea>
                </div>

                @if($categories->isNotEmpty())
                    <div class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                        <p class="font-body text-label-sm font-semibold text-ink">Rincian Porsi per Kategori</p>
                        @foreach($categories as $category)
                            <div class="flex items-center justify-between gap-sm">
                                <label class="font-body text-micro text-ink">{{ $category->name }}</label>
                                <input type="number" name="quotas[{{ $category->id }}]" x-model="form.quotas['{{ $category->id }}']" min="0" class="w-28 rounded border border-border-soft bg-surface px-sm py-xs font-mono text-body-md text-ink text-right">
                            </div>
                        @endforeach
                    </div>
                @endif

                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Perubahan (opsional untuk Log Revisi)</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>

                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                    Simpan Lokasi
                </button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
