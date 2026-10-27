<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SopConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'param_key',
        'param_name',
        'param_value',
        'unit',
        'category',
        'description',
        'updated_by',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $item = self::where('param_key', $key)->first();
        return $item ? $item->param_value : $default;
    }
}

