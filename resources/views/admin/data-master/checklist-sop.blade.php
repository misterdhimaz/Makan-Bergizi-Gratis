@php
    $itemsJson = $checklists->getCollection()->map(static fn ($item) => [
        'id' => $item->id,
        'code' => $item->code,
        'type' => $item->type->value,
        'title' => $item->title,
        'threshold_value' => $item->threshold_value,
        'frequency' => $item->frequency,
        'description' => $item->description,
        'update_url' => route('admin.data-master.checklist-sop.update', $item),
    ])->values();
@endphp

<x-layouts.admin
    title="Checklist SOP BGN"
    subtitle="Parameter pemeriksaan suhu, kebersihan sanitasi, dan kepatuhan APD dapur SPPG"
    breadcrumb="Administrator Sistem · Data Master"
>
    <x-slot:actions>
        <button type="button" x-data @click="$dispatch('open-sop-create')" class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Tambah Checklist SOP
        </button>
    </x-slot:actions>

    <div
        x-data="{
            panelOpen: false,
            mode: 'create',
            actionUrl: '{{ route('admin.data-master.checklist-sop.store') }}',
            form: { code: '', type: 'kebersihan', title: '', threshold_value: '', frequency: '', description: '', reason: '' },
            openCreate() {
                this.mode = 'create';
                this.actionUrl = '{{ route('admin.data-master.checklist-sop.store') }}';
                this.form = { code: '', type: 'kebersihan', title: '', threshold_value: '', frequency: '', description: '', reason: '' };
                this.panelOpen = true;
            },
            openEdit(item) {
                this.mode = 'edit';
                this.actionUrl = item.update_url;
                this.form = {
                    ...item,
                    threshold_value: item.threshold_value || '',
                    frequency: item.frequency || '',
                    description: item.description || '',
                    reason: '',
                };
                this.panelOpen = true;
            }
        }"
        @open-sop-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        <x-filter-bar :action="route('admin.data-master.checklist-sop.index')">
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari Checklist</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kode, judul, atau ambang batas" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
            </div>
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-type" class="font-body text-micro text-ink-muted">Jenis SOP</label>
                <select id="filter-type" name="type" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    <option value="all" @selected($filters['type'] === 'all')>Semua jenis</option>
                    @foreach($types as $type)
                        <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>
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
            @if($checklists->isEmpty())
                <x-empty-state icon="fact_check" title="Belum ada item checklist SOP" description="Tambahkan item checklist suhu, kebersihan, atau APD." />
            @else
                <div class="hidden md:block">
                    <x-data-table :headers="['Kode', 'Jenis', 'Judul Pemeriksaan', 'Ambang Batas', 'Frekuensi', 'Status', 'Aksi']">
                        @foreach($checklists as $idx => $item)
                            <tr class="hover:bg-surface-subtle/60">
                                <td class="px-md py-sm font-mono text-micro text-ink-muted">{{ $item->code }}</td>
                                <td class="px-md py-sm"><x-badge-status variant="neutral" :label="$item->type->label()" /></td>
                                <td class="px-md py-sm font-body text-label-sm font-semibold text-ink">{{ $item->title }}</td>
                                <td class="px-md py-sm font-mono text-micro text-ink">{{ $item->threshold_value ?? '-' }}</td>
                                <td class="px-md py-sm font-body text-micro text-ink-muted">{{ $item->frequency ?? '-' }}</td>
                                <td class="px-md py-sm"><x-badge-status :variant="$item->is_active ? 'success' : 'danger'" :label="$item->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="px-md py-sm text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                        <form method="POST" action="{{ $item->is_active ? route('admin.data-master.checklist-sop.deactivate', $item) : route('admin.data-master.checklist-sop.activate', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($checklists as $idx => $item)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-mono text-micro text-ink-muted">{{ $item->code }} &middot; {{ $item->type->label() }}</p>
                                    <p class="font-body text-label-sm font-semibold text-ink">{{ $item->title }}</p>
                                </div>
                                <x-badge-status :variant="$item->is_active ? 'success' : 'danger'" :label="$item->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <p class="font-body text-micro text-ink-muted">Ambang: {{ $item->threshold_value ?? '-' }} &middot; {{ $item->frequency ?? '-' }}</p>
                            <div class="flex gap-xs">
                                <button type="button" @click="openEdit(@js($itemsJson[$idx]))" class="flex-1 px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">Ubah</button>
                                <form method="POST" action="{{ $item->is_active ? route('admin.data-master.checklist-sop.deactivate', $item) : route('admin.data-master.checklist-sop.activate', $item) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full px-md py-xs rounded-full border border-border-soft bg-surface font-body text-micro font-semibold text-ink cursor-pointer">{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$checklists" />
            @endif
        </section>

        <x-slide-panel open="panelOpen" title="Formulir Checklist SOP BGN" subtitle="Pastikan ketiga jenis (suhu, kebersihan, APD) terisi">
            <form method="POST" :action="actionUrl" class="flex flex-col gap-md">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Kode SOP</label>
                        <input type="text" name="code" x-model="form.code" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Jenis</label>
                        <select name="type" x-model="form.type" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                            @foreach($types as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Judul Pemeriksaan</label>
                    <input type="text" name="title" x-model="form.title" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Ambang Batas / Standar</label>
                        <input type="text" name="threshold_value" x-model="form.threshold_value" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Frekuensi</label>
                        <input type="text" name="frequency" x-model="form.frequency" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-label-sm text-ink">Deskripsi</label>
                    <textarea name="description" x-model="form.description" rows="2" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink"></textarea>
                </div>
                <template x-if="mode === 'edit'">
                    <div class="flex flex-col gap-xs">
                        <label class="font-body text-label-sm text-ink">Alasan Perubahan</label>
                        <input type="text" name="reason" x-model="form.reason" class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                    </div>
                </template>
                <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Simpan Checklist SOP</button>
            </form>
        </x-slide-panel>
    </div>
</x-layouts.admin>
