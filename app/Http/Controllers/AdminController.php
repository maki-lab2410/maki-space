<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Lecturer;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Models\Setting;
use App\Models\Booking;

class AdminController extends Controller
{
    // ─── DEFAULT FALLBACK PASSWORD (hanya dipakai saat hash belum tersimpan di DB) ──
    // ⚠️  Segera ganti password via Admin Panel → Pengaturan Akun agar fallback ini tidak aktif.
    // Nilai dibaca dari ADMIN_FALLBACK_PASSWORD di .env (tidak hardcoded di kode).
    private static function getDefaultFallbackPassword(): string
    {
        return env('ADMIN_FALLBACK_PASSWORD', '');
    }

    // ─── DEFAULT RECOVERY KEY (Kunci Pemulihan / PIN untuk Lupa Password) ───────────
    // Nilai dibaca dari ADMIN_DEFAULT_RECOVERY_KEY di .env (tidak hardcoded di kode).
    private static function getDefaultRecoveryKey(): string
    {
        return env('ADMIN_DEFAULT_RECOVERY_KEY', '');
    }

    // ─── FITUR AUTENTIKASI ───────────────────────────────────────────────────────
    
    /**
     * Menampilkan Halaman Login
     */
    public function showLogin()
    {
        if (Session::get('admin_logged_in')) {
            return redirect('/admin'); 
        }
        return view('admin.login');
    }

    /**
     * Menampilkan Halaman Lupa Password
     */
    public function showForgotPassword()
    {
        if (Session::get('admin_logged_in')) {
            return redirect('/admin');
        }
        return view('admin.forgot-password');
    }

