<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Jalankan dengan: php artisan db:seed
     * Atau fresh install: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // 1. Seed Default Rooms (Lab)
        $rooms = [
            ['room_code' => 'LAB-01', 'name' => 'Koding dan Reimbursement', 'color' => 'bg-blue-500',   'order' => 1, 'is_active' => true],
            ['room_code' => 'LAB-02', 'name' => 'Statistik dan Pelaporan',  'color' => 'bg-green-500',  'order' => 2, 'is_active' => true],
            ['room_code' => 'LAB-03', 'name' => 'Anatomi Fisiologi',        'color' => 'bg-yellow-500', 'order' => 3, 'is_active' => true],
            ['room_code' => 'LAB-04', 'name' => 'Ruang Serbaguna',          'color' => 'bg-purple-500', 'order' => 4, 'is_active' => true],
        ];

        foreach ($rooms as $r) {
            Room::firstOrCreate(['room_code' => $r['room_code']], $r);
        }

        // 2. Seed Default Calendar Time Slots
        $slots = [
            ['start_time' => '07:00', 'label' => '07.00 - 08.40', 'order' => 1],
            ['start_time' => '08:40', 'label' => '08.40 - 10.20', 'order' => 2],
            ['start_time' => '10:20', 'label' => '10.20 - 12.00', 'order' => 3],
            ['start_time' => '12:30', 'label' => '12.30 - 14.10', 'order' => 4],
            ['start_time' => '14:10', 'label' => '14.10 - 15.50', 'order' => 5],
            ['start_time' => '16:00', 'label' => '16.00 - 17.40', 'order' => 6],
            ['start_time' => '18:00', 'label' => 'LEBIH > 18.00', 'order' => 7],
        ];

        foreach ($slots as $s) {
            TimeSlot::firstOrCreate(['start_time' => $s['start_time']], $s);
        }

        // 3. Seed Initial Settings (gunakan nilai dari .env jika tersedia)
        $settings = [
            ['key' => 'gemini_api_key',     'value' => env('GEMINI_API_KEY', ''),          'description' => 'API Key Google Gemini (aistudio.google.com)'],
            ['key' => 'gemini_model',       'value' => 'gemini-1.5-flash',                  'description' => 'Model AI yang digunakan'],
            ['key' => 'gemini_max_tokens',  'value' => '500',                               'description' => 'Jumlah maksimum token respons AI'],
            ['key' => 'gemini_temperature', 'value' => '0.2',                               'description' => 'Kreativitas AI (0=kaku, 1=kreatif)'],
            ['key' => 'nama_lab',           'value' => 'Maki Space',                        'description' => 'Nama laboratorium untuk tampilan'],
            ['key' => 'kontak_laboran',     'value' => 'Laboran - 08xxxxxxxxxx',            'description' => 'Nomor kontak laboran'],
            ['key' => 'nama_pj',            'value' => 'Nama Laboran, A.Md.',               'description' => 'Nama penanggung jawab laboran'],
            ['key' => 'nip_pj',             'value' => 'NIP.000.00.0000.000',               'description' => 'NIP penanggung jawab'],
            ['key' => 'jabatan_pj',         'value' => 'Laboran',                           'description' => 'Jabatan penanggung jawab'],
            ['key' => 'nama_atasan',        'value' => 'Nama Kepala Lab, M.Kom',            'description' => 'Nama atasan / kepala lab'],
            ['key' => 'nip_atasan',         'value' => 'NIP.000.00.0000.001',               'description' => 'NIP atasan'],
            ['key' => 'jabatan_atasan',     'value' => 'Kepala Laboratorium',               'description' => 'Jabatan atasan'],
            ['key' => 'nama_instansi',      'value' => 'Nama Fakultas / Instansi',          'description' => 'Nama instansi / fakultas'],
        ];

        foreach ($settings as $p) {
            Setting::firstOrCreate(['key' => $p['key']], $p);
        }

        // 4. Seed Data Dummy (Dosen, Matkul, Kelas, Booking)
        $this->call([
            LecturerSeeder::class,
            CourseSeeder::class,
            CourseClassSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
