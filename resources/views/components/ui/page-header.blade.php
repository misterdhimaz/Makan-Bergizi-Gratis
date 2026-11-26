@props(['title', 'subtitle' => null])

<header {{ $attributes->class(['ui-page-header flex flex-col gap-4 text-sm sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="text-xl font-semibold text-brand-navy">{{ $title }}</h1>
        @if ($subtitle !== null)
            <p class="mt-1 text-sm text-slate-600">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
