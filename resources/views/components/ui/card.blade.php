@props(['title' => null])

<section {{ $attributes->class(['ui-card min-w-0 rounded-xl border border-slate-200 bg-white p-5 text-sm text-brand-navy']) }}>
    @if ($title !== null || isset($actions))
        <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
            @if ($title !== null)
                <h2 class="text-base font-semibold">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif
    {{ $slot }}
</section>
