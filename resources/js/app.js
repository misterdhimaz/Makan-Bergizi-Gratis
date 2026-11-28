import Alpine from 'alpinejs';

const applyTheme = (isDark) => {
    document.documentElement.classList.toggle('dark', isDark);
    localStorage.setItem('rekagizi_theme', isDark ? 'dark' : 'light');
    window.dispatchEvent(new CustomEvent('rekagizi-theme-changed', { detail: { dark: isDark } }));
    return isDark;
};

const savedTheme = localStorage.getItem('rekagizi_theme');
if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark');
}

window.toggleRekagiziTheme = () => applyTheme(!document.documentElement.classList.contains('dark'));

window.Alpine = Alpine;
Alpine.start();
