<!DOCTYPE html>
<html lang="id" data-theme="light-only" data-theme-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk — {{ $branding['login_title'] ?? 'SIM-MBG SPPG Sungai Selayur 1' }}</title>
    @include('partials.theme-head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background-color: #F5F7FA;
            position: relative;
        }
        .login-photo {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0.18;
        }
        .login-batik {
            position: absolute;
            inset: 0;
            opacity: 0.04;
            pointer-events: none;
            background-image:
                radial-gradient(circle at 20% 30%, #061F47 1.5px, transparent 1.6px),
                radial-gradient(circle at 70% 60%, #C8A96A 1.2px, transparent 1.3px);
            background-size: 28px 28px, 36px 36px;
        }
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(6, 31, 71, 0.06), 0 8px 24px rgba(6, 31, 71, 0.05);
            padding: 1.75rem 1.5rem;
        }
        @media (min-width: 640px) {
            .login-card { padding: 2rem 1.75rem; }
        }
    </style>
</head>
<body class="antialiased selection:bg-brand-gold selection:text-brand-navy">
    <div class="login-shell">
        @if(!empty($backgroundUrl))
            <div class="login-photo" style="background-image: url('{{ $backgroundUrl }}');"></div>
        @endif
        <div class="login-batik" aria-hidden="true"></div>

        <main
            class="login-card"
            x-data="{ showPassword: false, capsLockOn: false, isSubmitting: false }"
        >
            <div class="text-center mb-6">
                <div class="inline-flex mb-3">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="Lambang Badan Gizi Nasional Republik Indonesia" class="h-16 w-16 rounded-full bg-white object-contain">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-brand-navy text-white flex items-center justify-center" aria-hidden="true">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                            </svg>
                        </div>
                    @endif
                </div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">SPPG Sungai Selayur 1</p>
                <h1 class="text-xl sm:text-2xl font-extrabold text-brand-navy leading-snug">
                    {{ $branding['login_title'] ?? 'SIM-MBG SPPG Sungai Selayur 1' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">
                    {{ $branding['login_tagline'] ?? 'Sistem Informasi Monitoring Program Makan Bergizi Gratis' }}
                </p>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-semibold flex gap-2" role="status">
                    <svg class="w-5 h-5 shrink-0 text-status-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex gap-2" role="alert">
                    <svg class="w-5 h-5 shrink-0 text-status-danger mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <div class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" @submit="isSubmitting = true" class="space-y-4" autocomplete="on">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-brand-navy mb-1.5">Email terdaftar</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        maxlength="255"
                        placeholder="nama@mbg-selayur1.id"
                        class="form-control"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-brand-navy">Kata sandi</label>
                        <span x-show="capsLockOn" x-cloak role="status" aria-live="polite" aria-atomic="true" class="status-badge status-badge--warning">Caps Lock aktif</span>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            maxlength="255"
                            placeholder="Masukkan kata sandi"
                            @keyup="capsLockOn = $event.getModifierState && $event.getModifierState('CapsLock')"
                            class="form-control pr-12"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                            aria-controls="password"
                            :aria-pressed="showPassword"
                            class="absolute inset-y-0 right-0 px-3 min-w-touch flex items-center text-slate-500 hover:text-brand-navy"
                        >
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex min-h-touch items-center gap-2 text-sm font-medium text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-navy focus:ring-brand-navy">
                        <span>Ingat sesi saya</span>
                    </label>
                    <span class="text-xs text-slate-500" title="Hubungi Administrator SPPG untuk reset kata sandi">Lupa kata sandi?</span>
                </div>

                <button type="submit" :disabled="isSubmitting" class="btn-primary w-full mt-2">
                    <svg x-show="isSubmitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <span x-text="isSubmitting ? 'Memverifikasi…' : 'Masuk ke SIM-MBG'"></span>
                </button>
            </form>

            <p class="mt-6 pt-4 border-t border-slate-200 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} SPPG Sungai Selayur 1 · Satuan Pelayanan Pemenuhan Gizi
            </p>
        </main>
    </div>
</body>
</html>
