<x-layouts.aslap title="Jadwal Distribusi Harian" :complaintBadge="$complaintBadge">
    <section class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col sm:flex-row sm:items-center justify-between gap-md">
        <form method="GET" action="{{ route('aslap.distribusi.index') }}" class="flex items-center gap-sm">
            <input type="date" name="tanggal" value="{{ $selectedDate }}" class="min-h-[44px] rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
            <button type="submit" class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">Tampilkan</button>
        </form>

        <form method="POST" action="{{ route('aslap.distribusi.store') }}" class="flex items-center gap-sm">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $selectedDate }}">
            <button type="submit" class="min-h-[44px] px-lg rounded-full bg-surface-subtle border border-border-soft text-ink font-body text-label-sm font-semibold cursor-pointer">
                Buat Rute dari Menu Disetujui
            </button>
        </form>
    </section>

    <section class="flex flex-col gap-md">
        @forelse($routes as $route)
            @php $isEditable = $route->status === \App\Enums\DeliveryStatus::DISIAPKAN; @endphp
            <article class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                    <div>
                        <span class="font-mono text-micro text-ink-muted">{{ $route->waybill_number }} · {{ $route->kelompok_rute ?? 'Rute' }}</span>
                        <h2 class="font-display text-section-title text-ink">{{ $route->location?->name }}</h2>
                        <p class="font-body text-micro text-ink-muted">
                            Target Porsi: <strong class="font-mono tabular-nums">{{ number_format($route->dispatched_portions, 0, ',', '.') }}</strong>
                            · Berangkat: <strong class="font-mono">{{ $route->planned_departure_time ?? $route->departed_at?->timezone('Asia/Jakarta')->format('H:i') ?? '-' }}</strong>
                            · Estimasi Tiba: <strong class="font-mono">{{ $route->scheduled_arrival_time ?? '-' }}</strong>
                        </p>
                    </div>
                    <span class="self-start px-md py-xs rounded-full text-micro bg-surface-subtle border border-border-soft text-ink">
                        {{ $route->status->label() }}
                    </span>
                </div>

                @if($isEditable)
                    <form method="POST" action="{{ route('aslap.distribusi.update', $route) }}" class="border-t border-border-soft pt-md grid grid-cols-1 sm:grid-cols-5 gap-sm items-end">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Petugas Pengantar</label>
                            <input type="text" name="courier_name" value="{{ $route->courier_name }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                        </div>
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Kendaraan (Plat)</label>
                            <input type="text" name="vehicle_plate" value="{{ $route->vehicle_plate }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                        </div>
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Berangkat Dapur</label>
                            <input type="time" name="planned_departure_time" value="{{ $route->planned_departure_time ?? '09:00' }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                        </div>
                        <div>
                            <label class="block font-body text-micro text-ink mb-xs">Estimasi Tiba</label>
                            <input type="time" name="scheduled_arrival_time" value="{{ $route->scheduled_arrival_time ?? '09:30' }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                        </div>
                        <div>
                            <button type="submit" class="min-h-[44px] w-full px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                Ubah Jadwal
                            </button>
                        </div>
                    </form>
                @else
                    <p class="font-body text-micro text-ink-muted">
                        Petugas: {{ $route->courier_name }} · Armada: {{ $route->vehicle_plate }} · Rute sudah berjalan dan terkunci.
                    </p>
                @endif
            </article>
        @empty
            <div class="bg-surface rounded-xl p-xl border border-border-soft text-center font-body text-body-md text-ink-muted">
                Belum ada rute pengiriman untuk tanggal ini.
            </div>
        @endforelse
    </section>
</x-layouts.aslap>
