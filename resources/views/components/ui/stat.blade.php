@props(['label', 'value' => null, 'unit' => null, 'hint' => null, 'tone' => null])

@php
    $toneClasses = match ($tone) {
        'success' => 'text-status-success',
        'warning' => 'text-status-warning',
        'danger' => 'text-status-danger',
        'info' => 'text-status-info',
        default => 'text-brand-navy',
    };
@endphp

<dl {{ $attributes->class(['ui-stat min-w-0 rounded-xl border border-slate-200 bg-white p-5 text-sm']) }}>
    <dt class="font-medium text-slate-600">{{ $label }}</dt>
    <dd class="mt-2 flex flex-wrap items-baseline gap-2">
        <span @class(['text-3xl font-semibold tabular-nums', $toneClasses])>{{ $value ?? '—' }}</span>
        @if ($value !== null && $unit !== null)
            <span class="text-sm text-slate-600">{{ $unit }}</span>
        @endif
    </dd>
    @if ($hint !== null)
        <dd class="mt-2 text-xs text-slate-600">{{ $hint }}</dd>
    @endif
</dl>
