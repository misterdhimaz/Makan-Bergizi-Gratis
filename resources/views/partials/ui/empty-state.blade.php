@php
    $title = $title ?? 'Belum ada data';
    $description = $description ?? null;
@endphp
<div class="py-6 px-2 text-center">
    <p class="text-sm font-bold text-brand-navy">{{ $title }}</p>
    @if($description)
        <p class="text-xs text-slate-500 mt-1">{{ $description }}</p>
    @endif
</div>
