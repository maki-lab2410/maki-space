<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';
    protected $guarded = ['id'];

    protected $appends = [
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'ruangan',
        'kategori_peminjam',
        'jenis_jadwal',
        'dosen_id',
        'mata_kuliah_id',
        'kelas_id',
        'nama_peminjam_bebas',
        'keperluan_bebas',
        'dosen',
        'mata_kuliah',
        'kelas',
    ];

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    // Relation aliases for backward compatibility
    public function dosen() { return $this->lecturer(); }
    public function mata_kuliah() { return $this->course(); }
    public function kelas() { return $this->courseClass(); }

    // Relationship accessors so $booking->dosen / mata_kuliah / kelas return eager loaded models and serialize to JSON
    public function getDosenAttribute()
    {
        if ($this->relationLoaded('lecturer')) {
            return $this->getRelation('lecturer');
        }
        return $this->lecturer_id ? $this->lecturer : null;
    }

    public function getMataKuliahAttribute()
    {
        if ($this->relationLoaded('course')) {
            return $this->getRelation('course');
        }
        return $this->course_id ? $this->course : null;
    }

    public function getKelasAttribute()
    {
        if ($this->relationLoaded('courseClass')) {
            return $this->getRelation('courseClass');
        }
        return $this->course_class_id ? $this->courseClass : null;
    }

    // Attribute accessors for backward compatibility
    public function getTanggalAttribute(): ?string { return $this->date; }
    public function getWaktuMulaiAttribute(): ?string { return $this->start_time; }
    public function getWaktuSelesaiAttribute(): ?string { return $this->end_time; }
    public function getRuanganAttribute(): ?string { return $this->room; }
    public function getKategoriPeminjamAttribute(): string { return $this->borrower_category === 'Lecturer' ? 'Dosen' : 'Umum'; }
    public function getJenisJadwalAttribute(): string { return $this->schedule_type === 'Regular' ? 'Tetap' : 'Insidental'; }
    public function getDosenIdAttribute(): ?int { return $this->lecturer_id; }
    public function getMataKuliahIdAttribute(): ?int { return $this->course_id; }
    public function getKelasIdAttribute(): ?int { return $this->course_class_id; }
    public function getNamaPeminjamBebasAttribute(): ?string { return $this->general_borrower_name; }
    public function getKeperluanBebasAttribute(): ?string { return $this->general_purpose; }
}
