{{--
    TEMA ENGINE MULTI-WARNA SIM-MBG SPPG
    Mendukung preset tema:
      1. SPPG Navy Resmi (Default BGN)
      2. Terang Bersih (Light Clean)
      3. Gelap Mewah (Dark Obsidian)
      4. Minimalis Monokrom (Nordic Slate)
      5. Emas Nusantara (Generasi 2045)
      6. Kustom (Custom Color Picker)
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Urbanist', 'sans-serif'],
                    display: ['Urbanist', 'sans-serif'],
                    mono: ['"JetBrains Mono"', 'monospace'],
                },
                colors: {
                    sppg: {
                        navy: '#071E49',
                        'navy-deep': '#04122E',
                        'navy-ink': '#020A1C',
                        green: '#92D05D',
                        sky: '#B5E0EA',
                        gold: '#D1B06C',
                    },
                    emerald: {
                        50: '#F4FAEE', 100: '#E6F4D8', 200: '#CFE9B3', 300: '#B3DC8A', 400: '#92D05D',
                        500: '#7BBE45', 600: '#629E33', 700: '#4B7B29', 800: '#3D6125', 900: '#335123', 950: '#192C0F',
                    },
                    teal: {
                        50: '#F2FAFC', 100: '#E3F3F7', 200: '#B5E0EA', 300: '#8DCFDE', 400: '#5FB8CD',
                        500: '#3F9CB3', 600: '#357F97', 700: '#30687B', 800: '#2E5666', 900: '#2A4857', 950: '#1A2F3A',
                    },
                    amber: {
                        50: '#FBF8F0', 100: '#F5ECD8', 200: '#EAD8B0', 300: '#DEC183', 400: '#D1B06C',
                        500: '#C49A4C', 600: '#A97D3E', 700: '#8B6135', 800: '#734F31', 900: '#60422B', 950: '#362215',
                    },
                    indigo: {
                        50: '#EEF3FB', 100: '#D9E3F5', 200: '#B8CBEC', 300: '#8AA8DE', 400: '#6189D3',
                        500: '#4169BE', 600: '#3152A0', 700: '#284282', 800: '#1E3266', 900: '#122452', 950: '#071E49',
                    },
                },
                boxShadow: {
                    'soft': 'var(--shadow-soft)',
                    'glow-emerald': '0 10px 28px -6px rgba(146, 208, 93, 0.40)',
                    'glow-gold': '0 10px 28px -6px rgba(209, 176, 108, 0.40)',
                },
            },
        },
    };
</script>

