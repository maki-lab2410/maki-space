<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $table = 'time_slots';
    protected $fillable = ['start_time', 'label', 'order'];

    // Accessors for backward compatibility
    public function getJamMulaiAttribute(): string { return $this->start_time; }
    public function getUrutanAttribute(): int { return (int) $this->order; }
}
