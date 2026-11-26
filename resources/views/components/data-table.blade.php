@props([
    'headers' => [],
])

<div class="w-full">
    {{-- Tabel Desktop --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            @if(!empty($headers))
                <thead>
                    <tr class="border-b border-border-soft font-body text-micro text-ink-muted">
                        @foreach($headers as $header)
                            <th class="px-md py-sm font-medium {{ is_array($header) ? ($header['class'] ?? '') : '' }}">
                                {{ is_array($header) ? ($header['label'] ?? '') : $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-border-soft font-body text-body-md">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Daftar Kartu Mobile --}}
    <div class="md:hidden flex flex-col gap-sm">
        {{ $mobile ?? $slot }}
    </div>
</div>
