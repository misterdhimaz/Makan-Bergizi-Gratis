@props(['tone' => 'neutral', 'label' => null])

@php
    $tone = in_array($tone, ['success', 'warning', 'danger', 'info', 'neutral'], true) ? $tone : 'neutral';
    $toneClasses = match ($tone) {
        'success' => 'status-badge--success border-green-200 bg-green-100 text-status-success',
        'warning' => 'status-badge--warning border-amber-200 bg-amber-100 text-status-warning',
        'danger' => 'status-badge--danger border-red-200 bg-red-100 text-status-danger',
        'info' => 'status-badge--info border-blue-200 bg-blue-100 text-status-info',
        default => 'status-badge--neutral border-slate-200 bg-slate-100 text-slate-600',
    };
@endphp

<span {{ $attributes->class(['ui-badge status-badge inline-flex items-center rounded-lg border px-2 py-1 text-xs font-semibold', $toneClasses]) }}>{{ $label ?? $slot }}</span>
