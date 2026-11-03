<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Maki Space</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body style="min-height:100vh; background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); display:flex; align-items:center; justify-content:center;">

    <div style="width:100%; max-width:400px; padding:0 16px;">

        {{-- ═══ BAGIAN HEADER LOGIN (Logo & Judul) ═══ --}}
        <div style="text-align:center; margin-bottom:32px;">
            <div style="width:56px; height:56px; background:#3b82f6; border-radius:16px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px; box-shadow:0 8px 24px rgba(59,130,246,0.35);">
                <svg style="width:28px;height:28px;color:white;" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 style="font-size:22px; font-weight:900; color:#1e3a8a; margin:0 0 4px;">Admin Panel</h1>
            <p style="font-size:13px; color:#64748b; margin:0;">Maki Space</p>
        </div>

        {{-- ═══ KARTU FORM LOGIN (Wadah putih utama) ═══ --}}
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

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Enter username..."
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    
                    @error('username')
                        <p style="font-size:12px; color:#dc2626; margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ showPass: false }" style="margin-bottom:26px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin:0;">Password</label>
                        <a href="{{ route('admin.forgot') }}" style="font-size:12px; color:#3b82f6; text-decoration:none; font-weight:600;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            Lupa Password?
                        </a>
                    </div>
                    <div style="position:relative;">
                        <input :type="showPass ? 'text' : 'password'" name="password" placeholder="Enter password..."
                               style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="button" @click="showPass = !showPass" title="Tampilkan / Sembunyikan Password"
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:6px; display:flex; align-items:center; justify-content:center;">
                            {{-- Mata Terbuka (Saat Password Tersembunyi) --}}
                            <svg x-show="!showPass" style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- Mata Dicoret (Saat Password Terlihat) --}}
                            <svg x-show="showPass" x-cloak style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    
                    @error('password')
                        <p style="font-size:12px; color:#dc2626; margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        style="width:100%; background:#3b82f6; color:white; border:none; border-radius:10px; padding:12px; font-size:14px; font-weight:700; cursor:pointer; transition:background 0.2s; box-shadow:0 4px 12px rgba(59,130,246,0.4);"
                        onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    Sign In to Admin Panel
                </button>
            </form>
        </div>

        <p style="text-align:center; font-size:12px; color:#94a3b8; margin-top:20px;">
            <a href="{{ url('/') }}" style="color:#3b82f6; text-decoration:none; font-weight:600;">← Back to Schedule</a>
        </p>
    </div>

</body>
</html>