<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AppearanceSettingController extends Controller
{
    /**
     * Definisi lengkap preset tema sistem SPPG (Kontras Tinggi & Harmonis).
     */
    public const THEME_PRESETS = [
        'sppg-navy' => [
            'id' => 'sppg-navy',
            'name' => 'SPPG Navy Resmi (BGN)',
            'description' => 'Identitas resmi Badan Gizi Nasional: Biru Navy, Hijau Segar, Emas Generasi 2045.',
            'category' => 'dark',
            'preview_badge' => 'Default Resmi',
            'colors' => [
                'bg_canvas' => '#030D22',
                'bg_surface' => '#0A2556',
                'bg_sidebar' => '#071E49',
                'bg_header' => 'rgba(4, 18, 46, 0.90)',
                'border_color' => 'rgba(181, 224, 234, 0.16)',
                'primary' => '#92D05D',
                'primary_hover' => '#B3DC8A',
                'primary_text' => '#071E49',
                'accent_gold' => '#D1B06C',
                'accent_sky' => '#B5E0EA',
                'text_main' => '#F8FAFC',
                'text_muted' => '#94A3BF',
                'input_bg' => 'rgba(2, 10, 28, 0.65)',
            ],
        ],
        'light-clean' => [
            'id' => 'light-clean',
            'name' => 'Terang Bersih (Light Clean)',
            'description' => 'Latar putih & abu terang higienis dengan teks gelap pekat dan aksen emerald segar.',
            'category' => 'light',
            'preview_badge' => 'Terang & Bersih',
            'colors' => [
                'bg_canvas' => '#F1F5F9',
                'bg_surface' => '#FFFFFF',
                'bg_sidebar' => '#FFFFFF',
                'bg_header' => 'rgba(255, 255, 255, 0.96)',
                'border_color' => '#E2E8F0',
                'primary' => '#059669',
                'primary_hover' => '#047857',
                'primary_text' => '#FFFFFF',
                'accent_gold' => '#D97706',
                'accent_sky' => '#0284C7',
                'text_main' => '#0F172A',
                'text_muted' => '#475569',
                'input_bg' => '#F8FAFC',
            ],
        ],
        'dark-obsidian' => [
            'id' => 'dark-obsidian',
            'name' => 'Gelap Mewah (Dark Obsidian)',
            'description' => 'Nuansa hitam pekat arsitektural dengan pendaran neon mint emerald dan teks putih jernih.',
            'category' => 'dark',
            'preview_badge' => 'Cyber Executive',
            'colors' => [
                'bg_canvas' => '#05080E',
                'bg_surface' => '#0D1422',
                'bg_sidebar' => '#090E17',
                'bg_header' => 'rgba(9, 14, 23, 0.92)',
                'border_color' => 'rgba(148, 163, 184, 0.18)',
                'primary' => '#10B981',
                'primary_hover' => '#34D399',
                'primary_text' => '#021F17',
                'accent_gold' => '#F59E0B',
                'accent_sky' => '#38BDF8',
                'text_main' => '#F8FAFC',
                'text_muted' => '#94A3B8',
                'input_bg' => '#070B12',
            ],
        ],
        'minimalist' => [
            'id' => 'minimalist',
            'name' => 'Minimalis Monokrom (Nordic Slate)',
            'description' => 'Desain simpel kontras tinggi hitam-putih, fokus pada tipografi data gizi dan porsi.',
            'category' => 'light',
            'preview_badge' => 'Simpel & Rapi',
            'colors' => [
                'bg_canvas' => '#F8FAFC',
                'bg_surface' => '#FFFFFF',
                'bg_sidebar' => '#0F172A',
                'bg_header' => 'rgba(255, 255, 255, 0.96)',
                'border_color' => '#E2E8F0',
                'primary' => '#0F172A',
                'primary_hover' => '#27272A',
                'primary_text' => '#FFFFFF',
                'accent_gold' => '#B45309',
                'accent_sky' => '#475569',
                'text_main' => '#0F172A',
                'text_muted' => '#475569',
                'input_bg' => '#FFFFFF',
            ],
        ],
        'royal-gold' => [
            'id' => 'royal-gold',
            'name' => 'Emas Nusantara (Generasi 2045)',
            'description' => 'Palet hangat tembaga dan emas lambang kemakmuran & masa depan gizi Indonesia.',
            'category' => 'dark',
            'preview_badge' => 'Emas Indonesia',
            'colors' => [
                'bg_canvas' => '#100C06',
                'bg_surface' => '#1D1710',
                'bg_sidebar' => '#16110A',
                'bg_header' => 'rgba(22, 17, 10, 0.92)',
                'border_color' => 'rgba(209, 176, 108, 0.22)',
                'primary' => '#D1B06C',
                'primary_hover' => '#DEC183',
                'primary_text' => '#1A1205',
                'accent_gold' => '#F59E0B',
                'accent_sky' => '#E5D3B3',
                'text_main' => '#FDFBF7',
                'text_muted' => '#C4AF91',
                'input_bg' => '#0C0905',
            ],
        ],
        'ocean-blue' => [
            'id' => 'ocean-blue',
            'name' => 'Biru Bahari (Ocean Professional)',
            'description' => 'Biru maritim korporat yang tenang dan berwibawa, dipadukan aksen cyan cerah.',
            'category' => 'dark',
            'preview_badge' => 'Biru Maritim',
            'colors' => [
                'bg_canvas' => '#07162C',
                'bg_surface' => '#0C2346',
                'bg_sidebar' => '#081B36',
                'bg_header' => 'rgba(8, 27, 54, 0.92)',
                'border_color' => 'rgba(56, 189, 248, 0.20)',
                'primary' => '#38BDF8',
                'primary_hover' => '#7DD3FC',
                'primary_text' => '#04162E',
                'accent_gold' => '#FBBF24',
                'accent_sky' => '#38BDF8',
                'text_main' => '#F0F9FF',
                'text_muted' => '#94A3B8',
                'input_bg' => 'rgba(3, 13, 28, 0.65)',
            ],
        ],
    ];

    public function edit(): View
    {
        return view('settings.appearance', [
            'settings' => AppSetting::allSettings(),
            'backgroundUrl' => AppSetting::fileUrl('login_background'),
            'logoUrl' => AppSetting::fileUrl('login_logo'),
            'themePresets' => self::THEME_PRESETS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_background' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg,gif', 'max:10240'],
            'login_logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg,gif,ico', 'max:10240'],
            'login_overlay' => ['required', 'integer', 'min:20', 'max:95'],
            'login_title' => ['required', 'string', 'max:80'],
            'login_tagline' => ['required', 'string', 'max:160'],
        ], [
            'login_background.image' => 'File background harus berupa gambar yang valid (JPG, PNG, WEBP, SVG).',
            'login_background.mimes' => 'Format foto background harus JPG, JPEG, PNG, WEBP, atau SVG.',
            'login_background.max' => 'Ukuran foto background maksimal 10 MB.',
            'login_logo.image' => 'File logo harus berupa gambar yang valid (PNG, JPG, SVG, WEBP, ICO).',
            'login_logo.mimes' => 'Format file logo harus PNG, JPG, JPEG, WEBP, SVG, atau ICO.',
            'login_logo.max' => 'Ukuran file logo maksimal 10 MB.',
            'login_overlay.required' => 'Tingkat kegelapan overlay wajib diisi.',
            'login_title.required' => 'Judul portal login wajib diisi.',
            'login_tagline.required' => 'Tagline portal login wajib diisi.',
        ]);

        $userId = Auth::id();

        foreach (['login_background' => 'branding/backgrounds', 'login_logo' => 'branding/logos'] as $key => $dir) {
            if ($request->hasFile($key)) {
                $this->deleteStoredFile($key);
                $path = $request->file($key)->store($dir, 'public');
                AppSetting::put($key, $path, $userId);
            }
        }

        AppSetting::put('login_overlay', (string) $validated['login_overlay'], $userId);
        AppSetting::put('login_title', strip_tags($validated['login_title']), $userId);
        AppSetting::put('login_tagline', strip_tags($validated['login_tagline']), $userId);

        return redirect()->route('settings.appearance')
            ->with('success', 'Pengaturan logo (aktif di semua sidebar & login) serta tampilan berhasil disimpan.');
    }

    /**
     * Simpan tema warna web pilihan Administrator (Preset atau Custom).
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_preset' => ['required', 'string', 'in:sppg-navy,light-clean,dark-obsidian,minimalist,royal-gold,ocean-blue,custom'],
            'custom_bg_canvas' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'custom_bg_surface' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'custom_primary' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'custom_primary_text' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'custom_text_main' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'custom_text_muted' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'custom_border' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $userId = Auth::id();
        AppSetting::put('theme_preset', $validated['theme_preset'], $userId);

        if ($validated['theme_preset'] === 'custom') {
            foreach (['custom_bg_canvas', 'custom_bg_surface', 'custom_primary', 'custom_primary_text', 'custom_text_main', 'custom_text_muted', 'custom_border'] as $field) {
                if (! empty($validated[$field])) {
                    AppSetting::put($field, strtoupper($validated[$field]), $userId);
                }
            }
        }

        return redirect()->route('settings.appearance')
            ->with('success', 'Tema warna sistem SPPG berhasil diperbarui dan diterapkan untuk seluruh pengguna.');
    }

    /**
     * Quick Switch Tema (Dipanggil langsung dari Topbar switcher via AJAX).
     */
    public function quickTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme_preset' => ['required', 'string', 'in:sppg-navy,light-clean,dark-obsidian,minimalist,royal-gold,ocean-blue,custom'],
        ]);

        AppSetting::put('theme_preset', $validated['theme_preset'], Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Tema sistem berhasil diubah menjadi ' . (self::THEME_PRESETS[$validated['theme_preset']]['name'] ?? 'Kustom'),
            'theme_preset' => $validated['theme_preset'],
        ]);
    }

    public function destroyFile(string $type): RedirectResponse
    {
        $key = match ($type) {
            'background' => 'login_background',
            'logo' => 'login_logo',
            default => abort(404),
        };

        $this->deleteStoredFile($key);
        AppSetting::put($key, null, Auth::id());

        return redirect()->route('settings.appearance')
            ->with('success', $type === 'background'
                ? 'Foto background login dihapus. Halaman login kembali memakai latar default SPPG.'
                : 'Logo kustom dihapus. Seluruh sidebar dan halaman login kembali memakai logo default SPPG.');
    }

    private function deleteStoredFile(string $key): void
    {
        $old = AppSetting::get($key);

        if ($old && str_starts_with($old, 'branding/') && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }
}
