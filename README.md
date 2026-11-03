# 🚀 Maki Space — Sistem Penjadwalan & Peminjaman Ruangan Lab

Sistem aplikasi web berbasis **Laravel 11**, **Tailwind CSS**, dan **Alpine.js** yang dirancang untuk mengelola peminjaman dan penjadwalan ruangan laboratorium kampus/institusi secara interaktif, real-time, anti-bentrok, dan dilengkapi asisten cerdas bertenaga **Google Gemini AI (LLM)**.

---

## ✨ Fitur Unggulan

1. **📅 Kalender Jadwal Interaktif (Monthly Grid)**
   - Tampilan matriks baris tanggal dan kolom slot jam.
   - Penandaan warna blok otomatis per hari (Senin=merah, Selasa=oranye, Rabu=kuning, Kamis=hijau, Jumat=biru, Sabtu=indigo, Minggu=pink).
   - Indikator khusus jadwal baru disimpan (*highlight* hijau) dan deteksi bentrok (*highlight* merah berkedip).

2. **🏢 Kustomisasi Ruangan Penuh (Multi-Room)**
   - Admin dapat **menambah**, **mengubah nama**, dan **menghapus** ruangan.
   - Pilihan warna dot indikator (Biru, Hijau, Kuning, Merah, Ungu, Pink, Indigo, Oranye, Teal, Abu-abu).
   - Validasi proteksi: ruangan yang masih memiliki jadwal tidak dapat dihapus sembarangan.

3. **⏰ Kustomisasi Rentang Slot Jam Kolom**
   - Admin dapat mengatur jam mulai kolom dan label header (misal: `07.00 - 08.40`, `08.40 - 10.20`, dst).
   - Fleksibel menyesuaikan jam operasional laboratorium kampus/instansi mana pun.

4. **🤖 Asisten Chat AI — Tanya Maki (Gemini Cloud LLM)**
   - Modal chat interaktif yang dapat digeser (*draggable*).
   - Membaca konteks database jadwal untuk menjawab pertanyaan ketersediaan ruangan secara natural dan cepat.
   - **Setting API AI via Admin Panel**: Konfigurasi API Key, pemilihan model (Gemini 1.5 Flash, Gemini 1.5 Pro, Gemini 2.0 Flash, dll), Max Tokens, dan Temperature langsung dari GUI tanpa edit file server.

5. **📖 Panduan Penggunaan Terintegrasi (In-App Guide Modal)**
   - Tersedia tombol cepat **"📖 Panduan Penggunaan"** di sidebar.
   - Panduan lengkap cara booking, aturan edit/hapus 2 menit, tanya AI, hingga administrasi sistem.

6. **🖨️ Cetak Jadwal Resmi**
   - Cetak semua jadwal bulanan (Landscape).
   - Cetak jadwal tetap reguler mingguan (Portrait A4) otomatis dikelompokkan per hari kerja (Senin - Jumat).
   - Cetak jadwal insidental/pengganti.

7. **🛡️ Keamanan & Anti-Bentrok**
   - Algoritma pengecekan *overlap* waktu peminjaman sebelum penyimpanan.
   - Batas waktu edit/hapus mandiri 2 menit bagi pengguna umum, kontrol penuh bagi Admin.
   - Manajemen massal (*bulk delete*) khusus admin.

---

## 🚀 Panduan Instalasi Cepat

### Persyaratan Sistem:
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB (atau SQLite)

### Langkah Pemasangan:

```bash
# 1. Clone repository
git clone https://github.com/username/maki-space.git
cd maki-space

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Frontend
npm install
npm run build

# 4. Salin environment file
cp .env.example .env

# 5. Generate Application Key
php artisan key:generate

# 6. Konfigurasi Database di file .env
# Sesuaikan DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 7. Jalankan Migrasi & Database Seeder (Membuat tabel dan data awal)
php artisan migrate --seed

# 8. Jalankan Local Server
php artisan serve
```

Akses sistem di browser: `http://localhost:8000`

---

## 🔑 Kredensial Default

- **URL Login Admin:** `/admin/login`
- **Username:** `admin`
- **Password:** *(diatur via `ADMIN_FALLBACK_PASSWORD` di file `.env` — lihat `.env.example`)*

> ⚠️ **Penting:** Setelah login pertama, segera ganti password melalui **Admin Panel → Pengaturan Akun**.

---

## 🤖 Menghubungkan API Google Gemini AI

1. Daftar / masuk ke [Google AI Studio](https://aistudio.google.com).
2. Buat API Key baru di menu **Get API Key**.
3. Masuk ke aplikasi sebagai **Admin** &rarr; buka tab **"Setting API & AI"**.
4. Masukkan API Key Gemini Anda, pilih model (misal: `gemini-1.5-flash`), lalu klik **Simpan Pengaturan**.
5. Asisten **Tanya Maki** pada halaman utama siap digunakan!

---

## 📂 Struktur Data Utama

- `ruangans`: Daftar ruangan dan warna penanda
- `jam_slots`: Daftar rentang jam slot kolom kalender
- `pengaturans`: Pengaturan kunci-nilai (API Key, nama lab, kontak laboran)
- `dosens`: Master data dosen / penanggung jawab
- `mata_kuliahs`: Master data mata kuliah
- `kelas`: Master data kelas
- `peminjamans`: Data transaksi jadwal dan peminjaman ruangan

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT License](LICENSE). Bebas digunakan, dimodifikasi, dan dikembangkan untuk keperluan akademik maupun komersial.
