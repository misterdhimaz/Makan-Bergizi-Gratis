<button
    type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @rekagizi-theme-changed.window="dark = $event.detail.dark"
    @click="dark = window.toggleRekagiziTheme()"
    :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
    :title="dark ? 'Mode Terang' : 'Mode Gelap'"
    class="min-w-[44px] min-h-[44px] px-sm sm:px-md inline-flex items-center justify-center gap-xs rounded-full bg-surface border border-border-soft text-ink hover:bg-surface-subtle transition-colors cursor-pointer shrink-0"
>
    <span class="material-symbols-outlined text-card-title" aria-hidden="true" x-text="dark ? 'light_mode' : 'dark_mode'">dark_mode</span>
    <span class="hidden sm:inline font-body text-label-sm" x-text="dark ? 'Terang' : 'Gelap'">Gelap</span>
</button>
