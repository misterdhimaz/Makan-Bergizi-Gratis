@extends('errors.layout')

@section('title', '419 — Sesi Kedaluwarsa')

@section('content')
<div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-400/30 flex items-center justify-center mx-auto mb-5">
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
</div>

<span class="font-mono text-xs font-black tracking-widest text-amber-400 uppercase">Kode Status: 419 Page Expired</span>
<h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1 mb-2">Sesi Formulir Kedaluwarsa</h1>

<p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-sm mx-auto mb-6">
    Demi menjaga keamanan data operasional SPPG, token sesi keamanan CSRF Anda telah kedaluwarsa karena tidak ada aktivitas dalam waktu tertentu.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <button onclick="window.location.reload()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition cursor-pointer">
        Muat Ulang Halaman
    </button>
    <a href="{{ route('login') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition">
        Masuk Ulang ke Sistem
    </a>
</div>
@endsection

