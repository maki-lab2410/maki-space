<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $table = 'lecturers';
    protected $fillable = ['nip', 'name'];
    protected $appends = ['nama_dosen'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'lecturer_id');
    }

    public function peminjaman()
    {
        return $this->bookings();
    }

    // Accessors for backward compatibility
    public function getNamaDosenAttribute(): string
    {
        return $this->name;
    }
}
