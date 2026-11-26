@php
    $fmt = static fn (int|float|null $n): string => number_format((int) $n, 0, ',', '.');
    $usersJson = $users->getCollection()->map(static function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role->value,
            'role_label' => $user->role->label(),
            'location_id' => $user->location_id,
            'location_name' => $user->location?->name,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i'),
            'update_url' => route('admin.users.update', $user),
            'deactivate_url' => route('admin.users.deactivate', $user),
            'activate_url' => route('admin.users.activate', $user),
            'reset_url' => route('admin.users.reset-password', $user),
        ];
    })->values();
@endphp

<x-layouts.admin
    title="Pengguna & Peran"
    subtitle="Kelola akun dan hak akses 7 peran SPPG Sungai Selayur 1"
    breadcrumb="Administrator Sistem"
>
    <x-slot:actions>
        <button
            type="button"
            x-data
            @click="$dispatch('admin-users-open-create')"
            class="inline-flex items-center gap-sm px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer"
        >
            <span class="material-symbols-outlined text-card-title" aria-hidden="true">person_add</span>
            Tambah Pengguna
        </button>
    </x-slot:actions>

    <div
        x-data="adminUsersPage(@js([
            'users' => $usersJson,
            'roles' => collect($roles)->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()])->values(),
            'locations' => $locations->map(fn ($l) => ['id' => $l->id, 'name' => $l->name])->values(),
            'storeUrl' => route('admin.users.store'),
            'csrf' => csrf_token(),
            'currentUserId' => auth()->id(),
        ]))"
        @admin-users-open-create.window="openCreate()"
        class="flex flex-col gap-lg"
    >
        @if(session('temporary_password'))
            <div role="status" class="rounded-xl border border-border-soft bg-lime-accent text-on-lime p-lg flex flex-col gap-sm">
                <p class="font-body text-label-sm font-semibold">Kata sandi sementara (tampil sekali)</p>
                <p class="font-body text-micro">
                    Untuk akun <span class="font-semibold">{{ session('temporary_password_user') }}</span>.
                    Salin sekarang — nilai ini tidak disimpan di halaman daftar.
                </p>
                <code class="font-mono text-card-title bg-surface/70 px-md py-sm rounded-md w-fit select-all">{{ session('temporary_password') }}</code>
            </div>
        @endif

        {{-- Statistik ringkas --}}
        <section aria-label="Ringkasan akun" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-md">
            <x-stat-card label="Akun aktif" :value="$fmt($stats['aktif'])" badge="Aktif" badge-variant="success" highlight />
            <x-stat-card label="Dinonaktifkan" :value="$fmt($stats['nonaktif'])" icon="person_off" />
            <x-stat-card label="Belum pernah login" :value="$fmt($stats['belum_login'])" icon="login" />
            <x-stat-card label="Peran terpakai" :value="$fmt($stats['peran_terpakai']).'/'.$fmt($stats['peran_total'])" hint="7 peran baku" icon="badge" />
        </section>

        {{-- Filter --}}
        <x-filter-bar :action="route('admin.users.index')">
            <div class="lg:col-span-4 flex flex-col gap-xs">
                <label for="filter-q" class="font-body text-micro text-ink-muted">Cari</label>
                <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Nama, username, atau email"
                       class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
            </div>
            <div class="lg:col-span-2 flex flex-col gap-xs">
                <label for="filter-role" class="font-body text-micro text-ink-muted">Peran</label>
                <select id="filter-role" name="role" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected($filters['role'] === 'all')>Semua peran</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected($filters['role'] === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2 flex flex-col gap-xs">
                <label for="filter-status" class="font-body text-micro text-ink-muted">Status</label>
                <select id="filter-status" name="status" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                    <option value="belum_login" @selected($filters['status'] === 'belum_login')>Belum pernah login</option>
                </select>
            </div>
            <div class="lg:col-span-3 flex flex-col gap-xs">
                <label for="filter-location" class="font-body text-micro text-ink-muted">Lokasi (Guru PIC)</label>
                <select id="filter-location" name="location_id" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                    <option value="all" @selected(($filters['location_id'] ?? 'all') === 'all')>Semua lokasi (Guru PIC)</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) $filters['location_id'] === (string) $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-1 flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-xs px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">search</span>
                    Filter
                </button>
            </div>
        </x-filter-bar>

        {{-- Tabel / kartu pengguna --}}
        <section aria-label="Daftar pengguna" class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            @if($users->isEmpty())
                <x-empty-state title="Tidak ada pengguna" description="Sesuaikan filter atau tambah akun baru." icon="group_off" />
            @else
                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                                <th class="px-md py-sm font-medium">Nama</th>
                                <th class="px-md py-sm font-medium">Username</th>
                                <th class="px-md py-sm font-medium">Peran</th>
                                <th class="px-md py-sm font-medium">Lokasi</th>
                                <th class="px-md py-sm font-medium">Status</th>
                                <th class="px-md py-sm font-medium">Login terakhir</th>
                                <th class="px-md py-sm font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft font-body text-body-md">
                            @foreach($users as $user)
                                <tr>
                                    <td class="px-md py-sm text-ink font-medium">{{ $user->name }}</td>
                                    <td class="px-md py-sm text-ink-muted font-mono text-micro">{{ $user->username }}</td>
                                    <td class="px-md py-sm text-ink">{{ $user->role->label() }}</td>
                                    <td class="px-md py-sm text-ink-muted">{{ $user->location?->name ?? 'Semua' }}</td>
                                    <td class="px-md py-sm">
                                        <x-badge-status
                                            :label="$user->is_active ? 'Aktif' : 'Nonaktif'"
                                            :variant="$user->is_active ? 'success' : 'neutral'"
                                            :dot="true"
                                        />
                                    </td>
                                    <td class="px-md py-sm text-ink-muted tabular-nums whitespace-nowrap">
                                        {{ $user->last_login_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}
                                    </td>
                                    <td class="px-md py-sm">
                                        <div class="flex flex-wrap justify-end gap-xs">
                                            <button type="button" class="px-md py-xs rounded-full border border-border-soft font-body text-micro text-ink hover:bg-surface-subtle cursor-pointer"
                                                    @click="openEdit({{ $user->id }})">Ubah</button>
                                            <button type="button" class="px-md py-xs rounded-full border border-border-soft font-body text-micro text-ink hover:bg-surface-subtle cursor-pointer"
                                                    @click="openReset({{ $user->id }})">Reset sandi</button>
                                            @if($user->is_active)
                                                @if($user->id !== auth()->id())
                                                    <button type="button" class="px-md py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro cursor-pointer"
                                                            @click="openDeactivate({{ $user->id }})">Nonaktifkan</button>
                                                @endif
                                            @else
                                                <button type="button" class="px-md py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro cursor-pointer"
                                                        @click="activateUser({{ $user->id }})">Aktifkan</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($users as $user)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-sm">
                            <div class="flex items-start justify-between gap-sm">
                                <div class="min-w-0">
                                    <p class="font-body text-label-sm text-ink font-semibold">{{ $user->name }}</p>
                                    <p class="font-body text-micro text-ink-muted">{{ '@'.$user->username }} · {{ $user->role->label() }}</p>
                                    <p class="font-body text-micro text-ink-muted">Lokasi: {{ $user->location?->name ?? 'Semua' }} · Login: {{ $user->last_login_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}</p>
                                </div>
                                <x-badge-status :label="$user->is_active ? 'Aktif' : 'Nonaktif'" :variant="$user->is_active ? 'success' : 'neutral'" :dot="true" />
                            </div>
                            <div class="flex flex-wrap gap-xs">
                                <button type="button" class="px-md py-xs rounded-full border border-border-soft font-body text-micro cursor-pointer" @click="openEdit({{ $user->id }})">Ubah</button>
                                <button type="button" class="px-md py-xs rounded-full border border-border-soft font-body text-micro cursor-pointer" @click="openReset({{ $user->id }})">Reset sandi</button>
                                @if($user->is_active && $user->id !== auth()->id())
                                    <button type="button" class="px-md py-xs rounded-full bg-alert-soft text-on-alert-soft font-body text-micro cursor-pointer" @click="openDeactivate({{ $user->id }})">Nonaktifkan</button>
                                @elseif(! $user->is_active)
                                    <button type="button" class="px-md py-xs rounded-full bg-success-soft text-on-success-soft font-body text-micro cursor-pointer" @click="activateUser({{ $user->id }})">Aktifkan</button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$users" label="akun" />
            @endif
        </section>

        {{-- Matriks hak akses read-only (sumber tunggal dari config/mbg.php untuk desktop & mobile) --}}
        @php
            $matrixColumns = config('mbg.role_matrix_columns', []);
            $matrixBadgeVariant = static function (string $hak): string {
                $lower = strtolower($hak);
                if (str_contains($lower, 'tidak ada') || str_contains($lower, 'dilarang')) {
                    return 'neutral';
                }
                if (str_contains($lower, 'terbatas') || str_contains($lower, 'verifikasi')) {
                    return 'warning';
                }
                if (str_contains($lower, 'kelola penuh') || str_contains($lower, 'setujui')) {
                    return 'success';
                }
                return 'info';
            };
        @endphp
        <section aria-labelledby="matrix-heading" class="bg-surface rounded-xl border border-border-soft p-xl flex flex-col gap-md">
            <div class="flex flex-wrap items-center justify-between gap-sm">
                <div>
                    <h2 id="matrix-heading" class="font-display text-section-title text-ink">Matriks hak akses</h2>
                    <p class="font-body text-micro text-ink-muted">Hanya lihat · 7 peran baku (dibaca dari satu sumber konfigurasi).</p>
                </div>
                <span class="font-body text-micro text-ink-muted">Peran bersifat tetap dan tidak dapat ditambah</span>
            </div>

            {{-- Tabel Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[780px]">
                    <thead>
                        <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                            <th class="py-sm pr-md font-medium">Peran</th>
                            @foreach($matrixColumns as $col)
                                <th class="py-sm px-sm font-medium">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft font-body text-body-md">
                        @foreach($roleMatrix as $key => $row)
                            <tr>
                                <td class="py-md pr-md align-top text-ink font-semibold whitespace-nowrap">{{ $row['label'] ?? $key }}</td>
                                @foreach($matrixColumns as $col)
                                    @php($hak = $row['akses'][$col] ?? 'Tidak ada akses')
                                    <td class="py-md px-sm align-top">
                                        <x-badge-status :label="$hak" :variant="$matrixBadgeVariant($hak)" />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu Mobile (sumber data sama dengan desktop) --}}
            <div class="md:hidden flex flex-col gap-sm">
                @foreach($roleMatrix as $key => $row)
                    <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                        <p class="font-body text-label-sm font-semibold text-ink">{{ $row['label'] ?? $key }}</p>
                        <dl class="flex flex-col gap-xs mt-xs">
                            @foreach($matrixColumns as $col)
                                @php($hak = $row['akses'][$col] ?? 'Tidak ada akses')
                                <div class="flex items-center justify-between gap-sm">
                                    <dt class="font-body text-micro text-ink-muted">{{ $col }}</dt>
                                    <dd><x-badge-status :label="$hak" :variant="$matrixBadgeVariant($hak)" /></dd>
                                </div>
                            @endforeach
                        </dl>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Panel tambah / ubah --}}
        <x-slide-panel open-var="panelOpen" title="Tambah / Ubah Pengguna" subtitle="Panel formulir">
            <form method="POST" :action="form.action" class="flex flex-col gap-md">
                <input type="hidden" name="_token" :value="csrf">
                <template x-if="form.method === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="flex flex-col gap-xs">
                    <label class="font-body text-micro text-ink-muted" for="user-name">Nama lengkap</label>
                    <input id="user-name" name="name" x-model="form.name" required maxlength="255"
                           class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-micro text-ink-muted" for="user-username">Username</label>
                    <input id="user-username" name="username" x-model="form.username" required maxlength="80"
                           class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-micro text-ink-muted" for="user-email">Email</label>
                    <input id="user-email" type="email" name="email" x-model="form.email" required
                           class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-body text-micro text-ink-muted" for="user-role">Peran</label>
                    <select id="user-role" name="role" x-model="form.role" required
                            class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                        <template x-for="role in roles" :key="role.value">
                            <option :value="role.value" x-text="role.label"></option>
                        </template>
                    </select>
                </div>
                <div class="flex flex-col gap-xs" x-show="form.role === 'guru_pic'" x-cloak>
                    <label class="font-body text-micro text-ink-muted" for="user-location">Lokasi penerima (wajib untuk Guru PIC)</label>
                    <select id="user-location" name="location_id" x-model="form.location_id"
                            class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                        <option value="">Pilih lokasi</option>
                        <template x-for="loc in locations" :key="loc.id">
                            <option :value="loc.id" x-text="loc.name"></option>
                        </template>
                    </select>
                </div>

                <p class="font-body text-micro text-ink-muted" x-show="form.method === 'POST'">
                    Kata sandi sementara dibuat acak otomatis, wajib diganti saat login pertama, dan hanya ditampilkan sekali setelah disimpan.
                </p>

                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="panelOpen = false" class="px-lg py-sm rounded-full border border-border-soft font-body text-label-sm cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Simpan</button>
                </div>
            </form>
        </x-slide-panel>

        {{-- Dialog nonaktifkan --}}
        <x-confirm-dialog
            open-var="deactivateOpen"
            title="Nonaktifkan akun"
            message="Akun tidak dihapus. Riwayat aktivitas akun tetap tersimpan. Perubahan ini dicatat di Log Aktivitas. Lanjutkan?"
            variant="danger"
        >
            <p class="font-body text-body-md text-ink" x-text="selected ? (selected.name + ' · ' + selected.role_label) : ''"></p>
            <form id="deactivate-form" method="POST" :action="selected ? selected.deactivate_url : '#'" class="flex flex-col gap-sm mt-sm">
                <input type="hidden" name="_token" :value="csrf">
                <input type="hidden" name="_method" value="PATCH">
                <label class="font-body text-micro text-ink-muted" for="deactivate-reason">Alasan (opsional)</label>
                <textarea id="deactivate-reason" name="reason" rows="2" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md"></textarea>
            </form>
            <x-slot:actions>
                <button type="submit" form="deactivate-form" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Nonaktifkan</button>
            </x-slot:actions>
        </x-confirm-dialog>

        {{-- Dialog reset sandi --}}
        <x-confirm-dialog
            open-var="resetOpen"
            title="Reset kata sandi"
            message="Sistem akan membuat kata sandi sementara acak (berlaku 24 jam). Kata sandi tidak ditampilkan di daftar pengguna."
        >
            <p class="font-body text-body-md text-ink" x-text="selected ? ('Reset untuk @' + selected.username) : ''"></p>
            <form id="reset-form" method="POST" :action="selected ? selected.reset_url : '#'" class="flex flex-col gap-sm mt-sm">
                <input type="hidden" name="_token" :value="csrf">
                <label class="font-body text-micro text-ink-muted" for="reset-reason">Alasan (opsional)</label>
                <input id="reset-reason" name="reason" class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md">
            </form>
            <x-slot:actions>
                <button type="submit" form="reset-form" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm cursor-pointer">Reset sandi</button>
            </x-slot:actions>
        </x-confirm-dialog>

        {{-- Form tersembunyi untuk aktifkan --}}
        <form x-ref="activateForm" method="POST" class="hidden">
            <input type="hidden" name="_token" :value="csrf">
            <input type="hidden" name="_method" value="PATCH">
        </form>
    </div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminUsersPage', (payload) => ({
            users: payload.users,
            roles: payload.roles,
            locations: payload.locations,
            storeUrl: payload.storeUrl,
            csrf: payload.csrf,
            currentUserId: payload.currentUserId,
            panelOpen: false,
            deactivateOpen: false,
            resetOpen: false,
            selected: null,
            form: {
                method: 'POST',
                action: payload.storeUrl,
                name: '',
                username: '',
                email: '',
                role: payload.roles[0]?.value ?? 'guru_pic',
                location_id: '',
            },
            findUser(id) {
                return this.users.find((u) => u.id === id) || null;
            },
            openCreate() {
                this.form = {
                    method: 'POST',
                    action: this.storeUrl,
                    name: '',
                    username: '',
                    email: '',
                    role: this.roles[0]?.value ?? 'admin',
                    location_id: '',
                };
                this.panelOpen = true;
            },
            openEdit(id) {
                const user = this.findUser(id);
                if (!user) return;
                this.form = {
                    method: 'PUT',
                    action: user.update_url,
                    name: user.name,
                    username: user.username,
                    email: user.email,
                    role: user.role,
                    location_id: user.location_id ?? '',
                };
                this.panelOpen = true;
            },
            openDeactivate(id) {
                this.selected = this.findUser(id);
                this.deactivateOpen = true;
            },
            openReset(id) {
                this.selected = this.findUser(id);
                this.resetOpen = true;
            },
            activateUser(id) {
                const user = this.findUser(id);
                if (!user) return;
                const form = this.$refs.activateForm;
                form.action = user.activate_url;
                form.submit();
            },
        }));
    });
</script>
@endpush
</x-layouts.admin>
