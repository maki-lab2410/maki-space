<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel – {{ $pengaturanAi['nama_lab'] ?? 'Jadwal Lab' }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        [x-cloak] { display: none !important; }

        /* ── Konsisten chevron untuk semua native <select> ── */
        .select-wrap { position: relative; display: block; }
        .select-wrap select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 2.25rem !important;
        }
        .select-wrap::after {
            content: '';
            pointer-events: none;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpath d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-size: 16px 16px;
        }
    </style>
</head>

<body class="bg-[#F5F5F7]" style="min-height:100vh;"
      x-data="{
        activeTab: '{{ request('tab', 'dosen') }}',
        showEditDosen: false, showDeleteDosen: false, editDosenData: {}, deleteDosenData: {},
        showEditMatkul: false, showDeleteMatkul: false, editMatkulData: {}, deleteMatkulData: {},
        showEditKelas: false, showDeleteKelas: false, editKelasData: {}, deleteKelasData: {},
        showEditRuangan: false, showDeleteRuangan: false, editRuanganData: {}, deleteRuanganData: {},
        showEditJam: false, showDeleteJam: false, editJamData: {}, deleteJamData: {},
        showApiKey: false,

        init() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.showEditDosen = this.showDeleteDosen = false;
                    this.showEditMatkul = this.showDeleteMatkul = false;
                    this.showEditKelas = this.showDeleteKelas = false;
                    this.showEditRuangan = this.showDeleteRuangan = false;
                    this.showEditJam = this.showDeleteJam = false;
                }
            });
        }
      }">

    <div style="display:flex; min-height:100vh;">
        <!-- SIDEBAR -->
        <aside style="width:220px; background:white; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; flex-shrink:0; position:fixed; top:0; left:0; height:100vh; z-index:40;">
            
            <div style="padding:10px 12px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:8px;">
                <img src="{{ !empty($pengaturanAi['logo']) ? asset($pengaturanAi['logo']) : asset('images/logo.png') }}"
                     alt="Logo" style="width:30px; height:30px; object-fit:contain; border-radius:8px; flex-shrink:0;">
                <div style="overflow:hidden; min-width:0;">
                    <h1 style="font-size:12px; font-weight:900; color:#1e3a8a; margin:0; line-height:1.25;">Admin Panel</h1>
                    <p style="font-size:10px; color:#94a3b8; margin:2px 0 0; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $pengaturanAi['nama_instansi'] ?? $pengaturanAi['nama_lab'] ?? 'Maki Space' }}</p>
                </div>
            </div>

            <nav class="p-2 flex-1 overflow-y-auto space-y-3">
                <div>
                    <h2 style="font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:0.07em; padding:0 8px; margin-bottom:4px;">Master Data</h2>
                    
                    <div class="space-y-0.5">
                        <button @click="activeTab = 'dosen'" type="button"
                                :class="activeTab === 'dosen' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span style="font-size:12px;">Lecturers</span>
                            <span class="ml-auto bg-blue-100 text-blue-700 font-bold px-1.5 py-0.5 rounded-full" style="font-size:10px;">{{ $daftarDosen->count() }}</span>
                        </button>

                        <button @click="activeTab = 'matkul'" type="button"
                                :class="activeTab === 'matkul' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0-3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span style="font-size:12px;">Courses</span>
                            <span class="ml-auto bg-blue-100 text-blue-700 font-bold px-1.5 py-0.5 rounded-full" style="font-size:10px;">{{ $daftarMatkul->count() }}</span>
                        </button>

                        <button @click="activeTab = 'kelas'" type="button"
                                :class="activeTab === 'kelas' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span style="font-size:12px;">Classes</span>
                            <span class="ml-auto bg-blue-100 text-blue-700 font-bold px-1.5 py-0.5 rounded-full" style="font-size:10px;">{{ $daftarKelas->count() }}</span>
                        </button>
                    </div>
                </div>

                <div>
                    <h2 style="font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:0.07em; padding:0 8px; margin-bottom:4px;">Calendar Customization</h2>
                    
                    <div class="space-y-0.5">
                        <button @click="activeTab = 'ruangan'" type="button"
                                :class="activeTab === 'ruangan' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span style="font-size:12px;">Rooms</span>
                            <span class="ml-auto bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.5 rounded-full" style="font-size:10px;">{{ $daftarRuangan->count() }}</span>
                        </button>

                        <button @click="activeTab = 'jam'" type="button"
                                :class="activeTab === 'jam' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span style="font-size:12px;">Time Slots</span>
                            <span class="ml-auto bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.5 rounded-full" style="font-size:10px;">{{ $daftarJamSlot->count() }}</span>
                        </button>
                    </div>
                </div>

                <div>
                    <h2 style="font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:0.07em; padding:0 8px; margin-bottom:4px;">Configuration</h2>
                    
                    <div class="space-y-0.5">
                        <button @click="activeTab = 'pengaturan'" type="button"
                                :class="activeTab === 'pengaturan' ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span style="font-size:12px;">AI &amp; System Settings</span>
                            <span class="ml-auto text-yellow-400" style="font-size:12px;">✨</span>
                        </button>

                        <button @click="activeTab = 'akun'" type="button"
                                :class="activeTab === 'akun' ? 'bg-amber-50 text-amber-700 font-bold' : 'text-gray-700 font-medium hover:bg-gray-100'"
                                class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span style="font-size:12px;">Admin Account</span>
                            <span class="ml-auto text-amber-500" style="font-size:11px;">🔐</span>
                        </button>

                        <a href="{{ url('/live') }}" target="_blank"
                           class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left text-slate-700 font-medium hover:bg-slate-100 group">
                            <svg class="w-3.5 h-3.5 mr-2 shrink-0 text-slate-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span style="font-size:12px;">TV Monitor Mode</span>
                            <span class="ml-auto w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </a>
                    </div>
                </div>
            </nav>

            <div style="padding:10px 12px; border-top:1px solid #e5e7eb;">
                <a href="{{ url('/') }}" style="display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:8px; text-decoration:none; color:#64748b; margin-bottom:4px; transition:background 0.15s;"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span style="font-size:12px; font-weight:500;">To Schedule Page</span>
                </a>
                
                <form action="{{ url('/admin/logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="width:100%; display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:8px; border:none; background:transparent; color:#dc2626; cursor:pointer; transition:background 0.15s;"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                        <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span style="font-size:12px; font-weight:600;">Logout</span>
                    </button>
                </form>

                <div style="padding-top:8px; margin-top:6px; border-top:1px solid #f1f5f9; text-align:center;">
                    <p style="font-size:10px; color:#94a3b8; margin:0;">© {{ date('Y') }} <strong>Maki</strong>. All rights reserved.</p>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main style="margin-left:220px; flex:1; padding:32px; min-height:100vh;">
            @if(session('success') || session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                 style="position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999; width:100%; max-width:440px; padding:0 16px;">
                <div style="display:flex; align-items:center; padding:14px 16px; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.12); border:1px solid {{ session('error') ? '#fecaca' : '#bbf7d0' }}; background:{{ session('error') ? '#fef2f2' : '#f0fdf4' }};">
                    <span style="font-size:20px; margin-right:10px;">{{ session('error') ? '⚠️' : '✅' }}</span>
                    <p style="font-size:13px; font-weight:700; color:{{ session('error') ? '#dc2626' : '#16a34a' }}; margin:0;">
                        {{ session('error') ?? session('success') }}
                    </p>
                </div>
            </div>
            @endif

            <div style="margin-bottom:28px;">
                <h2 style="font-size:24px; font-weight:900; color:#1e293b; margin:0 0 4px;">
                    <span x-show="activeTab === 'dosen'">Lecturer Data</span>
                    <span x-show="activeTab === 'matkul'" x-cloak>Course Data</span>
                    <span x-show="activeTab === 'kelas'" x-cloak>Class Data</span>
                    <span x-show="activeTab === 'ruangan'" x-cloak>Room Customization</span>
                    <span x-show="activeTab === 'jam'" x-cloak>Time Slot Customization</span>
                    <span x-show="activeTab === 'pengaturan'" x-cloak>AI API &amp; System Settings</span>
                    <span x-show="activeTab === 'akun'" x-cloak>Admin Account Settings</span>
                </h2>
                <p style="font-size:13px; color:#94a3b8; margin:0;">
                    <span x-show="activeTab === 'dosen' || activeTab === 'matkul' || activeTab === 'kelas'">Manage master data for the scheduling system</span>
                    <span x-show="activeTab === 'ruangan'" x-cloak>Add, edit names, and set room identity colors</span>
                    <span x-show="activeTab === 'jam'" x-cloak>Configure start times and column headers for the calendar matrix</span>
                    <span x-show="activeTab === 'pengaturan'" x-cloak>Configure LLM integration (Gemini API key, model, parameters) and lab branding</span>
                    <span x-show="activeTab === 'akun'" x-cloak>Securely update administrator username and login password</span>
                </p>
            </div>

            {{-- ================= KONTEN TAB 1: DOSEN ================= --}}
            <div x-show="activeTab === 'dosen'">
                <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px;">+ Add New Lecturer</h3>
                    <form action="{{ url('/admin/dosen') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end;">
                        @csrf
                        <div style="width:200px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">NIP / NIDN</label>
                            <input type="text" name="nip" placeholder="e.g. 06123456" 
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Lecturer Name</label>
                            <input type="text" name="nama_dosen" placeholder="e.g. Dr. Ahmad Fauzi, M.Kes" required
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <button type="submit" style="background:#3b82f6; color:white; border:none; border-radius:10px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                            Add Lecturer
                        </button>
                    </form>
                </div>

                <div style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; width:60px;">#</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:150px;">NIP / NIDN</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">Lecturer Name</th>
                                <th style="padding:12px 20px; text-align:center; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarDosen as $index => $dosen)
                            <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding:12px 20px; color:#94a3b8; font-weight:600;">{{ $index + 1 }}</td>
                                <td style="padding:12px 20px; font-weight:500; color:#64748b;">{{ $dosen->nip ?? '-' }}</td>
                                <td style="padding:12px 20px; font-weight:600; color:#1e293b;">{{ $dosen->nama_dosen }}</td>
                                <td style="padding:12px 20px; text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                        <button type="button" @click="editDosenData = {{ json_encode($dosen) }}; showEditDosen = true"
                                                style="color:#3b82f6; background:none; border:none; cursor:pointer; padding:4px;" title="Edit">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteDosenData = {{ json_encode($dosen) }}; showDeleteDosen = true"
                                                style="color:#ef4444; background:none; border:none; cursor:pointer; padding:4px;" title="Delete">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding:40px; text-align:center; color:#94a3b8; font-style:italic;">No lecturer data available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= KONTEN TAB 2: MATA KULIAH ================= --}}
            <div x-show="activeTab === 'matkul'" x-cloak>
                <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px;">+ Add New Course</h3>
                    <form action="{{ url('/admin/matkul') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end;">
                        @csrf
                        <div style="width:150px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Course Code</label>
                            <input type="text" name="kode_matkul" placeholder="e.g. CS101" 
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Course Name</label>
                            <input type="text" name="nama_matkul" placeholder="e.g. Human Anatomy & Physiology" required
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <button type="submit" style="background:#3b82f6; color:white; border:none; border-radius:10px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                            Add Course
                        </button>
                    </form>
                </div>

                <div style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:60px;">#</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:120px;">Course Code</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Course Name</th>
                                <th style="padding:12px 20px; text-align:center; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarMatkul as $index => $matkul)
                            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding:12px 20px; color:#94a3b8; font-weight:600;">{{ $index + 1 }}</td>
                                <td style="padding:12px 20px; font-weight:500; color:#64748b;">{{ $matkul->kode_matkul ?? '-' }}</td>
                                <td style="padding:12px 20px; font-weight:600; color:#1e293b;">{{ $matkul->nama_matkul }}</td>
                                <td style="padding:12px 20px; text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                        <button type="button" @click="editMatkulData = {{ json_encode($matkul) }}; showEditMatkul = true"
                                                style="color:#3b82f6; background:none; border:none; cursor:pointer; padding:4px;" title="Edit">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteMatkulData = {{ json_encode($matkul) }}; showDeleteMatkul = true"
                                                style="color:#ef4444; background:none; border:none; cursor:pointer; padding:4px;" title="Delete">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding:40px; text-align:center; color:#94a3b8; font-style:italic;">No course data available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= KONTEN TAB 3: KELAS ================= --}}
            <div x-show="activeTab === 'kelas'" x-cloak>
                <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px;">+ Add New Class</h3>
                    <form action="{{ url('/admin/kelas') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end;">
                        @csrf
                        <div style="width:150px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Class Code</label>
                            <input type="text" name="kode_kelas" placeholder="e.g. K01" 
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Class Name</label>
                            <input type="text" name="nama_kelas" placeholder="e.g. Class 2024-A" required
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <button type="submit" style="background:#3b82f6; color:white; border:none; border-radius:10px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                            Add Class
                        </button>
                    </form>
                </div>

                <div style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:60px;">#</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:120px;">Class Code</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Class Name</th>
                                <th style="padding:12px 20px; text-align:center; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarKelas as $index => $kelas)
                            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding:12px 20px; color:#94a3b8; font-weight:600;">{{ $index + 1 }}</td>
                                <td style="padding:12px 20px; font-weight:500; color:#64748b;">{{ $kelas->kode_kelas ?? '-' }}</td>
                                <td style="padding:12px 20px; font-weight:600; color:#1e293b;">{{ $kelas->nama_kelas }}</td>
                                <td style="padding:12px 20px; text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                        <button type="button" @click="editKelasData = {{ json_encode($kelas) }}; showEditKelas = true"
                                                style="color:#3b82f6; background:none; border:none; cursor:pointer; padding:4px;" title="Edit">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteKelasData = {{ json_encode($kelas) }}; showDeleteKelas = true"
                                                style="color:#ef4444; background:none; border:none; cursor:pointer; padding:4px;" title="Delete">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding:40px; text-align:center; color:#94a3b8; font-style:italic;">No class data available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= KONTEN TAB 4: RUANGAN ================= --}}
            <div x-show="activeTab === 'ruangan'" x-cloak>
                <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px;">+ Add New Room</h3>
                    <form action="{{ url('/admin/ruangan') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end;">
                        @csrf
                        <div style="width:150px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Room Code</label>
                            <input type="text" name="kode_ruangan" placeholder="e.g. LAB-01" required
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Room Name</label>
                            <input type="text" name="nama_ruangan" placeholder="e.g. Multimedia & Networking Lab" required
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="width:180px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Indicator Color</label>
                            <div class="select-wrap">
                                <select name="warna" required
                                        style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box; background:white;"
                                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                                    @foreach($pilihanWarna as $valWarna => $labelWarna)
                                        <option value="{{ $valWarna }}">{{ $labelWarna }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" style="background:#3b82f6; color:white; border:none; border-radius:10px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                            Add Room
                        </button>
                    </form>
                </div>

                <div style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:60px;">#</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:130px;">Room Code</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Room Name</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:100px;">Color</th>
                                <th style="padding:12px 20px; text-align:center; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarRuangan as $index => $ruang)
                            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding:12px 20px; color:#94a3b8; font-weight:600;">{{ $index + 1 }}</td>
                                <td style="padding:12px 20px; font-weight:500; color:#64748b;">{{ $ruang->kode_ruangan }}</td>
                                <td style="padding:12px 20px; font-weight:600; color:#1e293b;">{{ $ruang->nama_ruangan }}</td>
                                <td style="padding:12px 20px;">
                                    <span class="inline-block w-4 h-4 rounded-full {{ $ruang->warna }} shadow-sm"></span>
                                </td>
                                <td style="padding:12px 20px; text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                        <button type="button" @click="editRuanganData = {{ json_encode($ruang) }}; showEditRuangan = true"
                                                style="color:#3b82f6; background:none; border:none; cursor:pointer; padding:4px;" title="Edit">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteRuanganData = {{ json_encode($ruang) }}; showDeleteRuangan = true"
                                                style="color:#ef4444; background:none; border:none; cursor:pointer; padding:4px;" title="Delete">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-style:italic;">No rooms found. Add a new room above.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= KONTEN TAB 5: JAM SLOT ================= --}}
            <div x-show="activeTab === 'jam'" x-cloak>
                <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 8px;">+ Add New Column Time Slot</h3>
                    <p style="font-size:12px; color:#94a3b8; margin:0 0 16px;">Each time slot represents one column in the calendar matrix table.</p>
                    <form action="{{ url('/admin/jam-slot') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                        @csrf
                        <div style="width:160px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Start Time (HH:MM)</label>
                            <input type="time" name="jam_mulai" required
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1; min-width:220px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Header Column Label</label>
                            <input type="text" name="label" placeholder="e.g. 07:00 - 08:40 or Morning Session 1"
                                    style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <button type="submit" style="background:#4f46e5; color:white; border:none; border-radius:10px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                            + Add Time Slot
                        </button>
                    </form>
                </div>

                <div style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:60px;">Order</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:130px;">Start Time</th>
                                <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Calendar Header Label</th>
                                <th style="padding:12px 20px; text-align:center; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarJamSlot as $index => $slot)
                            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding:12px 20px; color:#94a3b8; font-weight:600;">Column #{{ $index + 1 }}</td>
                                <td style="padding:12px 20px; font-family:monospace; font-weight:700; color:#4f46e5;">
                                    {{ substr($slot->jam_mulai, 0, 5) }}
                                </td>
                                <td style="padding:12px 20px; font-weight:600; color:#1e293b;">
                                    {{ $slot->label }}
                                </td>
                                <td style="padding:12px 20px; text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                        <button type="button" @click="editJamData = { id: {{ $slot->id }}, jam_mulai: '{{ substr($slot->jam_mulai, 0, 5) }}', label: '{{ addslashes($slot->label) }}' }; showEditJam = true"
                                                style="color:#4f46e5; background:none; border:none; cursor:pointer; padding:4px;" title="Edit">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteJamData = {{ json_encode($slot) }}; showDeleteJam = true"
                                                style="color:#ef4444; background:none; border:none; cursor:pointer; padding:4px;" title="Delete">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding:40px; text-align:center; color:#94a3b8; font-style:italic;">No time slots registered.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= TAB 6 CONTENT: AI & SYSTEM CONFIGURATION ================= --}}
            <div x-show="activeTab === 'pengaturan'" x-cloak>
                <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start;">
                    
                    {{-- Configuration Form --}}
                    <div style="background:white; border-radius:16px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:16px;">
                            <span style="font-size:24px;">⚙️</span>
                            <div>
                                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0;">AI API &amp; System Configuration</h3>
                                <p style="font-size:12px; color:#94a3b8; margin:0;">These settings are saved directly to the database without modifying the .env file</p>
                            </div>
                        </div>

                        <form action="{{ url('/admin/pengaturan') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- Laboratory Name --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Laboratory Name</label>
                                <input type="text" name="nama_lab" value="{{ $pengaturanAi['nama_lab'] }}" required
                                       placeholder="e.g. Medical Record Laboratory"
                                       style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                       onfocus="this.style.borderColor='#9333ea'" onblur="this.style.borderColor='#e2e8f0'">
                                <span style="font-size:11px; color:#94a3b8;">Displayed in page titles, sidebar, AI prompt context, and print sheets.</span>
                            </div>

                            {{-- Institution Name --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Institution / University / Faculty Name</label>
                                <input type="text" name="nama_instansi" value="{{ $pengaturanAi['nama_instansi'] ?? '' }}"
                                       placeholder="e.g. Faculty of Health Sciences - University Name"
                                       style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                       onfocus="this.style.borderColor='#9333ea'" onblur="this.style.borderColor='#e2e8f0'">
                                <span style="font-size:11px; color:#94a3b8;">Displayed on print sheet letterheads, headers, and system branding.</span>
                            </div>

                            {{-- Upload Logo --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Institution / Laboratory Logo</label>
                                <div style="display:flex; align-items:center; gap:14px; padding:12px; border:1.5px dashed #cbd5e1; border-radius:12px; background:#f8fafc;">
                                    <img src="{{ !empty($pengaturanAi['logo']) ? asset($pengaturanAi['logo']) : asset('images/logo.png') }}"
                                         alt="Current Logo"
                                         style="width:50px; height:50px; object-fit:contain; border-radius:10px; background:white; border:1px solid #e2e8f0; padding:4px; flex-shrink:0;">
                                    <div style="flex:1;">
                                        <input type="file" name="logo" accept="image/*"
                                               style="font-size:12px; color:#475569; width:100%;">
                                        <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">Format: PNG, JPG, WEBP, or SVG. Max 2MB. Leave blank to keep current logo.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Laboratory Contact --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Laboratory Contact / Helpdesk</label>
                                <input type="text" name="kontak_laboran" value="{{ $pengaturanAi['kontak_laboran'] }}"
                                       placeholder="e.g. +62 812-xxxx-xxxx / admin@campus.ac.id"
                                       style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                       onfocus="this.style.borderColor='#9333ea'" onblur="this.style.borderColor='#e2e8f0'">
                                <span style="font-size:11px; color:#94a3b8;">Displayed to users when the 2-minute booking edit window expires and on print sheets.</span>
                            </div>

                            {{-- DEFAULT SYSTEM LANGUAGE --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Default System Language</label>
                                <div class="select-wrap">
                                    <select name="default_language"
                                            style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box; background:white;"
                                            onfocus="this.style.borderColor='#9333ea'" onblur="this.style.borderColor='#e2e8f0'">
                                        <option value="en" {{ ($pengaturanAi['default_language'] ?? 'en') === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                                        <option value="id" {{ ($pengaturanAi['default_language'] ?? 'en') === 'id' ? 'selected' : '' }}>🇮🇩 Bahasa Indonesia</option>
                                    </select>
                                </div>
                                <span style="font-size:11px; color:#94a3b8;">Default language served to new visitors and signage screens.</span>
                            </div>

                            {{-- CALENDAR & ACADEMIC YEAR FILTER RANGE --}}
                            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px; margin-bottom:18px;">
                                <p style="font-size:12px; font-weight:800; color:#1e40af; margin:0 0 8px; display:flex; align-items:center; gap:6px;">
                                    <span>📅</span> Academic Year &amp; Semester Filter Range
                                </p>
                                <p style="font-size:11px; color:#475569; margin:0 0 12px;">
                                    Configure how many years forward and backward are displayed in the calendar semester filter dropdown.
                                </p>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; color:#1e3a8a; margin-bottom:4px;">
                                            Years Forward
                                        </label>
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <input type="number" name="filter_years_future" value="{{ $pengaturanAi['filter_years_future'] ?? 2 }}" min="0" max="10" required
                                                   style="width:100%; border:1.5px solid #93c5fd; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;"
                                                   onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#93c5fd'">
                                            <span style="font-size:11px; font-weight:600; color:#64748b; white-space:nowrap;">Years</span>
                                        </div>
                                        <span style="font-size:10px; color:#64748b; display:block; margin-top:4px;">Example: <code>2</code> (includes up to {{ (int)date('Y') + 2 }}).</span>
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; color:#1e3a8a; margin-bottom:4px;">
                                            Years Backward
                                        </label>
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <input type="number" name="filter_years_past" value="{{ $pengaturanAi['filter_years_past'] ?? 3 }}" min="0" max="15" required
                                                   style="width:100%; border:1.5px solid #93c5fd; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;"
                                                   onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#93c5fd'">
                                            <span style="font-size:11px; font-weight:600; color:#64748b; white-space:nowrap;">Years</span>
                                        </div>
                                        <span style="font-size:10px; color:#64748b; display:block; margin-top:4px;">Example: <code>3</code> (includes back to {{ (int)date('Y') - 3 }}).</span>
                                    </div>
                                </div>
                            </div>

                            <hr style="border:none; border-top:1px dashed #e2e8f0; margin:24px 0;">

                            {{-- SCHEDULE PRINT SIGNATORY SECTION --}}
                            <h4 style="font-size:13px; font-weight:800; color:#059669; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 16px; display:flex; align-items:center; gap:6px;">
                                <span>🖋️</span> Schedule Print Signatory Authorities
                            </h4>

                            {{-- Laboratory PIC --}}
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px; margin-bottom:18px;">
                                <p style="font-size:12px; font-weight:800; color:#166534; margin:0 0 12px; display:flex; align-items:center; gap:6px;">
                                    <span>👤</span> 1. Laboratory Person in Charge (Lab PIC)
                                </p>
                                <div style="margin-bottom:12px;">
                                    <label style="display:block; font-size:11px; font-weight:700; color:#374151; margin-bottom:4px;">PIC Position / Title</label>
                                    <input type="text" name="jabatan_pj" value="{{ $pengaturanAi['jabatan_pj'] ?? 'Head of Maki Space Laboratory' }}"
                                           placeholder="e.g. Head of Medical Record Laboratory"
                                           style="width:100%; border:1.5px solid #bbf7d0; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; color:#374151; margin-bottom:4px;">Full Name &amp; Academic Title</label>
                                        <input type="text" name="nama_pj" value="{{ $pengaturanAi['nama_pj'] ?? '' }}"
                                               placeholder="e.g. Agung Wardoyo, M.Cs."
                                               style="width:100%; border:1.5px solid #bbf7d0; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; color:#374151; margin-bottom:4px;">NIP / Employee ID</label>
                                        <input type="text" name="nip_pj" value="{{ $pengaturanAi['nip_pj'] ?? '' }}"
                                               placeholder="e.g. 198501012010121001"
                                               style="width:100%; border:1.5px solid #bbf7d0; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                                    </div>
                                </div>
                            </div>

                            {{-- Approving Authority --}}
                            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px; margin-bottom:20px;">
                                <p style="font-size:12px; font-weight:800; color:#1e40af; margin:0 0 12px; display:flex; align-items:center; gap:6px;">
                                    <span>🏛️</span> 2. Approving Authority / Department Head
                                </p>
                                <div style="margin-bottom:12px;">
                                    <label style="display:block; font-size:11px; font-weight:700; color:#374151; margin-bottom:4px;">Authority Position / Title</label>
                                    <input type="text" name="jabatan_atasan" value="{{ $pengaturanAi['jabatan_atasan'] ?? 'Head of Department' }}"
                                           placeholder="e.g. Head of Medical Records Department"
                                           style="width:100%; border:1.5px solid #bfdbfe; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; color:#374151; margin-bottom:4px;">Full Name &amp; Academic Title</label>
                                        <input type="text" name="nama_atasan" value="{{ $pengaturanAi['nama_atasan'] ?? '' }}"
                                               placeholder="e.g. Dr. Rano Indradi S, M.Kes"
                                               style="width:100%; border:1.5px solid #bfdbfe; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:11px; font-weight:700; color:#374151; margin-bottom:4px;">NIP / Employee ID</label>
                                        <input type="text" name="nip_atasan" value="{{ $pengaturanAi['nip_atasan'] ?? '' }}"
                                               placeholder="e.g. 197005121998031002"
                                               style="width:100%; border:1.5px solid #bfdbfe; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                                    </div>
                                </div>
                            </div>

                            <hr style="border:none; border-top:1px dashed #e2e8f0; margin:24px 0;">

                            <h4 style="font-size:13px; font-weight:800; color:#2563eb; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 16px; display:flex; align-items:center; gap:6px;">
                                <span>✨</span> Google Gemini AI Parameters
                            </h4>

                            {{-- Gemini API Key --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Google Gemini API Key</label>
                                <div style="display:flex; gap:8px;">
                                    <input :type="showApiKey ? 'text' : 'password'" name="gemini_api_key" value="{{ $pengaturanAi['gemini_api_key'] }}"
                                           placeholder="AIzaSy..."
                                           style="flex:1; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; font-family:monospace; outline:none; box-sizing:border-box;"
                                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
                                    <button type="button" @click="showApiKey = !showApiKey"
                                            style="padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; background:#f8fafc; cursor:pointer; font-size:12px; font-weight:600; color:#64748b;">
                                        <span x-text="showApiKey ? 'Hide' : 'Show'"></span>
                                    </button>
                                </div>
                                <span style="font-size:11px; color:#94a3b8;">Get a free official API Key at <strong>aistudio.google.com</strong>.</span>
                            </div>

                            {{-- Gemini Model --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Gemini Model</label>
                                <div class="select-wrap">
                                    <select name="gemini_model" required
                                            style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box; background:white;"
                                            onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
                                        @php
                                            $geminiModels = [
                                                'gemini-2.5-flash'      => 'Gemini 2.5 Flash (Recommended - High Accuracy)',
                                                'gemini-3.5-flash-lite' => 'Gemini 3.5 Flash Lite (Fastest & 500 RPD)',
                                                'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (Lightweight & Efficient)',
                                                'gemini-3.1-flash-lite' => 'Gemini 3.1 Flash Lite (Ultra-Low Latency - 500 RPD)',
                                                'gemini-2.0-flash'      => 'Gemini 2.0 Flash (Fast Response)',
                                                'gemini-3-flash'        => 'Gemini 3 Flash (Advanced Flash)',
                                                'gemini-2.5-pro'        => 'Gemini 2.5 Pro (Deep Reasoning & Analysis)',
                                                'gemini-3.6-flash'      => 'Gemini 3.6 Flash',
                                                'gemini-3.8-flash'      => 'Gemini 3.8 Flash',
                                            ];
                                        @endphp
                                        @foreach($geminiModels as $mVal => $mLabel)
                                            <option value="{{ $mVal }}" {{ ($pengaturanAi['gemini_model'] ?? 'gemini-2.5-flash') == $mVal ? 'selected' : '' }}>
                                                {{ $mLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Max Tokens & Temperature --}}
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Max Output Tokens</label>
                                    <input type="number" name="gemini_max_tokens" value="{{ $pengaturanAi['gemini_max_tokens'] ?? 500 }}" min="50" max="4000" required
                                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
                                    <span style="font-size:11px; color:#94a3b8;">AI response length limit (default: 500).</span>
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px;">Temperature (0.0 - 1.0)</label>
                                    <input type="number" step="0.05" name="gemini_temperature" value="{{ $pengaturanAi['gemini_temperature'] ?? 0.2 }}" min="0" max="1" required
                                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;"
                                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
                                    <span style="font-size:11px; color:#94a3b8;">0.2 = consistent &amp; factual responses.</span>
                                </div>
                            </div>

                            <button type="submit" style="width:100%; background:#2563eb; color:white; border:none; border-radius:12px; padding:12px; font-size:14px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(37,99,235,0.3); transition:all 0.2s;"
                                    onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                                💾 Save All Settings
                            </button>
                        </form>
                    </div>

                    {{-- Guide Card: How to Get Google Gemini API Key --}}
                    <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                            <span style="font-size:22px;">✨</span>
                            <h4 style="font-size:14px; font-weight:800; color:#1e293b; margin:0;">How to Get a Free Google Gemini API Key</h4>
                        </div>
                        
                        <p style="font-size:12px; color:#64748b; line-height:1.6; margin:0 0 16px;">
                            Google provides free API access for Gemini models with up to 15 requests per minute (RPM) via Google AI Studio—perfect for laboratory scheduling assistance!
                        </p>

                        <ol style="margin:0; padding-left:18px; font-size:12px; color:#374151; line-height:1.8;">
                            <li>Open the portal <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:#2563eb; font-weight:700; text-decoration:underline;">aistudio.google.com/app/apikey</a></li>
                            <li>Sign in with your <strong>Google</strong> account.</li>
                            <li>Click the blue <strong>"Create API key"</strong> button.</li>
                            <li>Select an existing Google Cloud project or automatically create a new one.</li>
                            <li>Copy the API key that begins with <code>AIzaSy...</code></li>
                            <li>Paste it into the form on the left and click <strong>Save All Settings</strong>.</li>
                        </ol>

                        <div style="margin-top:20px; padding:14px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;">
                            <p style="font-size:11px; font-weight:700; color:#1d4ed8; margin:0 0 4px;">🚀 Why Google Gemini:</p>
                            <p style="font-size:11px; color:#1e40af; margin:0; line-height:1.5;">
                                Highly accurate schedule context comprehension, fluent conversational responses, and generous free usage without requiring a credit card.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ================= TAB 7 CONTENT: ADMIN ACCOUNT SETTINGS ================= --}}
            <div x-show="activeTab === 'akun'" x-cloak>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; align-items:start;">
                    
                    {{-- Manage Admin Credentials Form --}}
                    <div style="background:white; border-radius:16px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                            <div style="width:38px; height:38px; border-radius:10px; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; font-size:18px;">
                                🔐
                            </div>
                            <div>
                                <h3 style="font-size:15px; font-weight:800; color:#1e293b; margin:0;">Manage Admin Credentials</h3>
                                <p style="font-size:12px; color:#64748b; margin:0;">Update administrator panel login username and password</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.account.update') }}" method="POST">
                            @csrf
                            
                            {{-- Username --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Administrator Username</label>
                                <input type="text" name="username" value="{{ $pengaturanAi['admin_username'] ?? 'admin' }}" required
                                       style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                                       onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e2e8f0'">
                                <span style="font-size:11px; color:#94a3b8; display:block; margin-top:4px;">Username used to access the admin panel (Default: <code>admin</code>).</span>
                            </div>

                            <hr style="border:none; border-top:1px dashed #e2e8f0; margin:20px 0;">

                            {{-- New Password --}}
                            <div x-data="{ showNewPass: false }" style="margin-bottom:14px;">
                                <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">New Password (Optional)</label>
                                <div style="position:relative;">
                                    <input :type="showNewPass ? 'text' : 'password'" name="new_password" placeholder="Leave blank to keep current password"
                                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
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
                                <span style="font-size:11px; color:#94a3b8; display:block; margin-top:4px;">Minimum 6 characters. Leave empty if you only want to change username.</span>
                            </div>

                            {{-- Confirm New Password --}}
                            <div x-data="{ showConfirmPass: false }" style="margin-bottom:20px;">
                                <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Confirm New Password</label>
                                <div style="position:relative;">
                                    <input :type="showConfirmPass ? 'text' : 'password'" name="new_password_confirmation" placeholder="Repeat new password here"
                                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
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

                            <hr style="border:none; border-top:1px dashed #e2e8f0; margin:20px 0;">

                            {{-- Recovery PIN / Key (Lupa Password) --}}
                            <div style="margin-bottom:20px;">
                                <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">
                                    🔑 Recovery PIN / Secret Key (Untuk Lupa Password)
                                </label>
                                <input type="text" name="recovery_key" value="{{ $pengaturanAi['admin_recovery_key'] ?? '' }}" placeholder="Masukkan PIN / kode pemulihan baru..."
                                       style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                                       onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e2e8f0'">
                                <span style="font-size:11px; color:#94a3b8; display:block; margin-top:4px;">
                                    Kode keamanan ini digunakan untuk mereset password di halaman login jika Anda lupa password. Isi saat instalasi awal dan simpan di tempat aman.
                                </span>
                            </div>

                            <hr style="border:none; border-top:1px dashed #e2e8f0; margin:20px 0;">

                            {{-- Current Password (Required Verification) --}}
                            <div x-data="{ showCurrentPass: false }" style="margin-bottom:24px; background:#fffbeb; border:1px solid #fef3c7; border-radius:12px; padding:14px;">
                                <label style="display:block; font-size:11px; font-weight:800; color:#92400e; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">
                                    🔒 Current Password (Required for Confirmation)
                                </label>
                                <div style="position:relative;">
                                    <input :type="showCurrentPass ? 'text' : 'password'" name="current_password" required placeholder="Enter current password"
                                           style="width:100%; border:1.5px solid #fde68a; border-radius:10px; padding:10px 42px 10px 14px; font-size:14px; outline:none; box-sizing:border-box; background:white;"
                                           onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#fde68a'">
                                    <button type="button" @click="showCurrentPass = !showCurrentPass" title="Tampilkan / Sembunyikan Password"
                                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#92400e; cursor:pointer; padding:6px; display:flex; align-items:center; justify-content:center;">
                                        <svg x-show="!showCurrentPass" style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showCurrentPass" x-cloak style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                    </button>
                                </div>
                                <span style="font-size:11px; color:#b45309; display:block; margin-top:4px;">Required to verify that you are the authorized account owner.</span>
                            </div>

                            <button type="submit" style="width:100%; background:#d97706; color:white; border:none; border-radius:12px; padding:12px; font-size:14px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(217,119,6,0.3); transition:all 0.2s;"
                                    onmouseover="this.style.background='#b45309'" onmouseout="this.style.background='#d97706'">
                                💾 Save Account Changes
                            </button>
                        </form>
                    </div>

                    {{-- Security Guide & Information --}}
                    <div style="background:white; border-radius:16px; padding:28px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                            <span style="font-size:22px;">🛡️</span>
                            <h4 style="font-size:14px; font-weight:800; color:#1e293b; margin:0;">Account Security Guidelines</h4>
                        </div>
                        
                        <p style="font-size:12px; color:#64748b; line-height:1.6; margin:0 0 16px;">
                            These account credentials control full administrative access to laboratory schedule management, lecturer master records, courses, rooms, and bulk deletions.
                        </p>

                        <div style="background:#f8fafc; border-radius:12px; padding:16px; border:1px solid #e2e8f0; margin-bottom:18px;">
                            <h5 style="font-size:12px; font-weight:700; color:#334155; margin:0 0 8px;">Recommended Security Tips:</h5>
                            <ul style="margin:0; padding-left:18px; font-size:12px; color:#475569; line-height:1.7;">
                                <li>Use a combination of at least <strong>8 characters</strong> including letters, numbers, and symbols.</li>
                                <li>Avoid using common default usernames like <code>admin</code> on public campus networks.</li>
                                <li>Do not share login credentials outside the authorized laboratory management team.</li>
                                <li>Periodically update your password at the beginning of each academic semester.</li>
                            </ul>
                        </div>

                        <div style="padding:14px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;">
                            <p style="font-size:11px; font-weight:700; color:#1d4ed8; margin:0 0 4px;">ℹ️ Current Credential Status:</p>
                            <p style="font-size:11px; color:#1e40af; margin:0; line-height:1.5;">
                                Active username: <strong>{{ Session::get('admin_username', 'admin') }}</strong>.<br>
                                Password securely stored using industry-standard hashing (Bcrypt).
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    {{-- ================= EDIT & DELETE LECTURER MODALS ================= --}}
    <div x-show="showEditDosen" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:420px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15);" @click.away="showEditDosen = false">
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 20px;">Edit Lecturer</h3>
                <form :action="'{{ url('/admin/dosen') }}/' + editDosenData.id" method="POST">
                    @csrf @method('PUT') 
                    
                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">NIP / NIDN</label>
                    <input type="text" name="nip" :value="editDosenData.nip"
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:12px;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                           
                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Lecturer Name</label>
                    <input type="text" name="nama_dosen" :value="editDosenData.nama_dosen" required
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:20px;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" @click="showEditDosen = false"
                                style="padding:9px 18px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                        <button type="submit" style="padding:9px 18px; border:none; border-radius:9px; background:#3b82f6; color:white; font-size:13px; font-weight:700; cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showDeleteDosen" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:380px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15); text-align:center;" @click.away="showDeleteDosen = false">
                <div style="width:52px; height:52px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg style="width:24px;height:24px;" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 8px;">Delete Lecturer?</h3>
                <p style="font-size:13px; color:#64748b; margin:0 0 24px;">Lecturer <strong x-text="deleteDosenData.nama_dosen"></strong> will be permanently deleted.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="showDeleteDosen = false"
                            style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                    <form :action="'{{ url('/admin/dosen') }}/' + deleteDosenData.id" method="POST" style="flex:1;">
                        @csrf @method('DELETE') 
                        <button type="submit" style="width:100%; padding:10px; border:none; border-radius:9px; background:#dc2626; color:white; font-size:13px; font-weight:700; cursor:pointer;">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= EDIT & DELETE COURSE MODALS ================= --}}
    <div x-show="showEditMatkul" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:420px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15);" @click.away="showEditMatkul = false">
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 20px;">Edit Course</h3>
                <form :action="'{{ url('/admin/matkul') }}/' + editMatkulData.id" method="POST">
                    @csrf @method('PUT')
                    
                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Course Code</label>
                    <input type="text" name="kode_matkul" :value="editMatkulData.kode_matkul"
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:12px;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">

                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Course Name</label>
                    <input type="text" name="nama_matkul" :value="editMatkulData.nama_matkul" required
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:20px;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                           
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" @click="showEditMatkul = false" style="padding:9px 18px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                        <button type="submit" style="padding:9px 18px; border:none; border-radius:9px; background:#3b82f6; color:white; font-size:13px; font-weight:700; cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showDeleteMatkul" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:380px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15); text-align:center;" @click.away="showDeleteMatkul = false">
                <div style="width:52px; height:52px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg style="width:24px;height:24px;" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 8px;">Delete Course?</h3>
                <p style="font-size:13px; color:#64748b; margin:0 0 24px;">Course <strong x-text="deleteMatkulData.nama_matkul"></strong> will be permanently deleted.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="showDeleteMatkul = false" style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                    <form :action="'{{ url('/admin/matkul') }}/' + deleteMatkulData.id" method="POST" style="flex:1;">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%; padding:10px; border:none; border-radius:9px; background:#dc2626; color:white; font-size:13px; font-weight:700; cursor:pointer;">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= EDIT & DELETE CLASS MODALS ================= --}}
    <div x-show="showEditKelas" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:420px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15);" @click.away="showEditKelas = false">
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 20px;">Edit Class</h3>
                <form :action="'{{ url('/admin/kelas') }}/' + editKelasData.id" method="POST">
                    @csrf @method('PUT')
                    
                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Class Code</label>
                    <input type="text" name="kode_kelas" :value="editKelasData.kode_kelas"
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:12px;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                           
                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Class Name</label>
                    <input type="text" name="nama_kelas" :value="editKelasData.nama_kelas" required
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:20px;"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                           
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" @click="showEditKelas = false" style="padding:9px 18px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                        <button type="submit" style="padding:9px 18px; border:none; border-radius:9px; background:#3b82f6; color:white; font-size:13px; font-weight:700; cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showDeleteKelas" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:380px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15); text-align:center;" @click.away="showDeleteKelas = false">
                <div style="width:52px; height:52px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg style="width:24px;height:24px;" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 8px;">Delete Class?</h3>
                <p style="font-size:13px; color:#64748b; margin:0 0 24px;">Class <strong x-text="deleteKelasData.nama_kelas"></strong> will be permanently deleted.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="showDeleteKelas = false" style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                    <form :action="'{{ url('/admin/kelas') }}/' + deleteKelasData.id" method="POST" style="flex:1;">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%; padding:10px; border:none; border-radius:9px; background:#dc2626; color:white; font-size:13px; font-weight:700; cursor:pointer;">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= EDIT & DELETE ROOM MODALS ================= --}}
    <div x-show="showEditRuangan" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:440px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15);" @click.away="showEditRuangan = false">
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 20px;">Edit Room</h3>
                <form :action="'{{ url('/admin/ruangan') }}/' + editRuanganData.id" method="POST">
                    @csrf @method('PUT')
                    
                    <div style="display:flex; gap:12px; margin-bottom:14px;">
                        <div style="width:140px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Room Code</label>
                            <input type="text" name="kode_ruangan" :value="editRuanganData.kode_ruangan" required
                                   style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Room Name</label>
                            <input type="text" name="nama_ruangan" :value="editRuanganData.nama_ruangan" required
                                   style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                    </div>

                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Indicator Color</label>
                    <div class="select-wrap" style="margin-bottom:20px;">
                        <select name="warna" required x-model="editRuanganData.warna"
                                style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; background:white;">
                            @foreach($pilihanWarna as $valWarna => $labelWarna)
                                <option value="{{ $valWarna }}">{{ $labelWarna }}</option>
                            @endforeach
                        </select>
                    </div>
                           
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" @click="showEditRuangan = false" style="padding:9px 18px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                        <button type="submit" style="padding:9px 18px; border:none; border-radius:9px; background:#3b82f6; color:white; font-size:13px; font-weight:700; cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showDeleteRuangan" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:380px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15); text-align:center;" @click.away="showDeleteRuangan = false">
                <div style="width:52px; height:52px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg style="width:24px;height:24px;" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 8px;">Delete Room?</h3>
                <p style="font-size:13px; color:#64748b; margin:0 0 24px;">Room <strong x-text="deleteRuanganData.nama_ruangan"></strong> will be permanently deleted.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="showDeleteRuangan = false" style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                    <form :action="'{{ url('/admin/ruangan') }}/' + deleteRuanganData.id" method="POST" style="flex:1;">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%; padding:10px; border:none; border-radius:9px; background:#dc2626; color:white; font-size:13px; font-weight:700; cursor:pointer;">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= EDIT & DELETE TIME SLOT MODALS ================= --}}
    <div x-show="showEditJam" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:440px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15);" @click.away="showEditJam = false">
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 20px;">Edit Time Slot</h3>
                <form :action="'{{ url('/admin/jam-slot') }}/' + editJamData.id" method="POST">
                    @csrf @method('PUT')
                    
                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Start Time (HH:MM)</label>
                    <input type="time" name="jam_mulai" :value="editJamData.jam_mulai" required
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:14px;"
                           onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#e2e8f0'">

                    <label style="display:block; font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Column Header Label</label>
                    <input type="text" name="label" :value="editJamData.label" required
                           placeholder="e.g. 07.00 - 08.40"
                           style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box; margin-bottom:20px;"
                           onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#e2e8f0'">
                           
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" @click="showEditJam = false" style="padding:9px 18px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                        <button type="submit" style="padding:9px 18px; border:none; border-radius:9px; background:#4f46e5; color:white; font-size:13px; font-weight:700; cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showDeleteJam" x-cloak
         style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:200; background:rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
            <div style="background:white; border-radius:16px; width:380px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.15); text-align:center;" @click.away="showDeleteJam = false">
                <div style="width:52px; height:52px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg style="width:24px;height:24px;" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 style="font-size:16px; font-weight:800; color:#1e293b; margin:0 0 8px;">Delete Time Slot?</h3>
                <p style="font-size:13px; color:#64748b; margin:0 0 24px;">Column slot <strong x-text="deleteJamData.label || deleteJamData.jam_mulai"></strong> will be deleted from the calendar schedule.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="showDeleteJam = false" style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:white; font-size:13px; font-weight:600; color:#64748b; cursor:pointer;">Cancel</button>
                    <form :action="'{{ url('/admin/jam-slot') }}/' + deleteJamData.id" method="POST" style="flex:1;">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%; padding:10px; border:none; border-radius:9px; background:#dc2626; color:white; font-size:13px; font-weight:700; cursor:pointer;">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>