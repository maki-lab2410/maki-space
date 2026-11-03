<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['course_code' => 'MK-001', 'name' => 'Rekam Medis Elektronik'],
            ['course_code' => 'MK-002', 'name' => 'Statistik Kesehatan'],
            ['course_code' => 'MK-003', 'name' => 'Sistem Informasi Kesehatan'],
            ['course_code' => 'MK-004', 'name' => 'Anatomi Fisiologi'],
            ['course_code' => 'MK-005', 'name' => 'Koding Klinis ICD-10'],
            ['course_code' => 'MK-006', 'name' => 'Koding Klinis ICD-9-CM'],
            ['course_code' => 'MK-007', 'name' => 'Manajemen Informasi Kesehatan'],
            ['course_code' => 'MK-008', 'name' => 'Epidemiologi'],
            ['course_code' => 'MK-009', 'name' => 'Pemrograman Komputer Kesehatan'],
            ['course_code' => 'MK-010', 'name' => 'Reimbursement dan Klaim'],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate(['course_code' => $course['course_code']], $course);
        }
    }
}
