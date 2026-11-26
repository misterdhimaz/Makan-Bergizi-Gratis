@props([
    'label',
    'value',
    'badge' => null,
    'badgeVariant' => 'neutral',
    'hint' => null,
    'highlight' => false,
    'icon' => null,
])

<article {{ $attributes->merge([
    'class' => ($highlight ? 'bg-lime-accent text-on-lime' : 'bg-surface text-ink border border-border-soft') . ' rounded-lg p-lg flex flex-col justify-between gap-sm'
]) }}>
    <div class="flex items-center justify-between gap-sm">
        <span class="font-body text-label-sm {{ $highlight ? 'text-on-lime font-medium' : 'text-ink-muted' }}">{{ $label }}</span>
        @if($badge !== null)
            <x-badge-status :variant="$badgeVariant" :label="$badge" />
        @elseif($icon)
            <span class="material-symbols-outlined text-card-title {{ $highlight ? 'text-on-lime' : 'text-ink-muted' }}" aria-hidden="true">{{ $icon }}</span>
        @endif
    </div>

    <div class="flex flex-wrap items-baseline justify-between gap-xs">
        <span class="font-display text-kpi tabular-nums">{{ $value }}</span>
        @if($hint)
            <span class="font-body text-micro {{ $highlight ? 'text-on-lime' : 'text-ink-muted' }}">{{ $hint }}</span>
        @endif
    </div>
</article>
