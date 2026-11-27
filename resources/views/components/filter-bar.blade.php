@props([
    'action',
    'method' => 'GET',
])

<section aria-label="Filter data" class="bg-surface rounded-xl p-lg border border-border-soft">
    <form method="{{ $method }}" action="{{ $action }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-md items-end">
        {{ $slot }}
    </form>
</section>
