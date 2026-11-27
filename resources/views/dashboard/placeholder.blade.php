@component('layouts.auth', ['title' => 'Dashboard Belum Tersedia'])
    <div class="bg-surface rounded-xl border border-border-soft p-2xl shadow-sm flex flex-col items-center text-center gap-md">
        <img src="{{ asset('images/logo-bgn.png') }}" alt="Logo Badan Gizi Nasional" class="w-14 h-14 rounded-full object-contain">
        <div class="flex flex-col gap-xs">
            <p class="font-mono text-micro text-ink-muted uppercase tracking-wider">
                {{ auth()->user()->role->label() }}
            </p>
            <h1 class="font-display text-section-title text-ink">Dashboard untuk peran ini belum tersedia</h1>
            <p class="font-body text-body-md text-ink-muted">
                Halo {{ auth()->user()->name }}, modul kerja untuk peran <span class="font-semibold text-ink">{{ auth()->user()->role->label() }}</span> sedang dalam tahap pengembangan.
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-sm">
            @csrf
            <button type="submit" class="inline-flex items-center gap-xs px-xl py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold hover:opacity-90 cursor-pointer">
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">logout</span>
                Keluar dari Sistem
            </button>
        </form>
    </div>
@endcomponent
