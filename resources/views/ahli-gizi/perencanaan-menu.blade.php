@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $menusJson = $menus->getCollection()->map(static function ($m) {
        return [
            'id' => $m->id,
            'operational_date' => $m->operational_date?->format('Y-m-d'),
            'date_label' => $m->operational_date?->translatedFormat('d M Y'),
            'menu_name' => $m->menu_name,
            'target_portions' => $m->target_portions,
            'categories' => $m->categories_json ?? ['Siswa'],
            'components' => $m->components_json ?? [],
            'status' => $m->status,
            'notes' => $m->notes ?? '',
            'approval_notes' => $m->approval_notes ?? '',
            'history' => $m->status_history_json ?? [],
            'update_url' => route('ahli-gizi.menu.update', $m),
            'revision_url' => route('ahli-gizi.menu.request-revision', $m),
        ];
    })->values();
@endphp

<x-layouts.ahli-gizi
    title="Perencanaan Menu"
    subtitle="Susun draf menu harian, ajukan pemeriksaan PAGU ke Akuntan, dan pantau persetujuan Kepala Dapur"
    breadcrumb="Ahli Gizi · SPPG Sungai Selayur 1"
>
    <x-slot:actions>
        <button
            type="button"
            x-data
            @click="$dispatch('open-menu-create')"
            class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer"
        >
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">add</span>
            Buat Draf Menu
        </button>
    </x-slot:actions>

    <div
        x-data="ahliGiziMenuPage(@js([
            'menus' => $menusJson,
            'storeUrl' => route('ahli-gizi.menu.store'),
            'csrf' => csrf_token(),
        ]))"
        @open-menu-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        {{-- 4 KPI Cards --}}
        <section aria-label="Ringkasan status perencanaan menu" class="grid grid-cols-2 lg:grid-cols-4 gap-md items-stretch">
            <x-stat-card label="Draf" :value="$fmt($kpi['draft'])" badge="Belum diajukan" badge-variant="neutral" />
            <x-stat-card label="Menunggu pemeriksaan" :value="$fmt($kpi['pending'])" badge="Di Akuntan" badge-variant="neutral" />
            <x-stat-card label="Disetujui" :value="$fmt($kpi['approved'])" badge="Terkunci" badge-variant="success" highlight />
            <x-stat-card label="Perlu revisi" :value="$fmt($kpi['revision'])" badge="Tindak lanjut" badge-variant="warning" />
        </section>

        {{-- Tabel & Filter --}}
        <section class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <form method="GET" action="{{ route('ahli-gizi.menu.index') }}" class="flex flex-wrap items-center justify-between gap-sm">
                <div class="flex items-center flex-1 min-w-[200px] bg-surface-subtle border border-border-soft rounded-full px-md py-sm gap-sm">
                    <span class="material-symbols-outlined text-card-title text-ink-muted" aria-hidden="true">search</span>
                    <input type="search" name="q" value="{{ $filters['q'] }}" aria-label="Cari tanggal atau nama menu" placeholder="Cari tanggal atau menu..." class="w-full bg-transparent border-0 p-0 font-body text-body-md text-ink placeholder:text-ink-muted focus:ring-0">
                </div>

                <div class="flex flex-wrap items-center gap-sm">
                    <select name="status" aria-label="Filter status menu" onchange="this.form.submit()" class="rounded-full border border-border-soft bg-surface-subtle px-md py-sm font-body text-label-sm text-ink focus:border-ink focus:ring-0">
                        <option value="" @selected($filters['status'] === '')>Semua status</option>
                        <option value="draf" @selected($filters['status'] === 'draf')>Draf</option>
                        <option value="menunggu" @selected($filters['status'] === 'menunggu')>Menunggu Pemeriksaan Anggaran</option>
                        <option value="revisi-anggaran" @selected($filters['status'] === 'revisi-anggaran')>Perlu Revisi Anggaran</option>
                        <option value="disetujui" @selected($filters['status'] === 'disetujui')>Disetujui</option>
                        <option value="minta-revisi" @selected($filters['status'] === 'minta-revisi')>Minta Revisi</option>
                    </select>
                    <button type="submit" class="px-md py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Filter</button>
                </div>
            </form>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="py-sm pr-md font-body text-label-sm text-ink-muted whitespace-nowrap">Tanggal</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Menu</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Kategori</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted text-right whitespace-nowrap">Porsi</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted">Status</th>
                            <th class="py-sm px-md font-body text-label-sm text-ink-muted whitespace-nowrap">Catatan terakhir</th>
                            <th class="py-sm pl-md font-body text-label-sm text-ink-muted text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        @foreach($menus as $m)
                            @php
                                $cats = $m->categories_json ?? ['Siswa'];
                                $isApproved = $m->isLocked();
                                $canEdit = in_array($m->status, \App\Enums\MenuStatus::editableByAhliGizi(), true);
                                $badgeVariant = match ($m->status) {
                                    'disetujui' => 'success',
                                    'minta_revisi' => 'warning',
                                    'perlu_revisi_anggaran' => 'danger',
                                    default => 'neutral',
                                };
                                $badgeLabel = match ($m->status) {
                                    'disetujui' => 'Disetujui',
                                    'menunggu_pemeriksaan_anggaran' => 'Menunggu Pemeriksaan Anggaran',
                                    'lolos_pagu' => 'Lolos PAGU',
                                    'minta_revisi' => 'Minta Revisi',
                                    'perlu_revisi_anggaran' => 'Perlu Revisi Anggaran',
                                    default => 'Draf',
                                };
                            @endphp
                            <tr>
                                <td class="py-md pr-md font-mono text-label-sm text-ink whitespace-nowrap">{{ $m->operational_date?->translatedFormat('d M') }}</td>
                                <td class="py-md px-md font-body text-card-title text-ink">{{ $m->menu_name }}</td>
                                <td class="py-md px-md">
                                    @if(count($cats) >= 4)
                                        <span class="px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink whitespace-nowrap">Semua (4)</span>
                                    @else
                                        <div class="flex flex-wrap gap-xs">
                                            @foreach($cats as $cat)
                                                <span class="px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink">{{ $cat }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-md px-md font-mono text-body-md text-ink text-right tabular-nums">{{ $fmt($m->target_portions) }}</td>
                                <td class="py-md px-md">
                                    <span class="inline-flex items-center gap-xs">
                                        @if($isApproved)
                                            <span class="material-symbols-outlined text-micro text-on-success-soft" aria-hidden="true">lock</span>
                                        @endif
                                        <x-badge-status :label="$badgeLabel" :variant="$badgeVariant" />
                                    </span>
                                </td>
                                <td class="py-md px-md font-body text-micro text-ink-muted">{{ $m->approval_notes }}</td>
                                <td class="py-md pl-md text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-xs">
                                        <button type="button" @click="selectDetail({{ $m->id }})" class="px-md py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink hover:bg-surface-muted cursor-pointer">Detail</button>
                                        @if($isApproved)
                                            <button type="button" @click="openRevision({{ $m->id }})" class="px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro hover:opacity-90 cursor-pointer">Ajukan revisi menu</button>
                                        @elseif($canEdit)
                                            <button type="button" @click="openEdit({{ $m->id }})" class="px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro hover:opacity-90 cursor-pointer">Ubah</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden flex flex-col gap-sm">
                @foreach($menus as $m)
                    @php
                        $isApproved = $m->isLocked();
                        $canEdit = in_array($m->status, \App\Enums\MenuStatus::editableByAhliGizi(), true);
                    @endphp
                    <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                        <div class="flex items-start justify-between gap-sm">
                            <div>
                                <p class="font-mono text-micro text-ink-muted">{{ $m->operational_date?->translatedFormat('d M Y') }} · {{ $fmt($m->target_portions) }} porsi</p>
                                <p class="font-body text-card-title text-ink">{{ $m->menu_name }}</p>
                            </div>
                            <span class="px-sm py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink whitespace-nowrap">{{ $isApproved ? 'Disetujui' : $m->status_label }}</span>
                        </div>
                        <p class="font-body text-micro text-ink-muted">{{ $m->approval_notes }}</p>
                        <div class="flex gap-xs">
                            <button type="button" @click="selectDetail({{ $m->id }})" class="flex-1 px-md py-xs rounded-full bg-surface border border-border-soft font-body text-micro text-ink cursor-pointer">Detail</button>
                            @if($isApproved)
                                <button type="button" @click="openRevision({{ $m->id }})" class="flex-1 px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro cursor-pointer">Ajukan revisi menu</button>
                            @elseif($canEdit)
                                <button type="button" @click="openEdit({{ $m->id }})" class="flex-1 px-md py-xs rounded-full bg-obsidian text-on-obsidian font-body text-micro cursor-pointer">Ubah</button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <x-pagination :paginator="$menus" label="rencana menu" />
        </section>

        {{-- Panel Detail Riwayat Status (Hanya Baca) --}}
        <section aria-label="Panel detail linimasa status menu" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
            <div class="flex items-center justify-between gap-sm">
                <h2 class="font-display text-section-title text-ink" x-text="'Detail Menu · ' + (detailMenu?.date_label || '')">Detail Menu</h2>
                <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink-muted">
                    <span class="material-symbols-outlined text-micro" aria-hidden="true">visibility</span>
                    Hanya baca
                </span>
            </div>

            <ol class="flex flex-col gap-sm border-l-2 border-border-soft pl-md">
                <template x-for="(item, idx) in (detailMenu?.history || [])" :key="idx">
                    <li class="flex flex-col gap-xs">
                        <span class="font-body text-label-sm text-ink" x-text="item.title"></span>
                        <template x-if="item.note">
                            <p class="font-body text-micro text-ink-muted" x-text="'“' + item.note + '”'"></p>
                        </template>
                        <span class="font-mono text-micro text-ink-muted" x-text="item.at"></span>
                    </li>
                </template>
            </ol>
        </section>

        {{-- Slide Panel / Bottom Sheet: Buat / Ubah Draf Menu --}}
        <x-slide-panel open-var="formOpen" title="Ubah Draf Menu" subtitle="Formulir rencana menu harian SPPG">
            <form method="POST" :action="form.action" class="flex flex-col gap-md">
                <input type="hidden" name="_token" :value="csrf">
                <template x-if="form.method === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Tanggal pelaksanaan</span>
                    <input type="date" name="operational_date" x-model="form.operational_date" required class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </label>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Nama menu</span>
                    <input type="text" name="menu_name" x-model="form.menu_name" required class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </label>

                <div class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Pilihan bahan baku (Daftar Bahan · {{ $ingredients->count() }} bahan)</span>
                    <select aria-label="Pilih bahan dari Daftar Bahan" @change="appendIngredient($event.target.value); $event.target.value=''" class="w-full rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-body text-micro text-ink">
                        <option value="">Tambah bahan dari Daftar Bahan...</option>
                        @foreach($ingredients as $ing)
                            <option value="{{ $ing->name }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                        @endforeach
                    </select>

                    <div class="grid grid-cols-2 gap-xs">
                        <select name="components[pokok]" x-model="form.components.pokok" aria-label="Pokok" class="rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-body text-micro text-ink">
                            <option value="Beras">Pokok: Beras</option>
                        </select>
                        <select name="components[hewani]" x-model="form.components.hewani" aria-label="Lauk hewani" class="rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-body text-micro text-ink">
                            <option value="Ayam Fillet">Hewani: Ayam Fillet</option>
                            <option value="Ikan Patin">Hewani: Ikan Patin</option>
                            <option value="Telur Ayam">Hewani: Telur Ayam</option>
                            <option value="Daging Sapi">Hewani: Daging Sapi</option>
                        </select>
                        <select name="components[nabati]" x-model="form.components.nabati" aria-label="Lauk nabati" class="rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-body text-micro text-ink">
                            <option value="Tahu Putih">Nabati: Tahu Putih</option>
                            <option value="Tempe">Nabati: Tempe</option>
                        </select>
                        <select name="components[sayur]" x-model="form.components.sayur" aria-label="Sayur" class="rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-body text-micro text-ink">
                            <option value="Buncis">Sayur: Buncis</option>
                            <option value="Wortel">Sayur: Wortel</option>
                            <option value="Bayam">Sayur: Bayam</option>
                            <option value="Wortel & Buncis">Sayur: Wortel &amp; Buncis</option>
                        </select>
                    </div>
                    <select name="components[minuman]" x-model="form.components.minuman" aria-label="Minuman" class="rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-body text-micro text-ink">
                        <option value="Susu UHT">Minuman: Susu UHT</option>
                        <option value="Tanpa minuman">Tanpa minuman</option>
                    </select>
                </div>

                <div class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Kategori penerima</span>
                    <div class="flex flex-wrap gap-xs">
                        @foreach($categories as $cat)
                            <label class="inline-flex items-center gap-xs px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink cursor-pointer">
                                <input type="checkbox" name="categories[]" value="{{ $cat->name }}" :checked="form.categories.includes('{{ $cat->name }}')" @change="toggleCategory('{{ $cat->name }}')" class="rounded text-obsidian focus:ring-0">
                                {{ $cat->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Target porsi <span class="text-on-danger-soft">*</span></span>
                    <input type="number" name="target_portions" x-model="form.target_portions" min="1" required class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                    <span class="font-body text-micro text-ink-muted">Minimal 1 porsi.</span>
                </label>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Catatan</span>
                    <textarea name="notes" rows="2" x-model="form.notes" class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink"></textarea>
                </label>

                <div class="flex items-center justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="submit" name="submit_action" value="draft" class="px-md py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink cursor-pointer">Simpan Draf</button>
                    <button type="submit" name="submit_action" value="submit" class="px-md py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Ajukan ke Akuntan</button>
                </div>
            </form>
        </x-slide-panel>

        {{-- Slide Panel / Bottom Sheet: Ajukan Revisi Menu Disetujui --}}
        <x-slide-panel open-var="revisiOpen" title="Ajukan revisi menu" subtitle="Menu Disetujui (terkunci) · Persetujuan Kepala Dapur">
            <form method="POST" :action="revisionUrl" class="flex flex-col gap-md">
                <input type="hidden" name="_token" :value="csrf">
                <div class="p-md rounded-lg bg-surface-subtle border border-border-soft flex flex-col gap-xs">
                    <span class="font-body text-micro text-ink-muted">Menu yang diajukan revisi</span>
                    <p class="font-body text-body-md text-ink" x-text="revisionLabel"></p>
                </div>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Usulan perubahan (opsional)</span>
                    <input type="text" name="proposed_change" placeholder="Contoh: Sayur: Buncis → Buncis dan wortel" class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">
                </label>

                <label class="flex flex-col gap-xs">
                    <span class="font-body text-label-sm text-ink">Alasan revisi <span class="text-on-danger-soft">*</span></span>
                    <textarea name="reason" rows="3" required minlength="5" placeholder="Tuliskan alasan perubahan menu yang sudah disetujui..." class="w-full rounded-md border border-border-soft bg-surface px-md py-sm font-body text-body-md text-ink"></textarea>
                </label>

                <div class="flex items-center justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="revisiOpen = false" class="px-lg py-sm rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Kirim Pengajuan</button>
                </div>
            </form>
        </x-slide-panel>
    </div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ahliGiziMenuPage', (payload) => ({
            menus: payload.menus,
            storeUrl: payload.storeUrl,
            csrf: payload.csrf,
            formOpen: false,
            revisiOpen: false,
            detailMenu: payload.menus.find((m) => m.status === 'minta_revisi') || payload.menus[0] || null,
            revisionUrl: '#',
            revisionLabel: '',
            form: {
                method: 'POST',
                action: payload.storeUrl,
                operational_date: '',
                menu_name: '',
                target_portions: 1414,
                categories: ['Siswa', 'Ibu hamil', 'Ibu menyusui', 'Balita'],
                components: { pokok: 'Beras', hewani: 'Ayam Fillet', nabati: 'Tahu Putih', sayur: 'Buncis', minuman: 'Susu UHT' },
                notes: '',
            },
            findMenu(id) {
                return this.menus.find((m) => m.id === id) || null;
            },
            selectDetail(id) {
                const m = this.findMenu(id);
                if (m) this.detailMenu = m;
            },
            openCreate() {
                this.form = {
                    method: 'POST',
                    action: this.storeUrl,
                    operational_date: new Date().toISOString().slice(0, 10),
                    menu_name: '',
                    target_portions: 1414,
                    categories: ['Siswa', 'Ibu hamil', 'Ibu menyusui', 'Balita'],
                    components: { pokok: 'Beras', hewani: 'Ayam Fillet', nabati: 'Tahu Putih', sayur: 'Buncis', minuman: 'Susu UHT' },
                    notes: '',
                };
                this.formOpen = true;
            },
            openEdit(id) {
                const m = this.findMenu(id);
                if (!m) return;
                this.form = {
                    method: 'PUT',
                    action: m.update_url,
                    operational_date: m.operational_date,
                    menu_name: m.menu_name,
                    target_portions: m.target_portions,
                    categories: [...(m.categories || ['Siswa'])],
                    components: {
                        pokok: m.components?.pokok || 'Beras',
                        hewani: m.components?.hewani || 'Ayam Fillet',
                        nabati: m.components?.nabati || 'Tahu Putih',
                        sayur: m.components?.sayur || 'Buncis',
                        minuman: m.components?.minuman || 'Susu UHT',
                    },
                    notes: m.notes || '',
                };
                this.formOpen = true;
            },
            openRevision(id) {
                const m = this.findMenu(id);
                if (!m) return;
                this.revisionUrl = m.revision_url;
                this.revisionLabel = m.date_label + ' · ' + m.menu_name;
                this.revisiOpen = true;
            },
            toggleCategory(cat) {
                if (this.form.categories.includes(cat)) {
                    this.form.categories = this.form.categories.filter((c) => c !== cat);
                } else {
                    this.form.categories.push(cat);
                }
            },
            appendIngredient(name) {
                if (!name) return;
                this.form.menu_name = this.form.menu_name ? (this.form.menu_name + ', ' + name) : name;
            },
        }));
    });
</script>
@endpush
</x-layouts.ahli-gizi>
