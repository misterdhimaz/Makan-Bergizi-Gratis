<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemSettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $record = SystemSetting::where('key', $key)->first();

        return $record ? $record->value : $default;
    }

    public function getInt(string $key, int $default): int
    {
        return (int) $this->get($key, $default);
    }

    public function getBool(string $key, bool $default): bool
    {
        $val = $this->get($key);
        if ($val === null) {
            return $default;
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }
}
