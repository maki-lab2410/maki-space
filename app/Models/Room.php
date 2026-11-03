<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = ['room_code', 'name', 'color', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Color options (Tailwind classes)
    public static function colorOptions(): array
    {
        return [
            'bg-blue-500'   => '🔵 Biru',
            'bg-green-500'  => '🟢 Hijau',
            'bg-yellow-500' => '🟡 Kuning',
            'bg-red-500'    => '🔴 Merah',
            'bg-purple-500' => '🟣 Ungu',
            'bg-pink-500'   => '🩷 Pink',
            'bg-indigo-500' => '💙 Indigo',
            'bg-orange-500' => '🟠 Oranye',
            'bg-teal-500'   => '🩵 Teal',
            'bg-gray-500'   => '⚫ Abu-abu',
        ];
    }

    public static function pilihanWarna(): array
    {
        return static::colorOptions();
    }

    public function getColorDotAttribute(): string
    {
        return $this->color;
    }

    public function getWarnaDotAttribute(): string
    {
        return $this->color;
    }

    public function getColorHexAttribute(): string
    {
        return match($this->color) {
            'bg-blue-500'   => '#3b82f6',
            'bg-green-500'  => '#22c55e',
            'bg-yellow-500' => '#eab308',
            'bg-red-500'    => '#ef4444',
            'bg-purple-500' => '#a855f7',
            'bg-pink-500'   => '#ec4899',
            'bg-indigo-500' => '#6366f1',
            'bg-orange-500' => '#f97316',
            'bg-teal-500'   => '#14b8a6',
            'bg-gray-500'   => '#6b7280',
            default         => '#3b82f6',
        };
    }

    public function getWarnaHexAttribute(): string
    {
        return $this->getColorHexAttribute();
    }

    // Accessors for backward compatibility
    public function getNamaRuanganAttribute(): string { return $this->name; }
    public function getKodeRuanganAttribute(): ?string { return $this->room_code; }
    public function getWarnaAttribute(): string { return $this->color; }
    public function getUrutanAttribute(): int { return (int) $this->order; }
    public function getAktifAttribute(): bool { return (bool) $this->is_active; }
}
