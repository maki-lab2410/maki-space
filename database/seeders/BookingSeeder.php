<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Lecturer;
use App\Models\Course;
use App\Models\CourseClass;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data yang sudah di-seed
        $lecturers    = Lecturer::all();
        $courses      = Course::all();
        $classes      = CourseClass::all();

        if ($lecturers->isEmpty() || $courses->isEmpty() || $classes->isEmpty()) {
            $this->command->warn('Pastikan LecturerSeeder, CourseSeeder, dan CourseClassSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        $rooms     = ['LAB-01', 'LAB-02', 'LAB-03', 'LAB-04'];
        $timeSlots = [
            ['start' => '07:00:00', 'end' => '08:40:00'],
            ['start' => '08:40:00', 'end' => '10:20:00'],
            ['start' => '10:20:00', 'end' => '12:00:00'],
            ['start' => '12:30:00', 'end' => '14:10:00'],
            ['start' => '14:10:00', 'end' => '15:50:00'],
        ];

        // Minggu ini (Senin s/d Jumat)
        $monday = Carbon::now()->startOfWeek(); // Senin minggu ini

        // --- Booking Dosen (Jadwal Regular) ---
        $lecturerBookings = [
            // Senin
            [
                'date'           => $monday->copy()->format('Y-m-d'),
                'room'           => 'LAB-01',
                'slot'           => $timeSlots[0],
                'lecturer_idx'   => 0,
                'course_idx'     => 0,
                'class_idx'      => 0,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            [
                'date'           => $monday->copy()->format('Y-m-d'),
                'room'           => 'LAB-02',
                'slot'           => $timeSlots[1],
                'lecturer_idx'   => 1,
                'course_idx'     => 1,
                'class_idx'      => 1,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            // Selasa
            [
                'date'           => $monday->copy()->addDay()->format('Y-m-d'),
                'room'           => 'LAB-01',
                'slot'           => $timeSlots[2],
                'lecturer_idx'   => 2,
                'course_idx'     => 2,
                'class_idx'      => 2,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            [
                'date'           => $monday->copy()->addDay()->format('Y-m-d'),
                'room'           => 'LAB-03',
                'slot'           => $timeSlots[0],
                'lecturer_idx'   => 3,
                'course_idx'     => 3,
                'class_idx'      => 3,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            // Rabu
            [
                'date'           => $monday->copy()->addDays(2)->format('Y-m-d'),
                'room'           => 'LAB-02',
                'slot'           => $timeSlots[3],
                'lecturer_idx'   => 4,
                'course_idx'     => 4,
                'class_idx'      => 4,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            [
                'date'           => $monday->copy()->addDays(2)->format('Y-m-d'),
                'room'           => 'LAB-04',
                'slot'           => $timeSlots[1],
                'lecturer_idx'   => 5,
                'course_idx'     => 5,
                'class_idx'      => 5,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            // Kamis
            [
                'date'           => $monday->copy()->addDays(3)->format('Y-m-d'),
                'room'           => 'LAB-01',
                'slot'           => $timeSlots[4],
                'lecturer_idx'   => 6,
                'course_idx'     => 6,
                'class_idx'      => 6,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            [
                'date'           => $monday->copy()->addDays(3)->format('Y-m-d'),
                'room'           => 'LAB-03',
                'slot'           => $timeSlots[2],
                'lecturer_idx'   => 7,
                'course_idx'     => 7,
                'class_idx'      => 0,
                'schedule_type'  => 'Incidental',
                'status'         => 'Scheduled',
            ],
            // Jumat
            [
                'date'           => $monday->copy()->addDays(4)->format('Y-m-d'),
                'room'           => 'LAB-02',
                'slot'           => $timeSlots[0],
                'lecturer_idx'   => 8,
                'course_idx'     => 8,
                'class_idx'      => 1,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
            [
                'date'           => $monday->copy()->addDays(4)->format('Y-m-d'),
                'room'           => 'LAB-04',
                'slot'           => $timeSlots[3],
                'lecturer_idx'   => 9,
                'course_idx'     => 9,
                'class_idx'      => 2,
                'schedule_type'  => 'Regular',
                'status'         => 'Scheduled',
            ],
        ];

        foreach ($lecturerBookings as $b) {
            Booking::firstOrCreate(
                [
                    'room'       => $b['room'],
                    'date'       => $b['date'],
                    'start_time' => $b['slot']['start'],
                ],
                [
                    'end_time'          => $b['slot']['end'],
                    'borrower_category' => 'Lecturer',
                    'schedule_type'     => $b['schedule_type'],
                    'lecturer_id'       => $lecturers[$b['lecturer_idx']]->id,
                    'course_id'         => $courses[$b['course_idx']]->id,
                    'course_class_id'   => $classes[$b['class_idx']]->id,
                    'status'            => $b['status'],
                ]
            );
        }

        // --- Booking Umum (Public) ---
        $publicBookings = [
            [
                'date'                   => $monday->copy()->addDays(1)->format('Y-m-d'),
                'room'                   => 'LAB-04',
                'slot'                   => $timeSlots[4],
                'general_borrower_name'  => 'Panitia Seminar K3 Kampus',
                'general_purpose'        => 'Rapat persiapan seminar kesehatan kerja',
                'status'                 => 'Scheduled',
            ],
            [
                'date'                   => $monday->copy()->addDays(3)->format('Y-m-d'),
                'room'                   => 'LAB-02',
                'slot'                   => $timeSlots[4],
                'general_borrower_name'  => 'Himpunan Mahasiswa Program Studi',
                'general_purpose'        => 'Latihan presentasi tugas akhir',
                'status'                 => 'Scheduled',
            ],
        ];

        foreach ($publicBookings as $b) {
            Booking::firstOrCreate(
                [
                    'room'       => $b['room'],
                    'date'       => $b['date'],
                    'start_time' => $b['slot']['start'],
                ],
                [
                    'end_time'              => $b['slot']['end'],
                    'borrower_category'     => 'Public',
                    'schedule_type'         => 'Incidental',
                    'general_borrower_name' => $b['general_borrower_name'],
                    'general_purpose'       => $b['general_purpose'],
                    'status'                => $b['status'],
                ]
            );
        }

        $total = count($lecturerBookings) + count($publicBookings);
        $this->command->info("✅ {$total} dummy booking berhasil di-seed untuk minggu ini.");
    }
}
