@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'disabled' => false])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border-slate-300 bg-white text-brand-navy hover:bg-slate-50',
        'danger' => 'border-status-danger bg-status-danger text-white hover:bg-red-800',
        default => 'border-brand-navy bg-brand-navy text-white hover:bg-brand-navy/90',
    };
    $classes = 'ui-button inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy '.$variantClasses;
@endphp

@if ($href !== null)
    @if ($disabled)
        <a role="link" aria-disabled="true" tabindex="-1" inert {{ $attributes->except(['href', ':href', 'x-bind:href', 'role', 'aria-disabled', 'tabindex'])->class([$classes, 'pointer-events-none cursor-not-allowed opacity-50']) }}>{{ $slot }}</a>
    @else
        <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
    @endif
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->class([$classes, 'disabled:cursor-not-allowed disabled:opacity-50']) }}>{{ $slot }}</button>
@endif
