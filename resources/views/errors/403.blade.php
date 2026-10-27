@extends('errors.layout')

@section('title', '403 — Akses Dibatasi')

@section('content')
<div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-400/30 flex items-center justify-center mx-auto mb-5">
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
</div>

<span class="font-mono text-xs font-black tracking-widest text-amber-400 uppercase">Kode Status: 403 Forbidden</span>
<h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1 mb-2">Akses Dibatasi</h1>

<p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-sm mx-auto mb-6">
    Hak akses peran akun Anda saat ini tidak memiliki otorisasi untuk membuka modul atau menjalankan tindakan ini sesuai dengan pembagian wewenang SOP BGN.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition">
        &larr; Kembali
    </a>
    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition">
        Buka Dashboard
    </a>
</div>
@endsection

