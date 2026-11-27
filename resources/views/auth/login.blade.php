@component('layouts.auth', ['title' => 'Masuk ke Sistem'])
    <div x-data="{ showPassword: false, forgotOpen: false }" class="bg-surface rounded-xl border border-border-soft p-2xl shadow-sm flex flex-col gap-lg">
        {{-- Header Brand + Logo BGN --}}
        <div class="flex flex-col items-center text-center gap-sm">
            <img src="{{ asset('images/logo-bgn.png') }}" alt="Logo Badan Gizi Nasional" class="w-16 h-16 rounded-full object-contain">
            <div>
                <p class="font-mono text-micro text-ink-muted uppercase tracking-wider">Badan Gizi Nasional &middot; Tim REKAGIZI</p>
                <h1 class="font-display text-page-title text-ink mt-xs">Masuk ke Sistem</h1>
                <p class="font-body text-body-md text-ink-muted mt-xs">Gunakan username dan kata sandi akun REKAGIZI Anda.</p>
            </div>
        </div>

        {{-- Kebijakan Keamanan dari system_settings (bukan hardcode) --}}
        <div class="rounded-lg bg-surface-subtle border border-border-soft p-md flex flex-col gap-xs font-body text-micro text-ink-muted">
            <div class="flex items-center gap-xs text-ink">
                <span class="material-symbols-outlined text-card-title text-success" aria-hidden="true">verified_user</span>
                <span>Batas sesi otomatis {{ $sessionTimeout ?? 30 }} menit. Akun dikunci sementara setelah {{ $maxFailedAttempts ?? 5 }} kali gagal masuk.</span>
            </div>
            <div class="flex items-center gap-xs">
                <span class="material-symbols-outlined text-card-title text-ink-muted" aria-hidden="true">history</span>
                <span>Seluruh aktivitas masuk dan pengaturan ulang kata sandi tercatat di Log Aktivitas.</span>
            </div>
        </div>

        {{-- 4 Keadaan Pesan Status Masuk --}}
        @php
            $errors ??= new \Illuminate\Support\ViewErrorBag();
            $statusType = session('auth_status_type');
        @endphp

        @if(session('success'))
            <div role="status" class="rounded-lg bg-success-soft border border-border-soft p-md flex items-center gap-sm text-on-success-soft font-body text-label-sm">
                <span class="material-symbols-outlined text-card-title text-success shrink-0" aria-hidden="true">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($statusType === 'session_expired' || $statusType === 'locked')
            <div role="alert" class="rounded-lg bg-alert-soft border border-border-soft p-md flex items-center gap-sm text-on-alert-soft font-body text-label-sm">
                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">{{ $statusType === 'session_expired' ? 'schedule' : 'lock' }}</span>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @elseif($statusType === 'inactive')
            <div role="alert" class="rounded-lg bg-danger-soft border border-border-soft p-md flex items-center gap-sm text-on-danger-soft font-body text-label-sm">
                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">block</span>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @elseif($errors->any() || session('error'))
            <div role="alert" class="rounded-lg bg-danger-soft border border-border-soft p-md flex items-center gap-sm text-on-danger-soft font-body text-label-sm">
                <span class="material-symbols-outlined text-card-title shrink-0" aria-hidden="true">error</span>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Form Login (tanpa kotak Pertahankan sesi / pendaftaran mandiri) --}}
        <form method="POST" action="{{ route('login.post') }}" class="flex flex-col gap-md">
            @csrf

            <div class="flex flex-col gap-xs">
                <label for="login-username" class="font-body text-label-sm text-ink">Username</label>
                <input
                    id="login-username"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Contoh: arief.mahendra"
                    class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink placeholder:text-ink-muted focus:border-obsidian focus:ring-0"
                >
            </div>

            <div class="flex flex-col gap-xs">
                <div class="flex items-center justify-between gap-sm">
                    <label for="login-password" class="font-body text-label-sm text-ink">Kata sandi</label>
                    <button type="button" @click="forgotOpen = true" class="font-body text-micro text-ink-muted hover:text-ink underline underline-offset-4 cursor-pointer">
                        Lupa kata sandi?
                    </button>
                </div>
                <div class="flex items-center rounded border border-border-soft bg-surface-subtle focus-within:border-obsidian">
                    <input
                        id="login-password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="flex-1 min-w-0 bg-transparent border-0 px-md py-sm font-body text-body-md text-ink placeholder:text-ink-muted focus:ring-0 focus:outline-none"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        aria-label="Tampilkan atau sembunyikan kata sandi"
                        class="px-md py-sm shrink-0 text-ink-muted hover:text-ink cursor-pointer inline-flex items-center justify-center"
                    >
                        <span class="material-symbols-outlined text-card-title" aria-hidden="true" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-xs px-lg py-md rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 transition-opacity cursor-pointer">
                <span>Masuk</span>
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">arrow_forward</span>
            </button>
        </form>

        <footer class="pt-md border-t border-border-soft text-center font-body text-micro text-ink-muted">
            Akun dikelola oleh Administrator Sistem SPPG Sungai Selayur 1.
        </footer>

        {{-- Panel Permintaan Reset Kata Sandi (Lupa Sandi) --}}
        <x-slide-panel open-var="forgotOpen" title="Reset Kata Sandi" subtitle="Butuh verifikasi Administrator Sistem">
            <p class="font-body text-body-md text-ink-muted">
                Ajukan pengaturan ulang kata sandi sementara melalui Administrator Sistem. Kata sandi sementara berlaku 24 jam dan wajib diganti saat login pertama.
            </p>

            <form method="POST" action="{{ route('password.forgot') }}" class="flex flex-col gap-md">
                @csrf
                <div class="flex flex-col gap-xs">
                    <label for="forgot-identifier" class="font-body text-label-sm text-ink">Username terdaftar</label>
                    <input id="forgot-identifier" type="text" name="identifier" required placeholder="Masukkan username Anda"
                           class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                </div>

                <div class="flex flex-col gap-xs">
                    <label for="forgot-reason" class="font-body text-label-sm text-ink">Alasan permintaan</label>
                    <select id="forgot-reason" name="reason" required class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
                        <option value="Lupa kata sandi">Lupa kata sandi</option>
                        <option value="Ganti perangkat">Ganti perangkat</option>
                        <option value="Akun terkunci">Akun terkunci</option>
                    </select>
                </div>

                <div class="rounded-lg bg-surface-subtle border border-border-soft p-md flex items-start gap-sm font-body text-micro text-ink-muted">
                    <span class="material-symbols-outlined text-card-title text-ink shrink-0" aria-hidden="true">info</span>
                    <span>Permintaan ini dicatat di Log Aktivitas.</span>
                </div>

                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <button type="button" @click="forgotOpen = false" class="px-lg py-sm rounded-full bg-surface border border-border-soft text-ink font-body text-label-sm hover:bg-surface-subtle cursor-pointer">Batal</button>
                    <button type="submit" class="px-lg py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">Kirim Permintaan Reset</button>
                </div>
            </form>
        </x-slide-panel>
    </div>
@endcomponent
