<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiController;


// ─── CLEAR CACHE (Aman: Memerlukan Sesi Admin atau Token Rahasia) ────────────
Route::get('/clear-cache', function (\Illuminate\Http\Request $request) {
    $tokenProvided = $request->query('token');
    $secretToken   = env('DEPLOY_CACHE_TOKEN', 'maki_cache_sec_token_2026');
    $isAdmin       = \Illuminate\Support\Facades\Session::get('admin_logged_in', false);

    if (!$isAdmin && (!$tokenProvided || !hash_equals($secretToken, (string) $tokenProvided))) {
        abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk membersihkan cache.');
    }

    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return '<div style="font-family:-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; text-align:center; padding:60px 20px;">
        <div style="font-size:48px; margin-bottom:12px;">✅</div>
        <h2 style="color:#16A34A; margin:0 0 8px 0;">Cache Berhasil Dibersihkan!</h2>
        <p style="color:#64748B; margin:0 0 20px 0; font-size:14px;">Tampilan view Blade dan cache sistem telah diperbarui ke versi terbaru.</p>
        <a href="' . url('/') . '" style="display:inline-block; padding:10px 22px; background:#2563EB; color:#FFFFFF; text-decoration:none; border-radius:10px; font-weight:700; font-size:14px;">Kembali ke Jadwal</a>
    </div>';
});

// Route print
Route::get('/booking/print', [BookingController::class, 'print'])->name('booking.print');

// Switch Language (EN / ID)
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// Halaman utama kalender
Route::get('/', [BookingController::class, 'index']);

// Tambah jadwal baru
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

// Edit dan Hapus satu jadwal
Route::put('/booking/{id}', [BookingController::class, 'update'])->name('booking.update');
Route::delete('/booking/{id}', [BookingController::class, 'destroy'])->name('booking.destroy');

// ─── AUTH ADMIN ───────────────────────────────────────────────────────────────
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.post');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::get('/admin/forgot-password', [AdminController::class, 'showForgotPassword'])->name('admin.forgot');
Route::post('/admin/forgot-password', [AdminController::class, 'resetPasswordWithKey'])->middleware('throttle:5,1')->name('admin.reset.post');

// ─── PROTECTED ADMIN ROUTES (Memerlukan Autentikasi Admin) ────────────────────
Route::middleware('admin.auth')->group(function () {
    // Dashboard Admin
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // Hapus banyak jadwal sekaligus
    Route::delete('/booking-bulk', [BookingController::class, 'bulkDestroy'])->name('booking.bulk-destroy');

    // CRUD Dosen
    Route::post('/admin/dosen', [AdminController::class, 'storeDosen'])->name('admin.dosen.store');
    Route::put('/admin/dosen/{id}', [AdminController::class, 'updateDosen'])->name('admin.dosen.update');
    Route::delete('/admin/dosen/{id}', [AdminController::class, 'destroyDosen'])->name('admin.dosen.destroy');

    // CRUD Mata Kuliah
    Route::post('/admin/matkul', [AdminController::class, 'storeMatkul'])->name('admin.matkul.store');
    Route::put('/admin/matkul/{id}', [AdminController::class, 'updateMatkul'])->name('admin.matkul.update');
    Route::delete('/admin/matkul/{id}', [AdminController::class, 'destroyMatkul'])->name('admin.matkul.destroy');

    // CRUD Kelas
    Route::post('/admin/kelas', [AdminController::class, 'storeKelas'])->name('admin.kelas.store');
    Route::put('/admin/kelas/{id}', [AdminController::class, 'updateKelas'])->name('admin.kelas.update');
    Route::delete('/admin/kelas/{id}', [AdminController::class, 'destroyKelas'])->name('admin.kelas.destroy');

    // CRUD Ruangan
    Route::post('/admin/ruangan', [AdminController::class, 'storeRuangan'])->name('admin.ruangan.store');
    Route::put('/admin/ruangan/{id}', [AdminController::class, 'updateRuangan'])->name('admin.ruangan.update');
    Route::delete('/admin/ruangan/{id}', [AdminController::class, 'destroyRuangan'])->name('admin.ruangan.destroy');

    // CRUD Jam Slot
    Route::post('/admin/jam-slot', [AdminController::class, 'storeJamSlot'])->name('admin.jam.store');
    Route::put('/admin/jam-slot/{id}', [AdminController::class, 'updateJamSlot'])->name('admin.jam.update');
    Route::delete('/admin/jam-slot/{id}', [AdminController::class, 'destroyJamSlot'])->name('admin.jam.destroy');

    // Pengaturan AI & Sistem
    Route::post('/admin/pengaturan', [AdminController::class, 'updatePengaturan'])->name('admin.pengaturan.update');
    Route::post('/admin/account', [AdminController::class, 'updateAccount'])->name('admin.account.update');
});

// ─── LIVE SCHEDULE / DIGITAL SIGNAGE MONITOR ──────────────────────────────────
Route::get('/live', [BookingController::class, 'liveSchedule'])->name('booking.live');
Route::get('/api/live-data', [BookingController::class, 'liveData'])->name('booking.live-data');

// Route AI (dengan Rate Limiting untuk perlindungan kuota API)
Route::post('/api/ask-ai', [AiController::class, 'askAi'])->middleware('throttle:30,1');

