@props([
    'openVar' => 'confirmOpen',
    'open' => null,
    'title' => 'Konfirmasi Tindakan',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'variant' => 'alert',
])
@php
    $openVar = $open ?: $openVar;
@endphp

<template x-teleport="body">
    <div
        x-show="{{ $openVar }}"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-lg bg-obsidian/60 backdrop-blur-sm"
    >
        <div
            @click.outside="{{ $openVar }} = false"
            @keydown.escape.window="{{ $openVar }} = false"
            role="dialog"
            aria-modal="true"
            class="bg-surface rounded-xl border border-border-soft max-w-md w-full p-xl shadow-2xl flex flex-col gap-md"
        >
            <div class="flex items-start justify-between gap-md">
                <div class="flex items-center gap-sm">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $variant === 'danger' ? 'bg-danger-soft text-on-danger-soft' : 'bg-alert-soft text-on-alert-soft' }}">
                        <span class="material-symbols-outlined text-section-title" aria-hidden="true">warning</span>
                    </div>
                    <h3 class="font-display text-section-title text-ink">{{ $title }}</h3>
                </div>
                <button type="button" @click="{{ $openVar }} = false" aria-label="Tutup dialog" class="p-xs rounded-full text-ink-muted hover:text-ink cursor-pointer">
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">close</span>
                </button>
            </div>

            <p class="font-body text-body-md text-ink-muted">{{ $message }}</p>

            {{ $slot }}

            <div class="flex justify-end gap-sm pt-sm border-t border-border-soft">
                <button type="button" @click="{{ $openVar }} = false" class="px-lg py-sm rounded-full bg-surface border border-border-soft text-ink font-body text-label-sm hover:bg-surface-subtle cursor-pointer">
                    {{ $cancelText }}
                </button>
                {{ $actions ?? '' }}
            </div>
        </div>
    </div>
</template>
