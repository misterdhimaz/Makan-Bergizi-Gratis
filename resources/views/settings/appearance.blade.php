@extends('layouts.app')

@section('title', 'Branding Login')
@section('page_title', 'Branding Login')
@section('page_subtitle', 'Atur identitas SPPG yang tampil pada halaman masuk.')

@section('content')
@php
    $customLogoUrl = $customLogoUrl ?? $logoUrl;
@endphp
<div class="branding-settings-page space-y-6"
     x-data="{
         bgPreview: @js($backgroundUrl),
         logoPreview: @js($logoUrl),
         overlay: {{ (int) $settings['login_overlay'] }},
         title: @js($settings['login_title']),
         tagline: @js($settings['login_tagline']),
         pickFile(event, previewKey) {
             const file = event.target.files[0];
             if (!file) return;
             const acceptedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
             if (!acceptedTypes.includes(file.type)) {
                 alert('Pilih file JPG, PNG, WEBP, atau GIF.');
                 event.target.value = '';
                 return;
             }
             this[previewKey] = URL.createObjectURL(file);
         }
     }">
    <style>
        .branding-settings-page input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .branding-settings-page textarea {
            min-height: 44px;
            font-size: 16px;
        }

        .branding-settings-page button,
        .branding-settings-page .touch-target {
            min-height: 44px;
        }
    </style>

    <div class="surface-card rounded-2xl p-5 sm:p-6">
        <p class="text-sm leading-6 text-slate-600">
            Pengaturan ini hanya mengubah logo, foto latar, dan teks halaman masuk. Tampilan aplikasi tetap menggunakan tema terang SIM-MBG.
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <form method="POST" action="{{ route('settings.appearance.update') }}" enctype="multipart/form-data"
              class="xl:col-span-5 surface-card rounded-2xl p-5 sm:p-6 space-y-6">
            @csrf

            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Identitas aplikasi</p>
                <h2 class="mt-1 text-lg font-extrabold text-slate-900">Logo dan teks portal</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Lambang resmi BGN ditampilkan di sidebar dan halaman masuk; Anda dapat menggantinya melalui unggahan.</p>
            </div>

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-bold">Pengaturan belum tersimpan.</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div>
                    <label for="login_logo" class="block text-sm font-bold text-slate-800">Lambang BGN / logo pengganti</label>
                    <p class="mt-1 text-xs leading-5 text-slate-600">JPG, PNG, WEBP, atau GIF. Maksimal 10 MB.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-2">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Pratinjau logo SPPG" class="h-full w-full object-contain">
                        </template>
                        <template x-if="!logoPreview">
                            <span class="text-center text-[11px] font-semibold text-slate-500">Logo belum diatur</span>
                        </template>
                    </div>
                    <label for="login_logo" class="touch-target inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-800 hover:bg-slate-100">
                        Pilih logo
                    </label>
                    <input id="login_logo" type="file" name="login_logo" accept="image/jpeg,image/png,image/webp,image/gif"
                           class="sr-only" @change="pickFile($event, 'logoPreview')">
                </div>
                @error('login_logo')
                    <p class="text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div>
                    <label for="login_background" class="block text-sm font-bold text-slate-800">Foto latar halaman masuk</label>
                    <p class="mt-1 text-xs leading-5 text-slate-600">JPG, PNG, WEBP, atau GIF. Maksimal 10 MB.</p>
                </div>
                <label for="login_background" class="touch-target flex cursor-pointer items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-4 text-sm font-bold text-slate-800 hover:bg-slate-100">
                    Pilih foto latar
                </label>
                <input id="login_background" type="file" name="login_background" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="sr-only" @change="pickFile($event, 'bgPreview')">
                @error('login_background')
                    <p class="text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="login_overlay" class="text-sm font-bold text-slate-800">Lapisan gelap foto</label>
                    <span class="text-sm font-semibold tabular-nums text-slate-700" x-text="overlay + '%'"></span>
                </div>
                <input id="login_overlay" type="range" name="login_overlay" min="20" max="95" step="5" x-model="overlay"
                       class="w-full accent-[#061F47]">
                @error('login_overlay')
                    <p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4">
                <div>
                    <label for="login_title" class="mb-1.5 block text-sm font-bold text-slate-800">Judul halaman masuk</label>
                    <input id="login_title" type="text" name="login_title" x-model="title" maxlength="80" required
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 text-slate-900 focus:border-[#061F47] focus:outline-none focus:ring-2 focus:ring-[#061F47]/15">
                    @error('login_title')
                        <p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="login_tagline" class="mb-1.5 block text-sm font-bold text-slate-800">Tagline</label>
                    <input id="login_tagline" type="text" name="login_tagline" x-model="tagline" maxlength="160" required
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 text-slate-900 focus:border-[#061F47] focus:outline-none focus:ring-2 focus:ring-[#061F47]/15">
                    @error('login_tagline')
                        <p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-[#C8A96A] px-5 text-sm font-extrabold text-[#061F47] hover:brightness-95">
                Simpan branding login
            </button>
        </form>

        <div class="xl:col-span-7 space-y-6">
            <section class="surface-card rounded-2xl p-5 sm:p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Pratinjau halaman masuk</h2>
                        <p class="mt-1 text-sm text-slate-600">Pratinjau mengikuti perubahan pada isian.</p>
                    </div>
                    <a href="{{ route('login') }}" target="_blank" rel="noopener"
                       class="touch-target inline-flex items-center rounded-xl px-3 text-sm font-bold text-[#061F47] underline underline-offset-2">
                        Buka halaman masuk
                    </a>
                </div>

                <div class="relative flex min-h-80 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-[#061F47] p-5 sm:p-8">
                    <div class="absolute inset-0 bg-cover bg-center" x-show="bgPreview" :style="`background-image: url('${bgPreview}')`"></div>
                    <div class="absolute inset-0 bg-[#061F47]" :style="`opacity: ${overlay / 100}`"></div>
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-white/25 bg-white p-6 text-center shadow-xl">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-2">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="" class="h-full w-full object-contain">
                            </template>
                            <template x-if="!logoPreview">
                                <span class="text-[11px] font-bold text-slate-500">SIM-MBG</span>
                            </template>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">SIM-MBG · SPPG Sungai Selayur 1</p>
                        <p class="mt-2 text-lg font-extrabold text-[#061F47]" x-text="title"></p>
                        <p class="mt-1 text-sm text-slate-600" x-text="tagline"></p>
                        <div class="mt-5 space-y-3" aria-hidden="true">
                            <div class="h-10 rounded-lg border border-slate-200 bg-white"></div>
                            <div class="h-10 rounded-lg border border-slate-200 bg-white"></div>
                            <div class="h-10 rounded-lg bg-[#C8A96A]"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="surface-card rounded-2xl p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">File branding tersimpan</h2>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Foto latar</p>
                            <p class="mt-1 text-xs text-slate-600">{{ $backgroundUrl ? 'Foto khusus sedang digunakan' : 'Belum ada foto khusus' }}</p>
                        </div>
                        @if ($backgroundUrl)
                            <form method="POST" action="{{ route('settings.appearance.destroy', 'background') }}"
                                  onsubmit="return confirm('Hapus foto latar halaman masuk?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="touch-target rounded-lg border border-rose-200 bg-white px-3 text-sm font-bold text-rose-700 hover:bg-rose-50">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Lambang BGN / logo pengganti</p>
                            <p class="mt-1 text-xs text-slate-600">{{ $customLogoUrl ? 'Logo unggahan sedang digunakan' : 'Lambang BGN bawaan sedang digunakan' }}</p>
                        </div>
                        @if ($customLogoUrl)
                            <form method="POST" action="{{ route('settings.appearance.destroy', 'logo') }}"
                                  onsubmit="return confirm('Hapus logo khusus dan gunakan logo bawaan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="touch-target rounded-lg border border-rose-200 bg-white px-3 text-sm font-bold text-rose-700 hover:bg-rose-50">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
