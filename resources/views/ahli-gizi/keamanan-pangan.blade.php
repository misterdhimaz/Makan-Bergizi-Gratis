<x-layouts.ahli-gizi
    title="Keamanan Pangan & APD"
    subtitle="Pemeriksaan suhu, kebersihan dapur, kepatuhan APD, dan temuan SOP BGN"
    breadcrumb="Ahli Gizi · SPPG Sungai Selayur 1"
>
    <div
        x-data="{
            tab: '{{ $activeTab }}',
            temps: @js($temps),
            apds: @js($apds),
            compliantVolunteers: {{ (int) ($currentCheck?->compliant_volunteers ?? 35) }},
            fileName: 'Pilih berkas foto atau PDF'
        }"
        class="flex flex-col gap-lg"
    >
        {{-- 3 Tabs --}}
        <div role="tablist" aria-label="Bagian Keamanan Pangan dan APD" class="flex flex-wrap items-center gap-xs bg-surface rounded-full p-xs border border-border-soft w-fit">
            @foreach([
                'pemeriksaan' => 'Pemeriksaan',
                'rekap-apd' => 'Rekap APD',
                'temuan' => 'Temuan',
            ] as $key => $label)
                <button
                    type="button"
                    role="tab"
                    :aria-selected="(tab === '{{ $key }}').toString()"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-obsidian text-on-obsidian' : 'text-ink-muted hover:text-ink'"
                    class="px-lg py-sm rounded-full font-body text-label-sm whitespace-nowrap cursor-pointer transition-colors"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- TAB 1: Pemeriksaan --}}
        <section x-show="tab === 'pemeriksaan'" role="tabpanel" class="flex flex-col gap-lg">
            {{-- Filter Tanggal & Shift --}}
            <form method="GET" action="{{ route('ahli-gizi.safety.index') }}" class="bg-surface rounded-xl p-xl border border-border-soft flex flex-wrap items-center justify-between gap-md">
                <div class="flex flex-wrap items-center gap-lg">
                    <div class="flex flex-col gap-xs">
                        <span class="font-body text-micro text-ink-muted">Tanggal pemeriksaan</span>
                        <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" class="rounded-md border border-border-soft bg-surface-subtle px-md py-xs font-mono text-label-sm text-ink">
                    </div>

                    <div class="flex flex-col gap-xs">
                        <span class="font-body text-micro text-ink-muted">Pilih shift</span>
                        <div role="group" aria-label="Pilih shift" class="inline-flex items-center gap-xs bg-surface-subtle p-xs rounded-full border border-border-soft">
                            @foreach(['Pagi', 'Siang', 'Malam'] as $sh)
                                <button type="submit" name="shift" value="{{ $sh }}" class="px-md py-xs rounded-full font-body text-label-sm whitespace-nowrap cursor-pointer {{ $selectedShift === $sh ? 'bg-obsidian text-on-obsidian' : 'text-ink-muted hover:text-ink' }}">
                                    {{ $sh }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <span class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-lime-accent font-body text-micro text-on-lime">
                    <span class="material-symbols-outlined text-label-sm" aria-hidden="true">fact_check</span>
                    Acuan: Checklist SOP BGN
                </span>
            </form>

            <form method="POST" action="{{ route('ahli-gizi.safety.store') }}" enctype="multipart/form-data" class="flex flex-col gap-lg">
                @csrf
                <input type="hidden" name="check_date" value="{{ $selectedDate }}">
                <input type="hidden" name="shift" value="{{ $selectedShift }}">

                {{-- 1. Pemeriksaan Suhu --}}
                <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                    <div class="flex flex-wrap items-center justify-between gap-sm">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined text-section-title text-ink" aria-hidden="true">device_thermostat</span>
                            <h2 class="font-display text-section-title text-ink">1. Pemeriksaan Suhu (6 titik)</h2>
                        </div>
                        <span class="font-body text-micro text-ink-muted">Isi angka suhu aktual (°C) dan status kesesuaian SOP</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border-soft">
                                    <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Titik pemeriksaan</th>
                                    <th class="py-sm px-md font-body text-label-sm text-ink-muted">Standar SOP</th>
                                    <th class="py-sm px-md font-body text-label-sm text-ink-muted">Suhu aktual (°C)</th>
                                    <th class="py-sm pl-md font-body text-label-sm text-ink-muted text-right">Kesesuaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-soft">
                                <template x-for="(t, idx) in temps" :key="idx">
                                    <tr>
                                        <td class="py-sm pr-md font-body text-card-title text-ink">
                                            <span x-text="t.label"></span>
                                            <input type="hidden" :name="'temps[' + idx + '][label]'" :value="t.label">
                                            <input type="hidden" :name="'temps[' + idx + '][standard]'" :value="t.standard">
                                            <input type="hidden" :name="'temps[' + idx + '][compliant]'" :value="t.compliant ? '1' : '0'">
                                        </td>
                                        <td class="py-sm px-md font-mono text-label-sm text-ink-muted tabular-nums" x-text="t.standard"></td>
                                        <td class="py-sm px-md">
                                            <div class="inline-flex items-center gap-xs">
                                                <input type="number" step="0.1" :name="'temps[' + idx + '][actual]'" x-model="t.actual" class="w-24 rounded-md border border-border-soft bg-surface-subtle px-sm py-xs font-mono text-label-sm text-ink tabular-nums">
                                                <span class="font-body text-label-sm text-ink-muted">°C</span>
                                            </div>
                                        </td>
                                        <td class="py-sm pl-md text-right">
                                            <div class="inline-flex items-center gap-xs bg-surface-subtle p-xs rounded-full border border-border-soft">
                                                <button type="button" @click="t.compliant = true" :class="t.compliant ? 'bg-success-soft text-on-success-soft' : 'text-ink-muted'" class="px-md py-xs rounded-full font-body text-micro whitespace-nowrap cursor-pointer">Sesuai</button>
                                                <button type="button" @click="t.compliant = false" :class="!t.compliant ? 'bg-danger-soft text-on-danger-soft' : 'text-ink-muted'" class="px-md py-xs rounded-full font-body text-micro whitespace-nowrap cursor-pointer">Tidak sesuai</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. Kebersihan Dapur --}}
                <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                    <div class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-section-title text-ink" aria-hidden="true">cleaning_services</span>
                        <h2 class="font-display text-section-title text-ink">2. Kebersihan Dapur</h2>
                    </div>

                    @if($hygieneChecklist->isEmpty())
                        <div class="rounded-lg border border-dashed border-border-soft bg-surface-subtle p-xl flex flex-col items-center justify-center text-center gap-xs">
                            <span class="material-symbols-outlined text-page-title text-ink-muted" aria-hidden="true">playlist_add_check</span>
                            <span class="font-body text-card-title text-ink">Item kebersihan belum diisi Administrator</span>
                            <span class="font-body text-body-md text-ink-muted max-w-prose">Daftar butir kebersihan dapur akan muncul di sini setelah Administrator menambahkannya pada menu Checklist SOP di Data Master.</span>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
                            @foreach($hygieneChecklist as $item)
                                <div class="p-md rounded-md bg-surface-subtle border border-border-soft flex items-center justify-between gap-sm">
                                    <span class="font-body text-body-md text-ink">{{ $item->title }}</span>
                                    <span class="font-mono text-micro text-ink-muted">{{ $item->threshold_value }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 3. Kepatuhan APD --}}
                <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                    <div class="flex flex-wrap items-center justify-between gap-md">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined text-section-title text-ink" aria-hidden="true">masks</span>
                            <h2 class="font-display text-section-title text-ink">3. Kepatuhan APD ({{ count($apds) }} butir)</h2>
                        </div>

                        <label class="inline-flex items-center gap-sm bg-surface-subtle px-md py-xs rounded-full border border-border-soft">
                            <span class="font-body text-label-sm text-ink">Relawan patuh:</span>
                            <input type="number" name="compliant_volunteers" x-model="compliantVolunteers" min="0" max="35" required class="w-16 rounded-md border border-border-soft bg-surface px-sm py-xs font-mono text-label-sm text-ink text-center tabular-nums">
                            <span class="font-body text-label-sm text-ink-muted tabular-nums">dari 35</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
                        <template x-for="(a, idx) in apds" :key="idx">
                            <div class="flex items-center justify-between gap-sm p-md rounded-md bg-surface-subtle border border-border-soft">
                                <span class="font-body text-body-md text-ink" x-text="a.label"></span>
                                <input type="hidden" :name="'apds[' + idx + '][label]'" :value="a.label">
                                <input type="hidden" :name="'apds[' + idx + '][compliant]'" :value="a.compliant ? '1' : '0'">
                                <div class="inline-flex items-center gap-xs bg-surface p-xs rounded-full border border-border-soft shrink-0">
                                    <button type="button" @click="a.compliant = true" :class="a.compliant ? 'bg-success-soft text-on-success-soft' : 'text-ink-muted'" class="px-sm py-xs rounded-full font-body text-micro whitespace-nowrap cursor-pointer">Sesuai</button>
                                    <button type="button" @click="a.compliant = false" :class="!a.compliant ? 'bg-danger-soft text-on-danger-soft' : 'text-ink-muted'" class="px-sm py-xs rounded-full font-body text-micro whitespace-nowrap cursor-pointer">Tidak sesuai</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Lampiran, Catatan & Simpan --}}
                <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
                        <div class="flex flex-col gap-xs">
                            <span class="font-body text-card-title text-ink">Unggah bukti foto / dokumen</span>
                            <label class="flex items-center justify-between gap-md p-md rounded-md border border-dashed border-border-soft bg-surface-subtle cursor-pointer hover:bg-surface-muted/40">
                                <div class="flex items-center gap-sm min-w-0">
                                    <span class="material-symbols-outlined text-section-title text-ink-muted shrink-0" aria-hidden="true">upload_file</span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-body text-body-md text-ink truncate" x-text="fileName"></span>
                                        <span class="font-body text-micro text-ink-muted">maks. 2 MB, format JPG/PNG/PDF</span>
                                    </div>
                                </div>
                                <span class="px-md py-xs rounded-full bg-surface border border-border-soft font-body text-label-sm text-ink shrink-0">Pilih berkas</span>
                                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" @change="fileName = $event.target.files?.[0]?.name || 'Pilih berkas foto atau PDF'" class="sr-only">
                            </label>
                        </div>

                        <div class="flex flex-col gap-xs">
                            <label for="catatan-pemeriksaan" class="font-body text-card-title text-ink">Catatan pemeriksaan</label>
                            <textarea id="catatan-pemeriksaan" name="notes" rows="2" placeholder="Tuliskan catatan suhu, kendala alat, atau tindak lanjut awal..." class="w-full rounded-md border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink">{{ $currentCheck?->notes }}</textarea>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-md pt-md border-t border-border-soft">
                        <div class="flex items-center gap-xs text-ink-muted">
                            <span class="material-symbols-outlined text-label-sm" aria-hidden="true">schedule</span>
                            <span class="font-body text-micro tabular-nums">
                                {{ $currentCheck?->checked_at ? 'Terakhir disimpan: '.$currentCheck->checked_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB' : 'Stempel waktu otomatis saat simpan' }}
                            </span>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-sm px-xl py-sm rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 cursor-pointer">
                            <span class="material-symbols-outlined text-card-title" aria-hidden="true">save</span>
                            Simpan Pemeriksaan
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- TAB 2: Rekap APD --}}
        <section x-show="tab === 'rekap-apd'" x-cloak role="tabpanel" class="flex flex-col gap-lg">
            @php
                $pagi = $todayChecks->get('Pagi');
                $siang = $todayChecks->get('Siang');
                $malam = $todayChecks->get('Malam');
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-lg items-stretch">
                <div class="{{ $pagi ? 'bg-lime-accent text-on-lime' : 'bg-surface border border-border-soft text-ink' }} rounded-xl p-xl flex flex-col justify-between gap-md">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-body text-card-title">Shift Pagi</span>
                        <span class="px-sm py-xs rounded-full {{ $pagi ? 'bg-success-soft text-on-success-soft' : 'bg-surface-muted text-ink' }} font-body text-micro">
                            {{ $pagi ? $pagi->compliancePercent().' Patuh' : 'Belum diperiksa' }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-sm">
                        <span class="font-display text-kpi tabular-nums">{{ $pagi ? $pagi->compliant_volunteers.'/'.$pagi->total_volunteers : '—/35' }}</span>
                        <span class="font-body text-body-md">relawan</span>
                    </div>
                    <span class="font-body text-micro">{{ $pagi?->checked_at ? 'Diperiksa '.$pagi->checked_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB' : 'Jadwal pemeriksaan pukul 07:00 WIB' }}</span>
                </div>

                <div class="{{ $siang ? 'bg-lime-accent text-on-lime' : 'bg-surface border border-border-soft text-ink' }} rounded-xl p-xl flex flex-col justify-between gap-md">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-body text-card-title">Shift Siang</span>
                        <span class="px-sm py-xs rounded-full {{ $siang ? 'bg-success-soft text-on-success-soft' : 'bg-surface-muted text-ink' }} font-body text-micro">
                            {{ $siang ? $siang->compliancePercent().' Patuh' : 'Belum diperiksa' }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-sm">
                        <span class="font-display text-kpi tabular-nums">{{ $siang ? $siang->compliant_volunteers.'/'.$siang->total_volunteers : '—/35' }}</span>
                        <span class="font-body text-body-md">relawan</span>
                    </div>
                    <span class="font-body text-micro">{{ $siang?->checked_at ? 'Diperiksa '.$siang->checked_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB' : 'Jadwal pemeriksaan pukul 13:00 WIB' }}</span>
                </div>

                <div class="{{ $malam ? 'bg-lime-accent text-on-lime' : 'bg-surface border border-border-soft text-ink' }} rounded-xl p-xl flex flex-col justify-between gap-md">
                    <div class="flex items-center justify-between gap-sm">
                        <span class="font-body text-card-title">Shift Malam</span>
                        <span class="px-sm py-xs rounded-full {{ $malam ? 'bg-success-soft text-on-success-soft' : 'bg-surface-muted text-ink' }} font-body text-micro">
                            {{ $malam ? $malam->compliancePercent().' Patuh' : 'Belum diperiksa' }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-sm">
                        <span class="font-display text-kpi tabular-nums">{{ $malam ? $malam->compliant_volunteers.'/'.$malam->total_volunteers : '—/35' }}</span>
                        <span class="font-body text-body-md">relawan</span>
                    </div>
                    <span class="font-body text-micro">{{ $malam?->checked_at ? 'Diperiksa '.$malam->checked_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB' : 'Jadwal pemeriksaan pukul 19:00 WIB' }}</span>
                </div>
            </div>

            <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                <div class="flex flex-wrap items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-ink">Riwayat Kepatuhan APD 7 Hari Terakhir</h2>
                    <span class="font-body text-micro text-ink-muted">Total relawan aktif per shift: 35 orang</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-soft">
                                <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Tanggal</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted">Shift Pagi</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted">Shift Siang</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted">Shift Malam</th>
                                <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft">
                            @foreach($historyRows as $row)
                                @php
                                    $fmtShift = static fn ($c) => $c ? $c->compliant_volunteers.'/'.$c->total_volunteers.' ('.$c->compliancePercent().')' : 'Belum diperiksa';
                                    $note = collect([$row['pagi']?->notes, $row['siang']?->notes, $row['malam']?->notes])->filter()->first() ?? '';
                                @endphp
                                <tr>
                                    <td class="py-sm pr-md font-mono text-label-sm text-ink tabular-nums">{{ $row['date_label'] }}</td>
                                    <td class="py-sm px-md font-mono text-body-md text-ink tabular-nums">{{ $fmtShift($row['pagi']) }}</td>
                                    <td class="py-sm px-md font-mono text-body-md text-ink-muted tabular-nums">{{ $fmtShift($row['siang']) }}</td>
                                    <td class="py-sm px-md font-mono text-body-md text-ink-muted tabular-nums">{{ $fmtShift($row['malam']) }}</td>
                                    <td class="py-sm pl-md font-body text-body-md text-ink-muted">{{ $note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- TAB 3: Temuan (Hanya Lihat) --}}
        <section x-show="tab === 'temuan'" x-cloak role="tabpanel" class="flex flex-col gap-lg">
            <div class="rounded-lg bg-surface-subtle border border-border-soft px-lg py-md flex items-center gap-sm">
                <span class="material-symbols-outlined text-card-title text-ink-muted shrink-0" aria-hidden="true">info</span>
                <span class="font-body text-body-md text-ink-muted">Status temuan bersifat hanya lihat. Pembaruan status tindak lanjut dilakukan oleh Kepala Dapur.</span>
            </div>

            <div class="bg-surface rounded-xl p-xl border border-border-soft flex flex-col gap-md">
                <div class="flex items-center justify-between gap-sm">
                    <h2 class="font-display text-section-title text-ink">Daftar Temuan Keamanan Pangan &amp; APD</h2>
                    <span class="font-body text-micro text-ink-muted">{{ $findings->count() }} temuan tercatat</span>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-soft">
                                <th class="py-sm pr-md font-body text-label-sm text-ink-muted">Tanggal</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted">Jenis</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted">Uraian</th>
                                <th class="py-sm px-md font-body text-label-sm text-ink-muted">Status</th>
                                <th class="py-sm pl-md font-body text-label-sm text-ink-muted">Ditindaklanjuti oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft">
                            @foreach($findings as $finding)
                                <tr>
                                    <td class="py-md pr-md font-mono text-label-sm text-ink tabular-nums whitespace-nowrap">{{ $finding->finding_date?->translatedFormat('d M Y') }}</td>
                                    <td class="py-md px-md">
                                        <span class="px-sm py-xs rounded-full bg-surface-subtle border border-border-soft font-body text-micro text-ink">{{ $finding->type }}</span>
                                    </td>
                                    <td class="py-md px-md font-body text-body-md text-ink">{{ $finding->description }}</td>
                                    <td class="py-md px-md">
                                        <x-badge-status :label="$finding->status === 'ditindaklanjuti' ? 'Ditindaklanjuti' : 'Terbuka'" :variant="$finding->status === 'ditindaklanjuti' ? 'success' : 'danger'" />
                                    </td>
                                    <td class="py-md pl-md font-body text-body-md text-ink-muted">
                                        {{ $finding->handler_note ?: ($finding->handler ? 'Kepala Dapur ('.$finding->handler->name.')' : 'Menunggu Kepala Dapur') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden flex flex-col gap-sm">
                    @foreach($findings as $finding)
                        <article class="rounded-lg border border-border-soft bg-surface-subtle p-md flex flex-col gap-xs">
                            <div class="flex items-center justify-between gap-sm">
                                <span class="font-mono text-micro text-ink-muted">{{ $finding->finding_date?->translatedFormat('d M Y') }} · {{ $finding->type }}</span>
                                <x-badge-status :label="$finding->status === 'ditindaklanjuti' ? 'Ditindaklanjuti' : 'Terbuka'" :variant="$finding->status === 'ditindaklanjuti' ? 'success' : 'danger'" />
                            </div>
                            <p class="font-body text-body-md text-ink">{{ $finding->description }}</p>
                            <span class="font-body text-micro text-ink-muted">{{ $finding->handler_note ?: 'Menunggu Kepala Dapur' }}</span>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-layouts.ahli-gizi>
