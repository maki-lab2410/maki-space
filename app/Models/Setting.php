<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get setting value by key, with default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Save or update setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // Backward compatibility aliases
    public static function ambil(string $key, mixed $default = null): mixed
    {
        return static::get($key, $default);
    }

    public static function simpan(string $key, mixed $value): void
    {
        static::set($key, $value);
    }
}
