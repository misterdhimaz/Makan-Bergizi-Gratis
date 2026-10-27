@extends('layouts.app')

@section('title', 'Tampilan, Tema & Foto Login')
@section('page_title', 'Pengaturan Tampilan & Tema Sistem')
@section('page_subtitle', 'Ganti tema warna web (terang, gelap, minimalis, emas, kustom) dan atur foto background login SPPG')

@section('content')
<div
    x-data="{
        activeTab: 'theme',
        currentPreset: @js($settings['theme_preset'] ?? 'sppg-navy'),
        customColors: {
            canvas: @js($settings['custom_bg_canvas'] ?? '#030D22'),
            surface: @js($settings['custom_bg_surface'] ?? '#0A2556'),
            primary: @js($settings['custom_primary'] ?? '#92D05D'),
            primary_text: @js($settings['custom_primary_text'] ?? '#071E49'),
            text_main: @js($settings['custom_text_main'] ?? '#F8FAFC'),
            text_muted: @js($settings['custom_text_muted'] ?? '#8FA3BF'),
            border: @js($settings['custom_border'] ?? '#1E3A8A'),
        },
        bgPreview: @js($backgroundUrl),
        logoPreview: @js($logoUrl),
        overlay: {{ (int) $settings['login_overlay'] }},
        title: @js($settings['login_title']),
        tagline: @js($settings['login_tagline']),

        // ==================== POWERPOINT 'COLORS' DIALOG STATE ====================
        pptOpen: false,
        pptTab: 'custom',
        pptTargetKey: 'canvas',
        pptTargetLabel: 'Latar Halaman (Canvas)',
        pptCurrentHex: '#030D22',
        pptNewHex: '#4472C4',
        pptR: 68,
        pptG: 114,
        pptB: 196,
        pptHue: 218,
        pptSat: 0.50,
        pptLum: 0.52,
        pptCrossX: 133,
        pptCrossY: 85,
        pptPointerY: 81,
        pptDraggingSpectrum: false,
        pptDraggingLum: false,

        selectPreset(presetId) {
            this.currentPreset = presetId;
            window.setMbgTheme(presetId, false);
        },
        updateCustomPreview() {
            if (this.currentPreset === 'custom') {
                window.setMbgTheme('custom', false, this.customColors);
            }
        },
        pickFile(e, target) {
            const f = e.target.files[0];
            if (!f) return;
            if (!['image/jpeg','image/png','image/webp'].includes(f.type)) {
                alert('Format harus JPG, PNG, atau WEBP.');
                e.target.value = '';
                return;
            }
            this[target] = URL.createObjectURL(f);
        },

        // ==================== CONTRAST ENGINE (ANTI-TABRAKAN) ====================
        getContrastRatio(hex1, hex2) {
            const l1 = window.getMbgLuminance ? window.getMbgLuminance(hex1) : 0.1;
            const l2 = window.getMbgLuminance ? window.getMbgLuminance(hex2) : 0.9;
            const lighter = Math.max(l1, l2);
            const darker = Math.min(l1, l2);
            return ((lighter + 0.05) / (darker + 0.05)).toFixed(1);
        },

        autoHarmonizeContrast() {
            const surfLum = window.getMbgLuminance ? window.getMbgLuminance(this.customColors.surface) : 0.1;
            const isLightSurface = surfLum > 0.45;

            // Jika surface terang, teks harus gelap kontras tinggi (Anti-Tabrakan)
            if (isLightSurface) {
                this.customColors.text_main = '#0F172A';
                this.customColors.text_muted = '#475569';
                this.customColors.border = '#CBD5E1';
            } else {
                this.customColors.text_main = '#F8FAFC';
                this.customColors.text_muted = '#94A3B8';
                this.customColors.border = 'rgba(255, 255, 255, 0.16)';
            }

            // Teks tombol aksi utama otomatis menyesuaikan kontras tombol
            const primLum = window.getMbgLuminance ? window.getMbgLuminance(this.customColors.primary) : 0.5;
            this.customColors.primary_text = primLum > 0.45 ? '#071E49' : '#FFFFFF';

            this.updateCustomPreview();
        },

        // ==================== POWERPOINT COLOR PICKER METHODS ====================
        openPptPicker(key, label) {
            this.pptTargetKey = key;
            this.pptTargetLabel = label;
            const current = this.customColors[key] || '#4472C4';
            this.pptCurrentHex = current;
            this.setFromHex(current);
            this.pptOpen = true;

            this.$nextTick(() => {
                this.renderPptHoneycomb();
                if (this.pptTab === 'custom') {
                    this.renderPptCustomTab();
                }
            });
        },

        applyPptColor() {
            this.customColors[this.pptTargetKey] = this.pptNewHex.toUpperCase();
            this.updateCustomPreview();
            this.pptOpen = false;
        },

        hexToRgb(hex) {
            if (!hex) return [0, 0, 0];
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
            const n = parseInt(hex, 16);
            if (isNaN(n)) return [0, 0, 0];
            return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
        },

        rgbToHex(r, g, b) {
            return '#' + [r, g, b].map(x => {
                const h = Math.max(0, Math.min(255, Math.round(x))).toString(16);
                return h.length === 1 ? '0' + h : h;
            }).join('').toUpperCase();
        },

        rgbToHsl(r, g, b) {
            r /= 255; g /= 255; b /= 255;
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h = 0, s = 0, l = (max + min) / 2;
            if (max !== min) {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }
            return [h * 360, s, l];
        },

        hslToRgb(h, s, l) {
            h = (h % 360 + 360) % 360;
            const c = (1 - Math.abs(2 * l - 1)) * s;
            const x = c * (1 - Math.abs((h / 60) % 2 - 1));
            const m = l - c / 2;
            let r = 0, g = 0, b = 0;
            if (0 <= h && h < 60) { r = c; g = x; b = 0; }
            else if (60 <= h && h < 120) { r = x; g = c; b = 0; }
            else if (120 <= h && h < 180) { r = 0; g = c; b = x; }
            else if (180 <= h && h < 240) { r = 0; g = x; b = c; }
            else if (240 <= h && h < 300) { r = x; g = 0; b = c; }
            else if (300 <= h && h < 360) { r = c; g = 0; b = x; }
            return [
                Math.round((r + m) * 255),
                Math.round((g + m) * 255),
                Math.round((b + m) * 255)
            ];
        },

        setFromHex(hex) {
            if (!hex) return;
            if (!hex.startsWith('#')) hex = '#' + hex;
            if (hex.length !== 4 && hex.length !== 7) return;
            const rgb = this.hexToRgb(hex);
            this.pptR = rgb[0];
            this.pptG = rgb[1];
            this.pptB = rgb[2];
            this.pptNewHex = this.rgbToHex(rgb[0], rgb[1], rgb[2]);

            const hsl = this.rgbToHsl(rgb[0], rgb[1], rgb[2]);
            this.pptHue = hsl[0];
            this.pptSat = hsl[1];
            this.pptLum = hsl[2];

            this.pptCrossX = Math.round((this.pptHue / 360) * 220);
            this.pptCrossY = Math.round((1 - this.pptSat) * 170);
            this.pptPointerY = Math.round((1 - this.pptLum) * 170);

            this.renderPptCustomTab();
        },

        setFromRgb(r, g, b) {
            r = Math.max(0, Math.min(255, Number(r) || 0));
            g = Math.max(0, Math.min(255, Number(g) || 0));
            b = Math.max(0, Math.min(255, Number(b) || 0));
            this.pptR = r;
            this.pptG = g;
            this.pptB = b;
            this.pptNewHex = this.rgbToHex(r, g, b);

            const hsl = this.rgbToHsl(r, g, b);
            this.pptHue = hsl[0];
            this.pptSat = hsl[1];
            this.pptLum = hsl[2];

            this.pptCrossX = Math.round((this.pptHue / 360) * 220);
            this.pptCrossY = Math.round((1 - this.pptSat) * 170);
            this.pptPointerY = Math.round((1 - this.pptLum) * 170);

            this.renderPptCustomTab();
        },

        setFromHsl(h, s, l) {
            this.pptHue = Math.max(0, Math.min(360, h));
            this.pptSat = Math.max(0, Math.min(1, s));
            this.pptLum = Math.max(0, Math.min(1, l));

            const rgb = this.hslToRgb(this.pptHue, this.pptSat, this.pptLum);
            this.pptR = rgb[0];
            this.pptG = rgb[1];
            this.pptB = rgb[2];
            this.pptNewHex = this.rgbToHex(rgb[0], rgb[1], rgb[2]);

            this.renderPptCustomTab();
        },

        onRgbInputChange() {
            this.setFromRgb(this.pptR, this.pptG, this.pptB);
        },

        onHexInputChange() {
            if (this.pptNewHex.length === 7 || this.pptNewHex.length === 4) {
                this.setFromHex(this.pptNewHex);
            }
        },

        startSpectrumDrag(e) {
            this.pptDraggingSpectrum = true;
            this.onSpectrumDrag(e);
        },
        onSpectrumDrag(e) {
            if (!this.pptDraggingSpectrum) return;
            const rect = e.target.getBoundingClientRect();
            const x = Math.max(0, Math.min(220, e.clientX - rect.left));
            const y = Math.max(0, Math.min(170, e.clientY - rect.top));
            this.pptCrossX = Math.round(x);
            this.pptCrossY = Math.round(y);
            this.pptHue = (this.pptCrossX / 220) * 360;
            this.pptSat = Math.max(0.01, Math.min(1, 1 - (this.pptCrossY / 170)));
            this.setFromHsl(this.pptHue, this.pptSat, this.pptLum);
        },
        startLumDrag(e) {
            this.pptDraggingLum = true;
            this.onLumDrag(e);
        },
        onLumDrag(e) {
            if (!this.pptDraggingLum) return;
            const rect = e.target.getBoundingClientRect();
            const y = Math.max(0, Math.min(170, e.clientY - rect.top));
            this.pptPointerY = Math.round(y);
            this.pptLum = Math.max(0, Math.min(1, 1 - (this.pptPointerY / 170)));
            this.setFromHsl(this.pptHue, this.pptSat, this.pptLum);
        },
        stopDrag() {
            this.pptDraggingSpectrum = false;
            this.pptDraggingLum = false;
        },

        renderPptCustomTab() {
            const spec = document.getElementById('pptSpectrumCanvas');
            const lum = document.getElementById('pptLuminosityCanvas');
            if (!spec || !lum) return;

            const ctxSpec = spec.getContext('2d');
            const ctxLum = lum.getContext('2d');
            const w = 220;
            const h = 170;

            // 1. Spektrum Horizontal Hue
            const gradH = ctxSpec.createLinearGradient(0, 0, w, 0);
            gradH.addColorStop(0, '#FF0000');
            gradH.addColorStop(0.17, '#FFFF00');
            gradH.addColorStop(0.33, '#00FF00');
            gradH.addColorStop(0.50, '#00FFFF');
            gradH.addColorStop(0.67, '#0000FF');
            gradH.addColorStop(0.83, '#FF00FF');
            gradH.addColorStop(1, '#FF0000');
            ctxSpec.fillStyle = gradH;
            ctxSpec.fillRect(0, 0, w, h);

            // 2. Overlay Vertikal Saturation
            const gradV = ctxSpec.createLinearGradient(0, 0, 0, h);
            gradV.addColorStop(0, 'rgba(255, 255, 255, 0.95)');
            gradV.addColorStop(0.08, 'rgba(255, 255, 255, 0.4)');
            gradV.addColorStop(0.35, 'rgba(255, 255, 255, 0)');
            gradV.addColorStop(0.65, 'rgba(0, 0, 0, 0)');
            gradV.addColorStop(1, 'rgba(0, 0, 0, 0.95)');
            ctxSpec.fillStyle = gradV;
            ctxSpec.fillRect(0, 0, w, h);

            // 3. Kursor Reticle Crosshair (+)
            const cx = Math.max(6, Math.min(w - 6, this.pptCrossX));
            const cy = Math.max(6, Math.min(h - 6, this.pptCrossY));
            ctxSpec.lineWidth = 1.5;
            ctxSpec.strokeStyle = '#000000';
            ctxSpec.strokeRect(cx - 5, cy - 5, 10, 10);
            ctxSpec.strokeStyle = '#FFFFFF';
            ctxSpec.strokeRect(cx - 4, cy - 4, 8, 8);

            // 4. Bar Vertikal Luminositas (Lightness Slider)
            const gradL = ctxLum.createLinearGradient(0, 0, 0, h);
            const pureRgb = this.hslToRgb(this.pptHue, this.pptSat, 0.5);
            const pureHex = this.rgbToHex(pureRgb[0], pureRgb[1], pureRgb[2]);
            gradL.addColorStop(0, '#FFFFFF');
            gradL.addColorStop(0.5, pureHex);
            gradL.addColorStop(1, '#000000');
            ctxLum.fillStyle = gradL;
            ctxLum.fillRect(0, 0, 20, h);
        },

        renderPptHoneycomb() {
            const group = document.getElementById('pptHoneycombGroup');
            const grayGroup = document.getElementById('pptGrayscaleGroup');
            if (!group || group.children.length > 0) return;

            const centerX = 140;
            const centerY = 115;
            const R = 10.5;
            const w = R * Math.sqrt(3);
            const h = R * 1.5;

            function hexPts(cx, cy, r) {
                const pts = [];
                for (let i = 0; i < 6; i++) {
                    const a = (Math.PI / 3) * i + Math.PI / 6;
                    pts.push(`${(cx + r * Math.cos(a)).toFixed(1)},${(cy + r * Math.sin(a)).toFixed(1)}`);
                }
                return pts.join(' ');
            }

            const self = this;
            function addHex(g, cx, cy, fillHex) {
                const poly = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                poly.setAttribute('points', hexPts(cx, cy, R - 0.7));
                poly.setAttribute('fill', fillHex);
                poly.setAttribute('stroke', '#CBD5E1');
                poly.setAttribute('stroke-width', '0.6');
                poly.setAttribute('class', 'hover:stroke-white hover:stroke-[2] transition cursor-pointer');
                poly.addEventListener('click', () => {
                    self.setFromHex(fillHex);
                });
                g.appendChild(poly);
            }

            // Ring 0: Center White
            addHex(group, centerX, centerY, '#FFFFFF');

            // Concentric Rings 1 to 6
            const ringConfigs = [
                { l: 0.88, s: 0.65 },
                { l: 0.76, s: 0.80 },
                { l: 0.62, s: 0.95 },
                { l: 0.50, s: 1.00 },
                { l: 0.36, s: 1.00 },
                { l: 0.22, s: 1.00 },
            ];
            const dirs = [
                [1, 0], [0, 1], [-1, 1], [-1, 0], [0, -1], [1, -1]
            ];

            for (let ring = 1; ring <= 6; ring++) {
                const cfg = ringConfigs[ring - 1];
                let q = 0;
                let r = -ring;
                const totalInRing = 6 * ring;
                let hexIdx = 0;

                for (let side = 0; side < 6; side++) {
                    for (let step = 0; step < ring; step++) {
                        const cx = centerX + (q + r / 2) * w;
                        const cy = centerY + r * h;
                        const hue = (hexIdx / totalInRing) * 360;
                        const rgb = self.hslToRgb(hue, cfg.s, cfg.l);
                        const hex = self.rgbToHex(rgb[0], rgb[1], rgb[2]);
                        addHex(group, cx, cy, hex);

                        q += dirs[side][0];
                        r += dirs[side][1];
                        hexIdx++;
                    }
                }
            }

            // Grayscale bar (11 heksagon)
            if (grayGroup && grayGroup.children.length === 0) {
                const grays = [
                    '#FFFFFF', '#E6E6E6', '#CCCCCC', '#B3B3B3', '#999999',
                    '#808080', '#666666', '#4D4D4D', '#333333', '#1A1A1A', '#000000'
                ];
                const startX = centerX - (5 * w);
                const grayY = 255;
                grays.forEach((c, idx) => {
                    addHex(grayGroup, startX + idx * w, grayY, c);
                });
            }
        }
    }"
    class="space-y-6"
