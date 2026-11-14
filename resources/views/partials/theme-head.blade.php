{{--
  SIM-MBG design tokens — light-only
  Approved SIM-MBG light-only design tokens
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                },
                colors: {
                    brand: {
                        navy: '#061F47',
                        gold: '#C8A96A',
                        surface: '#FFFFFF',
                        muted: '#F1F5F9',
                    },
                    status: {
                        success: '#15803D',
                        warning: '#B45309',
                        danger: '#B91C1C',
                        info: '#1D4ED8',
                    },
                    sppg: {
                        navy: '#061F47',
                        gold: '#C8A96A',
                        green: '#15803D',
                        sky: '#1D4ED8',
                    },
                },
                boxShadow: {
                    soft: '0 1px 2px rgba(15, 23, 42, 0.06), 0 4px 12px rgba(15, 23, 42, 0.05)',
                },
                minHeight: {
                    touch: '44px',
                },
                minWidth: {
                    touch: '44px',
                },
            },
        },
    };
</script>

<style>
    :root {
        --color-brand-navy: #061F47;
        --color-brand-gold: #C8A96A;
        --color-surface: #FFFFFF;
        --color-surface-muted: #F1F5F9;
        --color-border: #E2E8F0;
        --color-text-main: #061F47;
        --color-text-muted: #64748B;
        --color-text-faint: #94A3B8;
        --color-status-success: #15803D;
        --color-status-warning: #B45309;
        --color-status-danger: #B91C1C;
        --color-status-info: #1D4ED8;
        --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        --shadow-soft: 0 1px 2px rgba(15, 23, 42, 0.06), 0 4px 12px rgba(15, 23, 42, 0.05);
        --shadow-soft-hover: 0 2px 4px rgba(15, 23, 42, 0.08), 0 8px 20px rgba(15, 23, 42, 0.08);
        --bg-canvas: var(--color-surface-muted);
        --bg-surface: var(--color-surface);
        --bg-surface-solid: var(--color-surface);
        --bg-surface-subtle: #EEF2F7;
        --bg-sidebar: var(--color-brand-navy);
        --bg-header: rgba(255, 255, 255, 0.96);
        --border-color: var(--color-border);
        --text-main: var(--color-text-main);
        --text-muted: var(--color-text-muted);
        --primary: var(--color-brand-gold);
        --primary-text: var(--color-brand-navy);
        --input-bg: #FFFFFF;
        --modal-bg: #FFFFFF;
        --chart-label: #64748B;
        --chart-grid: rgba(6, 31, 71, 0.08);
        color-scheme: light;
    }

    html, body {
        font-family: var(--font-sans);
        background-color: var(--color-surface-muted);
        color: var(--color-text-main);
        -webkit-font-smoothing: antialiased;
    }

    .tabular-nums { font-variant-numeric: tabular-nums; }

    .glass-header {
        background: var(--bg-header);
        border-bottom: 1px solid var(--color-border);
    }

    .sidebar-obsidian {
        background: var(--color-brand-navy);
        color: #F8FAFC;
    }

    .surface-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-soft);
        color: var(--color-text-main);
    }

    a.surface-card:hover,
    button.surface-card:hover {
        box-shadow: var(--shadow-soft-hover);
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 44px;
        padding: 0.625rem 1.25rem;
        border-radius: 0.75rem;
        background: var(--color-brand-gold);
        color: var(--color-brand-navy);
        font-weight: 700;
        font-size: 0.875rem;
        transition: filter 0.15s ease, opacity 0.15s ease;
    }
    .btn-primary:hover { filter: brightness(0.96); }
    .btn-primary:disabled { opacity: 0.55; cursor: wait; }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 44px;
        padding: 0.625rem 1.25rem;
        border-radius: 0.75rem;
        background: var(--color-surface);
        color: var(--color-brand-navy);
        border: 1px solid var(--color-border);
        font-weight: 600;
        font-size: 0.875rem;
    }
    .btn-secondary:hover { background: var(--color-surface-muted); }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-height: 44px;
        padding: 0.625rem 0.875rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: rgba(248, 250, 252, 0.82);
        transition: background 0.15s ease, color 0.15s ease;
    }
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }
    .nav-link-active {
        background: var(--color-brand-gold);
        color: var(--color-brand-navy);
        font-weight: 700;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .status-badge--success { background: #DCFCE7; color: #15803D; border-color: #BBF7D0; }
    .status-badge--warning { background: #FEF3C7; color: #B45309; border-color: #FDE68A; }
    .status-badge--danger { background: #FEE2E2; color: #B91C1C; border-color: #FECACA; }
    .status-badge--info { background: #DBEAFE; color: #1D4ED8; border-color: #BFDBFE; }
    .status-badge--neutral { background: #F1F5F9; color: #475569; border-color: #E2E8F0; }

    .form-control {
        width: 100%;
        min-height: 44px;
        padding: 0.625rem 0.875rem;
        border-radius: 0.75rem;
        border: 1px solid var(--color-border);
        background: #fff;
        color: var(--color-text-main);
        font-size: 16px;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--color-brand-navy);
        box-shadow: 0 0 0 3px rgba(6, 31, 71, 0.12);
    }

    :where(a, button, input, select, textarea, [tabindex]):focus-visible {
        outline: 3px solid #1D4ED8;
        outline-offset: 2px;
    }

    .form-control:focus-visible,
    main input:focus-visible,
    main select:focus-visible,
    main textarea:focus-visible {
        outline: 3px solid #1D4ED8;
        outline-offset: 2px;
    }

    .sidebar-obsidian :focus-visible {
        outline-color: #FDE68A;
    }

    .btn-logout {
        color: #B91C1C;
    }
    .btn-logout:hover {
        background: #FEE2E2;
    }

    main input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    main select,
    main textarea {
        font-size: 16px;
    }

    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--color-surface-muted); }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

    /* Compatibility: ignore legacy dark/theme JS without breaking pages */
    .gsap-page-wrapper { opacity: 1; transform: none; }
</style>

<script>
    window.setMbgTheme = function () {
        document.documentElement.setAttribute('data-theme', 'light-only');
        document.documentElement.setAttribute('data-theme-mode', 'light');
        document.documentElement.classList.remove('dark');
        try { localStorage.setItem('mbg_theme', 'light-only'); } catch (e) {}
    };
    document.documentElement.setAttribute('data-theme', 'light-only');
    document.documentElement.setAttribute('data-theme-mode', 'light');
    document.documentElement.classList.remove('dark');
</script>
