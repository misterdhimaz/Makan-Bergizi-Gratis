@component('layouts.auth', ['title' => 'Ganti Kata Sandi Wajib'])
    <div class="bg-surface rounded-xl border border-border-soft p-2xl shadow-sm flex flex-col gap-lg">
        <div class="flex flex-col gap-xs">
            <span class="px-md py-xs rounded-full bg-lime-accent text-on-lime font-body text-micro w-fit">Login pertama</span>
            <h1 class="font-display text-page-title text-ink">Ganti Kata Sandi</h1>
            <p class="font-body text-body-md text-ink-muted">
                Wajib mengganti kata sandi sementara pada login pertama sebelum masuk ke sistem.
            </p>
        </div>

        @if(session('warning'))
            <div role="status" class="rounded-lg bg-alert-soft border border-border-soft p-md text-on-alert-soft font-body text-label-sm">
                {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="rounded-lg bg-danger-soft border border-border-soft p-md text-on-danger-soft font-body text-label-sm">
                <ul class="list-disc list-inside space-y-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.change.store') }}" class="flex flex-col gap-md">
            @csrf

            <div class="flex flex-col gap-xs">
                <label for="current-password" class="font-body text-label-sm text-ink">Kata sandi sementara</label>
                <input id="current-password" type="password" name="current_password" required autocomplete="current-password"
                       class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
            </div>

            <div class="flex flex-col gap-xs">
                <label for="new-password" class="font-body text-label-sm text-ink">Kata sandi baru</label>
                <input id="new-password" type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
            </div>

            <div class="flex flex-col gap-xs">
                <label for="password-confirmation" class="font-body text-label-sm text-ink">Konfirmasi kata sandi</label>
                <input id="password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded border border-border-soft bg-surface-subtle px-md py-sm font-body text-body-md text-ink focus:border-obsidian focus:ring-0">
            </div>

            <div class="rounded-lg bg-surface-subtle border border-border-soft p-md flex items-center gap-sm font-body text-micro text-ink-muted">
                <span class="material-symbols-outlined text-card-title text-ink shrink-0" aria-hidden="true">checklist</span>
                <span>
                    Aturan kata sandi: Minimal {{ $minLength ?? 8 }} karakter{{ ($requireMixedCaseNumber ?? true) ? ', ada huruf besar, huruf kecil, dan angka.' : '.' }}
                </span>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-xs px-lg py-md rounded-full bg-obsidian text-on-obsidian font-body text-label-sm hover:opacity-90 transition-opacity cursor-pointer">
                <span>Simpan &amp; Lanjut</span>
                <span class="material-symbols-outlined text-card-title" aria-hidden="true">arrow_forward</span>
            </button>
        </form>
    </div>
@endcomponent
