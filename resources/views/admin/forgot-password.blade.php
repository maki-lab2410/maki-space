<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Admin – Maki Space</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body style="min-height:100vh; background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); display:flex; align-items:center; justify-content:center; padding:20px 0;">

    <div style="width:100%; max-width:440px; padding:0 16px;">

        {{-- ═══ HEADER ═══ --}}
        <div style="text-align:center; margin-bottom:28px;">
            <div style="width:58px; height:58px; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius:18px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px; box-shadow:0 8px 24px rgba(217,119,6,0.35);">
                <span style="font-size:26px;">🔑</span>
            </div>
            <h1 style="font-size:22px; font-weight:900; color:#1e3a8a; margin:0 0 4px;">Reset Password Admin</h1>
            <p style="font-size:13px; color:#64748b; margin:0;">Maki Space</p>
        </div>

        {{-- ═══ KARTU FORM ═══ --}}
        <div style="background:white; border-radius:20px; padding:32px; box-shadow:0 20px 60px rgba(0,0,0,0.1); border:1px solid rgba(255,255,255,0.8);">

            @if(session('error'))
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <span style="font-size:18px;">⚠️</span>
                <p style="font-size:13px; color:#dc2626; font-weight:600; margin:0;">{{ session('error') }}</p>
            </div>
            @endif

            @if(session('success'))
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <span style="font-size:18px;">✅</span>
                <p style="font-size:13px; color:#16a34a; font-weight:600; margin:0;">{{ session('success') }}</p>
            </div>
            @endif

            <form action="{{ route('admin.reset.post') }}" method="POST">
                @csrf

                {{-- Username --}}
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Username Admin</label>
                    <input type="text" name="username" value="{{ old('username', 'admin') }}" required placeholder="Contoh: admin"
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                           onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e2e8f0'">
                    @error('username')
                        <p style="font-size:12px; color:#dc2626; margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recovery Key / PIN --}}
                <div x-data="{ showKey: false }" style="margin-bottom:18px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">
                        Kode Pemulihan / PIN Keamanan
                    </label>
                    <div style="position:relative;">
                        <input :type="showKey ? 'text' : 'password'" name="recovery_key" required placeholder="Masukkan PIN / Kunci Keamanan"
                               style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                               onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="button" @click="showKey = !showKey" title="Tampilkan / Sembunyikan Kode"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:6px; display:flex; align-items:center; justify-content:center;">
                            <svg x-show="!showKey" style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showKey" x-cloak style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    <span style="font-size:11px; color:#94a3b8; display:block; margin-top:4px;">
                        💡 Masukkan Kode Pemulihan / PIN rahasia yang telah Anda atur di Pengaturan Akun Admin.
                    </span>
                    @error('recovery_key')
                        <p style="font-size:12px; color:#dc2626; margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <hr style="border:none; border-top:1px dashed #e2e8f0; margin:18px 0;">

                {{-- Password Baru --}}
                <div x-data="{ showNewPass: false }" style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Password Baru</label>
                    <div style="position:relative;">
                        <input :type="showNewPass ? 'text' : 'password'" name="new_password" required placeholder="Minimal 6 karakter..."
                               style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                               onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="button" @click="showNewPass = !showNewPass" title="Tampilkan / Sembunyikan Password"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:6px; display:flex; align-items:center; justify-content:center;">
                            <svg x-show="!showNewPass" style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNewPass" x-cloak style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('new_password')
                        <p style="font-size:12px; color:#dc2626; margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div x-data="{ showConfirmPass: false }" style="margin-bottom:24px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Ulangi Password Baru</label>
                    <div style="position:relative;">
                        <input :type="showConfirmPass ? 'text' : 'password'" name="new_password_confirmation" required placeholder="Ketik ulang password baru..."
                               style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                               onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="button" @click="showConfirmPass = !showConfirmPass" title="Tampilkan / Sembunyikan Password"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:6px; display:flex; align-items:center; justify-content:center;">
                            <svg x-show="!showConfirmPass" style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirmPass" x-cloak style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        style="width:100%; background:linear-gradient(135deg, #d97706 0%, #b45309 100%); color:white; border:none; border-radius:10px; padding:12px; font-size:14px; font-weight:700; cursor:pointer; transition:opacity 0.2s; box-shadow:0 4px 14px rgba(217,119,6,0.35);"
                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Simpan Password Baru
                </button>
            </form>
        </div>

        <p style="text-align:center; font-size:13px; color:#94a3b8; margin-top:20px;">
            <a href="{{ route('admin.login') }}" style="color:#3b82f6; text-decoration:none; font-weight:600;">← Kembali ke Halaman Login</a>
        </p>
    </div>

</body>
</html>