    /**
     * Memproses Reset Password menggunakan Kode Pemulihan / PIN
     */
    public function resetPasswordWithKey(Request $request)
    {
        $request->validate([
            'username'                  => 'required|string',
            'recovery_key'              => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
        ], [
            'username.required'         => 'Username wajib diisi.',
            'recovery_key.required'     => 'Kode Pemulihan / PIN Keamanan wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal 6 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $storedUser = Setting::get('admin_username', 'admin');
        $storedKey  = Setting::get('admin_recovery_key', self::getDefaultRecoveryKey());

        // Periksa apakah username cocok
        if (trim($request->username) !== $storedUser) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', 'Username tidak ditemukan!');
        }

        // Periksa apakah recovery_key cocok (gunakan perbandingan konstan time aman tanpa bypass string hardcoded)
        $inputKey = trim($request->recovery_key);
        $isValidKey = !empty($storedKey) && hash_equals((string) $storedKey, (string) $inputKey);

        if (!$isValidKey) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', 'Kode Pemulihan / PIN Keamanan salah!');
        }

        // Simpan hash password baru
        Setting::set('admin_password_hash', \Illuminate\Support\Facades\Hash::make($request->new_password));

        return redirect()->route('admin.login')
            ->with('success', 'Password admin berhasil direset! Silakan login dengan password baru.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $storedUser = Setting::get('admin_username', 'admin');
        $storedHash = Setting::get('admin_password_hash', null);

        $valid = false;
        if ($request->username === $storedUser) {
            if ($storedHash) {
                $valid = \Illuminate\Support\Facades\Hash::check($request->password, $storedHash);
            } else {
                $valid = ($request->password === self::getDefaultFallbackPassword());
            }
        }

        if ($valid) {
            $request->session()->regenerate();
            Session::put('admin_logged_in', true);
            Session::put('admin_username', $request->username);
            
            return redirect('/admin')->with('success', 'Successfully logged in to Admin Panel!');
        }

        return redirect()->back()->with('error', 'Invalid username or password!');
    }

    /**
     * Memproses Logout Admin
     */
    public function logout(Request $request)
    {
        Session::forget('admin_logged_in');
        Session::forget('admin_username');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    // ─── DASHBOARD MASTER DATA ───────────────────────────────────────────────────
    
    /**
     * Menampilkan Panel Admin (Manajemen Dosen, Matkul, Kelas, Ruangan, Jam, Pengaturan)
     */
    public function index()
    {
        if (!Session::get('admin_logged_in')) {
            return redirect('/admin/login');
        }

        // Ambil semua daftar master data
        $daftarDosen   = Lecturer::orderBy('name')->get();
        $daftarMatkul  = Course::orderBy('name')->get();
        $daftarKelas   = CourseClass::orderBy('name')->get();
        $daftarRuangan = Room::orderBy('order')->orderBy('name')->get();
        $daftarJamSlot = TimeSlot::orderBy('order')->get();
        $pilihanWarna  = Room::colorOptions();

        // Pengaturan AI & Penanggung Jawab
        $pengaturanAi = [
            'gemini_api_key'     => Setting::get('gemini_api_key', ''),
            'gemini_model'       => Setting::get('gemini_model', 'gemini-2.5-flash'),
            'gemini_max_tokens'  => Setting::get('gemini_max_tokens', '2048'),
            'gemini_temperature' => Setting::get('gemini_temperature', '0.2'),
            'nama_lab'           => Setting::get('nama_lab', 'Maki Space'),
            'nama_instansi'      => Setting::get('nama_instansi', 'Nama Instansi / Fakultas'),
            'logo'               => Setting::get('logo', 'images/logo.png'),
            'kontak_laboran'     => Setting::get('kontak_laboran', ''),
            'nama_pj'            => Setting::get('nama_pj', ''),
            'nip_pj'             => Setting::get('nip_pj', ''),
            'jabatan_pj'         => Setting::get('jabatan_pj', 'Penanggung Jawab'),
            'nama_atasan'        => Setting::get('nama_atasan', ''),
            'nip_atasan'         => Setting::get('nip_atasan', ''),
            'jabatan_atasan'     => Setting::get('jabatan_atasan', 'Ketua Program Studi'),
            'admin_username'     => Setting::get('admin_username', 'admin'),
            'admin_recovery_key' => Setting::get('admin_recovery_key', self::getDefaultRecoveryKey()),
            'filter_years_future' => Setting::get('filter_years_future', '2'),
            'filter_years_past'   => Setting::get('filter_years_past', '3'),
            'default_language'    => Setting::get('default_language', 'en'),
        ];

        return view('admin.index', compact(
            'daftarDosen', 'daftarMatkul', 'daftarKelas',
            'daftarRuangan', 'daftarJamSlot', 'pilihanWarna', 'pengaturanAi'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI CRUD: ENTITAS DOSEN / LECTURER
    // ═══════════════════════════════════════════════════════════════════════════
    
    public function storeDosen(Request $request)
    {
        $name = trim($request->name ?? $request->nama_dosen);
        $nip  = $request->nip ? trim($request->nip) : null;

        $request->merge(['name' => $name]);
        $request->validate([
            'name' => 'required|string|max:255|unique:lecturers,name',
            'nip'  => 'nullable|string|max:100'
        ]);

        Lecturer::create([
            'nip'  => $nip,
            'name' => $name
        ]);
        
        return redirect('/admin?tab=dosen')->with('success', 'Lecturer added successfully!');
    }

    public function updateDosen(Request $request, $id)
    {
        $name = trim($request->name ?? $request->nama_dosen);
        $nip  = $request->nip ? trim($request->nip) : null;

        $request->merge(['name' => $name]);
        $request->validate([
            'name' => 'required|string|max:255|unique:lecturers,name,' . $id,
            'nip'  => 'nullable|string|max:100'
        ]);
        
        $dosen = Lecturer::findOrFail($id);
        $dosen->update([
            'nip'  => $nip,
            'name' => $name
        ]);
        
        return redirect('/admin?tab=dosen')->with('success', 'Lecturer updated successfully!');
    }

    public function destroyDosen($id)
    {
        $dosen = Lecturer::findOrFail($id);
        if ($dosen->bookings()->count() > 0) {
            return redirect('/admin?tab=dosen')->with('error', 'Cannot delete lecturer because they have active schedules!');
        }
        $dosen->delete();
        return redirect('/admin?tab=dosen')->with('success', 'Lecturer deleted successfully!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI CRUD: ENTITAS MATA KULIAH / COURSE
    // ═══════════════════════════════════════════════════════════════════════════
    
    public function storeMatkul(Request $request)
    {
        $name = trim($request->name ?? $request->nama_matkul);
        $code = $request->course_code ?? $request->kode_matkul ? trim($request->course_code ?? $request->kode_matkul) : null;

        $request->merge(['name' => $name]);
        $request->validate([
            'name'        => 'required|string|max:255|unique:courses,name',
            'course_code' => 'nullable|string|max:50'
        ]);

        Course::create([
            'course_code' => $code,
            'name'        => $name
        ]);
        return redirect('/admin?tab=matkul')->with('success', 'Course added successfully!');
    }

    public function updateMatkul(Request $request, $id)
    {
        $name = trim($request->name ?? $request->nama_matkul);
        $code = $request->course_code ?? $request->kode_matkul ? trim($request->course_code ?? $request->kode_matkul) : null;

        $request->merge(['name' => $name]);
        $request->validate([
            'name'        => 'required|string|max:255|unique:courses,name,' . $id,
            'course_code' => 'nullable|string|max:50'
        ]);

        $matkul = Course::findOrFail($id);
        $matkul->update([
            'course_code' => $code,
            'name'        => $name
        ]);
        return redirect('/admin?tab=matkul')->with('success', 'Course updated successfully!');
    }

    public function destroyMatkul($id)
    {
        $matkul = Course::findOrFail($id);
        if ($matkul->bookings()->count() > 0) {
            return redirect('/admin?tab=matkul')->with('error', 'Cannot delete course because it is used in active schedules!');
        }
        $matkul->delete();
        return redirect('/admin?tab=matkul')->with('success', 'Course deleted successfully!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI CRUD: ENTITAS KELAS / COURSE CLASS
    // ═══════════════════════════════════════════════════════════════════════════
    
    public function storeKelas(Request $request)
    {
        $name = trim($request->name ?? $request->nama_kelas);
        $code = $request->class_code ?? $request->kode_kelas ? trim($request->class_code ?? $request->kode_kelas) : null;

        $request->merge(['name' => $name]);
        $request->validate([
            'name'       => 'required|string|max:255|unique:course_classes,name',
            'class_code' => 'nullable|string|max:50'
        ]);

        CourseClass::create([
            'class_code' => $code,
            'name'       => $name
        ]);
        return redirect('/admin?tab=kelas')->with('success', 'Class added successfully!');
    }

    public function updateKelas(Request $request, $id)
    {
        $name = trim($request->name ?? $request->nama_kelas);
        $code = $request->class_code ?? $request->kode_kelas ? trim($request->class_code ?? $request->kode_kelas) : null;

        $request->merge(['name' => $name]);
        $request->validate([
            'name'       => 'required|string|max:255|unique:course_classes,name,' . $id,
            'class_code' => 'nullable|string|max:50'
        ]);

        $kelas = CourseClass::findOrFail($id);
        $kelas->update([
            'class_code' => $code,
            'name'       => $name
        ]);
        return redirect('/admin?tab=kelas')->with('success', 'Class updated successfully!');
    }

    public function destroyKelas($id)
    {
        $kelas = CourseClass::findOrFail($id);
        if ($kelas->bookings()->count() > 0) {
            return redirect('/admin?tab=kelas')->with('error', 'Cannot delete class because it is used in active schedules!');
        }
        $kelas->delete();
        return redirect('/admin?tab=kelas')->with('success', 'Class deleted successfully!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI CRUD: ENTITAS RUANGAN / ROOM
    // ═══════════════════════════════════════════════════════════════════════════
    
    public function storeRuangan(Request $request)
    {
        $code  = strtoupper(trim($request->room_code ?? $request->kode_ruangan));
        $name  = trim($request->name ?? $request->nama_ruangan);
        $color = $request->color ?? $request->warna;

        $request->merge(['room_code' => $code, 'name' => $name, 'color' => $color]);
        $request->validate([
            'room_code' => 'required|string|max:50|unique:rooms,room_code',
            'name'      => 'required|string|max:255|unique:rooms,name',
            'color'     => 'required|string|max:100',
        ]);

        $order = Room::max('order') + 1;
        Room::create([
            'room_code' => $code,
            'name'      => $name,
            'color'     => $color,
            'order'     => $order,
            'is_active' => true,
        ]);
        return redirect('/admin?tab=ruangan')->with('success', 'Room added successfully!');
    }

    public function updateRuangan(Request $request, $id)
    {
        $code  = strtoupper(trim($request->room_code ?? $request->kode_ruangan));
        $name  = trim($request->name ?? $request->nama_ruangan);
        $color = $request->color ?? $request->warna;

        $request->merge(['room_code' => $code, 'name' => $name, 'color' => $color]);
        $request->validate([
            'room_code' => 'required|string|max:50|unique:rooms,room_code,' . $id,
            'name'      => 'required|string|max:255|unique:rooms,name,' . $id,
            'color'     => 'required|string|max:100',
        ]);

        $ruangan = Room::findOrFail($id);
        $namaLama = $ruangan->name;
        $ruangan->update([
            'room_code' => $code,
            'name'      => $name,
            'color'     => $color,
        ]);

        // Jika nama berubah, update juga kolom 'room' di bookings
        if ($namaLama !== $name) {
            Booking::where('room', $namaLama)->update(['room' => $name]);
        }

        return redirect('/admin?tab=ruangan')->with('success', 'Room updated successfully!');
    }

    public function destroyRuangan($id)
    {
        $ruangan = Room::findOrFail($id);
        $jumlahJadwal = Booking::where('room', $ruangan->name)->count();
        if ($jumlahJadwal > 0) {
            return redirect('/admin?tab=ruangan')->with('error', 'Cannot delete room because it still has ' . $jumlahJadwal . ' active schedules!');
        }
        $ruangan->delete();
        return redirect('/admin?tab=ruangan')->with('success', 'Room deleted successfully!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI CRUD: ENTITAS JAM SLOT / TIME SLOT
    // ═══════════════════════════════════════════════════════════════════════════

    public function storeJamSlot(Request $request)
    {
        $startTime = $request->start_time ?? $request->jam_mulai;
        $request->merge(['start_time' => $startTime]);
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'label'      => 'nullable|string|max:100',
        ]);

        // Cek duplikat jam
        $exists = TimeSlot::where('start_time', $startTime . ':00')->first();
        if ($exists) {
            return redirect('/admin?tab=jam')->with('error', 'Time slot ' . $startTime . ' already exists!');
        }

        $order = TimeSlot::max('order') + 1;
        $label = $request->label ?: $startTime;

        TimeSlot::create([
            'start_time' => $startTime,
            'label'      => $label,
            'order'      => $order,
        ]);

        return redirect('/admin?tab=jam')->with('success', 'Time slot added successfully!');
    }

    public function updateJamSlot(Request $request, $id)
    {
        $startTime = $request->start_time ?? $request->jam_mulai;
        $request->merge(['start_time' => $startTime]);
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'label'      => 'nullable|string|max:100',
        ]);

        $jamSlot = TimeSlot::findOrFail($id);
        $label   = $request->label ?: $startTime;

        $jamSlot->update([
            'start_time' => $startTime,
            'label'      => $label,
        ]);

        return redirect('/admin?tab=jam')->with('success', 'Time slot updated successfully!');
    }

    public function destroyJamSlot($id)
    {
        $total = TimeSlot::count();
        if ($total <= 2) {
            return redirect('/admin?tab=jam')->with('error', 'Must have at least 2 time slots!');
        }
        TimeSlot::findOrFail($id)->delete();
        // Re-order
        TimeSlot::orderBy('start_time')->each(function($slot, $i) {
            $slot->update(['order' => $i + 1]);
        });
        return redirect('/admin?tab=jam')->with('success', 'Time slot deleted successfully!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI: PENGATURAN AI (GOOGLE GEMINI) & SISTEM
    // ═══════════════════════════════════════════════════════════════════════════

    public function updatePengaturan(Request $request)
    {
        $request->validate([
            'gemini_api_key'     => 'nullable|string|max:500',
            'gemini_model'       => 'required|string|max:100',
            'gemini_max_tokens'  => 'required|integer|min:50|max:4000',
            'gemini_temperature' => 'required|numeric|min:0|max:1',
            'nama_lab'           => 'required|string|max:100',
            'nama_instansi'      => 'nullable|string|max:150',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'kontak_laboran'     => 'nullable|string|max:50',
            'nama_pj'            => 'nullable|string|max:150',
            'nip_pj'             => 'nullable|string|max:100',
            'jabatan_pj'         => 'nullable|string|max:150',
            'nama_atasan'        => 'nullable|string|max:150',
            'nip_atasan'         => 'nullable|string|max:100',
            'jabatan_atasan'     => 'nullable|string|max:150',
            'filter_years_future' => 'required|integer|min:0|max:10',
            'filter_years_past'   => 'required|integer|min:0|max:15',
            'default_language'    => 'required|in:en,id',
        ]);

        $keys = [
            'gemini_api_key', 'gemini_model', 'gemini_max_tokens', 'gemini_temperature',
            'nama_lab', 'nama_instansi', 'kontak_laboran',
            'nama_pj', 'nip_pj', 'jabatan_pj',
            'nama_atasan', 'nip_atasan', 'jabatan_atasan',
            'filter_years_future', 'filter_years_past', 'default_language'
        ];
        foreach ($keys as $key) {
            Setting::set($key, $request->input($key, ''));
        }

        // Upload file Logo jika diunggah (hanya format gambar aman)
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            $extension = in_array(strtolower($file->extension()), ['jpeg', 'jpg', 'png', 'webp']) ? $file->extension() : 'png';
            $filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $file->move(public_path('images'), $filename);
            Setting::set('logo', 'images/' . $filename);
        }

        return redirect('/admin?tab=pengaturan')->with('success', 'System settings, institution info, logo, and signers saved successfully!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FUNGSI: PENGATURAN AKUN ADMIN
    // ═══════════════════════════════════════════════════════════════════════════

    public function updateAccount(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect('/admin/login');
        }

        $request->validate([
            'username'         => 'required|string|max:50',
            'current_password' => 'required|string',
            'new_password'     => 'nullable|string|min:6|confirmed',
            'recovery_key'     => 'nullable|string|min:4|max:50',
        ], [
            'username.required'         => 'Username is required.',
            'current_password.required' => 'Current password is required for security verification.',
            'new_password.min'          => 'New password must be at least 6 characters.',
            'new_password.confirmed'    => 'New password confirmation does not match.',
            'recovery_key.min'          => 'Recovery PIN/Key must be at least 4 characters.',
        ]);

        $storedUser = Setting::get('admin_username', 'admin');
        $storedHash = Setting::get('admin_password_hash', null);

        // Verifikasi password saat ini
        $currentValid = false;
        if ($storedHash) {
            $currentValid = \Illuminate\Support\Facades\Hash::check($request->current_password, $storedHash);
        } else {
            $currentValid = ($request->current_password === self::getDefaultFallbackPassword());
        }

        if (!$currentValid) {
            return redirect('/admin?tab=akun')->with('error', 'Current password incorrect! Changes cancelled.');
        }

        // Simpan username baru
        Setting::set('admin_username', trim($request->username));
        Session::put('admin_username', trim($request->username));

        // Simpan recovery key jika diisi
        if (!empty($request->recovery_key)) {
            Setting::set('admin_recovery_key', trim($request->recovery_key));
        }

        // Simpan password baru jika diisi
        if (!empty($request->new_password)) {
            Setting::set('admin_password_hash', \Illuminate\Support\Facades\Hash::make($request->new_password));
        }

        return redirect('/admin?tab=akun')->with('success', 'Admin account updated successfully!');
    }
}