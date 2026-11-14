{{-- Indikator koneksi jaringan (online/offline) untuk topbar --}}
<div
    x-data="{
        online: navigator.onLine,
        init() {
            window.addEventListener('online', () => { this.online = true; });
            window.addEventListener('offline', () => { this.online = false; });
        },
    }"
    class="hidden sm:inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold"
    :class="online ? 'text-status-success' : 'text-status-danger'"
    :title="online ? 'Terhubung ke jaringan' : 'Tidak ada koneksi jaringan'"
    :aria-label="online ? 'Status koneksi: online' : 'Status koneksi: offline'"
    role="status"
>
    <span
        class="inline-block h-2 w-2 rounded-full"
        :class="online ? 'bg-status-success' : 'bg-status-danger animate-pulse'"
        aria-hidden="true"
    ></span>
    <span x-text="online ? 'Online' : 'Offline'"></span>
</div>