<style>
    /* ========================================================
       SISTEM VARIABEL TEMA SPPG MULTI-PRESET & KUSTOM
       ======================================================== */

    /* 1. DEFAULT: SPPG NAVY RESMI (BGN) */
    :root,
    [data-theme="sppg-navy"] {
        --bg-canvas: #030D22;
        --bg-canvas-gradient: radial-gradient(circle at 12% 10%, rgba(146, 208, 93, 0.08) 0%, transparent 38%),
                              radial-gradient(circle at 88% 85%, rgba(209, 176, 108, 0.07) 0%, transparent 42%),
                              linear-gradient(160deg, #030D22 0%, #051738 60%, #030D22 100%);
        --bg-surface: linear-gradient(160deg, rgba(10, 37, 86, 0.76) 0%, rgba(6, 24, 58, 0.90) 100%);
        --bg-surface-solid: #0A2556;
        --bg-surface-subtle: rgba(3, 13, 34, 0.55);
        --bg-sidebar: linear-gradient(185deg, #071E49 0%, #051534 55%, #030D22 100%);
        --bg-sidebar-header: #071E49;
        --bg-header: rgba(4, 18, 46, 0.85);
        --border-color: rgba(181, 224, 234, 0.12);
        --border-hover: rgba(146, 208, 93, 0.35);
        --text-main: #F8FAFC;
        --text-muted: #8FA3BF;
        --text-faint: #5D7090;
        --primary: #92D05D;
        --primary-hover: #B3DC8A;
        --primary-text: #071E49;
        --accent-gold: #D1B06C;
        --accent-sky: #B5E0EA;
        --input-bg: rgba(2, 10, 28, 0.60);
        --modal-bg: linear-gradient(160deg, #0A2556 0%, #061A40 100%);
        --shadow-soft: 0 18px 40px -14px rgba(2, 10, 28, 0.70);
        --scrollbar-thumb: #1E3266;
        --chart-label: #8FA3BF;
        --chart-grid: rgba(181, 224, 234, 0.08);
        color-scheme: dark;
    }

    /* 2. TEMA TERANG BERSIH (LIGHT CLEAN - HIGIENIS & KLINIK GIZI) */
    [data-theme="light-clean"] {
        --bg-canvas: #F8FAFC;
        --bg-canvas-gradient: radial-gradient(circle at 10% 10%, rgba(16, 185, 129, 0.05) 0%, transparent 40%),
                              radial-gradient(circle at 90% 90%, rgba(2, 132, 199, 0.04) 0%, transparent 40%),
                              linear-gradient(180deg, #F8FAFC 0%, #F1F5F9 100%);
        --bg-surface: #FFFFFF;
        --bg-surface-solid: #FFFFFF;
        --bg-surface-subtle: #F1F5F9;
        --bg-sidebar: #FFFFFF;
        --bg-sidebar-header: #FFFFFF;
        --bg-header: rgba(255, 255, 255, 0.94);
        --border-color: #E2E8F0;
        --border-hover: rgba(5, 150, 105, 0.45);
        --text-main: #0F172A;
        --text-muted: #64748B;
        --text-faint: #94A3B8;
        --primary: #059669;
        --primary-hover: #047857;
        --primary-text: #FFFFFF;
        --accent-gold: #D97706;
        --accent-sky: #0284C7;
        --input-bg: #FFFFFF;
        --modal-bg: #FFFFFF;
        --shadow-soft: 0 10px 30px -6px rgba(15, 23, 42, 0.06), 0 4px 12px -2px rgba(15, 23, 42, 0.03);
        --scrollbar-thumb: #CBD5E1;
        --chart-label: #475569;
        --chart-grid: rgba(100, 116, 139, 0.12);
        color-scheme: light;
    }

    /* 3. TEMA GELAP MEWAH (DARK OBSIDIAN / CYBER ENTERPRISE) */
    [data-theme="dark-obsidian"] {
        --bg-canvas: #05080E;
        --bg-canvas-gradient: radial-gradient(circle at 15% 15%, rgba(16, 185, 129, 0.08) 0%, transparent 35%),
                              radial-gradient(circle at 85% 85%, rgba(56, 189, 248, 0.06) 0%, transparent 35%),
                              linear-gradient(160deg, #05080E 0%, #0A111E 60%, #05080E 100%);
        --bg-surface: linear-gradient(160deg, rgba(16, 24, 38, 0.88) 0%, rgba(9, 14, 23, 0.95) 100%);
        --bg-surface-solid: #0D1422;
        --bg-surface-subtle: rgba(9, 15, 26, 0.75);
        --bg-sidebar: linear-gradient(185deg, #090E17 0%, #060A10 100%);
        --bg-sidebar-header: #090E17;
        --bg-header: rgba(9, 14, 23, 0.88);
        --border-color: rgba(148, 163, 184, 0.14);
        --border-hover: rgba(16, 185, 129, 0.40);
        --text-main: #F1F5F9;
        --text-muted: #94A3B8;
        --text-faint: #64748B;
        --primary: #10B981;
        --primary-hover: #34D399;
        --primary-text: #021F17;
        --accent-gold: #F59E0B;
        --accent-sky: #38BDF8;
        --input-bg: #070B12;
        --modal-bg: linear-gradient(160deg, #0D1422 0%, #080D16 100%);
        --shadow-soft: 0 20px 45px -15px rgba(0, 0, 0, 0.85);
        --scrollbar-thumb: #1E293B;
        --chart-label: #94A3B8;
        --chart-grid: rgba(148, 163, 184, 0.10);
        color-scheme: dark;
    }

    /* 4. TEMA MINIMALIS SKANDINAVIA (NORDIC SLATE / MONOKROM BERSIH) */
    [data-theme="minimalist"] {
        --bg-canvas: #FAFAFA;
        --bg-canvas-gradient: #FAFAFA;
        --bg-surface: #FFFFFF;
        --bg-surface-solid: #FFFFFF;
        --bg-surface-subtle: #F4F4F5;
        --bg-sidebar: #09090B;
        --bg-sidebar-header: #09090B;
        --bg-header: rgba(255, 255, 255, 0.94);
        --border-color: #E4E4E7;
        --border-hover: #71717A;
        --text-main: #18181B;
        --text-muted: #71717A;
        --text-faint: #A1A1AA;
        --primary: #18181B;
        --primary-hover: #27272A;
        --primary-text: #FFFFFF;
        --accent-gold: #B45309;
        --accent-sky: #475569;
        --input-bg: #FFFFFF;
        --modal-bg: #FFFFFF;
        --shadow-soft: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        --scrollbar-thumb: #D4D4D8;
        --chart-label: #52525B;
        --chart-grid: rgba(113, 113, 122, 0.12);
        color-scheme: light;
    }

    /* 5. TEMA EMAS NUSANTARA (ROYAL GOLD / GENERASI EMAS 2045) */
    [data-theme="royal-gold"] {
        --bg-canvas: #100C06;
        --bg-canvas-gradient: radial-gradient(circle at 15% 15%, rgba(209, 176, 108, 0.14) 0%, transparent 40%),
                              radial-gradient(circle at 85% 85%, rgba(245, 158, 11, 0.08) 0%, transparent 40%),
                              linear-gradient(160deg, #100C06 0%, #1A140B 60%, #100C06 100%);
        --bg-surface: linear-gradient(160deg, rgba(38, 29, 16, 0.85) 0%, rgba(24, 18, 10, 0.94) 100%);
        --bg-surface-solid: #1D1710;
        --bg-surface-subtle: rgba(20, 15, 8, 0.70);
        --bg-sidebar: linear-gradient(185deg, #181209 0%, #0F0B05 100%);
        --bg-sidebar-header: #181209;
        --bg-header: rgba(24, 18, 10, 0.92);
        --border-color: rgba(209, 176, 108, 0.22);
        --border-hover: rgba(245, 158, 11, 0.45);
        --text-main: #FDFBF7;
        --text-muted: #C4AF91;
        --text-faint: #806D54;
        --primary: #D1B06C;
        --primary-hover: #DEC183;
        --primary-text: #1A1205;
        --accent-gold: #F59E0B;
        --accent-sky: #E5D3B3;
        --input-bg: #0C0905;
        --modal-bg: linear-gradient(160deg, #261D10 0%, #140E06 100%);
        --shadow-soft: 0 20px 45px -15px rgba(0, 0, 0, 0.85);
        --scrollbar-thumb: #3A2D19;
        --chart-label: #C4AF91;
        --chart-grid: rgba(209, 176, 108, 0.12);
        color-scheme: dark;
    }

    /* 6. TEMA BIRU BAHARI (OCEAN PROFESSIONAL) */
    [data-theme="ocean-blue"] {
        --bg-canvas: #07162C;
        --bg-canvas-gradient: radial-gradient(circle at 12% 12%, rgba(56, 189, 248, 0.12) 0%, transparent 40%),
                              radial-gradient(circle at 88% 88%, rgba(14, 165, 233, 0.08) 0%, transparent 40%),
                              linear-gradient(160deg, #07162C 0%, #0C2346 60%, #07162C 100%);
        --bg-surface: linear-gradient(160deg, rgba(12, 35, 70, 0.88) 0%, rgba(8, 24, 50, 0.95) 100%);
        --bg-surface-solid: #0C2346;
        --bg-surface-subtle: rgba(4, 15, 32, 0.60);
        --bg-sidebar: linear-gradient(185deg, #081B36 0%, #051326 100%);
        --bg-sidebar-header: #081B36;
        --bg-header: rgba(8, 27, 54, 0.92);
        --border-color: rgba(56, 189, 248, 0.20);
        --border-hover: rgba(56, 189, 248, 0.45);
        --text-main: #F0F9FF;
        --text-muted: #94A3B8;
        --text-faint: #64748B;
        --primary: #38BDF8;
        --primary-hover: #7DD3FC;
        --primary-text: #04162E;
        --accent-gold: #FBBF24;
        --accent-sky: #38BDF8;
        --input-bg: rgba(3, 13, 28, 0.65);
        --modal-bg: linear-gradient(160deg, #0C2346 0%, #06152B 100%);
        --shadow-soft: 0 20px 45px -15px rgba(0, 0, 0, 0.85);
        --scrollbar-thumb: #0E3666;
        --chart-label: #94A3B8;
        --chart-grid: rgba(56, 189, 248, 0.12);
        color-scheme: dark;
    }

    /* 7. TEMA KUSTOM (DIATUR OLEH WARNA KUSTOM DARI ADMIN DENGAN COLOR PICKER POWERPOINT) */
    [data-theme="custom"] {
        --bg-canvas: var(--custom-bg-canvas, #030D22);
        --bg-canvas-gradient: var(--custom-bg-canvas, #030D22);
        --bg-surface: var(--custom-bg-surface, #0A2556);
        --bg-surface-solid: var(--custom-bg-surface, #0A2556);
        --bg-surface-subtle: rgba(0, 0, 0, 0.18);
        --bg-sidebar: var(--custom-bg-surface, #0A2556);
        --bg-sidebar-header: var(--custom-bg-surface, #0A2556);
        --bg-header: var(--custom-bg-surface, #0A2556);
        --border-color: var(--custom-border, rgba(181, 224, 234, 0.15));
        --border-hover: var(--custom-primary, #92D05D);
        --text-main: var(--custom-text-main, #F8FAFC);
        --text-muted: var(--custom-text-muted, #8FA3BF);
        --text-faint: var(--custom-text-muted, #8FA3BF);
        --primary: var(--custom-primary, #92D05D);
        --primary-hover: var(--custom-primary, #92D05D);
        --primary-text: var(--custom-primary-text, #071E49);
        --accent-gold: #D1B06C;
        --accent-sky: #B5E0EA;
        --input-bg: rgba(0, 0, 0, 0.25);
        --modal-bg: var(--custom-bg-surface, #0A2556);
        --shadow-soft: 0 16px 36px -10px rgba(0, 0, 0, 0.50);
        --scrollbar-thumb: var(--custom-border, #334155);
        --chart-label: var(--custom-text-muted, #8FA3BF);
        --chart-grid: var(--custom-border, rgba(148, 163, 184, 0.12));
    }

    /* ========================================================
       APLIKASI ELEMEN GLOBAL KE VARIABEL TEMA
       ======================================================== */
    body {
        background-color: var(--bg-canvas) !important;
        background-image: var(--bg-canvas-gradient) !important;
        color: var(--text-main) !important;
        transition: background-color .3s ease, color .3s ease;
    }

    .glass-header {
        background: var(--bg-header) !important;
        border-bottom: 1px solid var(--border-color) !important;
        transition: background .3s ease, border-color .3s ease;
    }

    .sidebar-obsidian {
        background: var(--bg-sidebar) !important;
        border-right: 1px solid var(--border-color) !important;
        transition: background .3s ease, border-color .3s ease;
    }

    .surface-card {
        background: var(--bg-surface) !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: var(--shadow-soft) !important;
        color: var(--text-main) !important;
        transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease, background .3s ease;
    }
    .surface-card:hover {
        border-color: var(--border-hover) !important;
    }

    /* Penyesuaian Dinamis Tag & Komponen di Dalam Main */
    main .bg-white,
    main .bg-white\/90,
    main .bg-white\/80 {
        background-color: var(--bg-surface-solid) !important;
        border-color: var(--border-color) !important;
        color: var(--text-main) !important;
    }

    main .bg-slate-50,
    main .bg-slate-50\/90,
    main .bg-slate-50\/80,
    main .bg-slate-50\/70,
    main .bg-slate-50\/50,
    main .bg-slate-100,
    main .bg-slate-100\/80,
    main .bg-slate-100\/70 {
        background-color: var(--bg-surface-subtle) !important;
        border-color: var(--border-color) !important;
        color: var(--text-main) !important;
    }

    main .text-slate-900,
    main .text-slate-800 {
        color: var(--text-main) !important;
    }
    main .text-slate-700,
    main .text-slate-600 {
        color: var(--text-main) !important;
        opacity: 0.88;
    }
    main .text-slate-500,
    main .text-slate-400 {
        color: var(--text-muted) !important;
    }
    main .border-slate-100,
    main .border-slate-200,
    main .border-slate-200\/80,
    main .border-slate-200\/70,
    main .border-slate-200\/90,
    main .divide-slate-100 > :not([hidden]) ~ :not([hidden]) {
        border-color: var(--border-color) !important;
    }

    /* Tombol Utama Theme */
    main .bg-emerald-600,
    main .bg-emerald-500 {
        background-color: var(--primary) !important;
        color: var(--primary-text) !important;
    }
    main .bg-emerald-600:hover,
    main .hover\:bg-emerald-700:hover {
        background-color: var(--primary-hover) !important;
    }
    main [class*="from-emerald-6"].text-white,
    main .bg-emerald-600.text-white {
        color: var(--primary-text) !important;
    }

    /* Form Fields */
    main input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    main select,
    main textarea {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-main) !important;
    }
    main input::placeholder,
    main textarea::placeholder {
        color: var(--text-faint) !important;
    }
    main input:focus,
    main select:focus,
    main textarea:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(146, 208, 93, 0.20) !important;
    }
    main select option {
        background-color: var(--bg-surface-solid);
        color: var(--text-main);
    }

    /* Modal */
    .fixed .bg-white.rounded-3xl {
        background: var(--modal-bg) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
    }

    ::-webkit-scrollbar { width: 7px; height: 7px; }
    ::-webkit-scrollbar-track { background: var(--bg-canvas); }
    ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

    /* ========================================================
       RULES KONTRAS UNIVERSAL & ANTI-TABRAKAN
       (Menjamin teks terbaca 100% pada mode terang maupun gelap)
       ======================================================== */
    [data-theme-mode="light"] body {
        background-color: #F8FAFC !important;
        background-image: none !important;
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian,
    [data-theme="light-clean"] .sidebar-obsidian,
    [data-theme="minimalist"] .sidebar-obsidian {
        background-color: #FFFFFF !important;
        border-right: 1px solid #E2E8F0 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .text-white,
    [data-theme="light-clean"] .sidebar-obsidian .text-white,
    [data-theme="minimalist"] .sidebar-obsidian .text-white {
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .text-emerald-400,
    [data-theme-mode="light"] .sidebar-obsidian .text-emerald-400\/80,
    [data-theme="light-clean"] .sidebar-obsidian .text-emerald-400,
    [data-theme="minimalist"] .sidebar-obsidian .text-emerald-400 {
        color: #047857 !important;
        font-weight: 700 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .text-emerald-300,
    [data-theme="light-clean"] .sidebar-obsidian .text-emerald-300,
    [data-theme="minimalist"] .sidebar-obsidian .text-emerald-300 {
        color: #065F46 !important;
        font-weight: 700 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .bg-emerald-500\/20,
    [data-theme="light-clean"] .sidebar-obsidian .bg-emerald-500\/20,
    [data-theme="minimalist"] .sidebar-obsidian .bg-emerald-500\/20 {
        background-color: #DCFCE7 !important;
        border-color: #86EFAC !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .text-slate-500 {
        color: #64748B !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .gsap-nav-item:not([class*="bg-gradient"]) {
        color: #1E293B !important; /* slate-800: tajam, pekat, anti-blur */
        font-weight: 700 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .gsap-nav-item:not([class*="bg-gradient"]):hover {
        color: #0F172A !important;
        background-color: #F1F5F9 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .gsap-nav-item:not([class*="bg-gradient"]) svg {
        color: #475569 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .gsap-nav-item:not([class*="bg-gradient"]):hover svg {
        color: #059669 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .gsap-nav-item[class*="from-sppg-gold"] {
        background: linear-gradient(to right, #D97706, #B45309) !important;
        color: #FFFFFF !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .gsap-nav-item[class*="from-sppg-green"] {
        background: linear-gradient(to right, #059669, #10B981) !important;
        color: #FFFFFF !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .bg-black\/25,
    [data-theme-mode="light"] .sidebar-obsidian .bg-white\/5,
    [data-theme="light-clean"] .sidebar-obsidian .bg-black\/25,
    [data-theme="light-clean"] .sidebar-obsidian .bg-white\/5,
    [data-theme="minimalist"] .sidebar-obsidian .bg-black\/25,
    [data-theme="minimalist"] .sidebar-obsidian .bg-white\/5 {
        background-color: #F8FAFC !important;
        border-color: #E2E8F0 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .border-emerald-500\/15 {
        border-color: #E2E8F0 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .bg-indigo-500\/20 {
        background-color: #EEF2FF !important;
        color: #4338CA !important;
        border: 1px solid #C7D2FE !important;
    }
    [data-theme-mode="light"] .sidebar-obsidian .text-indigo-300 {
        color: #4338CA !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .bg-cyan-500\/20 {
        background-color: #ECFEFF !important;
        color: #0E7490 !important;
        border: 1px solid #A5F3FC !important;
    }
    [data-theme-mode="light"] .sidebar-obsidian .text-cyan-300 {
        color: #0E7490 !important;
    }

    [data-theme-mode="light"] .sidebar-obsidian .bg-amber-500\/20 {
        background-color: #FEF3C7 !important;
        color: #B45309 !important;
        border: 1px solid #FDE68A !important;
    }
    [data-theme-mode="light"] .sidebar-obsidian .text-amber-300 {
        color: #B45309 !important;
    }

    /* TOPBAR (GLASS-HEADER) PADA MODE TERANG */
    [data-theme-mode="light"] .glass-header,
    [data-theme="light-clean"] .glass-header,
    [data-theme="minimalist"] .glass-header {
        background: rgba(255, 255, 255, 0.95) !important;
        border-bottom: 1px solid #E2E8F0 !important;
    }

    [data-theme-mode="light"] .glass-header h1,
    [data-theme-mode="light"] .glass-header .text-white,
    [data-theme-mode="light"] .glass-header .text-slate-100,
    [data-theme="light-clean"] .glass-header .text-white,
    [data-theme="light-clean"] .glass-header .text-slate-100,
    [data-theme="minimalist"] .glass-header .text-white,
    [data-theme="minimalist"] .glass-header .text-slate-100 {
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .glass-header .text-slate-400,
    [data-theme="light-clean"] .glass-header .text-slate-400,
    [data-theme="minimalist"] .glass-header .text-slate-400 {
        color: #475569 !important;
    }

    [data-theme-mode="light"] .glass-header kbd,
    [data-theme-mode="light"] .glass-header .bg-black\/60,
    [data-theme="light-clean"] .glass-header kbd,
    [data-theme="minimalist"] .glass-header kbd {
        background-color: #F1F5F9 !important;
        border: 1px solid #CBD5E1 !important;
        color: #475569 !important;
    }

    [data-theme-mode="light"] .glass-header input {
        background-color: #F8FAFC !important;
        border: 1px solid #CBD5E1 !important;
        color: #0F172A !important;
    }
    [data-theme-mode="light"] .glass-header input::placeholder {
        color: #94A3B8 !important;
    }

    [data-theme-mode="light"] .glass-header .bg-slate-900\/80,
    [data-theme-mode="light"] .glass-header .bg-slate-950\/60,
    [data-theme="light-clean"] .glass-header .bg-slate-900\/80,
    [data-theme="light-clean"] .glass-header .bg-slate-950\/60,
    [data-theme="minimalist"] .glass-header .bg-slate-900\/80,
    [data-theme="minimalist"] .glass-header .bg-slate-950\/60 {
        background-color: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .glass-header .border-emerald-500\/20,
    [data-theme-mode="light"] .glass-header .border-emerald-500\/15 {
        border-color: #E2E8F0 !important;
    }

    [data-theme-mode="light"] .glass-header .bg-emerald-500\/15,
    [data-theme-mode="light"] .glass-header .bg-emerald-500\/20 {
        background-color: #DCFCE7 !important;
        border-color: #86EFAC !important;
    }

    [data-theme-mode="light"] .glass-header .text-emerald-300,
    [data-theme-mode="light"] .glass-header .text-emerald-400 {
        color: #047857 !important;
    }

    [data-theme-mode="light"] .glass-header .bg-\[\#061A40\],
    [data-theme="light-clean"] .glass-header .bg-\[\#061A40\],
    [data-theme="minimalist"] .glass-header .bg-\[\#061A40\] {
        background: #FFFFFF !important;
        border-color: #E2E8F0 !important;
        color: #0F172A !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.15) !important;
    }

    [data-theme-mode="light"] .glass-header .bg-\[\#061A40\] .text-white,
    [data-theme="light-clean"] .glass-header .bg-\[\#061A40\] .text-white,
    [data-theme="minimalist"] .glass-header .bg-\[\#061A40\] .text-white {
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .glass-header .bg-\[\#061A40\] .bg-slate-950\/70,
    [data-theme="light-clean"] .glass-header .bg-\[\#061A40\] .bg-slate-950\/70,
    [data-theme="minimalist"] .glass-header .bg-\[\#061A40\] .bg-slate-950\/70 {
        background-color: #F8FAFC !important;
        border-color: #E2E8F0 !important;
    }

    [data-theme-mode="light"] .glass-header .bg-\[\#061A40\] .text-slate-200,
    [data-theme="light-clean"] .glass-header .bg-\[\#061A40\] .text-slate-200,
    [data-theme="minimalist"] .glass-header .bg-\[\#061A40\] .text-slate-200 {
        color: #1E293B !important;
    }

    [data-theme-mode="light"] .glass-header .bg-\[\#061A40\] .text-slate-400,
    [data-theme="light-clean"] .glass-header .bg-\[\#061A40\] .text-slate-400,
    [data-theme="minimalist"] .glass-header .bg-\[\#061A40\] .text-slate-400 {
        color: #64748B !important;
    }

    [data-theme-mode="light"] .glass-header .bg-\[\#061A40\] .border-slate-800,
    [data-theme="light-clean"] .glass-header .bg-\[\#061A40\] .border-slate-800,
    [data-theme="minimalist"] .glass-header .bg-\[\#061A40\] .border-slate-800 {
        border-color: #E2E8F0 !important;
    }

    /* KARTU KONTEN (SURFACE-CARD) PADA MODE TERANG */
    [data-theme-mode="light"] .surface-card,
    [data-theme="light-clean"] .surface-card,
    [data-theme="minimalist"] .surface-card {
        background-color: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        color: #0F172A !important;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05) !important;
    }

    [data-theme-mode="light"] .surface-card h1,
    [data-theme-mode="light"] .surface-card h2,
    [data-theme-mode="light"] .surface-card h3,
    [data-theme-mode="light"] .surface-card h4 {
        color: #0F172A !important;
    }

    /* Pastikan tombol solid bertema gelap mempertahankan teks putih kontras tinggi */
    button.text-white,
    a.text-white,
    span.text-white,
    .surface-card button.text-white,
    .surface-card a.text-white,
    .surface-card span.text-white,
    button.bg-teal-600,
    button.bg-teal-700,
    button.bg-emerald-600,
    button.bg-emerald-700,
    button.bg-indigo-600,
    button.bg-indigo-700,
    button.bg-rose-600,
    button.bg-slate-900,
    button.bg-slate-800,
    a.bg-teal-600,
    a.bg-teal-700,
    a.bg-emerald-600,
    a.bg-emerald-700,
    a.bg-indigo-600,
    a.bg-rose-600,
    a.bg-slate-900,
    a.bg-slate-800,
    .bg-slate-900.text-white,
    .bg-slate-800.text-white,
    .bg-teal-600.text-white,
    .bg-indigo-600.text-white,
    .bg-rose-600.text-white {
        color: #FFFFFF !important;
    }

    /* Khusus Elemen Logout (Bebas Tabrakan Warna di Mode Terang & Gelap) */
    .btn-logout {
        color: #BE123C !important; /* rose-700 di mode terang */
        background-color: #FFE4E6 !important; /* rose-100 lembut */
        border: 1px solid #FDA4AF !important; /* rose-300 */
        font-weight: 800 !important;
    }
    .btn-logout svg {
        color: #BE123C !important;
        stroke: #BE123C !important;
    }
    .btn-logout:hover {
        background-color: #FECDD3 !important; /* rose-200 */
        color: #9F1239 !important; /* rose-800 */
    }

    :root:not([data-theme-mode="light"]) .btn-logout {
        color: #FDA4AF !important; /* rose-300 di mode gelap */
        background-color: rgba(244, 63, 94, 0.16) !important;
        border: 1px solid rgba(244, 63, 94, 0.35) !important;
        font-weight: 800 !important;
    }
    :root:not([data-theme-mode="light"]) .btn-logout svg {
        color: #FDA4AF !important;
        stroke: #FDA4AF !important;
    }
    :root:not([data-theme-mode="light"]) .btn-logout:hover {
        background-color: rgba(244, 63, 94, 0.28) !important;
        color: #FFFFFF !important;
    }

    .sidebar-obsidian [class*="bg-gradient-"] {
        color: #FFFFFF !important;
    }

    [data-theme-mode="light"] .surface-card .text-slate-400 {
        color: #475569 !important;
    }

    [data-theme-mode="light"] .surface-card .text-slate-300 {
        color: #334155 !important;
    }

    [data-theme-mode="light"] .text-sppg-gold {
        color: #B45309 !important;
        font-weight: 800 !important;
    }

    [data-theme-mode="light"] .text-sppg-green {
        color: #047857 !important;
        font-weight: 700 !important;
    }

    [data-theme-mode="light"] .text-sppg-sky {
        color: #0369A1 !important;
        font-weight: 700 !important;
    }

    [data-theme-mode="light"] .surface-card .bg-black\/20,
    [data-theme-mode="light"] .surface-card .bg-black\/25,
    [data-theme-mode="light"] .surface-card .bg-black\/30,
    [data-theme-mode="light"] .surface-card .bg-black\/40 {
        background-color: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .surface-card input:not([type="checkbox"]):not([type="radio"]):not([type="file"]) {
        background-color: #FFFFFF !important;
        border: 1px solid #CBD5E1 !important;
        color: #0F172A !important;
    }

    [data-theme-mode="light"] button.text-slate-300 {
        color: #475569 !important;
    }
    [data-theme-mode="light"] button.text-slate-300:hover {
        color: #0F172A !important;
    }

    [data-theme-mode="light"] .surface-card .border-sppg-sky\/10,
    [data-theme-mode="light"] .surface-card .border-sppg-sky\/15,
    [data-theme-mode="light"] .surface-card .border-sppg-sky\/20 {
        border-color: #E2E8F0 !important;
    }

    [data-theme-mode="light"] .inline-flex.rounded-2xl.bg-black\/30 {
        background-color: #F1F5F9 !important;
        border: 1px solid #CBD5E1 !important;
    }
    [data-theme-mode="light"] .inline-flex.rounded-2xl.bg-black\/30 button.text-slate-300 {
        color: #475569 !important;
    }
    [data-theme-mode="light"] .inline-flex.rounded-2xl.bg-black\/30 button.text-slate-300:hover {
        color: #0F172A !important;
    }

    /* ========================================================
       KONTRAS TINGGI OTOMATIS MODE GELAP (Bebas Bentrok & Terbaca Jelas)
       ======================================================== */
    :root:not([data-theme-mode="light"]) main .text-emerald-700,
    :root:not([data-theme-mode="light"]) main .text-emerald-800,
    :root:not([data-theme-mode="light"]) main .text-emerald-900 {
        color: #6EE7B7 !important; /* emerald-300: cerah bercahaya di dark mode */
    }
    :root:not([data-theme-mode="light"]) main .text-teal-700,
    :root:not([data-theme-mode="light"]) main .text-teal-800,
    :root:not([data-theme-mode="light"]) main .text-teal-900 {
        color: #5EEAD4 !important; /* teal-300 */
    }
    :root:not([data-theme-mode="light"]) main .text-indigo-600,
    :root:not([data-theme-mode="light"]) main .text-indigo-700,
    :root:not([data-theme-mode="light"]) main .text-indigo-800 {
        color: #A5B4FC !important; /* indigo-300 */
    }
    :root:not([data-theme-mode="light"]) main .text-amber-700,
    :root:not([data-theme-mode="light"]) main .text-amber-800,
    :root:not([data-theme-mode="light"]) main .text-amber-900 {
        color: #FCD34D !important; /* amber-300 */
    }
    :root:not([data-theme-mode="light"]) main .text-rose-700,
    :root:not([data-theme-mode="light"]) main .text-rose-800 {
        color: #FDA4AF !important; /* rose-300 */
    }

    /* Badges / Pill backgrounds in Dark Mode (Anti Glare & Anti Blob) */
    :root:not([data-theme-mode="light"]) main .bg-emerald-100 {
        background-color: rgba(16, 185, 129, 0.16) !important;
        border: 1px solid rgba(16, 185, 129, 0.35) !important;
        color: #6EE7B7 !important;
    }
    :root:not([data-theme-mode="light"]) main .bg-teal-100 {
        background-color: rgba(20, 184, 166, 0.16) !important;
        border: 1px solid rgba(20, 184, 166, 0.35) !important;
        color: #5EEAD4 !important;
    }
    :root:not([data-theme-mode="light"]) main .bg-indigo-100 {
        background-color: rgba(99, 102, 241, 0.16) !important;
        border: 1px solid rgba(99, 102, 241, 0.35) !important;
        color: #A5B4FC !important;
    }
    :root:not([data-theme-mode="light"]) main .bg-amber-100 {
        background-color: rgba(245, 158, 11, 0.16) !important;
        border: 1px solid rgba(245, 158, 11, 0.35) !important;
        color: #FCD34D !important;
    }
    :root:not([data-theme-mode="light"]) main .bg-rose-100 {
        background-color: rgba(244, 63, 94, 0.16) !important;
        border: 1px solid rgba(244, 63, 94, 0.35) !important;
        color: #FDA4AF !important;
    }

    /* Table Hover in Dark Mode */
    :root:not([data-theme-mode="light"]) main tr:hover {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }

    /* ========================================================
       KONTRAS TINGGI OTOMATIS MODE TERANG (Teks Tajam & Bersih)
       ======================================================== */
    [data-theme-mode="light"] main .text-slate-400 {
        color: #475569 !important; /* slate-600 */
    }
    [data-theme-mode="light"] main .text-slate-500 {
        color: #334155 !important; /* slate-700 */
    }
    [data-theme-mode="light"] main .text-slate-600 {
        color: #1E293B !important; /* slate-800 */
    }
    [data-theme-mode="light"] main th {
        color: #475569 !important;
    }
    [data-theme-mode="light"] main .bg-slate-50\/70,
    [data-theme-mode="light"] main .bg-slate-50\/80,
    [data-theme-mode="light"] main .bg-slate-50\/90 {
        background-color: #F8FAFC !important;
        border-color: #E2E8F0 !important;
    }
</style>

@php
    $serverDefaultTheme = App\Models\AppSetting::get('theme_preset', 'sppg-navy');
    $customThemeColors = [
        'canvas' => App\Models\AppSetting::get('custom_bg_canvas', '#030D22'),
        'surface' => App\Models\AppSetting::get('custom_bg_surface', '#0A2556'),
        'primary' => App\Models\AppSetting::get('custom_primary', '#92D05D'),
        'primary_text' => App\Models\AppSetting::get('custom_primary_text', '#071E49'),
        'text_main' => App\Models\AppSetting::get('custom_text_main', '#F8FAFC'),
        'text_muted' => App\Models\AppSetting::get('custom_text_muted', '#8FA3BF'),
        'border' => App\Models\AppSetting::get('custom_border', '#1E3A8A'),
    ];
    $themePresetsConfig = App\Http\Controllers\AppearanceSettingController::THEME_PRESETS;
@endphp

{{-- SCRIPT RUNTIME THEME SWITCHER (Instan 1-Klik & Bebas Kedip / FOUC) --}}
<script>
    (function () {
        function getLuminance(hex) {
            if (!hex) return 0.5;
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
            const num = parseInt(hex, 16);
            if (isNaN(num)) return 0.5;
            const r = (num >> 16) & 255;
            const g = (num >> 8) & 255;
            const b = num & 255;
            const a = [r, g, b].map(v => {
                v /= 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
        }

        window.getMbgLuminance = getLuminance;
        window.getMbgContrastText = function (bgHex) {
            return getLuminance(bgHex) > 0.45 ? '#0F172A' : '#F8FAFC';
        };

        function updateThemeModeAttr(themeName, customObj = null) {
            let isLight = false;
            if (themeName === 'custom' && customObj) {
                const bg = customObj.canvas || customObj.surface || '#030D22';
                isLight = getLuminance(bg) > 0.45;
            } else {
                isLight = ['light-clean', 'minimalist'].includes(themeName);
            }
            document.documentElement.setAttribute('data-theme-mode', isLight ? 'light' : 'dark');
            if (isLight) {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        }

        // Baca tema default dari server atau localStorage
        const serverDefault = {!! json_encode($serverDefaultTheme) !!};
        const savedTheme = localStorage.getItem('mbg_theme') || serverDefault;
        document.documentElement.setAttribute('data-theme', savedTheme);

        // Jika tema kustom, terapkan variabel warna kustom dari server/localStorage
        const customColors = {!! json_encode($customThemeColors) !!};

        function applyCustomVars(colors) {
            const r = document.documentElement;
            if (colors.canvas) r.style.setProperty('--custom-bg-canvas', colors.canvas);
            if (colors.surface) r.style.setProperty('--custom-bg-surface', colors.surface);
            if (colors.primary) r.style.setProperty('--custom-primary', colors.primary);
            if (colors.primary_text) r.style.setProperty('--custom-primary-text', colors.primary_text);
            if (colors.text_main) r.style.setProperty('--custom-text-main', colors.text_main);
            if (colors.text_muted) r.style.setProperty('--custom-text-muted', colors.text_muted);
            if (colors.border) r.style.setProperty('--custom-border', colors.border);
        }

        if (savedTheme === 'custom') {
            applyCustomVars(customColors);
            updateThemeModeAttr('custom', customColors);
        } else {
            updateThemeModeAttr(savedTheme);
        }

        // Global Theme Switcher Helper yang bisa dipanggil kapan saja
        window.setMbgTheme = async function (themeName, saveToServer = false, customObj = null) {
            document.documentElement.setAttribute('data-theme', themeName);
            localStorage.setItem('mbg_theme', themeName);

            if (themeName === 'custom' && customObj) {
                applyCustomVars(customObj);
                updateThemeModeAttr('custom', customObj);
            } else {
                updateThemeModeAttr(themeName);
            }

            // Update Chart.js agar kontras grafik selalu sempurna
            if (window.Chart) {
                const isLight = document.documentElement.getAttribute('data-theme-mode') === 'light';
                Chart.defaults.color = isLight ? '#475569' : '#8FA3BF';
                Chart.defaults.borderColor = isLight ? 'rgba(100, 116, 139, 0.16)' : 'rgba(181, 224, 234, 0.08)';

                // Update seluruh chart instance yang sedang aktif di halaman
                if (typeof Chart.instances === 'object') {
                    Object.values(Chart.instances).forEach(inst => {
                        try { inst.update(); } catch (e) {}
                    });
                }
            }

            // Dispatch event untuk reaktivitas Alpine.js / komponen
            window.dispatchEvent(new CustomEvent('mbg-theme-changed', { detail: { theme: themeName } }));

            // Simpan ke database jika diminta (Administrator)
            if (saveToServer) {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    await fetch('{{ route("settings.theme.quick") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ theme_preset: themeName })
                    });
                } catch (err) {
                    console.warn('Gagal menyimpan tema default ke server:', err);
                }
            }
        };

        window.MBG_THEME_PRESETS = {!! json_encode($themePresetsConfig) !!};
    })();
</script>
