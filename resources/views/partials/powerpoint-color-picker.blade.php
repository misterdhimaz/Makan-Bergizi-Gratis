{{--
    PALET WARNA INTERAKTIF (STANDAR HONEYCOMB & KUSTOM SPECTRUM 2D)
    Komponen palet warna interaktif lengkap:
      - Tab "Standar": Honeycomb 127 sel heksagonal warna-warni + bar heksagonal grayscale (putih ke hitam).
      - Tab "Kustom": Canvas 2D spektrum warna (Hue x Saturation) dengan kursor bidik (+),
                      slider vertikal luminositas dengan panah (◄),
                      pilihan model warna RGB, input numerik Red/Green/Blue, dan input Hex.
      - Kotak perbandingan "Baru (New)" vs "Saat Ini (Current)".
      - Tombol Terapkan (OK) dan Batal (Cancel).
--}}
<template x-teleport="body">
    <div
        x-show="pptOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md select-none overflow-y-auto"
        @keydown.escape.window="pptOpen = false"
        x-cloak
    >
        <!-- Modal Window -->
        <div
            @click.outside="pptOpen = false"
            class="w-full max-w-[420px] bg-[#F8FAFC] text-slate-800 rounded-2xl shadow-2xl border border-slate-300 font-sans overflow-hidden text-xs my-auto"
            style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(0, 0, 0, 0.1);"
        >
            <!-- Window Dialog Title Bar -->
            <div class="h-10 px-4 bg-white border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-xs"></span>
                    <span class="font-bold text-slate-800 text-[13px]">Palet Warna — <span class="font-semibold text-emerald-700" x-text="pptTargetLabel"></span></span>
                </div>
                <button
                    type="button"
                    @click="pptOpen = false"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:bg-rose-500 hover:text-white transition cursor-pointer text-sm font-bold"
                    title="Tutup Palet"
                >
                    ✕
                </button>
            </div>

            <!-- Body Area -->
            <div class="p-4 space-y-3.5">
                <!-- Header Top: Tabs (Standar / Kustom) & Action Buttons (Terapkan / Batal) -->
                <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
                    <!-- Tabs -->
                    <div class="inline-flex rounded-lg bg-slate-200/80 p-0.5 border border-slate-300">
                        <button
                            type="button"
                            @click="pptTab = 'standard'"
                            :class="pptTab === 'standard' ? 'bg-white text-slate-900 shadow-xs font-bold border-slate-300' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="px-3.5 py-1 rounded-md text-xs transition cursor-pointer"
                        >
                            Standar
                        </button>
                        <button
                            type="button"
                            @click="pptTab = 'custom'; $nextTick(() => renderPptCustomTab())"
                            :class="pptTab === 'custom' ? 'bg-white text-slate-900 shadow-xs font-bold border-slate-300' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="px-3.5 py-1 rounded-md text-xs transition cursor-pointer"
                        >
                            Kustom
                        </button>
                    </div>

                    <!-- Action Buttons: Terapkan & Batal -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button
                            type="button"
                            @click="applyPptColor()"
                            class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer min-w-[70px] flex items-center justify-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Terapkan</span>
                        </button>
                        <button
                            type="button"
                            @click="pptOpen = false"
                            class="px-3.5 py-1.5 rounded-lg bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-300 shadow-xs transition cursor-pointer min-w-[64px]"
                        >
                            Batal
                        </button>
                    </div>
                </div>

                <!-- ==================== TAB 1: STANDAR (HONEYCOMB HEXAGON PALETTE) ==================== -->
                <div x-show="pptTab === 'standard'" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-600">Pilih Warna Sarang Lebah (Honeycomb):</span>
                        <span class="text-[11px] text-emerald-700 font-mono font-bold" x-text="'Kode: ' + pptNewHex"></span>
                    </div>

                    <!-- Honeycomb SVG Container -->
                    <div class="bg-white rounded-xl p-2.5 border border-slate-300 flex items-center justify-center overflow-hidden shadow-inner">
                        <svg
                            id="pptHoneycombSvg"
                            class="w-full max-w-[310px] h-auto cursor-pointer"
                            viewBox="0 0 280 295"
                        >
                            <!-- Dynamic Hexagons injected via Alpine init -->
                            <g id="pptHoneycombGroup"></g>
                            <!-- Grayscale Bar -->
                            <g id="pptGrayscaleGroup"></g>
                        </svg>
                    </div>
                </div>

                <!-- ==================== TAB 2: KUSTOM (2D SPECTRUM + LUMINOSITY SLIDER) ==================== -->
                <div x-show="pptTab === 'custom'" class="space-y-3">
                    <span class="text-[11px] font-semibold text-slate-600 block">Spektrum Warna & Tingkat Kecerahan:</span>

                    <!-- Row 1: Spectrum Canvas (Left) + Luminosity Slider (Right) -->
                    <div class="flex items-center justify-center gap-3 bg-white rounded-xl p-3 border border-slate-300 shadow-inner">
                        <!-- 2D Canvas Spectrum Box (220 x 170) -->
                        <div class="relative w-[220px] h-[170px] rounded-lg border border-slate-400 overflow-hidden cursor-crosshair shadow-xs">
                            <canvas
                                id="pptSpectrumCanvas"
                                width="220"
                                height="170"
                                class="w-[220px] h-[170px] block"
                                @mousedown="startSpectrumDrag($event)"
                                @mousemove="onSpectrumDrag($event)"
                                @mouseup="stopDrag()"
                                @mouseleave="stopDrag()"
                            ></canvas>
                        </div>

                        <!-- Vertical Luminosity Slider Bar (20 x 170) + Pointer Arrow (◄) -->
                        <div class="relative flex items-center h-[170px]">
                            <div class="w-[20px] h-[170px] rounded-lg border border-slate-400 overflow-hidden cursor-ns-resize shadow-xs">
                                <canvas
                                    id="pptLuminosityCanvas"
                                    width="20"
                                    height="170"
                                    class="w-[20px] h-[170px] block"
                                    @mousedown="startLumDrag($event)"
                                    @mousemove="onLumDrag($event)"
                                    @mouseup="stopDrag()"
                                    @mouseleave="stopDrag()"
                                    title="Tarik untuk mengatur tingkat kecerahan"
                                ></canvas>
                            </div>
                            <!-- Pointer Arrow (Segitiga hitam penunjuk) -->
                            <div
                                class="absolute left-[22px] pointer-events-none transition-all duration-75 text-slate-900 font-bold text-xs"
                                :style="`top: ${Math.max(0, Math.min(160, pptPointerY - 7))}px;`"
                            >
                                ◀
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Controls & Numeric Steppers (RGB, Hex) + Color Comparison (New vs Current) -->
                    <div class="grid grid-cols-12 gap-3 pt-1">
                        <!-- Numeric Inputs (Left 7 Cols) -->
                        <div class="col-span-7 space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <label class="text-[11px] text-slate-600">Model Warna:</label>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-300 text-[11px] font-bold text-slate-700">RGB</span>
                            </div>

                            <!-- Red -->
                            <div class="flex items-center justify-between gap-2">
                                <label class="text-[11px] font-semibold text-slate-700 w-16">Merah (R):</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="255"
                                    x-model.number="pptR"
                                    @input="onRgbInputChange()"
                                    class="w-20 px-2 py-0.5 rounded bg-white border border-slate-300 text-slate-900 font-mono text-xs text-right font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500"
                                >
                            </div>

                            <!-- Green -->
                            <div class="flex items-center justify-between gap-2">
                                <label class="text-[11px] font-semibold text-slate-700 w-16">Hijau (G):</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="255"
                                    x-model.number="pptG"
                                    @input="onRgbInputChange()"
                                    class="w-20 px-2 py-0.5 rounded bg-white border border-slate-300 text-slate-900 font-mono text-xs text-right font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500"
                                >
                            </div>

                            <!-- Blue -->
                            <div class="flex items-center justify-between gap-2">
                                <label class="text-[11px] font-semibold text-slate-700 w-16">Biru (B):</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="255"
                                    x-model.number="pptB"
                                    @input="onRgbInputChange()"
                                    class="w-20 px-2 py-0.5 rounded bg-white border border-slate-300 text-slate-900 font-mono text-xs text-right font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500"
                                >
                            </div>

                            <!-- Hex Input -->
                            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-200">
                                <label class="text-[11px] font-semibold text-slate-700 w-16">Kode Hex:</label>
                                <input
                                    type="text"
                                    maxlength="7"
                                    x-model="pptNewHex"
                                    @input="onHexInputChange()"
                                    class="w-20 px-2 py-0.5 rounded bg-white border border-slate-300 text-slate-900 font-mono text-xs text-right font-extrabold uppercase focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500"
                                >
                            </div>
                        </div>

                        <!-- Comparison Swatch Box: New vs Current (Right 5 Cols) -->
                        <div class="col-span-5 flex flex-col items-center justify-center bg-white p-2 rounded-xl border border-slate-200">
                            <span class="text-[10px] font-semibold text-slate-600 mb-1">Baru (Pilihan)</span>
                            <div class="w-20 h-20 rounded-lg border border-slate-400 overflow-hidden shadow-sm flex flex-col">
                                <!-- New Color (Top Half) -->
                                <div class="flex-1 transition-colors" :style="`background-color: ${pptNewHex};`"></div>
                                <!-- Current Color (Bottom Half) -->
                                <div class="flex-1" :style="`background-color: ${pptCurrentHex};`"></div>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 mt-1">Saat Ini (Aktif)</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Transparency / Hex Quick Display -->
                <div class="pt-2.5 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-600">
                    <div class="flex items-center gap-1.5">
                        <span>Transparansi:</span>
                        <input type="range" min="0" max="0" value="0" disabled class="w-20 accent-emerald-600 opacity-50 cursor-not-allowed">
                        <span class="font-mono text-slate-700 font-bold">0 %</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs" :style="`background-color: ${pptNewHex}`"></span>
                        <span class="font-mono font-extrabold text-slate-900" x-text="pptNewHex"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
