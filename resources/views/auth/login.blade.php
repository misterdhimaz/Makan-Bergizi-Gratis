<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk — {{ $branding['login_title'] ?? 'SIM-MBG SPPG Sungai Selayur 1' }}</title>

    @include('partials.theme-head')

    <!-- Alpine.js & GSAP -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

    @php
        $overlay = max(20, min(95, (int) ($branding['login_overlay'] ?? 75))) / 100;
    @endphp

    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: #030712;
            font-family: 'Urbanist', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #F8FAFC;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Foto background SPPG jika diunggah */
        .login-photo {
            position: fixed;
            inset: 0;
            background-size: cover;
            background-position: center;
            transform: scale(1.02);
            will-change: transform;
            transition: transform 3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .login-overlay {
            position: fixed;
            inset: 0;
            background:
                linear-gradient(145deg, rgba(3, 7, 18, {{ $overlay * 0.90 }}) 0%, rgba(6, 19, 48, {{ $overlay }}) 60%, rgba(3, 7, 18, {{ $overlay * 0.96 }}) 100%),
                radial-gradient(circle at 50% 30%, rgba(16, 185, 129, 0.12) 0%, transparent 65%);
        }

        /* Latar aurora default */
        .aurora-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(160deg, #020617 0%, #061A3D 50%, #020617 100%);
        }

        /* Grid arsitektural halus */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-size: 44px 44px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(ellipse at 50% 50%, black 40%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at 50% 50%, black 40%, transparent 80%);
            pointer-events: none;
        }

        /* Ambient Glowing Liquid Orbs */
        .ambient-orb-1 {
            position: fixed;
            top: 15%;
            left: 20%;
            width: 32rem;
            height: 32rem;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(16, 185, 129, 0) 70%);
            filter: blur(60px);
            pointer-events: none;
            animation: driftOrb1 16s ease-in-out infinite alternate;
        }

        .ambient-orb-2 {
            position: fixed;
            bottom: 12%;
            right: 20%;
            width: 30rem;
            height: 30rem;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.14) 0%, rgba(217, 119, 6, 0) 70%);
            filter: blur(60px);
            pointer-events: none;
            animation: driftOrb2 18s ease-in-out infinite alternate;
        }

        .ambient-orb-3 {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 26rem;
            height: 26rem;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.10) 0%, rgba(56, 189, 248, 0) 70%);
            filter: blur(50px);
            pointer-events: none;
            animation: pulseCore 8s ease-in-out infinite alternate;
        }

        @keyframes driftOrb1 {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, -40px) scale(1.15); }
            100% { transform: translate(-40px, 50px) scale(0.95); }
        }

        @keyframes driftOrb2 {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-50px, 40px) scale(1.12); }
            100% { transform: translate(40px, -60px) scale(0.92); }
        }

        @keyframes pulseCore {
            0% { transform: translate(-50%, -50%) scale(0.9); opacity: 0.4; }
            100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.8; }
        }

        /* Kartu Glassmorphism Tunggal (Single Luxury Card) */
        .executive-card {
            background: linear-gradient(165deg, rgba(8, 22, 53, 0.82) 0%, rgba(4, 13, 33, 0.92) 100%);
            backdrop-filter: blur(36px) saturate(190%);
            -webkit-backdrop-filter: blur(36px) saturate(190%);
            border: 1px solid rgba(181, 224, 234, 0.16);
            box-shadow:
                0 32px 80px -20px rgba(0, 0, 0, 0.85),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 1px 1px 0 rgba(255, 255, 255, 0.15);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease, border-color 0.4s ease;
        }

        .executive-card:hover {
            border-color: rgba(16, 185, 129, 0.30);
            box-shadow:
                0 40px 95px -20px rgba(0, 0, 0, 0.95),
                0 0 40px -10px rgba(16, 185, 129, 0.18),
                inset 0 1px 1px 0 rgba(255, 255, 255, 0.22);
            transform: translateY(-2px);
        }

        /* Animated Glowing Border Halo */
        .card-halo {
            position: absolute;
            inset: -1px;
            border-radius: 32px;
            padding: 1px;
            background: linear-gradient(115deg, rgba(16, 185, 129, 0.5), transparent 40%, rgba(217, 119, 6, 0.4) 70%, rgba(56, 189, 248, 0.5));
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: 0.8;
            transition: opacity 0.4s ease;
        }
        .executive-card:hover .card-halo {
            opacity: 1;
        }

        /* Input Glass */
        .input-glass {
            background: rgba(2, 8, 23, 0.72);
            border: 1px solid rgba(181, 224, 234, 0.16);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .input-glass:focus {
            background: rgba(2, 8, 23, 0.94);
            border-color: #10B981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.16), 0 4px 18px -2px rgba(16, 185, 129, 0.18);
            outline: none;
        }

        /* Tombol Masuk dengan Efek Gelombang Cahaya */
        .btn-portal {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #10B981 0%, #059669 60%, #047857 100%);
            box-shadow: 0 12px 28px -6px rgba(16, 185, 129, 0.45);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-portal::after {
            content: '';
            position: absolute;
            top: 0;
            left: -120%;
            width: 70%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-20deg);
            animation: lightSweep 4s ease-in-out infinite;
        }
        @keyframes lightSweep {
            0%, 40% { left: -120%; }
            70%, 100% { left: 160%; }
        }
        .btn-portal:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px -6px rgba(16, 185, 129, 0.65);
            filter: brightness(1.06);
        }
        .btn-portal:active {
            transform: translateY(1px);
        }

        /* Aura Lingkaran Logo */
        @keyframes logoAura {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.12); opacity: 0.9; }
        }
        .animate-logo-aura {
            animation: logoAura 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 sm:p-6 lg:p-8 selection:bg-emerald-500 selection:text-white">

    {{-- ====== LAPISAN LATAR BELAKANG ====== --}}
    @if($backgroundUrl)
        <div class="login-photo" style="background-image: url('{{ $backgroundUrl }}');"></div>
        <div class="login-overlay"></div>
    @else
        <div class="aurora-bg"></div>
        <div class="grid-pattern"></div>
        <div class="ambient-orb-1"></div>
        <div class="ambient-orb-2"></div>
        <div class="ambient-orb-3"></div>
    @endif

    {{-- ====== SATU KARTU LOGIN EKSKLUSIF (SINGLE CENTERED CARD) ====== --}}
    <main
        x-data="{
            showPassword: false,
            capsLockOn: false,
            isSubmitting: false,
            emailFocused: false,
            passwordFocused: false
        }"
        id="loginCardWrapper"
        class="relative z-10 w-full max-w-[450px] my-auto py-4"
        style="opacity: 0; transform: translateY(24px) scale(0.97); filter: blur(10px);"
    >
        <div class="executive-card relative rounded-[32px] p-7 sm:p-9">
            <div class="card-halo"></div>

            {{-- HEADER IDENTITAS SPPG / BADAN GIZI NASIONAL --}}
            <div class="text-center mb-6">
                <!-- Emblem Logo Mewah dengan Pendaran Aura Lembut -->
                <div class="relative inline-flex mb-3.5">
                    <div class="absolute -inset-2.5 rounded-3xl bg-gradient-to-r from-emerald-500/35 via-teal-400/25 to-amber-400/30 blur-md animate-logo-aura"></div>

                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo SPPG" class="relative h-14 w-auto max-w-[170px] object-contain drop-shadow-xl p-1 bg-slate-950/60 rounded-2xl border border-white/20">
                    @else
                        <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-950 via-[#071E49] to-emerald-950 flex items-center justify-center border border-emerald-400/40 shadow-xl">
                            <svg class="w-7 h-7 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="M9 12l2 2 4-4" stroke-width="2.5" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Badge Kemitraan Resmi BGN -->
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-300">Badan Gizi Nasional</span>
                    </div>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-300">
                        SPPG 1
                    </span>
                </div>

                <!-- Judul Utama Sistem -->
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white leading-snug">
                    {{ $branding['login_title'] ?? 'SIM-MBG SPPG Selayur 1' }}
                </h1>
                <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto leading-relaxed">
                    {{ $branding['login_tagline'] ?? 'Portal Komando Operasional MBG (1.414 Porsi)' }}
                </p>
            </div>

            {{-- FEEDBACK / NOTIFIKASI ERROR ATAU SUKSES --}}
            @if(session('success'))
                <div class="mb-4 p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-100 text-xs font-semibold flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mb-4 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-400/30 text-rose-100 text-xs font-semibold flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <div class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- FORMULIR LOGIN BERSIH & AMAN (TANPA AKSES CEPAT) --}}
            <form method="POST" action="{{ route('login.post') }}" @submit="isSubmitting = true" class="space-y-4" autocomplete="on">
                @csrf

                <!-- Field Email -->
                <div>
                    <label for="email" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Email Terdaftar</span>
                        <span x-show="emailFocused" x-transition class="text-[10px] font-mono text-emerald-400">Aktif</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors" :class="emailFocused ? 'text-emerald-400' : 'text-slate-500'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
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
                            @focus="emailFocused = true"
                            @blur="emailFocused = false"
                            class="input-glass w-full pl-10 pr-4 py-3 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500"
                        >
                    </div>
                </div>

                <!-- Field Kata Sandi -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-300">Kata Sandi</label>
                        <span x-show="capsLockOn" x-cloak class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-300 bg-amber-500/20 px-2 py-0.5 rounded-full border border-amber-400/30 animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Caps Lock Aktif
                        </span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors" :class="passwordFocused ? 'text-emerald-400' : 'text-slate-500'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            maxlength="255"
                            placeholder="Masukkan kata sandi akun..."
                            @focus="passwordFocused = true"
                            @blur="passwordFocused = false"
                            @keyup="capsLockOn = $event.getModifierState && $event.getModifierState('CapsLock')"
                            class="input-glass w-full pl-10 pr-11 py-3 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-400 transition-colors cursor-pointer"
                        >
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Opsi Tambahan: Ingat Sesi & Bantuan Sandi -->
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-300 cursor-pointer select-none group">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-400/30 cursor-pointer">
                        <span class="group-hover:text-white transition-colors">Ingat sesi saya</span>
                    </label>
                    <span class="text-[11px] font-medium text-slate-400 hover:text-amber-300 transition-colors cursor-help" title="Untuk pengaturan ulang kata sandi, hubungi Administrator Sistem SPPG">
                        Lupa kata sandi?
                    </span>
                </div>

                <!-- Tombol Masuk Utama -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="btn-portal group w-full py-3.5 rounded-2xl text-white font-black text-xs sm:text-sm tracking-wide flex items-center justify-center gap-2.5 cursor-pointer disabled:opacity-60 disabled:cursor-wait select-none"
                    >
                        <svg x-show="isSubmitting" x-cloak class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Memverifikasi Akses...' : 'Masuk ke Sistem Komando'"></span>
                        <svg x-show="!isSubmitting" class="w-4 h-4 text-white group-hover:translate-x-1.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </form>

            {{-- FOOTER KEAMANAN STANDAR PRODUKSI --}}
            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-mono text-[10px] text-emerald-300/90 font-semibold">TLS 256-Bit</span>
                </div>
                <div class="flex items-center gap-1 text-[10px] text-slate-400">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Otoritas SPPG Sungai Selayur 1</span>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <p class="text-center text-[10px] sm:text-[11px] text-slate-400/80 mt-4 font-medium">
            &copy; {{ date('Y') }} SPPG Sungai Selayur 1 &middot; Satuan Pelayanan Pemenuhan Gizi
        </p>
    </main>

    {{-- ====== SKRIP ANIMASI ELEGAN (SMOOTH FOCUS & REVEAL) ====== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('loginCardWrapper');
            if (card) {
                gsap.to(card, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    filter: 'blur(0px)',
                    duration: 1.1,
                    ease: 'power4.out',
                    clearProps: 'filter'
                });
            }
        });
    </script>
</body>
</html>
