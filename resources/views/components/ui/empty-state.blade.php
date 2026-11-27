@props(['title' => 'Belum ada data', 'description' => null])

<div {{ $attributes->class(['ui-empty-state px-2 py-6 text-center text-sm']) }}>
    <p class="text-sm font-semibold text-brand-navy">{{ $title }}</p>
    @if ($description !== null)
        <p class="mt-1 text-xs text-slate-600">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4 flex justify-center">{{ $action }}</div>
    @endisset
</div>
