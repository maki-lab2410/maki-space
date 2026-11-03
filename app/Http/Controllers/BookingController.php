<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Lecturer;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\TimeSlot;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    public function index(Request $request){
        Carbon::setLocale('en');
        $currentDate = $request->has('date') ? Carbon::parse($request->date)->startOfMonth() : Carbon::now()->startOfMonth();

        // ─── Ambil daftar ruangan dari database ──────────────────────────────
        $daftarRuanganDb = Room::orderBy('order')->orderBy('name')->get();
        $ruanganDefault  = $daftarRuanganDb->first()?->name ?? 'Ruangan';
        $ruanganAktif    = $request->query('ruangan', $ruanganDefault);

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth   = $currentDate->copy()->endOfMonth();

        $hariSebulan = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $hariSebulan[] = [
                'tanggal_asli' => $date->format('Y-m-d'),
                'format_tampil' => $date->translatedFormat('l, d M Y')
            ];
        }

        $bulan = $currentDate->month;
        $tahun = $currentDate->year;
        if ($bulan >= 8 || $bulan == 1) {
            $namaSemester = __('Odd Semester');
            $tahunAjaran = $bulan == 1 ? ($tahun - 1) . '/' . $tahun : $tahun . '/' . ($tahun + 1);
        } else {
            $namaSemester = __('Even Semester');
            $tahunAjaran = ($tahun - 1) . '/' . $tahun;
        }
        $teksSemester = "$namaSemester $tahunAjaran";

        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m-d');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m-d');
        $bulanTampil = $currentDate->translatedFormat('F Y');

        // ─── Data Jadwal Bulan Ini (Semua Ruangan & Ruangan Aktif) ────────────
        $semuaJadwalBulanIni = Booking::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                        ->with(['lecturer', 'course', 'courseClass'])
                        ->get();

        $semuaJadwal = $semuaJadwalBulanIni->where('room', $ruanganAktif);

        // ─── Data Khusus Fitur Split View Antar Ruangan ──────────────────────
        $viewMode           = $request->query('view', 'calendar'); // 'calendar' atau 'split'
        $splitDate          = $request->query('split_date', Carbon::today()->format('Y-m-d'));
        $splitDateCarbon    = Carbon::parse($splitDate);
        $splitDateFormatted = $splitDateCarbon->translatedFormat('l, d F Y');
        $splitDatePrev      = $splitDateCarbon->copy()->subDay()->format('Y-m-d');
        $splitDateNext      = $splitDateCarbon->copy()->addDay()->format('Y-m-d');

        // Jadwal pada tanggal split view terpilih untuk semua ruangan
        $jadwalSplitDate = Booking::where('date', $splitDate)
                        ->with(['lecturer', 'course', 'courseClass'])
                        ->get();

        $daftarDosen  = Lecturer::orderBy('name')->get();
        $daftarMatkul = Course::orderBy('name')->get();
        $daftarKelas  = CourseClass::orderBy('name')->get();

        // ─── Ambil jam slot dari database ────────────────────────────────────
        $daftarJamSlot = TimeSlot::orderBy('order')->get();

        // ─── Ambil pengaturan sistem ─────────────────────────────────────────
        $namaLab           = Setting::get('nama_lab', 'Maki Space');
        $namaInstansi      = Setting::get('nama_instansi', 'Nama Instansi / Fakultas');
        $logoPath          = Setting::get('logo', 'images/logo.png');
        $kontakLaboran     = Setting::get('kontak_laboran', '');
        $filterYearsFuture = (int) Setting::get('filter_years_future', 2);
        $filterYearsPast   = (int) Setting::get('filter_years_past', 3);

        // ─── Cek apakah user saat ini adalah admin ───────────────────────────
        $isAdmin = Session::get('admin_logged_in', false);

        // ─── Deteksi Perangkat (Smartphone vs Desktop/Tablet) ────────────────
        $userAgent = $request->header('User-Agent', '');
        // Tablet seperti iPad atau Android Tablet tetap disajikan versi Desktop/Tablet
        $isTablet = (bool) preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $userAgent);
        // Smartphone (Android Mobile, iPhone, iPod, BlackBerry, Windows Phone, Opera Mini, dll.)
        $isMobilePhone = !$isTablet && (bool) preg_match('/(android.*mobile|iphone|ipod|blackberry|iemobile|opera mini|mobile)/i', $userAgent);

        // Dukungan manual switch via query string (?mode=phone atau ?mode=desktop)
        if ($request->has('mode')) {
            $isMobilePhone = ($request->query('mode') === 'phone');
            Session::put('device_mode', $isMobilePhone ? 'phone' : 'desktop');
        } elseif (Session::has('device_mode')) {
            $isMobilePhone = (Session::get('device_mode') === 'phone');
        }

        $viewName = $isMobilePhone ? 'booking.phone' : 'booking.index';

        return view($viewName, compact(
            'hariSebulan', 'teksSemester', 'bulanTampil', 'prevMonth', 'nextMonth',
            'semuaJadwal', 'semuaJadwalBulanIni', 'ruanganAktif', 'daftarDosen', 'daftarMatkul', 'daftarKelas',
            'isAdmin', 'daftarRuanganDb', 'daftarJamSlot', 'namaLab', 'namaInstansi', 'logoPath', 'kontakLaboran',
            'viewMode', 'splitDate', 'splitDateFormatted', 'splitDatePrev', 'splitDateNext', 'jadwalSplitDate',
            'filterYearsFuture', 'filterYearsPast', 'isMobilePhone'
        ));
    }

    public function print(Request $request){
        Carbon::setLocale(app()->getLocale());
        $currentDate = $request->has('date') ? Carbon::parse($request->date)->startOfMonth() : Carbon::now()->startOfMonth();
        $ruanganDefault = Room::orderBy('order')->first()?->name ?? 'Ruangan';
        $ruanganAktif = $request->query('ruangan', $ruanganDefault);
        
        // Menangkap filter jenis jadwal
        $jenis = $request->query('jenis'); 

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth   = $currentDate->copy()->endOfMonth();

        $bulan = $currentDate->month;
        $tahun = $currentDate->year;
        $namaSemester = ($bulan >= 8 || $bulan == 1) ? __('Odd Semester') : __('Even Semester');
        $tahunAjaran  = ($bulan >= 8) ? $tahun . '/' . ($tahun + 1) : ($tahun - 1) . '/' . $tahun;
        $teksSemester = "$namaSemester $tahunAjaran";
        $bulanTampil  = $currentDate->translatedFormat('F Y');

        $namaLab       = Setting::get('nama_lab', 'Maki Space');
        $namaInstansi  = Setting::get('nama_instansi', 'Nama Instansi / Fakultas');
        $logoPath      = Setting::get('logo', 'images/logo.png');
        $kontakLaboran = Setting::get('kontak_laboran', '');
        $daftarJamSlot = TimeSlot::orderBy('order')->get();

        // ─── Data Penanggung Jawab & Atasan untuk Tanda Tangan Cetak ─────────
        $namaPj        = Setting::get('nama_pj', '');
        $nipPj         = Setting::get('nip_pj', '');
        $jabatanPj     = Setting::get('jabatan_pj', 'Penanggung Jawab ' . $namaLab);
        $namaAtasan    = Setting::get('nama_atasan', '');
        $nipAtasan     = Setting::get('nip_atasan', '');
        $jabatanAtasan = Setting::get('jabatan_atasan', 'Ketua Program Studi');

        // Query Database
        $semuaJadwal = Booking::where('room', $ruanganAktif)
                        ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                        ->when($jenis, function ($query, $jenis) {
                            $schedType = in_array($jenis, ['Tetap', 'Regular']) ? 'Regular' : 'Incidental';
                            return $query->where('schedule_type', $schedType);
                        })
                        ->with(['lecturer', 'course', 'courseClass'])
                        ->orderBy('date')
                        ->orderBy('start_time')
                        ->get();

        // ─── LOGIKA KHUSUS: MASTER JADWAL TETAP (MINGGUAN) ───
        $jadwalTetapMingguan = collect();
        
        if ($jenis == 'Tetap' || $jenis == 'Regular') {
            $unik = [];
            foreach ($semuaJadwal as $j) {
                $dayOfWeek = Carbon::parse($j->date)->dayOfWeekIso; // 1 = Senin, 7 = Minggu
                $jamMulai = substr($j->start_time, 0, 5);
                
                // FILTER: Hanya Senin (1) sd Jumat (5) DAN Jam mulai sebelum 18:00
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5 && $jamMulai < '18:00') {
                    $key = $dayOfWeek . '-' . $jamMulai . '-' . $j->course_class_id;
                    
                    if (!isset($unik[$key])) {
                        $j->hari_angka = $dayOfWeek;
                        $j->hari_nama  = Carbon::parse($j->date)->translatedFormat('l');
                        $unik[$key] = $j;
                        $jadwalTetapMingguan->push($j);
                    }
                }
            }
            
            // Urutkan berdasarkan Hari (Senin-Jumat) lalu Jam, lalu kelompokkan
            $jadwalTetapMingguan = $jadwalTetapMingguan->sortBy([
                ['hari_angka', 'asc'],
                ['start_time', 'asc']
            ])->groupBy('hari_nama');
        }

        return view('booking.print', compact(
            'teksSemester', 'bulanTampil', 'semuaJadwal', 'jadwalTetapMingguan',
            'ruanganAktif', 'jenis', 'namaLab', 'kontakLaboran', 'daftarJamSlot',
            'namaPj', 'nipPj', 'jabatanPj', 'namaAtasan', 'nipAtasan', 'jabatanAtasan'
        ));
    }

    public function store(Request $request){
        $isLecturer = ($request->kategori_peminjam == 'Dosen' || $request->borrower_category == 'Lecturer');

        // ── Validasi fleksibel sesuai kategori peminjam ──────────────────────
        if ($isLecturer) {
            $request->validate([
                'ruangan'     => 'required|string|max:150',
                'tanggal'     => 'required|date',
                'waktu_mulai' => 'required|string',
            ], [
                'ruangan.required'     => 'Ruangan laboratorium wajib dipilih.',
                'tanggal.required'     => 'Tanggal jadwal wajib dipilih.',
                'waktu_mulai.required' => 'Jam mulai wajib dipilih.',
            ]);
        } else {
            $request->validate([
                'ruangan'             => 'required|string|max:150',
                'tanggal'             => 'required|date',
                'nama_peminjam_bebas' => 'required|string|max:150',
                'keperluan_bebas'     => 'required|string|max:200',
            ], [
                'ruangan.required'             => 'Ruangan laboratorium wajib dipilih.',
                'tanggal.required'             => 'Tanggal jadwal wajib dipilih.',
                'nama_peminjam_bebas.required' => 'Nama peminjam / organisasi wajib diisi.',
                'keperluan_bebas.required'     => 'Keperluan kegiatan wajib diisi.',
            ]);
        }

        $ruangan     = trim($request->ruangan ?? $request->room);
        $ulangi      = min(16, max(1, (int) $request->input('ulangi', 1)));
        $tanggalAwal = Carbon::parse($request->tanggal ?? $request->date);
        $sks         = (int) ($request->sks ?: 2);

        if ($isLecturer) {
            $waktuMulai   = Carbon::parse($request->waktu_mulai ?? $request->start_time ?? '07:00');
            $waktuSelesai = $waktuMulai->copy()->addMinutes($sks * 50)->format('H:i:s');
            $waktuMulai   = $waktuMulai->format('H:i:s');
        } else {
            $wmBebas      = $request->waktu_mulai_bebas ?: '07:00';
            $wsBebas      = $request->waktu_selesai_bebas ?: '08:40';
            $waktuMulai   = Carbon::parse($wmBebas)->format('H:i:s');
            $waktuSelesai = Carbon::parse($wsBebas)->format('H:i:s');
        }

        // Sanitasi ID integer agar tidak pernah mengirim string kosong "" ke kolom integer MySQL
        $dosenId  = ($isLecturer && !empty($request->dosen_id)) ? (int) $request->dosen_id : (!empty($request->lecturer_id) ? (int) $request->lecturer_id : null);
        $matkulId = ($isLecturer && !empty($request->mata_kuliah_id)) ? (int) $request->mata_kuliah_id : (!empty($request->course_id) ? (int) $request->course_id : null);
        $kelasId  = ($isLecturer && !empty($request->kelas_id)) ? (int) $request->kelas_id : (!empty($request->course_class_id) ? (int) $request->course_class_id : null);

        $jadwalDisimpan = [];

        for ($i = 0; $i < $ulangi; $i++) {
            $tanggalCek = $tanggalAwal->copy()->addWeeks($i)->format('Y-m-d');

            $bentrok = Booking::where('room', $ruangan)
                ->where('date', $tanggalCek)
                ->where(function($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('start_time', '<', $waktuSelesai)
                      ->where('end_time', '>', $waktuMulai);
                })->first();

            if ($bentrok) {
                $peminjam   = $bentrok->lecturer->name ?? $bentrok->general_borrower_name ?? 'Kegiatan lain';
                $kegiatan   = $bentrok->course->name ?? $bentrok->general_purpose ?? 'Jadwal lab';
                $tglBentrok = Carbon::parse($tanggalCek)->format('d-m-Y');

                return redirect()->back()
                    ->withInput()
                    ->with('error', "Gagal simpan! Ruangan sudah terisi pada $tglBentrok jam " . substr($bentrok->start_time, 0, 5) . " - " . substr($bentrok->end_time, 0, 5) . " oleh $peminjam ($kegiatan).")
                    ->with('conflicting_id', $bentrok->id);
            }

            $jadwalDisimpan[] = [
                'room'                  => $ruangan,
                'date'                  => $tanggalCek,
                'start_time'            => $waktuMulai,
                'end_time'              => $waktuSelesai,
                'borrower_category'     => $isLecturer ? 'Lecturer' : 'Public',
                'schedule_type'         => in_array($request->schedule_type ?? $request->jenis_jadwal, ['Regular', 'Tetap']) ? 'Regular' : 'Incidental',
                'course_class_id'       => $kelasId,
                'lecturer_id'           => $dosenId,
                'course_id'             => $matkulId,
                'general_borrower_name' => $isLecturer ? null : ($request->general_borrower_name ?? $request->nama_peminjam_bebas),
                'general_purpose'       => $isLecturer ? null : ($request->general_purpose ?? $request->keperluan_bebas),
                'status'                => 'Scheduled',
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }

        $newIds = [];
        foreach ($jadwalDisimpan as $data) {
            $saved    = Booking::create($data);
            $newIds[] = $saved->id;
        }

        // Simpan ID jadwal ke session pembuat untuk hak akses edit/hapus dalam window 2 menit
        $myBookings = session('my_booking_ids', []);
        if (!is_array($myBookings)) {
            $myBookings = [];
        }
        $myBookings = array_unique(array_merge($myBookings, $newIds));
        session(['my_booking_ids' => $myBookings]);

        $pesanSukses = $ulangi > 1
            ? "Berhasil! Jadwal telah disimpan untuk $ulangi minggu ke depan."
            : "Berhasil! Jadwal telah berhasil disimpan.";

        return redirect(url('/?ruangan=' . urlencode($ruangan) . '&date=' . $tanggalAwal->format('Y-m-d')))
            ->with('success', $pesanSukses)
            ->with('new_booking_ids', $newIds);
    }

    public function update(Request $request, $id){
        $booking = Booking::findOrFail($id);
        $isAdmin = Session::get('admin_logged_in', false);

        // ─── Jika bukan admin, pastikan jadwal ini dibuat oleh sesi pengguna & masih dalam window 2 menit ────
        if (!$isAdmin) {
            $myBookings = session('my_booking_ids', []);
            if (!is_array($myBookings) || !in_array((int) $booking->id, array_map('intval', $myBookings), true)) {
                return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah jadwal ini.');
            }

            $selisihMenit = Carbon::parse($booking->created_at)->diffInMinutes(now());
            if ($selisihMenit > 2) {
                return redirect()->back()->with('error', 'Batas waktu ubah (2 menit) telah berakhir. Silakan hubungi admin laboratorium.');
            }
        }

        $isLecturer = ($request->kategori_peminjam == 'Dosen' || $request->borrower_category == 'Lecturer');

        // Validasi input pembaruan jadwal
        if ($isLecturer) {
            $request->validate([
                'dosen_id'        => 'nullable|integer',
                'mata_kuliah_id'  => 'nullable|integer',
                'kelas_id'        => 'nullable|integer',
                'schedule_type'   => 'nullable|string|max:50',
            ]);
        } else {
            $request->validate([
                'nama_peminjam_bebas' => 'required|string|max:150',
                'keperluan_bebas'     => 'required|string|max:200',
                'schedule_type'       => 'nullable|string|max:50',
            ]);
        }

        $dosenId  = ($isLecturer && !empty($request->dosen_id)) ? (int) $request->dosen_id : (!empty($request->lecturer_id) ? (int) $request->lecturer_id : null);
        $matkulId = ($isLecturer && !empty($request->mata_kuliah_id)) ? (int) $request->mata_kuliah_id : (!empty($request->course_id) ? (int) $request->course_id : null);
        $kelasId  = ($isLecturer && !empty($request->kelas_id)) ? (int) $request->kelas_id : (!empty($request->course_class_id) ? (int) $request->course_class_id : null);

        $booking->update([
            'borrower_category'     => $isLecturer ? 'Lecturer' : 'Public',
            'schedule_type'         => in_array($request->schedule_type ?? $request->jenis_jadwal, ['Regular', 'Tetap']) ? 'Regular' : 'Incidental',
            'lecturer_id'           => $dosenId,
            'course_id'             => $matkulId,
            'course_class_id'       => $kelasId,
            'general_borrower_name' => $isLecturer ? null : ($request->general_borrower_name ?? $request->nama_peminjam_bebas),
            'general_purpose'       => $isLecturer ? null : ($request->general_purpose ?? $request->keperluan_bebas),
        ]);

        return redirect()->back()->with('success', 'Rincian jadwal berhasil diperbarui!');
    }

    public function destroy($id){
        $booking = Booking::findOrFail($id);
        $isAdmin = Session::get('admin_logged_in', false);

        // ─── Jika bukan admin, pastikan jadwal dibuat oleh sesi pengguna & masih dalam 2 menit ────────────────
        if (!$isAdmin) {
            $myBookings = session('my_booking_ids', []);
            if (!is_array($myBookings) || !in_array((int) $booking->id, array_map('intval', $myBookings), true)) {
                return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk menghapus jadwal ini.');
            }

            $selisihMenit = Carbon::parse($booking->created_at)->diffInMinutes(now());
            if ($selisihMenit > 2) {
                return redirect()->back()->with('error', 'Batas waktu hapus (2 menit) telah berakhir. Silakan hubungi admin laboratorium.');
            }
        }

        $clearedSlot = [
            'tanggal' => $booking->date,
            'waktu'   => substr($booking->start_time, 0, 5)
        ];

        $booking->delete();

        return redirect()->back()
            ->with('success', 'Schedule deleted successfully!')
            ->with('cleared_slot', $clearedSlot);
    }

    // ─── BULK DELETE (hanya admin) ────────────────────────────────────────────
    public function bulkDestroy(Request $request){
        if (!Session::get('admin_logged_in', false)) {
            return redirect()->back()->with('error', 'Access denied! Only admins can perform bulk delete.');
        }

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No schedules selected.');
        }

        $jumlah = Booking::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', "$jumlah schedules successfully deleted!");
    }

    // ─── LIVE SCHEDULE / DIGITAL SIGNAGE MONITOR ──────────────────────────────
    public function liveSchedule(Request $request)
    {
        Carbon::setLocale(app()->getLocale());
        $today = Carbon::today()->format('Y-m-d');
        $selectedDate = $request->query('date', $today);
        $selectedRoom = $request->query('room', 'all');

        $rooms = Room::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $settings = [
            'nama_lab'      => Setting::get('nama_lab', 'Maki Space'),
            'nama_instansi' => Setting::get('nama_instansi', 'Nama Instansi / Fakultas'),
            'logo'          => Setting::get('logo', 'images/logo.png'),
            'kontak_laboran'=> Setting::get('kontak_laboran', ''),
        ];

        return view('booking.live', compact('rooms', 'settings', 'selectedDate', 'selectedRoom'));
    }

    public function liveData(Request $request)
    {
        Carbon::setLocale(app()->getLocale());
        $today = Carbon::today()->format('Y-m-d');
        $date = $request->query('date', $today);
        $nowTime = Carbon::now()->format('H:i:s');

        $rooms = Room::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        
        $bookings = Booking::where('date', $date)
            ->where('status', 'Scheduled')
            ->with(['lecturer', 'course', 'courseClass'])
            ->orderBy('start_time')
            ->get();

        $roomsData = $rooms->map(function($room) use ($bookings, $nowTime) {
            $roomBookings = $bookings->where('room', $room->name);
            
            // Sedang Berlangsung (Current ongoing)
            $current = $roomBookings->first(function($b) use ($nowTime) {
                return $b->start_time <= $nowTime && $b->end_time >= $nowTime;
            });

            // Jadwal Berikutnya (Upcoming next)
            $upcoming = $roomBookings->filter(function($b) use ($nowTime) {
                return $b->start_time > $nowTime;
            })->first();

            return [
                'id'          => $room->id,
                'code'        => $room->room_code ?? 'R-' . $room->id,
                'name'        => $room->name,
                'color'       => $room->color,
                'color_hex'   => $room->color_hex,
                'is_occupied' => (bool)$current,
                'current'     => $current ? [
                    'id'            => $current->id,
                    'course'        => $current->course->name ?? $current->general_purpose ?? 'Kegiatan Lab',
                    'lecturer'      => $current->lecturer->name ?? $current->general_borrower_name ?? '-',
                    'class'         => $current->courseClass->name ?? '-',
                    'start_time'    => substr($current->start_time, 0, 5),
                    'end_time'      => substr($current->end_time, 0, 5),
                    'category'      => $current->borrower_category,
                ] : null,
                'upcoming'    => $upcoming ? [
                    'id'            => $upcoming->id,
                    'course'        => $upcoming->course->name ?? $upcoming->general_purpose ?? 'Kegiatan Lab',
                    'lecturer'      => $upcoming->lecturer->name ?? $upcoming->general_borrower_name ?? '-',
                    'class'         => $upcoming->courseClass->name ?? '-',
                    'start_time'    => substr($upcoming->start_time, 0, 5),
                    'end_time'      => substr($upcoming->end_time, 0, 5),
                    'category'      => $upcoming->borrower_category,
                ] : null,
                'schedules'   => $roomBookings->map(function($b) use ($nowTime) {
                    $status = 'upcoming';
                    if ($b->start_time <= $nowTime && $b->end_time >= $nowTime) {
                        $status = 'ongoing';
                    } elseif ($b->end_time < $nowTime) {
                        $status = 'past';
                    }

                    return [
                        'id'            => $b->id,
                        'course'        => $b->course->name ?? $b->general_purpose ?? 'Kegiatan',
                        'lecturer'      => $b->lecturer->name ?? $b->general_borrower_name ?? '-',
                        'class'         => $b->courseClass->name ?? '-',
                        'start_time'    => substr($b->start_time, 0, 5),
                        'end_time'      => substr($b->end_time, 0, 5),
                        'status'        => $status,
                    ];
                })->values()
            ];
        });

        return response()->json([
            'date'        => $date,
            'date_text'   => Carbon::parse($date)->translatedFormat('l, d F Y'),
            'server_time' => Carbon::now()->format('H:i:s'),
            'rooms'       => $roomsData,
        ]);
    }
}