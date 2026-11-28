<x-layouts.tim-distribusi title="Update Status & Serah Terima">
    <section class="flex flex-col gap-md">
        @foreach($routes as $route)
            @php
                $canDepart = $route->status === \App\Enums\DeliveryStatus::DISIAPKAN;
                $canArrive = $route->status === \App\Enums\DeliveryStatus::BERANGKAT;
                $diserahkanDiff = $route->jumlah_diserahkan !== null ? ($route->jumlah_diserahkan - $route->dispatched_portions) : null;
            @endphp
            <article id="route-{{ $route->id }}" class="bg-surface rounded-xl p-lg border border-border-soft flex flex-col gap-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-sm">
                    <div>
                        <span class="font-mono text-micro text-ink-muted">{{ $route->waybill_number }} · {{ $route->courier_name }} ({{ $route->vehicle_plate }})</span>
                        <h2 class="font-display text-section-title text-ink">{{ $route->location?->name }}</h2>
                        <p class="font-body text-micro text-ink-muted tabular-nums">
                            Target Porsi: <strong>{{ number_format($route->dispatched_portions, 0, ',', '.') }}</strong>
                            @if($route->jumlah_diserahkan !== null)
                                · Diserahkan: <strong>{{ number_format($route->jumlah_diserahkan, 0, ',', '.') }}</strong>
                                @if($diserahkanDiff !== 0)
                                    <span class="ml-xs px-sm py-0.5 rounded-full bg-alert-soft text-on-alert-soft">Selisih {{ $diserahkanDiff }}</span>
                                @endif
                            @endif
                        </p>
                    </div>
                    <span class="self-start px-md py-xs rounded-full text-micro bg-surface-subtle border border-border-soft text-ink">
                        {{ $route->status->label() }}
                    </span>
                </div>

                @if($route->documents->isNotEmpty())
                    <div class="flex flex-wrap gap-sm">
                        @foreach($route->documents as $doc)
                            <a href="{{ route('distribusi.dokumen.download', $doc) }}" class="min-h-[44px] px-md inline-flex items-center gap-xs rounded-full bg-surface-subtle border border-border-soft font-body text-label-sm text-ink">
                                <span class="material-symbols-outlined text-card-title" aria-hidden="true">attach_file</span>
                                <span>{{ $doc->original_name ?? 'Bukti Pengiriman' }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($canDepart || $canArrive)
                    <form method="POST" action="{{ route('distribusi.pengiriman.update', $route) }}" enctype="multipart/form-data" class="border-t border-border-soft pt-md flex flex-col gap-md">
                        @csrf
                        <h3 class="font-display text-card-title text-ink">Catat Status &amp; Serah Terima</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-sm">
                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">Status Pengiriman</label>
                                <select name="status" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                                    @if($canDepart)
                                        <option value="{{ \App\Enums\DeliveryStatus::BERANGKAT->value }}">Berangkat</option>
                                    @endif
                                    @if($canArrive)
                                        <option value="{{ \App\Enums\DeliveryStatus::TIBA->value }}">Tiba (Serah Terima)</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">
                                    {{ $canDepart ? 'Jumlah Porsi Dikirim' : 'Jumlah Ompreng Diserahkan' }}
                                </label>
                                <input type="number" name="portions" min="0" max="{{ $route->location?->target_portions ?? $route->dispatched_portions }}" value="{{ $route->dispatched_portions }}" required class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                            </div>

                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">
                                    Nama Penerima {{ $canArrive ? '(Wajib)' : '(Opsional)' }}
                                </label>
                                <input type="text" name="nama_penerima_serah" value="{{ $route->nama_penerima_serah }}" @if($canArrive) required @endif placeholder="Nama penerima di lokasi" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                            </div>

                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">Waktu Serah Terima / Berangkat</label>
                                <input type="datetime-local" name="event_time" value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-mono text-body-md text-ink">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">Catatan Lapangan</label>
                                <input type="text" name="catatan_lapangan" value="{{ $route->catatan_lapangan }}" placeholder="Catatan kondisi perjalanan/serah terima..." class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle px-md font-body text-body-md text-ink">
                            </div>
                            <div>
                                <label class="block font-body text-label-sm text-ink mb-xs">Unggah Bukti (JPG/PNG/PDF, maks. 2 MB)</label>
                                <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="min-h-[44px] w-full rounded-lg border border-border-soft bg-surface-subtle p-xs font-body text-label-sm text-ink">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="min-h-[44px] px-lg rounded-full bg-obsidian text-on-obsidian font-body text-label-sm font-semibold cursor-pointer">
                                Simpan Status &amp; Serah Terima
                            </button>
                        </div>
                    </form>
                @else
                    <p class="font-body text-micro text-ink-muted">
                        Penerima: {{ $route->nama_penerima_serah ?? $route->confirmer?->name ?? '-' }} · Status sudah mencapai {{ $route->status->label() }}.
                    </p>
                @endif
            </article>
        @endforeach
    </section>
</x-layouts.tim-distribusi>
