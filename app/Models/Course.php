<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';
    protected $fillable = ['course_code', 'name'];
    protected $appends = ['nama_matkul', 'kode_matkul'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'course_id');
    }

    public function peminjaman()
    {
        return $this->bookings();
    }

    // Accessors for backward compatibility
    public function getKodeMatkulAttribute(): ?string { return $this->course_code; }
    public function getNamaMatkulAttribute(): string { return $this->name; }
}
