@extends('errors.layout')

@section('title', '500 — Kesalahan Server Internal')

@section('content')
<div class="w-16 h-16 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-400/30 flex items-center justify-center mx-auto mb-5">
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
</div>

<span class="font-mono text-xs font-black tracking-widest text-rose-400 uppercase">Kode Status: 500 Server Error</span>
<h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1 mb-2">Terjadi Kendala Sistem</h1>

<p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-sm mx-auto mb-6">
    Sistem mendeteksi kendala pemrosesan pada peladen. Tim administrator telah menerima laporan log dan sedang memverifikasi integritas basis data.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition">
        Kembali ke Dashboard Utama
    </a>
</div>
@endsection

