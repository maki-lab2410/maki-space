<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lecturer;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        $lecturers = [
            ['nip' => 'NIP.001.2020.001', 'name' => 'Dr. Budi Santoso, M.Kom'],
            ['nip' => 'NIP.001.2020.002', 'name' => 'Siti Rahayu, S.KM., M.Kes'],
            ['nip' => 'NIP.001.2020.003', 'name' => 'Agus Widodo, M.T'],
            ['nip' => 'NIP.001.2020.004', 'name' => 'Dewi Lestari, S.KM., M.KM'],
            ['nip' => 'NIP.001.2020.005', 'name' => 'Hendra Kurniawan, M.Sc'],
            ['nip' => 'NIP.001.2020.006', 'name' => 'Rini Marliani, S.KM., M.Kes'],
            ['nip' => 'NIP.001.2020.007', 'name' => 'Fajar Nugroho, M.Kom'],
            ['nip' => 'NIP.001.2020.008', 'name' => 'Eka Purwanti, M.KM'],
            ['nip' => 'NIP.001.2020.009', 'name' => 'Joko Prabowo, S.T., M.T'],
            ['nip' => 'NIP.001.2020.010', 'name' => 'Yuni Astuti, M.Kes'],
        ];

        foreach ($lecturers as $lecturer) {
            Lecturer::firstOrCreate(['nip' => $lecturer['nip']], $lecturer);
        }
    }
}
