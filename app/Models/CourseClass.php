<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasFactory;

    protected $table = 'course_classes';
    protected $fillable = ['class_code', 'name'];
    protected $appends = ['nama_kelas', 'kode_kelas'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'course_class_id');
    }

    public function peminjaman()
    {
        return $this->bookings();
    }

    // Accessors for backward compatibility
    public function getKodeKelasAttribute(): ?string { return $this->class_code; }
    public function getNamaKelasAttribute(): string { return $this->name; }
}
