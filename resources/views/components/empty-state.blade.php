@props([
    'title' => 'Belum ada data',
    'description' => 'Data yang ditambahkan akan tampil di sini.',
    'icon' => 'inbox',
])

<div {{ $attributes->merge(['class' => 'rounded-lg bg-surface-subtle border border-dashed border-border-soft p-xl flex flex-col items-center justify-center text-center gap-xs']) }}>
    <span class="material-symbols-outlined text-page-title text-ink-muted" aria-hidden="true">{{ $icon }}</span>
    <p class="font-body text-card-title text-ink">{{ $title }}</p>
    <p class="font-body text-micro text-ink-muted">{{ $description }}</p>
    {{ $slot ?? '' }}
</div>
