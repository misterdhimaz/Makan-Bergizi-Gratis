@props([
    'openVar' => 'panelOpen',
    'open' => null,
    'title' => 'Formulir',
    'subtitle' => null,
])
@php
    $openVar = $open ?: $openVar;
@endphp

<template x-teleport="body">
    <div
        x-show="{{ $openVar }}"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-md bg-obsidian/60 backdrop-blur-sm"
    >
        <div
            @click.outside="{{ $openVar }} = false"
            @keydown.escape.window="{{ $openVar }} = false"
            role="dialog"
            aria-modal="true"
            class="w-full max-w-lg max-h-[90vh] bg-surface rounded-xl border border-border-soft p-xl shadow-2xl flex flex-col gap-md overflow-y-auto"
        >
            <div class="flex items-start justify-between gap-md pb-md border-b border-border-soft">
                <div class="flex flex-col gap-xs min-w-0">
                    @if($subtitle)
                        <span class="font-body text-micro text-ink-muted">{{ $subtitle }}</span>
                    @endif
                    <h3 class="font-display text-section-title text-ink truncate">{{ $title }}</h3>
                </div>
                <button type="button" @click="{{ $openVar }} = false" aria-label="Tutup panel" class="w-8 h-8 rounded-full bg-surface-subtle border border-border-soft flex items-center justify-center text-ink-muted hover:text-ink cursor-pointer shrink-0">
                    <span class="material-symbols-outlined text-card-title" aria-hidden="true">close</span>
                </button>
            </div>

            <div class="flex-1 flex flex-col gap-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</template>
