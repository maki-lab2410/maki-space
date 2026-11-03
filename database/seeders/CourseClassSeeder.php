<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseClass;

class CourseClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['class_code' => 'A-2022', 'name' => 'Kelas A Angkatan 2022'],
            ['class_code' => 'B-2022', 'name' => 'Kelas B Angkatan 2022'],
            ['class_code' => 'C-2022', 'name' => 'Kelas C Angkatan 2022'],
            ['class_code' => 'A-2023', 'name' => 'Kelas A Angkatan 2023'],
            ['class_code' => 'B-2023', 'name' => 'Kelas B Angkatan 2023'],
            ['class_code' => 'C-2023', 'name' => 'Kelas C Angkatan 2023'],
            ['class_code' => 'A-2024', 'name' => 'Kelas A Angkatan 2024'],
            ['class_code' => 'B-2024', 'name' => 'Kelas B Angkatan 2024'],
        ];

        foreach ($classes as $class) {
            CourseClass::firstOrCreate(['class_code' => $class['class_code']], $class);
        }
    }
}
