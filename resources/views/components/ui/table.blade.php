@props(['caption' => 'Daftar data', 'columns' => [], 'label' => null])

<div role="region" aria-label="{{ $label ?? trim(strip_tags((string) $caption)) }}" tabindex="0" {{ $attributes->except(['role', 'aria-label', 'tabindex'])->class(['ui-table min-w-0 max-w-full overflow-x-auto rounded-xl border border-slate-200 bg-white text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy']) }}>
    <table class="w-full border-collapse text-left text-sm text-brand-navy">
        <caption class="px-4 py-3 text-left text-base font-semibold">{{ $caption }}</caption>
        <thead class="border-b border-slate-200 bg-slate-50 text-xs text-slate-600">
            <tr>
                @if ($columns instanceof \Illuminate\View\ComponentSlot)
                    {{ $columns }}
                @else
                    @foreach ($columns as $column)
                        <th scope="col" class="px-4 py-3 font-semibold">{{ $column }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">{{ $rows ?? $slot }}</tbody>
    </table>
</div>
