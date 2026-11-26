@props([
    'label',
    'variant' => 'neutral',
    'dot' => false,
])
@php
    $classes = match ($variant) {
        'success' => 'bg-success-soft text-on-success-soft',
        'alert', 'warning' => 'bg-alert-soft text-on-alert-soft',
        'danger' => 'bg-danger-soft text-on-danger-soft',
        'lime' => 'bg-lime-accent text-on-lime',
        'obsidian' => 'bg-obsidian text-on-obsidian',
        default => 'bg-surface-subtle border border-border-soft text-ink-muted',
    };
    $dotColor = match ($variant) {
        'success' => 'bg-success',
        'alert', 'warning' => 'bg-on-alert-soft',
        'danger' => 'bg-on-danger-soft',
        'lime', 'obsidian' => 'bg-current',
        default => 'bg-ink-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-xs px-sm py-xs rounded-full font-body text-micro shrink-0 {$classes}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}" aria-hidden="true"></span>
    @endif
    {{ $label }}
</span>
