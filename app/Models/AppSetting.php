<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    private const CACHE_KEY = 'app_settings.all';

    /**
     * Nilai default branding SPPG apabila belum diatur oleh Administrator.
     */
    public const DEFAULTS = [
        'login_background' => null,
        'login_logo' => null,
        'login_overlay' => '70',
        'login_title' => 'SIM-MBG SPPG Sungai Selayur 1',
        'login_tagline' => 'Sistem Informasi Monitoring Program Makan Bergizi Gratis',
        'theme_preset' => 'sppg-navy', // sppg-navy, light-clean, dark-obsidian, minimalist, royal-gold, custom
        'custom_bg_canvas' => '#030D22',
        'custom_bg_surface' => '#0A2556',
        'custom_primary' => '#92D05D',
        'custom_primary_text' => '#071E49',
        'custom_text_main' => '#F8FAFC',
        'custom_text_muted' => '#8FA3BF',
        'custom_border' => '#1E3A8A',
    ];

    /**
     * Ambil seluruh pengaturan (di-cache agar halaman tetap cepat).
     */
    public static function allSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            if (! Schema::hasTable('app_settings')) {
                return self::DEFAULTS;
            }

            return array_merge(
                self::DEFAULTS,
                self::query()->pluck('value', 'key')->toArray()
            );
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::allSettings()[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, ?string $value, ?int $userId = null): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $userId]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function set(string $key, ?string $value, ?int $userId = null): void
    {
        self::put($key, $value, $userId);
    }

    /**
     * URL publik untuk file yang disimpan di disk "public".
     */
    public static function fileUrl(string $key): ?string
    {
        $path = self::get($key);

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        // asset() mengikuti host request aktif sehingga aman untuk domain hosting apa pun
        return asset('storage/' . ltrim($path, '/')) . '?v=' . Storage::disk('public')->lastModified($path);
    }
}
