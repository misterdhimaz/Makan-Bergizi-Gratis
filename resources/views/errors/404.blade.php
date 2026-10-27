@extends('errors.layout')

@section('title', '404 — Halaman Tidak Ditemukan')

@section('content')
<div class="w-16 h-16 rounded-2xl bg-teal-500/20 text-teal-300 border border-teal-400/30 flex items-center justify-center mx-auto mb-5">
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
</div>

<span class="font-mono text-xs font-black tracking-widest text-teal-400 uppercase">Kode Status: 404 Not Found</span>
<h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1 mb-2">Halaman Tidak Ditemukan</h1>

<p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-sm mx-auto mb-6">
    Halaman, data formulir, atau rute yang Anda tuju tidak tersedia atau telah dipindahkan ke modul operasional lainnya.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition">
        Kembali ke Dashboard Utama
    </a>
</div>
@endsection