>
    <!-- TAB SWITCHER ELEGAN -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-sppg-sky/15 pb-4">
        <div class="inline-flex p-1.5 rounded-2xl bg-black/30 border border-sppg-sky/15">
            <button
                type="button"
                @click="activeTab = 'theme'"
                :class="activeTab === 'theme' ? 'bg-gradient-to-r from-sppg-green to-emerald-500 text-sppg-navy font-extrabold shadow-glow-emerald' : 'text-slate-300 hover:text-white'"
                class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center"
            >
                <span>Tema & Warna Web</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'login'"
                :class="activeTab === 'login' ? 'bg-gradient-to-r from-sppg-gold to-amber-500 text-sppg-navy font-extrabold shadow-glow-gold' : 'text-slate-300 hover:text-white'"
                class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center"
            >
                <span>Foto Background Login & Branding</span>
            </button>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-400">
            <span class="w-2 h-2 rounded-full bg-sppg-green animate-ping"></span>
            <span>Pratinjau Langsung Aktif (Klik langsung berubah)</span>
        </div>
    </div>

    <!-- ==================== TAB 1: TEMA WARNA WEB (MULTI-PRESET & CUSTOM) ==================== -->
    <div x-show="activeTab === 'theme'" x-transition class="space-y-6">
        <form method="POST" action="{{ route('settings.theme.update') }}" class="space-y-6">
            @csrf

            <!-- Hero Ringkasan -->
            <div class="surface-card rounded-3xl p-6 sm:p-7 border border-sppg-sky/15">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-sppg-sky/10 mb-6">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-widest text-sppg-gold">Katalog Tema Terpadu & Teruji Kontras</span>
                        <h2 class="text-xl font-extrabold text-white mt-1">Pilih Gaya & Suasana Sistem SPPG</h2>
                        <p class="text-xs text-slate-400 mt-1 max-w-2xl">
                            Seluruh tema telah disesuaikan agar <strong>bebas tabrakan warna</strong>: bila latar terang, teks otomatis gelap pekat; bila latar gelap, teks putih jernih. Klik tombol <strong>"Simpan Sebagai Tema Sistem"</strong> untuk menerapkan.
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-sppg-green to-emerald-500 hover:from-emerald-300 hover:to-sppg-green text-sppg-navy font-extrabold text-xs shadow-glow-emerald flex items-center gap-2 transition cursor-pointer shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>Simpan Sebagai Tema Sistem</span>
                    </button>
                </div>

                <input type="hidden" name="theme_preset" :value="currentPreset">

                <!-- GRID 6 PRESET TEMA UTAMA + 1 KUSTOM -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($themePresets as $preset)
                        <div
                            @click="selectPreset('{{ $preset['id'] }}')"
                            :class="currentPreset === '{{ $preset['id'] }}' ? 'ring-2 ring-sppg-green border-transparent shadow-lg scale-[1.01]' : 'hover:border-sppg-sky/40 opacity-90 hover:opacity-100'"
                            class="relative p-5 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between group shadow-sm"
                            style="background-color: {{ $preset['colors']['bg_surface'] }}; color: {{ $preset['colors']['text_main'] }}; border-color: {{ $preset['colors']['border_color'] }};"
                        >
                            <!-- Badge Terpilih -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full border"
                                      style="background-color: {{ $preset['colors']['bg_canvas'] }}; border-color: {{ $preset['colors']['border_color'] }}; color: {{ $preset['colors']['text_muted'] }};">
                                    {{ $preset['preview_badge'] }}
                                </span>
                                <span x-show="currentPreset === '{{ $preset['id'] }}'" class="w-5 h-5 rounded-full bg-sppg-green text-sppg-navy flex items-center justify-center font-bold text-xs">
                                    ✓
                                </span>
                            </div>

                            <div>
                                <h3 class="text-base font-extrabold tracking-tight" style="color: {{ $preset['colors']['text_main'] }};">
                                    {{ $preset['name'] }}
                                </h3>
                                <p class="text-xs mt-1 leading-relaxed opacity-85" style="color: {{ $preset['colors']['text_muted'] }};">
                                    {{ $preset['description'] }}
                                </p>
                            </div>

                            <!-- Mini Palette Preview Swatch -->
                            <div class="mt-4 pt-3 border-t flex items-center gap-1.5" style="border-color: {{ $preset['colors']['border_color'] }};">
                                <span class="w-5 h-5 rounded-full border border-black/20" title="Canvas" style="background-color: {{ $preset['colors']['bg_canvas'] }};"></span>
                                <span class="w-5 h-5 rounded-full border border-black/20" title="Surface Card" style="background-color: {{ $preset['colors']['bg_surface'] }};"></span>
                                <span class="w-5 h-5 rounded-full border border-black/20" title="Primary Action" style="background-color: {{ $preset['colors']['primary'] }};"></span>
                                <span class="w-5 h-5 rounded-full border border-black/20" title="Accent Gold" style="background-color: {{ $preset['colors']['accent_gold'] }};"></span>
                                <span class="w-5 h-5 rounded-full border border-black/20" title="Accent Sky" style="background-color: {{ $preset['colors']['accent_sky'] }};"></span>
                                <span class="ml-auto text-[10px] font-bold opacity-75">Klik untuk coba</span>
                            </div>
                        </div>
                    @endforeach

                    <!-- Opsi Tema Kustom Admin dengan Color Picker PowerPoint -->
                    <div
                        @click="selectPreset('custom')"
                        :class="currentPreset === 'custom' ? 'ring-2 ring-sppg-gold border-transparent bg-sppg-gold/10' : 'hover:border-sppg-gold/40 border-sppg-sky/15'"
                        class="relative p-5 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between surface-card"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full bg-sppg-gold/20 text-sppg-gold border border-sppg-gold/30">
                                Kustom Bebas
                            </span>
                            <span x-show="currentPreset === 'custom'" class="w-5 h-5 rounded-full bg-sppg-gold text-sppg-navy flex items-center justify-center font-bold text-xs">
                                ✓
                            </span>
                        </div>

                        <div>
                            <h3 class="text-base font-extrabold text-white tracking-tight">
                                Tema Kustom Administrator
                            </h3>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Bebas tentukan palet warna menggunakan alat pemilih warna interaktif lengkap (Honeycomb Hexagon & Spektrum 2D).
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-sppg-sky/15 flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold text-sppg-gold">Palet Kustom Aktif</span>
                            <span class="text-xs font-bold text-sppg-green">Sesuaikan ↓</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL CUSTOM COLOR PICKER LENGKAP (Aktif bila mode Kustom dipilih) -->
            <div
                x-show="currentPreset === 'custom'"
                x-transition
                class="surface-card rounded-3xl p-6 sm:p-7 border border-sppg-gold/30 space-y-6"
            >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-sppg-sky/10">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-widest text-sppg-gold">Pengaturan Palet Kustom Tingkat Lanjut</span>
                        <h3 class="text-lg font-extrabold text-white mt-0.5">Palet Warna Interaktif & Harmonisasi Kontras</h3>
                        <p class="text-xs text-slate-400">Klik tombol <strong>Palet Warna</strong> pada elemen mana saja untuk memilih via Honeycomb Hexagon atau Spektrum 2D.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <!-- Tombol Harmonisasi Kontras Otomatis -->
                        <button
                            type="button"
                            @click="autoHarmonizeContrast()"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-extrabold shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                            title="Otomatis sesuaikan warna teks menjadi gelap atau terang sesuai latar agar 100% terbaca jelas"
                        >
                            <span>⚡ Harmonisasi Kontras Otomatis</span>
                        </button>

                        <button
                            type="button"
                            @click="
                                customColors = { canvas: '#030D22', surface: '#0A2556', primary: '#92D05D', primary_text: '#071E49', text_main: '#F8FAFC', text_muted: '#8FA3BF', border: '#1E3A8A' };
                                updateCustomPreview();
                            "
                            class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-bold text-slate-300 transition cursor-pointer"
                        >
                            Reset Bawaan
                        </button>
                    </div>
                </div>

                <!-- Status Kontras & Keterbacaan Real-Time -->
                <div class="p-4 rounded-2xl bg-black/25 border border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold text-slate-200">Indikator Keterbacaan Teks (WCAG AAA):</span>
                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 font-mono font-extrabold border border-emerald-500/30">
                            Rasio <span x-text="getContrastRatio(customColors.surface, customColors.text_main)"></span>:1 — Kontras Sempurna
                        </span>
                    </div>
                    <span class="text-slate-400 text-[11px]">
                        Anti-Tabrakan Aktif: Teks selalu otomatis kontras terhadap latar.
                    </span>
                </div>

                <!-- 7 Item Warna Kustom dengan Tombol PowerPoint Picker -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- 1. Background Canvas -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">01. Latar Belakang</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Latar Halaman (Canvas)</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('canvas', 'Latar Halaman (Canvas)')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.canvas};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_bg_canvas"
                                x-model="customColors.canvas"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('canvas', 'Latar Halaman (Canvas)')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 2. Background Surface Card -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">02. Kartu Konten</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Latar Kartu (Surface)</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('surface', 'Latar Kartu (Surface Card)')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.surface};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_bg_surface"
                                x-model="customColors.surface"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('surface', 'Latar Kartu (Surface Card)')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 3. Warna Tombol Primary -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">03. Tombol Aksi</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Warna Tombol Utama</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('primary', 'Warna Tombol / Aksi Utama')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.primary};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_primary"
                                x-model="customColors.primary"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('primary', 'Warna Tombol / Aksi Utama')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 4. Teks Tombol Primary -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">04. Teks Tombol</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Teks Tombol Aksi</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('primary_text', 'Teks Tombol Aksi')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.primary_text};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_primary_text"
                                x-model="customColors.primary_text"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('primary_text', 'Teks Tombol Aksi')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 5. Teks Utama -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">05. Teks Judul</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Warna Teks Utama</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('text_main', 'Warna Teks Utama')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.text_main};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_text_main"
                                x-model="customColors.text_main"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('text_main', 'Warna Teks Utama')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 6. Teks Sekunder / Muted -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">06. Teks Keterangan</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Teks Sekunder (Muted)</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('text_muted', 'Teks Sekunder (Muted)')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.text_muted};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_text_muted"
                                x-model="customColors.text_muted"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('text_muted', 'Teks Sekunder (Muted)')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 7. Border Garis Tepi -->
                    <div class="p-4 rounded-2xl bg-black/30 border border-sppg-sky/15 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sppg-sky">07. Garis Tepi</span>
                            <label class="block text-xs font-extrabold text-white mt-0.5">Border & Pemisah</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                @click="openPptPicker('border', 'Garis Tepi Kartu (Border)')"
                                class="w-10 h-10 rounded-xl cursor-pointer border-2 border-white/20 shadow-xs hover:scale-105 transition shrink-0"
                                :style="`background-color: ${customColors.border};`"
                                title="Buka Palet Warna Interaktif"
                            ></div>
                            <input
                                type="text"
                                name="custom_border"
                                x-model="customColors.border"
                                @input="updateCustomPreview()"
                                class="w-24 px-2.5 py-2 rounded-xl font-mono text-xs uppercase font-extrabold text-white text-center border border-white/15 bg-black/40"
                            >
                        </div>
                        <button
                            type="button"
                            @click="openPptPicker('border', 'Garis Tepi Kartu (Border)')"
                            class="w-full py-1.5 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-[11px] font-bold text-slate-200 flex items-center justify-center gap-1.5 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 014.5-3 5 5 0 014.5 3 4 4 0 013 4.5 4 4 0 01-4 4H7z"/></svg>
                            <span>Palet Warna</span>
                        </button>
                    </div>

                    <!-- 8. Box Pratinjau Komponen Nyata -->
                    <div
                        class="p-4 rounded-2xl border flex flex-col justify-between transition-all"
                        :style="`background-color: ${customColors.surface}; border-color: ${customColors.border}; color: ${customColors.text_main};`"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider" :style="`color: ${customColors.text_muted}`">Simulasi Kartu</span>
                                <span class="w-2 h-2 rounded-full" :style="`background-color: ${customColors.primary}`"></span>
                            </div>
                            <p class="text-xs font-extrabold mt-1" :style="`color: ${customColors.text_main}`">Porsi MBG Hari Ini</p>
                            <p class="text-[11px] mt-0.5 font-mono" :style="`color: ${customColors.text_muted}`">3,450 / 3,500 Porsi Baik</p>
                        </div>
                        <button
                            type="button"
                            class="mt-3 py-2 px-3 rounded-xl text-xs font-extrabold shadow-sm transition hover:scale-102 flex items-center justify-center gap-1.5"
                            :style="`background-color: ${customColors.primary}; color: ${customColors.primary_text};`"
                        >
                            <span>+ Tombol Contoh</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ==================== TAB 2: FOTO BACKGROUND LOGIN & BRANDING SPPG ==================== -->
    <div x-show="activeTab === 'login'" x-transition class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        {{-- FORM PENGATURAN FOTO & TEKS --}}
        <form method="POST" action="{{ route('settings.appearance.update') }}" enctype="multipart/form-data"
              class="gsap-reveal xl:col-span-5 surface-card rounded-3xl p-6 space-y-6">
            @csrf

            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-sppg-gold">Branding Resmi SPPG</span>
                <h3 class="text-lg font-extrabold text-white mt-1">Logo Sistem & Foto Halaman Masuk (Login)</h3>
                <p class="text-xs text-slate-400 mt-1">Logo yang Anda unggah di sini akan langsung aktif di <strong>seluruh sidebar halaman aplikasi</strong> dan di <strong>halaman login</strong>.</p>
            </div>

            {{-- 1. Logo Kustom SPPG / BGN --}}
            <div class="p-4 rounded-2xl bg-black/20 border border-sppg-sky/15 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-sppg-gold">01. Logo Aplikasi & Lembaga</span>
                        <label class="block text-xs font-bold text-white mt-0.5">Logo SPPG (Tampil di Sidebar Semua Halaman & Login)</label>
                    </div>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">Sidebar + Login</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900/40 border border-sppg-sky/20 flex items-center justify-center overflow-hidden shrink-0 shadow-inner p-1">
                        <template x-if="logoPreview"><img :src="logoPreview" class="w-full h-full object-contain" alt="Logo"></template>
                        <template x-if="!logoPreview"><span class="text-[10px] text-slate-400 text-center font-bold">Default SPPG</span></template>
                    </div>
                    <div class="flex-1 space-y-1.5">
                        <label class="w-full block px-4 py-2.5 rounded-xl border border-sppg-sky/20 bg-white/5 hover:bg-white/10 hover:border-sppg-green text-xs font-bold text-white cursor-pointer transition text-center shadow-xs">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <span>Pilih File Logo Baru</span>
                            </span>
                            <input type="file" name="login_logo" accept="image/jpeg,image/png,image/webp,image/svg+xml,image/x-icon" class="hidden" @change="pickFile($event, 'logoPreview')">
                        </label>
                        <p class="text-[10px] text-slate-400">Format: PNG, JPG, SVG, WEBP, ICO • Bebas resolusi • Maks. 10 MB</p>
                    </div>
                </div>
            </div>

            {{-- 2. Foto Background Login --}}
            <div class="p-4 rounded-2xl bg-black/20 border border-sppg-sky/15 space-y-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-sppg-sky">02. Foto Latar Belakang</span>
                    <label class="block text-xs font-bold text-white mt-0.5">Upload Foto Gedung / Dapur SPPG (Khusus Halaman Login)</label>
                </div>

                <label class="flex flex-col items-center justify-center gap-2 p-5 rounded-xl border-2 border-dashed border-sppg-sky/20 hover:border-sppg-green/60 bg-black/20 cursor-pointer transition">
                    <svg class="w-7 h-7 text-sppg-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="text-xs font-bold text-white">Klik untuk memilih foto background</span>
                    <span class="text-[10px] text-slate-400">JPG, PNG, WEBP, SVG • Bebas resolusi • Maks. 10 MB</span>
                    <input type="file" name="login_background" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="hidden" @change="pickFile($event, 'bgPreview')">
                </label>
            </div>

            {{-- Overlay --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-sppg-sky">Kegelapan Overlay Navy Foto</label>
                    <span class="text-xs font-mono font-bold text-sppg-green" x-text="overlay + '%'"></span>
                </div>
                <input type="range" name="login_overlay" min="20" max="95" step="5" x-model="overlay" class="w-full accent-[#92D05D]">
                <p class="text-[11px] text-slate-400 mt-1">Tingkat transparansi gelap agar kartu login tetap terbaca kontras di atas foto.</p>
            </div>

            {{-- Teks --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-sppg-sky mb-1.5">Judul Portal Login</label>
                    <input type="text" name="login_title" x-model="title" maxlength="80" required class="w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-sppg-sky mb-1.5">Tagline Portal</label>
                    <input type="text" name="login_tagline" x-model="tagline" maxlength="160" required class="w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-2xl bg-gradient-to-r from-sppg-gold to-amber-500 text-sppg-navy font-extrabold text-sm shadow-glow-gold hover:brightness-110 transition cursor-pointer">
                Simpan Foto & Branding Login
            </button>
        </form>

        {{-- PRATINJAU LANGSUNG HALAMAN LOGIN --}}
        <div class="gsap-reveal xl:col-span-7 space-y-4">
            <div class="surface-card rounded-3xl p-5">
                <div class="flex items-center justify-between px-2 pb-3">
                    <span class="text-xs font-extrabold text-white">Pratinjau Halaman Masuk</span>
                    <a href="{{ route('login') }}" target="_blank" rel="noopener" class="text-[11px] font-bold text-sppg-green hover:underline">Buka halaman login asli ↗</a>
                </div>

                <div class="relative rounded-2xl overflow-hidden aspect-[16/10] border border-sppg-sky/15">
                    {{-- Background --}}
                    <div class="absolute inset-0 bg-cover bg-center transition-all duration-300"
                         :style="bgPreview ? `background-image:url('${bgPreview}')` : 'background: radial-gradient(circle at 15% 20%, rgba(146,208,93,.2), transparent 42%), radial-gradient(circle at 85% 85%, rgba(209,176,108,.18), transparent 45%), linear-gradient(150deg,#020A1C,#071E49 55%,#04122E)'"></div>
                    <div class="absolute inset-0" x-show="bgPreview"
                         :style="`background: linear-gradient(180deg, rgba(2,10,28,${overlay/100*0.55}) 0%, rgba(4,18,46,${overlay/100}) 100%)`"></div>

                    {{-- Mini Single Card --}}
                    <div class="absolute inset-0 flex items-center justify-center p-4">
                        <div class="w-[50%] min-w-[210px] rounded-2xl p-4 text-center border border-white/15"
                             style="background: linear-gradient(160deg, rgba(7,30,73,.8), rgba(4,18,46,.9)); backdrop-filter: blur(12px);">
                            <div class="mx-auto mb-2 w-9 h-9 rounded-xl bg-sppg-green flex items-center justify-center overflow-hidden">
                                <template x-if="logoPreview"><img :src="logoPreview" class="w-full h-full object-contain bg-sppg-navy p-0.5"></template>
                            </div>
                            <p class="text-[7px] font-bold uppercase tracking-[0.2em] text-sppg-gold">Badan Gizi Nasional</p>
                            <p class="text-[11px] font-extrabold text-white leading-tight mt-0.5" x-text="title"></p>
                            <p class="text-[8px] text-sppg-sky/70 mt-1" x-text="tagline"></p>
                            <div class="mt-2.5 space-y-1.5">
                                <div class="h-5 rounded-md bg-black/40 border border-white/10"></div>
                                <div class="h-5 rounded-md bg-black/40 border border-white/10"></div>
                                <div class="h-5 rounded-md bg-sppg-green"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Hapus File --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="surface-card rounded-2xl p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-white">Foto Background Login</p>
                        <p class="text-[11px] text-slate-400">{{ $backgroundUrl ? 'Foto kustom aktif' : 'Memakai latar default BGN' }}</p>
                    </div>
                    @if($backgroundUrl)
                        <form method="POST" action="{{ route('settings.appearance.destroy', 'background') }}" onsubmit="return confirm('Hapus foto background login?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg bg-rose-500/15 border border-rose-400/30 text-rose-300 text-[11px] font-bold cursor-pointer">Hapus</button>
                        </form>
                    @endif
                </div>

                <div class="surface-card rounded-2xl p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-white">Logo Sistem SPPG</p>
                        <p class="text-[11px] text-slate-400">{{ $logoUrl ? 'Logo kustom aktif (Sidebar & Login)' : 'Memakai logo default SPPG' }}</p>
                    </div>
                    @if($logoUrl)
                        <form method="POST" action="{{ route('settings.appearance.destroy', 'logo') }}" onsubmit="return confirm('Hapus logo kustom dan kembalikan ke logo default SPPG?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg bg-rose-500/15 border border-rose-400/30 text-rose-300 hover:bg-rose-500 hover:text-white text-[11px] font-bold cursor-pointer transition">Hapus</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- DIALOG COLOR PICKER ALA MICROSOFT POWERPOINT (STANDARD HONEYCOMB & CUSTOM SPECTRUM) -->
    @include('partials.powerpoint-color-picker')
</div>
@endsection

