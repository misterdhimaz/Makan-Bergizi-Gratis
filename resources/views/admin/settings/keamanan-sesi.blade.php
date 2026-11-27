<x-layouts.admin
    title="Keamanan Sesi"
    subtitle="Kebijakan batas waktu sesi, penguncian akun otomatis, dan standar kata sandi"
    breadcrumb="Administrator Sistem · Pengaturan Sistem"
>
    <form method="POST" action="{{ route('admin.settings.keamanan-sesi.update') }}" class="flex flex-col gap-lg">
        @csrf
        @method('PUT')

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            <div>
                <h2 class="font-display text-section-title text-ink">Kebijakan Sesi &amp; Penguncian Akun</h2>
                <p class="font-body text-micro text-ink-muted">Pengaturan ini langsung diterapkan oleh middleware batas sesi dan proses login.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                <div class="flex flex-col gap-xs">
                    <label for="session_idle_timeout_minutes" class="font-body text-label-sm text-ink">Batas Sesi Tidak Aktif (menit)</label>
                    <input id="session_idle_timeout_minutes" type="number" min="5" max="1440" name="session_idle_timeout_minutes" value="{{ old('session_idle_timeout_minutes', $values['session_idle_timeout_minutes']) }}" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>

                <div class="flex flex-col gap-xs">
                    <label for="max_failed_login_attempts" class="font-body text-label-sm text-ink">Batas Gagal Login</label>
                    <input id="max_failed_login_attempts" type="number" min="3" max="20" name="max_failed_login_attempts" value="{{ old('max_failed_login_attempts', $values['max_failed_login_attempts']) }}" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>

                <div class="flex flex-col gap-xs">
                    <label for="account_lock_duration_minutes" class="font-body text-label-sm text-ink">Durasi Kunci Akun (menit)</label>
                    <input id="account_lock_duration_minutes" type="number" min="1" max="1440" name="account_lock_duration_minutes" value="{{ old('account_lock_duration_minutes', $values['account_lock_duration_minutes']) }}" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>
            </div>

            <label class="flex items-center gap-sm pt-xs cursor-pointer">
                <input type="checkbox" name="logout_on_role_change" value="1" @checked(old('logout_on_role_change', $values['logout_on_role_change'])) class="rounded border-border-soft text-obsidian focus:ring-0">
                <span class="font-body text-body-md text-ink">Akhiri sesi aktif otomatis saat peran akun diubah oleh Administrator</span>
            </label>
        </section>

        <section class="bg-surface rounded-xl border border-border-soft p-lg flex flex-col gap-md">
            <div>
                <h2 class="font-display text-section-title text-ink">Kebijakan Kata Sandi</h2>
                <p class="font-body text-micro text-ink-muted">Berlaku saat pembuatan kata sandi baru maupun penggantian sandi wajib.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="flex flex-col gap-xs">
                    <label for="password_min_length" class="font-body text-label-sm text-ink">Panjang Minimal Kata Sandi (karakter)</label>
                    <input id="password_min_length" type="number" min="6" max="64" name="password_min_length" value="{{ old('password_min_length', $values['password_min_length']) }}" required class="rounded border border-border-soft bg-surface-subtle px-md py-sm font-mono text-body-md text-ink">
                </div>
            </div>

            <div class="flex flex-col gap-sm">
                <label class="flex items-center gap-sm cursor-pointer">
                    <input type="checkbox" name="password_require_mixed_case_number" value="1" @checked(old('password_require_mixed_case_number', $values['password_require_mixed_case_number'])) class="rounded border-border-soft text-obsidian focus:ring-0">
                    <span class="font-body text-body-md text-ink">Wajib kombinasi huruf besar, huruf kecil, dan angka</span>
                </label>

                <label class="flex items-center gap-sm cursor-pointer">
                    <input type="checkbox" name="force_password_change_first_login" value="1" @checked(old('force_password_change_first_login', $values['force_password_change_first_login'])) class="rounded border-border-soft text-obsidian focus:ring-0">
                    <span class="font-body text-body-md text-ink">Wajib ganti kata sandi sementara saat login pertama</span>
                </label>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-sm px-xl py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold hover:opacity-90 cursor-pointer">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">save</span>
                Simpan Pengaturan Keamanan
            </button>
        </div>
    </form>
</x-layouts.admin>
