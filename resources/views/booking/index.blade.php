<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Booking {{ $namaLab }}</title>
    
    <link rel="icon" type="image/png" href="{{ !empty($logoPath) ? asset($logoPath) : asset('images/logo.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        [x-cloak] { display: none !important; }
        .manajemen-table thead th { position: sticky !important; top: 0 !important; z-index: 40 !important; background-color: #f9fafb !important; }
        ::-webkit-scrollbar { height: 6px; width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── Konsisten chevron untuk semua native <select> ── */
        .select-wrap { position: relative; display: block; }
        .select-wrap select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 2.25rem !important; /* ruang untuk chevron */
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

        /* ── Button Card Styling (sesuai referensi media_1792159948035_6b8727f6.png) ── */
        .h-\[38px\] { height: 38px !important; }
        .w-\[38px\] { width: 38px !important; }
        .text-\[13px\] { font-size: 13px !important; line-height: 1.25rem !important; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }

        /* ── Stepper Tanggal (Persis Gambar 4 Referensi User) ── */
        .today-stepper-group {
            height: 38px !important;
            display: inline-flex !important;
            align-items: stretch !important;
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .today-stepper-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: #FFFFFF !important;
            color: #1E293B !important;
            text-decoration: none !important;
            transition: background-color 0.15s ease-in-out !important;
            height: 100% !important;
            box-sizing: border-box !important;
        }
        .today-stepper-btn:hover {
            background-color: #F8FAFC !important;
        }
        .today-stepper-arrow {
            width: 38px !important;
            color: #334155 !important;
            flex-shrink: 0 !important;
        }
        .today-stepper-label {
            padding: 0 20px !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            color: #1E293B !important;
            white-space: nowrap !important;
        }
        .today-stepper-divider {
            width: 1px !important;
            background-color: #E2E8F0 !important;
            height: 100% !important;
            flex-shrink: 0 !important;
        }

        /* ── Segmented Switcher (Persis seperti Gambar 2 Referensi User) ── */
        .segmented-switcher-track {
            background-color: #F1F3F7 !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 12px !important;
            padding: 3px 4px !important;
            height: 38px !important;
            display: inline-flex !important;
            align-items: center !important;
            box-sizing: border-box !important;
            gap: 4px !important;
        }
        .segmented-switcher-track .switcher-btn {
            border: none !important;
            outline: none !important;
            background-color: transparent !important;
            box-shadow: none !important;
            color: #475569 !important;
            font-weight: 600 !important;
            box-sizing: border-box !important;
            cursor: pointer !important;
            height: 30px !important;
            padding: 0 14px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            font-size: 13px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            border-radius: 9px !important;
            transition: all 0.15s ease-in-out !important;
            font-family: inherit !important;
            text-decoration: none !important;
        }
        .segmented-switcher-track .switcher-btn:hover {
            color: #0F172A !important;
        }
        .segmented-switcher-track .switcher-btn.pill-active {
            background-color: #FFFFFF !important;
            color: #2563EB !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
            font-weight: 700 !important;
        }
    </style>
</head>

<body class="bg-[#F5F5F7] h-screen overflow-hidden flex relative" 
      x-data="{ 
        sidebarOpen: false, showModal: false, showDetailModal: false, showKelolaModal: false, showEditModal: false, 
        showAiChat: (function() { try { return localStorage.getItem('maki_ai_open') === 'true'; } catch(e) { return false; } })(),
        showGuide: false,
        viewMode: '{{ $viewMode }}',
        splitDate: '{{ $splitDate }}',
        splitDateFormatted: '{{ $splitDateFormatted }}',
        activeSplitRooms: {{ json_encode($daftarRuanganDb->pluck('nama_ruangan')->toArray()) }},
        allRooms: {{ json_encode($daftarRuanganDb->map(fn($r) => ['nama_ruangan' => $r->nama_ruangan, 'warna' => $r->warna])->toArray()) }},
        activeTab: 'dosen', activeEditTab: 'dosen', selectedTanggal: '', selectedTanggalFormat: '', selectedWaktuMulai: '',
        selectedRuangan: '{{ $ruanganAktif }}', selectedBooking: {}, editBookingData: {}, editSource: 'kelola',
        selectedBookingTanggalFormat: '',

        openAiChat() {
            this.showAiChat = true;
            try { localStorage.setItem('maki_ai_open', 'true'); } catch(e) {}
        },
        closeAiChat() {
            this.showAiChat = false;
            try { localStorage.setItem('maki_ai_open', 'false'); } catch(e) {}
        },

        toggleSplitRoom(namaRuangan) {
            if (this.activeSplitRooms.includes(namaRuangan)) {
                if (this.activeSplitRooms.length > 1) {
                    this.activeSplitRooms = this.activeSplitRooms.filter(r => r !== namaRuangan);
                }
            } else {
                this.activeSplitRooms.push(namaRuangan);
            }
        },
        changeSplitDate(newDate) {
            if (!newDate) return;
            window.location.href = '{{ url('/') }}?ruangan=' + encodeURIComponent(this.selectedRuangan) + '&date=' + newDate.substring(0,7) + '-01&split_date=' + newDate + '&view=split';
        },
        openBooking(tanggal, tanggalFormat, waktu, ruangan = null) {
            this.showDetailModal = false; this.selectedTanggal = tanggal; this.selectedTanggalFormat = tanggalFormat;
            this.selectedWaktuMulai = waktu;
            if (ruangan) {
                this.selectedRuangan = ruangan;
            }
            this.showModal = true;
        },
        openDetail(booking, tanggalFormat) {
            this.showModal = false;
            let b = Object.assign({}, booking);
            b.waktu_mulai = b.waktu_mulai || b.start_time || '';
            b.waktu_selesai = b.waktu_selesai || b.end_time || '';
            b.tanggal = b.tanggal || b.date || '';
            b.ruangan = b.ruangan || b.room || '';
            b.nama_peminjam_bebas = b.nama_peminjam_bebas || b.general_borrower_name || '';
            b.keperluan_bebas = b.keperluan_bebas || b.general_purpose || '';
            b.dosen = b.dosen || b.lecturer || null;
            b.mata_kuliah = b.mata_kuliah || b.course || null;
            b.kelas = b.kelas || b.course_class || b.courseClass || null;
            this.selectedBooking = b;
            this.selectedBookingTanggalFormat = tanggalFormat;
            this.showDetailModal = true;
        },
        openEdit(booking, source = 'kelola') {
            let b = Object.assign({}, booking);
            b.tanggal = b.tanggal || b.date || '';
            b.waktu_mulai = b.waktu_mulai || b.start_time || '';
            b.waktu_selesai = b.waktu_selesai || b.end_time || '';
            b.dosen_id = b.dosen_id || b.lecturer_id || '';
            b.mata_kuliah_id = b.mata_kuliah_id || b.course_id || '';
            b.kelas_id = b.kelas_id || b.course_class_id || '';
            b.nama_peminjam_bebas = b.nama_peminjam_bebas || b.general_borrower_name || '';
            b.keperluan_bebas = b.keperluan_bebas || b.general_purpose || '';
            b.jenis_jadwal = (b.jenis_jadwal === 'Tetap' || b.schedule_type === 'Regular') ? 'Tetap' : 'Insidental';
            b.dosen = b.dosen || (b.lecturer ? { nama_dosen: b.lecturer.nama_dosen || b.lecturer.name } : null);
            b.mata_kuliah = b.mata_kuliah || (b.course ? { nama_matkul: b.course.nama_matkul || b.course.name } : null);
            b.kelas = b.kelas || (b.course_class ? { nama_kelas: b.course_class.nama_kelas || b.course_class.name } : (b.courseClass ? { nama_kelas: b.courseClass.nama_kelas || b.courseClass.name } : null));

            this.editBookingData = b; 
            const isDosen = (b.kategori_peminjam === 'Dosen' || b.borrower_category === 'Lecturer');
            this.activeEditTab = isDosen ? 'dosen' : 'umum';
            this.editSource = source; 
            this.showDetailModal = false; 
            this.showKelolaModal = false; 
            this.showEditModal = true;
        },
        init() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.showEditModal) { this.showEditModal = false; this.showKelolaModal = true; } 
                    else if (this.showKelolaModal) { this.showKelolaModal = false; } 
                    else if (this.showDetailModal) { this.showDetailModal = false; } 
                    else if (this.showModal) { this.showModal = false; } 
                    else if (this.showAiChat) { this.closeAiChat(); }
                    else if (this.showGuide) { this.showGuide = false; }
                    else if (this.sidebarOpen) { this.sidebarOpen = false; }
                }
            });
        }
      }">

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-transition.opacity x-cloak></div>

    <aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" class="-translate-x-full fixed inset-y-0 left-0 z-50 w-56 bg-white/95 backdrop-blur-md border-r border-gray-200 flex flex-col shrink-0 transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 shadow-2xl lg:shadow-none">
        
        <div class="px-3 py-2.5 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2 overflow-hidden min-w-0">
                <img src="{{ !empty($logoPath) ? asset($logoPath) : asset('images/logo.png') }}" alt="Logo" class="w-7 h-7 object-contain shrink-0 drop-shadow-sm rounded">
                <div class="min-w-0">
                    <h1 class="font-black text-blue-900 leading-tight truncate" style="font-size:12px;">{{ $namaLab }} {{ __('Schedule') }}</h1>
                    @if(!empty($namaInstansi))
                        <p class="text-gray-400 font-medium leading-tight mt-0.5 truncate" style="font-size:10px;" title="{{ $namaInstansi }}">{{ $namaInstansi }}</p>
                    @endif
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-red-500 p-1 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        
        <div class="px-2 py-2 flex-1 overflow-y-auto">
            <h2 class="font-bold text-gray-400 uppercase tracking-wider mb-1.5 px-1" style="font-size:10px;">{{ __('Rooms') }}</h2>
            <ul class="space-y-0.5">
                @foreach($daftarRuanganDb as $ruangDb)
                    @php $isActive = $ruanganAktif == $ruangDb->nama_ruangan; @endphp
                    <li>
                        <a href="{{ url('/') }}?ruangan={{ urlencode($ruangDb->nama_ruangan) }}&date={{ request('date', date('Y-m-d')) }}" 
                           class="flex items-center px-2 py-1.5 rounded-lg transition {{ $isActive ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-100' }}">
                            <span class="w-2 h-2 rounded-full mr-2 shrink-0" style="background-color: {{ $ruangDb->warna_hex }};"></span>
                            <span class="{{ $isActive ? 'text-blue-700 font-bold' : 'text-gray-700 font-medium' }} leading-tight" style="font-size:12px;">{{ $ruangDb->nama_ruangan }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        
        <div class="px-2 py-2 border-t border-gray-200 bg-white space-y-0.5 shrink-0">
            {{-- Tombol Split View --}}
            <button @click="viewMode = (viewMode === 'split' ? 'calendar' : 'split'); sidebarOpen = false"
                    :class="viewMode === 'split' ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-200' : 'text-gray-700 hover:bg-gray-100 font-medium'"
                    class="w-full flex items-center px-2 py-1.5 rounded-lg transition text-left">
                <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2.5" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18"/></svg>
                <span style="font-size:12px;" x-text="viewMode === 'split' ? '{{ __('Back to Calendar') }}' : '{{ __('Room Split View') }}'"></span>
            </button>

            {{-- Tombol Panduan --}}
            <button @click="showGuide = true; sidebarOpen = false"
                    class="w-full flex items-center px-2 py-1.5 rounded-lg text-gray-700 hover:bg-amber-50 hover:text-amber-800 transition font-medium text-left">
                <span class="mr-2 shrink-0">📖</span>
                <span style="font-size:12px;">{{ __('User Guide') }}</span>
            </button>

            @if($isAdmin)
                <a href="{{ url('/admin') }}"
                   class="w-full flex items-center px-2 py-1.5 rounded-lg text-blue-700 bg-blue-50/70 hover:bg-blue-100 transition font-bold">
                    <span class="mr-2 shrink-0">⚙️</span>
                    <span style="font-size:12px;">{{ __('Admin Panel') }}</span>
                </a>
                <form action="{{ url('/admin/logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center px-2 py-1.5 rounded-lg text-red-600 hover:bg-red-50 transition font-medium text-left">
                        <span class="mr-2 shrink-0">🚪</span>
                        <span style="font-size:12px;">{{ __('Admin Logout') }}</span>
                    </button>
                </form>
            @else
                <a href="{{ url('/admin/login') }}"
                   class="w-full flex items-center px-2 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition font-medium">
                    <span class="mr-2 shrink-0">🔒</span>
                    <span style="font-size:12px;">{{ __('Admin Login') }}</span>
                </a>
            @endif

            {{-- Language Toggle in Sidebar --}}
            <div class="px-2 py-1.5 flex items-center justify-between text-xs text-slate-500 border-t border-gray-100">
                <span class="font-medium text-[11px]">{{ __('Language') }}:</span>
                <div class="flex items-center gap-1 font-bold text-[11px]">
                    <a href="{{ route('lang.switch', 'en') }}" class="px-1.5 py-0.5 rounded {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">EN</a>
                    <span>/</span>
                    <a href="{{ route('lang.switch', 'id') }}" class="px-1.5 py-0.5 rounded {{ app()->getLocale() === 'id' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">ID</a>
                </div>
            </div>

            <div class="pt-2 text-center border-t border-gray-100 text-gray-400 font-medium" style="font-size:10px;">
                © {{ date('Y') }} Maki. All rights reserved.
            </div>
        </div>
    </aside>

    @if(session('error') || session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4"
         class="fixed top-5 left-1/2 -translate-x-1/2 w-full max-w-[90%] lg:max-w-md px-4 z-[9999]">
        <div class="flex items-center p-4 rounded-xl shadow-2xl border {{ session('error') ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' }}">
            <div class="flex-shrink-0 mr-3"><span class="text-2xl">{{ session('error') ? '⚠️' : '✅' }}</span></div>
            <div class="flex-1">
                <p class="text-sm font-bold leading-none mb-1">{{ session('error') ? __('System Notice') : __('Success') }}</p>
                <p class="text-xs font-medium opacity-90">{{ session('error') ?? session('success') }}</p>
            </div>
            <button @click="show = false" class="ml-auto pl-3 opacity-50 hover:opacity-100 text-lg">&times;</button>
        </div>
    </div>
    @endif

    <main class="flex-1 flex flex-col bg-white overflow-hidden w-full relative">   
        
        <header class="border-b border-gray-200 flex flex-col justify-center px-4 lg:px-6 shrink-0 bg-white relative z-40">
            
            {{-- Baris 1: Judul Bulan (Kiri) + Stepper Tanggal, Filter, & Mode Kalender/Split View (Kanan) --}}
            <div class="flex flex-wrap items-center justify-between w-full py-2.5 gap-2 lg:gap-3">
                {{-- Kiri: Judul Bulan & Tombol Sidebar Mobile --}}
                <div class="flex items-center gap-2.5">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 transition shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h1 class="text-xl lg:text-2xl font-black text-gray-900 leading-tight">{{ $bulanTampil }}</h1>
                </div>

                {{-- Kanan: Stepper Tanggal + Filter Semester + Language Switcher + Segmented Switcher --}}
                <div class="flex items-center gap-2.5 flex-wrap justify-end">
                    {{-- Stepper Bulan: Prev - Today - Next (Persis Gambar 4 Referensi User) --}}
                    <div class="today-stepper-group shrink-0">
                        <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ $prevMonth }}" class="today-stepper-btn today-stepper-arrow" title="{{ __('Previous Month') }}">
                            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <div class="today-stepper-divider"></div>
                        <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ date('Y-m-d') }}#hari-ini" class="today-stepper-btn today-stepper-label">
                            {{ __('Today') }}
                        </a>
                        <div class="today-stepper-divider"></div>
                        <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ $nextMonth }}" class="today-stepper-btn today-stepper-arrow" title="{{ __('Next Month') }}">
                            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    {{-- Dropdown Filter Tahun & Semester (Format Bersih Sederhana Persis Dropdown Print) --}}
                    <div class="relative" x-data="{ openFilter: false }">
                        <button @click="openFilter = !openFilter" @click.away="openFilter = false" type="button" 
                                class="w-[38px] h-[38px] bg-white border border-[#E2E8F0] hover:bg-gray-50 text-blue-600 rounded-lg transition shadow-sm flex items-center justify-center shrink-0 cursor-pointer" 
                                title="{{ __('Select Academic Year & Semester') }}">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        </button>
                        
                        <div x-show="openFilter" x-transition x-cloak class="absolute right-0 mt-1.5 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden text-xs max-h-[300px] overflow-y-auto">
                             @php
                                 $tglAktifStr = request('date', date('Y-m-d'));
                                 $thnAktif = (int) date('Y', strtotime($tglAktifStr));
                                 $blnAktif = (int) date('m', strtotime($tglAktifStr));
                                 $smtAktif = ($blnAktif >= 8 || $blnAktif == 1) ? 'Odd' : 'Even';
                                 $tahunSekarang = (int) date('Y');
                                 $yearsFuture = isset($filterYearsFuture) ? (int)$filterYearsFuture : (int)\App\Models\Setting::get('filter_years_future', 2);
                                 $yearsPast   = isset($filterYearsPast) ? (int)$filterYearsPast : (int)\App\Models\Setting::get('filter_years_past', 3);
                                 $startYear   = $tahunSekarang + $yearsFuture;
                                 $endYear     = $tahunSekarang - $yearsPast;
                             @endphp
                             
                             @for($t = $startYear; $t >= $endYear; $t--)
                                 @foreach(['Odd' => '08', 'Even' => '02'] as $label => $bln)
                                     @php $isAktif = ($thnAktif == $t && $smtAktif == $label); @endphp
                                     <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ $t }}-{{ $bln }}-01" 
                                        class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 last:border-b-0 transition {{ $isAktif ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium' }}">
                                         <span>{{ $t }} &ndash; {{ __($label . ' Semester') }}</span>
                                         @if($isAktif)
                                             <span class="text-blue-600 font-bold text-sm">✓</span>
                                         @endif
                                     </a>
                                 @endforeach
                             @endfor
                        </div>
                    </div>

                    {{-- Language Switcher (EN / ID) - Mengikuti Desain Segmented Track Calendar & Split View --}}
                    <div class="segmented-switcher-track shrink-0" title="Change Language / Ubah Bahasa">
                        <a href="{{ route('lang.switch', 'en') }}" 
                           class="switcher-btn {{ app()->getLocale() === 'en' ? 'pill-active' : '' }}"
                           style="padding: 0 10px !important; min-width: 32px;"
                           title="Switch to English">
                           EN
                        </a>
                        <a href="{{ route('lang.switch', 'id') }}" 
                           class="switcher-btn {{ app()->getLocale() === 'id' ? 'pill-active' : '' }}"
                           style="padding: 0 10px !important; min-width: 32px;"
                           title="Ubah ke Bahasa Indonesia">
                           ID
                        </a>
                    </div>

                    {{-- Segmented Control: Calendar vs Split View (Persis seperti Gambar 2 Referensi User) --}}
                    <div class="segmented-switcher-track shrink-0">
                        <button type="button" @click="viewMode = 'calendar'"
                                class="switcher-btn {{ $viewMode === 'calendar' ? 'pill-active' : '' }}"
                                :class="{ 'pill-active': (viewMode || 'calendar') === 'calendar' }">
                            <svg class="w-4 h-4 shrink-0 transition-colors" :class="(viewMode || 'calendar') === 'calendar' ? 'text-blue-600' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span :class="(viewMode || 'calendar') === 'calendar' ? 'text-blue-600 font-bold' : 'text-slate-600 font-semibold'">{{ __('Calendar') }}</span>
                        </button>
                        <button type="button" @click="viewMode = 'split'"
                                class="switcher-btn {{ $viewMode === 'split' ? 'pill-active' : '' }}"
                                :class="{ 'pill-active': viewMode === 'split' }">
                            <svg class="w-4 h-4 shrink-0 transition-colors" :class="viewMode === 'split' ? 'text-blue-600' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <rect width="18" height="18" x="3" y="3" rx="2.5" stroke-width="2.2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 3v18"/>
                            </svg>
                            <span :class="viewMode === 'split' ? 'text-blue-600 font-bold' : 'text-slate-600 font-semibold'">{{ __('Split View') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Garis Batas Pemisah Baris 1 & Baris 2 --}}
            <div class="w-full border-t border-gray-100"></div>

            {{-- Baris 2: Subtitle Info Ruangan (Kiri) + SELURUH MENU AKSI DI SEBELAH KANAN (Tepat di Tengah-Tengah Antara Garis Batas) --}}
            <div class="flex flex-wrap items-center justify-between w-full py-2.5 gap-2">
                
                {{-- Kiri: Konteks Semester & Ruangan Aktif --}}
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-extrabold text-gray-400 uppercase tracking-wider text-[11px]">{{ $teksSemester }}</span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg text-xs shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        <span>{{ $ruanganAktif }}</span>
                    </span>
                </div>
                
                {{-- Kanan: SEMUA MENU DAN FITUR DIRAPIKAN BERJAJAR DI SEBELAH KANAN (PERSIS SEPERTI GAMBAR CONTOH USER) --}}
                <div class="flex items-center gap-2.5 flex-wrap justify-end">
                    
                    {{-- TOMBOL TANYA AI (ASK MAKI) --}}
                    <button @click="openAiChat()" 
                            class="h-[38px] px-4 bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:border-gray-400 rounded-lg transition shadow-sm flex items-center font-semibold text-[13px] whitespace-nowrap gap-2"
                            title="Open Maki AI Assistant">
                        <span class="text-sm">✨</span>
                        <span>{{ __('Ask Maki') }}</span>
                    </button>

                    {{-- TOMBOL LIVE MONITOR TV --}}
                    <a href="{{ url('/live') }}" target="_blank" 
                       class="h-[38px] px-4 bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:border-gray-400 rounded-lg transition shadow-sm flex items-center font-semibold text-[13px] whitespace-nowrap gap-2" 
                       title="Open Real-time TV Display Monitor">
                        <span class="text-sm">🖥️</span>
                        <span>{{ __('TV Monitor') }}</span>
                    </a>

                    {{-- TOMBOL CETAK DROPDOWN --}}
                    <div class="relative" x-data="{ showPrintMenu: false }">
                        <button type="button" @click="showPrintMenu = !showPrintMenu" @click.away="showPrintMenu = false" 
                                class="h-[38px] px-4 bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:border-gray-400 rounded-lg transition shadow-sm flex items-center font-semibold text-[13px] whitespace-nowrap gap-2">
                            <span class="text-sm">🖨️</span>
                            <span>{{ __('Print') }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <div x-show="showPrintMenu" x-transition x-cloak class="absolute right-0 mt-1.5 w-52 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden text-xs">
                            <a href="{{ url('/booking/print') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ request('date', date('Y-m-d')) }}" target="_blank" class="block px-4 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-bold border-b border-gray-100">
                                📑 {{ __('Print All Schedules') }}
                            </a>
                            <a href="{{ url('/booking/print') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ request('date', date('Y-m-d')) }}&jenis=Tetap" target="_blank" class="block px-4 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium border-b border-gray-100">
                                📌 {{ __('Regular Schedules Only') }}
                            </a>
                            <a href="{{ url('/booking/print') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ request('date', date('Y-m-d')) }}&jenis=Insidental" target="_blank" class="block px-4 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                ⚡ {{ __('Incidental Schedules Only') }}
                            </a>
                        </div>
                    </div>
                    
                    {{-- TOMBOL KELOLA JADWAL (HANYA ADMIN) --}}
                    @if($isAdmin)
                    <button @click="showKelolaModal = true" 
                            class="h-[38px] px-4 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 rounded-lg transition shadow-sm flex items-center font-bold text-[13px] whitespace-nowrap gap-2">
                        <span class="text-sm">⚙️</span>
                        <span>{{ __('Manage Schedules') }}</span>
                    </button>
                    @endif

                    {{-- TOMBOL PINJAM (PRIMARY CTA) --}}
                    <button @click="showModal = true" 
                            class="h-[38px] px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm flex items-center font-bold text-[13px] whitespace-nowrap gap-1.5">
                        <span class="text-base font-black leading-none">+</span>
                        <span>{{ __('Book Room') }}</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- ═══════════ TAMPILAN KALENDER BULANAN (viewMode = 'calendar') ═══════════ --}}
        <div x-show="viewMode === 'calendar'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 overflow-auto bg-gray-50 relative w-full">
            @php
                // Bangun array jam mulai dari DB untuk dipakai di loop sel
                $jamSlotArr = $daftarJamSlot->pluck('jam_mulai')->map(fn($j) => substr($j, 0, 5))->toArray();
            @endphp
            <table class="w-full table-fixed border-separate border-spacing-0 min-w-[1200px] text-sm text-left">
                <thead class="sticky top-0 z-30 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    <tr>
                        <th class="sticky left-0 z-30 bg-gray-200 border-b border-r border-gray-300 py-4 px-4 w-48 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">{{ __('Day | Date') }}</th>
                        @foreach($daftarJamSlot as $jamSlotItem)
                            <th class="bg-gray-100 border-b border-r border-gray-300 py-4 px-2 text-center w-40">{{ $jamSlotItem->label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($hariSebulan as $hari)
                    @php $isToday = $hari['tanggal_asli'] == date('Y-m-d'); @endphp
                    <tr {!! $isToday ? 'id="hari-ini"' : '' !!} class="transition group {{ $isToday ? 'bg-blue-50/60' : 'hover:bg-blue-50/30' }}">
                        <td class="sticky left-0 z-20 {{ $isToday ? 'bg-blue-50 border-l-4 border-blue-500 text-blue-900 font-bold' : 'bg-white group-hover:bg-blue-50/30 text-gray-700 border-l-4 border-transparent' }} border-b border-r border-gray-300 py-3 px-4 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] transition-colors truncate">
                            {{ $hari['format_tampil'] }}
                        </td>
                        @for ($i = 0; $i < count($jamSlotArr); $i++)
                            @php
                                $waktuSlot = $jamSlotArr[$i];
                                $waktuSlotSelesai = $jamSlotArr[$i + 1] ?? '23:59';
                                $jadwalDiSlotIni = $semuaJadwal->filter(function($j) use ($hari, $waktuSlot, $waktuSlotSelesai) { return $j->tanggal == $hari['tanggal_asli'] && substr($j->waktu_mulai, 0, 5) >= $waktuSlot && substr($j->waktu_mulai, 0, 5) < $waktuSlotSelesai; });
                            @endphp
                            <td id="cell-{{ $hari['tanggal_asli'] }}-{{ $i }}" @click="openBooking('{{ $hari['tanggal_asli'] }}', '{{ $hari['format_tampil'] }}', '{{ $waktuSlot }}')" class="border-b border-r border-gray-200 p-2 cursor-pointer relative h-20 min-w-[160px] hover:bg-gray-50/50 transition-all duration-500 {{ (session('cleared_slot') && session('cleared_slot')['tanggal'] == $hari['tanggal_asli'] && session('cleared_slot')['waktu'] == $waktuSlot) ? 'bg-green-100 ring-2 ring-inset ring-green-400' : '' }}">                                
                                @if($jadwalDiSlotIni->count() > 0)
                                    @foreach($jadwalDiSlotIni as $j)
                                        @php 
                                            $isConflict = session('conflicting_id') == $j->id; $isNew = is_array(session('new_booking_ids')) && in_array($j->id, session('new_booking_ids')); $dayOfWeek = date('N', strtotime($hari['tanggal_asli']));
                                            if ($isConflict) { $blokWarna = 'bg-red-500 text-white animate-pulse ring-4 ring-red-200'; } elseif ($isNew) { $blokWarna = 'bg-green-500 text-white animate-pulse ring-4 ring-green-200 shadow-lg scale-105 z-20'; } else {
                                                $baseColor = match((int)$dayOfWeek) { 1 => 'bg-rose-100 text-rose-800 border-rose-200 hover:bg-rose-200', 2 => 'bg-orange-100 text-orange-800 border-orange-200 hover:bg-orange-200', 3 => 'bg-amber-100 text-amber-800 border-amber-200 hover:bg-amber-200', 4 => 'bg-emerald-100 text-emerald-800 border-emerald-200 hover:bg-emerald-200', 5 => 'bg-sky-100 text-sky-800 border-sky-200 hover:bg-sky-200', 6 => 'bg-indigo-100 text-indigo-800 border-indigo-200 hover:bg-indigo-200', 7 => 'bg-fuchsia-100 text-fuchsia-800 border-fuchsia-200 hover:bg-fuchsia-200', default => 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200' }; $blokWarna = $baseColor . ' border shadow-sm';
                                            }
                                        @endphp
                                        <div id="booking-{{ $j->id }}" @click.stop="openDetail({{ json_encode($j) }}, '{{ $hari['format_tampil'] }}')" class="p-2 rounded-lg text-[10px] leading-tight mb-1 relative cursor-pointer transition-all duration-500 {{ $blokWarna }}">     
                                            <div class="font-bold truncate uppercase">{{ $isConflict ? '⚠️ CONFLICT HERE!' : ($isNew ? '✨ NEWLY SAVED' : ($j->mata_kuliah->nama_matkul ?? $j->keperluan_bebas)) }} {{ $j->kelas->nama_kelas ?? '-' }}</div>
                                            <div class="opacity-90 truncate font-medium mt-0.5">{{ $j->dosen->nama_dosen ?? $j->nama_peminjam_bebas }}</div>
                                            <div class="mt-1 font-mono font-bold">{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    @if(session('cleared_slot') && session('cleared_slot')['tanggal'] == $hari['tanggal_asli'] && session('cleared_slot')['waktu'] == $waktuSlot)
                                        <div class="flex flex-col items-center justify-center h-full animate-pulse"><span class="text-green-600 font-bold text-[10px] uppercase">✨ {{ __('Available Slot !') }}</span></div>
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition duration-200"><span class="text-blue-600 font-medium text-[10px] bg-white border border-blue-200 px-3 py-1 rounded-full shadow-sm">+ {{ __('Book') }}</span></div>
                                    @endif
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ═══════════ TAMPILAN SPLIT VIEW (viewMode = 'split') ═══════════ --}}
        <div x-show="viewMode === 'split'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak class="flex-1 flex flex-col overflow-hidden bg-gray-50 w-full">

            {{-- Toolbar Split View: Navigasi Tanggal + Toggle Ruangan --}}
            <div class="bg-white border-b border-gray-200 px-4 py-2 overflow-x-auto shrink-0">
                <div class="flex items-center gap-3 min-w-max">
                {{-- Navigasi Tanggal (Stepper persis seperti header + tombol tanggal interaktif) --}}
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-600 uppercase tracking-wide shrink-0" style="font-size: 12px;">📆 {{ __('Date:') }}</span>
                    
                    {{-- Stepper: [ < ] [ > ] --}}
                    <div class="flex items-center bg-white border border-gray-200 rounded-md shadow-sm overflow-hidden h-9">
                        <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ substr($splitDatePrev, 0, 7) }}-01&split_date={{ $splitDatePrev }}&view=split"
                           class="px-2.5 flex items-center justify-center hover:bg-gray-50 text-gray-600 border-r border-gray-200 transition h-full" title="{{ __('Previous Day') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ substr($splitDateNext, 0, 7) }}-01&split_date={{ $splitDateNext }}&view=split"
                           class="px-2.5 flex items-center justify-center hover:bg-gray-50 text-gray-600 transition h-full" title="{{ __('Next Day') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    {{-- Tombol Tanggal yang Aktif (Bisa diklik untuk buka picker kalender) --}}
                    <label class="relative flex items-center cursor-pointer bg-blue-50 hover:bg-blue-100/80 border border-blue-200 text-blue-700 px-3 h-9 rounded-md transition shadow-sm gap-1.5" title="{{ __('Click to choose another date') }}">
                        <span class="text-xs">📅</span>
                        <span class="font-bold text-xs whitespace-nowrap">{{ $splitDateFormatted }}</span>
                        <svg class="w-3.5 h-3.5 text-blue-500 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <input type="date" value="{{ $splitDate }}" @change="changeSplitDate($event.target.value)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                    </label>
                </div>

                {{-- Toggle Ruangan yang Ditampilkan (Chips) --}}
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-gray-500 uppercase tracking-wide shrink-0 mr-1" style="font-size: 12px;">{{ __('Rooms:') }}</span>
                    @foreach($daftarRuanganDb as $ruangDb)
                    <button @click="toggleSplitRoom('{{ $ruangDb->nama_ruangan }}')"
                            :class="activeSplitRooms.includes('{{ $ruangDb->nama_ruangan }}')
                                ? 'bg-blue-600 text-white shadow-sm border-blue-600'
                                : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400 hover:bg-gray-50'"
                            class="px-3 py-1 rounded-full border transition-all duration-150 flex items-center gap-1.5 font-semibold whitespace-nowrap shadow-xs"
                            style="font-size: 12px;">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs"
                              :class="activeSplitRooms.includes('{{ $ruangDb->nama_ruangan }}') ? 'ring-1 ring-white/80' : 'ring-1 ring-black/10'"
                              style="background-color: {{ $ruangDb->warna_hex }};"></span>
                        <span>{{ $ruangDb->nama_ruangan }}</span>
                    </button>
                    @endforeach
                </div>
                </div>{{-- end min-w-max --}}
            </div>{{-- end toolbar --}}

            {{-- Tabel Matriks Split View --}}
            <div class="flex-1 overflow-auto">
                @php
                    $jamSlotArr = $daftarJamSlot->pluck('jam_mulai')->map(fn($j) => substr($j, 0, 5))->toArray();
                    $splitDateDay = date('N', strtotime($splitDate)); // 1=Mon..7=Sun
                @endphp
                <table class="w-full border-separate border-spacing-0 min-w-max" style="font-size: 12px;">
                    <thead class="sticky top-0 z-30">
                        <tr>
                            {{-- Kolom Header: Slot Jam --}}
                            <th class="sticky left-0 z-40 bg-gray-100 border-b border-r border-gray-300 py-3 px-3 w-40 font-bold text-gray-600 uppercase tracking-wider text-left" style="font-size: 12px;">
                                ⏰ {{ __('Time Slot') }}
                            </th>
                            {{-- Kolom Header: Setiap Ruangan --}}
                            @foreach($daftarRuanganDb as $ruangDb)
                            <th x-show="activeSplitRooms.includes('{{ $ruangDb->nama_ruangan }}')" 
                                class="bg-gray-50 border-b border-r border-gray-300 py-3 px-3 text-center w-56">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-sm" style="background-color: {{ $ruangDb->warna_hex }};"></span>
                                    <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-white border border-gray-200 text-gray-700 shadow-2xs">{{ $ruangDb->kode_ruangan }}</span>
                                    <span class="font-black text-gray-900" style="font-size: 12px;">{{ $ruangDb->nama_ruangan }}</span>
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($daftarJamSlot as $idx => $jamSlotItem)
                        @php
                            $waktuSlot = substr($jamSlotItem->jam_mulai, 0, 5);
                            $waktuSlotSelesai = isset($jamSlotArr[$idx + 1]) ? $jamSlotArr[$idx + 1] : '23:59';
                        @endphp
                        <tr class="group hover:bg-gray-50/40 transition">
                            {{-- Sel Jam --}}
                            <td class="sticky left-0 z-20 bg-gray-50 border-b border-r border-gray-300 py-2.5 px-3 w-40 group-hover:bg-gray-100 transition shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                <div class="font-mono font-black text-gray-800" style="font-size: 12px;">{{ $waktuSlot }} - {{ $waktuSlotSelesai != '23:59' ? $waktuSlotSelesai : 'End' }}</div>
                                <div class="text-gray-500 font-medium mt-0.5" style="font-size: 12px;">{{ $jamSlotItem->label }}</div>
                            </td>

                            {{-- Sel per Ruangan --}}
                            @foreach($daftarRuanganDb as $ruangDb)
                            @php
                                $jadwalDiSini = $jadwalSplitDate->filter(function($j) use ($ruangDb, $waktuSlot, $waktuSlotSelesai) {
                                    return $j->ruangan === $ruangDb->nama_ruangan
                                        && substr($j->waktu_mulai, 0, 5) >= $waktuSlot
                                        && substr($j->waktu_mulai, 0, 5) < $waktuSlotSelesai;
                                });
                                $adaJadwal = $jadwalDiSini->count() > 0;
                            @endphp
                            <td x-show="activeSplitRooms.includes('{{ $ruangDb->nama_ruangan }}')"
                                class="border-b border-r border-gray-200 p-2 relative h-28 w-56 align-top transition-colors {{ $adaJadwal ? '' : 'cursor-pointer hover:bg-emerald-50/50' }}"
                                @if(!$adaJadwal) @click="openBooking('{{ $splitDate }}', '{{ $splitDateFormatted }}', '{{ $waktuSlot }}', '{{ $ruangDb->nama_ruangan }}')" @endif>

                                @if($adaJadwal)
                                    {{-- Slot TERISI --}}
                                    @foreach($jadwalDiSini as $j)
                                    @php
                                        // Soft card backgrounds with border
                                        $dayColorMap = [
                                            1 => 'background-color: #ffe4e6; color: #9f1239; border-color: #fecdd3;', // Senin: rose
                                            2 => 'background-color: #ffedd5; color: #9a3412; border-color: #fed7aa;', // Selasa: orange
                                            3 => 'background-color: #fef3c7; color: #92400e; border-color: #fde68a;', // Rabu: amber
                                            4 => 'background-color: #dcfce7; color: #166534; border-color: #bbf7d0;', // Kamis: emerald
                                            5 => 'background-color: #e0f2fe; color: #075985; border-color: #bae6fd;', // Jumat: sky
                                            6 => 'background-color: #e0e7ff; color: #3730a3; border-color: #c7d2fe;', // Sabtu: indigo
                                            7 => 'background-color: #f5d0fe; color: #86198f; border-color: #f0abfc;', // Minggu: fuchsia
                                        ];
                                        $cardStyle = $dayColorMap[(int)$splitDateDay] ?? 'background-color: #f1f5f9; color: #1e293b; border-color: #e2e8f0;';
                                    @endphp
                                    <div @click.stop="openDetail({{ json_encode($j) }}, '{{ $splitDateFormatted }}')" 
                                         class="p-2.5 rounded-lg mb-1 cursor-pointer border shadow-sm transition-all hover:shadow hover:scale-[1.01]"
                                         style="{{ $cardStyle }} font-size: 12px; line-height: 1.35;">
                                        <div class="font-black uppercase truncate" style="font-size: 12px;">{{ $j->mata_kuliah->nama_matkul ?? $j->keperluan_bebas ?? '-' }}</div>
                                        <div class="font-medium truncate opacity-90 mt-0.5" style="font-size: 12px;">{{ $j->dosen->nama_dosen ?? $j->nama_peminjam_bebas ?? '-' }}</div>
                                        @if($j->kelas)
                                            <div class="font-semibold mt-0.5 opacity-85" style="font-size: 12px;">{{ __('Class') }} {{ $j->kelas->nama_kelas }}</div>
                                        @endif
                                        <div class="font-mono font-bold mt-1 inline-block" style="font-size: 12px;">⏰ {{ substr($j->waktu_mulai, 0, 5) }}–{{ substr($j->waktu_selesai, 0, 5) }}</div>
                                    </div>
                                    @endforeach
                                @else
                                    {{-- Slot KOSONG --}}
                                    <div class="h-full min-h-[85px] w-full rounded-lg flex flex-col items-center justify-center p-2 transition-all group/empty hover:shadow-sm"
                                         style="border: 1.5px dashed #86efac; background-color: #f0fdf4;">
                                        <span class="font-black tracking-wide" style="font-size: 12px; color: #16a34a;">✨ {{ __('AVAILABLE') }}</span>
                                        <span class="mt-1.5 text-blue-600 font-medium bg-white border border-blue-200 px-3 py-0.5 rounded-full shadow-sm transition-all group-hover/empty:border-blue-300"
                                              style="font-size: 12px;">
                                            + {{ __('Book') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Empty state if all rooms are hidden --}}
                <div x-show="activeSplitRooms.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400" x-cloak>
                    <span class="text-4xl mb-3">🏠</span>
                    <p class="font-bold text-gray-600" style="font-size: 12px;">{{ __('No rooms selected.') }}</p>
                    <p class="text-gray-500 mt-0.5" style="font-size: 12px;">{{ __('Please click one of the room buttons above to view schedules.') }}</p>
                </div>
            </div>

            {{-- Footer info Split View --}}
            <div class="bg-white border-t border-gray-200 px-4 py-2 flex items-center justify-between text-gray-500 shrink-0" style="font-size: 12px;">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded inline-block" style="border: 1.5px dashed #86efac; background-color: #f0fdf4;"></span> {{ __('Empty Slot (Available)') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded inline-block" style="background-color: #e0f2fe; border: 1px solid #bae6fd;"></span> {{ __('Booked Slot') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span>{{ __('Showing:') }} <strong class="text-gray-700" x-text="activeSplitRooms.length + ' {{ __('Room(s)') }}'"></strong></span>
                    <span class="text-gray-400">© {{ date('Y') }} Maki</span>
                </div>
            </div>
        </div>

    </main>

    <div x-show="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto overflow-hidden max-h-[90vh] flex flex-col" @click.away="showModal = false">
            <div class="flex border-b border-gray-200 bg-gray-50 shrink-0">
                <button type="button" @click="activeTab = 'dosen'" :class="activeTab === 'dosen' ? 'bg-white font-bold text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 font-medium hover:text-gray-800 hover:bg-gray-100 border-b-2 border-transparent'" class="flex-1 py-3 lg:py-4 text-sm transition text-center">{{ __('Lecture / Academic') }}</button>
                <button type="button" @click="activeTab = 'umum'" :class="activeTab === 'umum' ? 'bg-white font-bold text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 font-medium hover:text-gray-800 hover:bg-gray-100 border-b-2 border-transparent'" class="flex-1 py-3 lg:py-4 text-sm transition text-center">{{ __('General Event') }}</button>
            </div>
            <div class="overflow-y-auto flex-1 p-4 lg:p-6">
                <form action="{{ route('booking.store') }}" method="POST">
                    @csrf
                    {{-- Info Banner: Tanggal + Jam + Ruangan --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 lg:p-4 mb-5 flex items-center gap-3">
                        <span class="text-xl">📅</span>
                        <div class="flex-1">
                            <p class="text-[10px] lg:text-xs font-bold text-blue-500 uppercase tracking-wide m-0">{{ __('Booking schedule for') }}</p>
                            <p class="text-sm lg:text-base font-black text-blue-800 mt-0.5" x-text="selectedTanggalFormat + ' · {{ __('Time') }} ' + selectedWaktuMulai"></p>
                            <p class="text-xs font-bold text-blue-600 mt-0.5 flex items-center gap-1">
                                🏠 <span x-text="selectedRuangan"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Dropdown Pilih Ruangan — tampil jika dari Split View --}}
                    <div x-show="viewMode === 'split'" class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Room') }} <span class="text-red-400">*</span></label>
                        <div class="select-wrap">
                            <select name="ruangan" x-model="selectedRuangan" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 font-medium">
                                @foreach($daftarRuanganDb as $ruangDb)
                                    <option value="{{ $ruangDb->nama_ruangan }}">[{{ $ruangDb->kode_ruangan }}] {{ $ruangDb->nama_ruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Input hidden ruangan (kalender mode) --}}
                    <input type="hidden" name="ruangan" x-model="selectedRuangan">
                    <input type="hidden" name="tanggal" x-model="selectedTanggal">
                    <input type="hidden" name="waktu_mulai" x-model="selectedWaktuMulai">
                    <input type="hidden" name="kategori_peminjam" x-bind:value="activeTab === 'dosen' ? 'Dosen' : 'Umum'">


                    <div x-show="activeTab === 'dosen'" class="space-y-4">
                        <div x-data="{ openDosen: false, searchDosen: '', selectedDosen: '', selectedDosenId: '' }">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Lecturer Name') }}</label><input type="hidden" name="dosen_id" :value="selectedDosenId">
                            <div class="relative"><button type="button" @click="openDosen = !openDosen" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm text-left flex justify-between items-center focus:ring-blue-500 focus:border-blue-500"><span :class="selectedDosen ? 'text-gray-800' : 'text-gray-400'" x-text="selectedDosen || '{{ __('Select Lecturer...') }}'"></span><svg style="width:16px; height:16px; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                                <div x-show="openDosen" @click.away="openDosen = false" x-cloak style="position:absolute; top:100%; left:0; right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); margin-top:4px;"><div style="padding:8px;"><input type="text" x-model="searchDosen" placeholder="{{ __('Search lecturer...') }}" @click.stop style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;"></div><div style="overflow-y:auto; max-height:200px;"><div @click="selectedDosen=''; selectedDosenId=''; openDosen=false" style="padding:8px 12px; font-size:13px; color:#9ca3af; cursor:pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">{{ __('Select Lecturer...') }}</div>@foreach($daftarDosen as $dosen)<div x-show="'{{ strtolower(addslashes($dosen->nama_dosen)) }}'.includes(searchDosen.toLowerCase())" @click="selectedDosen='{{ addslashes($dosen->nama_dosen) }}'; selectedDosenId='{{ $dosen->id }}'; openDosen=false; searchDosen=''" style="padding:8px 12px; font-size:13px; color:#374151; cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">{{ $dosen->nama_dosen }}</div>@endforeach</div></div>
                            </div>
                        </div>

                        <div x-data="{ openMatkul: false, searchMatkul: '', selectedMatkul: '', selectedMatkulId: '' }">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Course / Subject') }}</label><input type="hidden" name="mata_kuliah_id" :value="selectedMatkulId">
                            <div class="relative"><button type="button" @click="openMatkul = !openMatkul" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm text-left flex justify-between items-center focus:ring-blue-500 focus:border-blue-500"><span :class="selectedMatkul ? 'text-gray-800' : 'text-gray-400'" x-text="selectedMatkul || '{{ __('Select Course...') }}'"></span><svg style="width:16px; height:16px; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                                <div x-show="openMatkul" @click.away="openMatkul = false" x-cloak style="position:absolute; top:100%; left:0; right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); margin-top:4px;"><div style="padding:8px;"><input type="text" x-model="searchMatkul" placeholder="{{ __('Search course...') }}" @click.stop style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;"></div><div style="overflow-y:auto; max-height:200px;"><div @click="selectedMatkul=''; selectedMatkulId=''; openMatkul=false" style="padding:8px 12px; font-size:13px; color:#9ca3af; cursor:pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">{{ __('Select Course...') }}</div>@foreach($daftarMatkul as $matkul)<div x-show="'{{ strtolower(addslashes($matkul->nama_matkul)) }}'.includes(searchMatkul.toLowerCase())" @click="selectedMatkul='{{ addslashes($matkul->nama_matkul) }}'; selectedMatkulId='{{ $matkul->id }}'; openMatkul=false; searchMatkul=''" style="padding:8px 12px; font-size:13px; color:#374151; cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">{{ $matkul->nama_matkul }}</div>@endforeach</div></div>
                            </div>
                        </div>

                        <div x-data="{ openKelas: false, searchKelas: '', selectedKelas: '', selectedKelasId: '' }">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Class') }}</label><input type="hidden" name="kelas_id" :value="selectedKelasId">
                            <div class="relative"><button type="button" @click="openKelas = !openKelas" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm text-left flex justify-between items-center focus:ring-blue-500 focus:border-blue-500"><span :class="selectedKelas ? 'text-gray-800' : 'text-gray-400'" x-text="selectedKelas || '{{ __('Select Class...') }}'"></span><svg style="width:16px; height:16px; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                                <div x-show="openKelas" @click.away="openKelas = false" x-cloak style="position:absolute; top:100%; left:0; right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); margin-top:4px;"><div style="padding:8px;"><input type="text" x-model="searchKelas" placeholder="{{ __('Search class...') }}" @click.stop style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;"></div><div style="overflow-y:auto; max-height:200px;"><div @click="selectedKelas=''; selectedKelasId=''; openKelas=false" style="padding:8px 12px; font-size:13px; color:#9ca3af; cursor:pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">{{ __('Select Class...') }}</div>@foreach($daftarKelas as $kelas)<div x-show="'{{ strtolower(addslashes($kelas->nama_kelas)) }}'.includes(searchKelas.toLowerCase())" @click="selectedKelas='{{ addslashes($kelas->nama_kelas) }}'; selectedKelasId='{{ $kelas->id }}'; openKelas=false; searchKelas=''" style="padding:8px 12px; font-size:13px; color:#374151; cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">{{ $kelas->nama_kelas }}</div>@endforeach</div></div>
                            </div>
                        </div>

                        <div class="pt-2"><label class="block text-xs font-semibold text-gray-500 mb-2">{{ __('Duration (Credits / SKS)') }}</label><div class="flex flex-wrap gap-4"><label class="flex items-center text-sm cursor-pointer"><input type="radio" name="sks" value="2" class="mr-2" checked> <span>{{ __('2 Credits') }}</span></label><label class="flex items-center text-sm cursor-pointer"><input type="radio" name="sks" value="3" class="mr-2"> <span>{{ __('3 Credits') }}</span></label><label class="flex items-center text-sm cursor-pointer"><input type="radio" name="sks" value="4" class="mr-2"> <span>{{ __('4 Credits') }}</span></label></div></div>
                    </div>

                    <div x-show="activeTab === 'umum'" class="space-y-4" x-cloak>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Borrower / Organization') }}</label><input type="text" name="nama_peminjam_bebas" placeholder="{{ __('Enter borrower name...') }}" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Purpose / Event') }}</label><input type="text" name="keperluan_bebas" placeholder="{{ __('e.g. Coordination Meeting') }}" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                        <div class="pt-2"><label class="block text-xs font-semibold text-gray-500 mb-2">{{ __('Time Range') }}</label><div class="flex items-center space-x-2"><input type="time" name="waktu_mulai_bebas" class="bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm w-full focus:ring-blue-500 focus:border-blue-500"><span class="text-gray-400">{{ __('to') }}</span><input type="time" name="waktu_selesai_bebas" class="bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm w-full focus:ring-blue-500 focus:border-blue-500"></div></div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">{{ __('Schedule Type') }}</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center text-sm cursor-pointer">
                                <input type="radio" name="jenis_jadwal" value="Tetap" class="mr-2" x-model="editBookingData.jenis_jadwal" checked>
                                <span class="font-bold text-blue-700">{{ __('Regular (Weekly)') }}</span>
                            </label>
                            <label class="flex items-center text-sm cursor-pointer">
                                <input type="radio" name="jenis_jadwal" value="Insidental" class="mr-2" x-model="editBookingData.jenis_jadwal">
                                <span class="font-medium text-gray-700">{{ __('Incidental (Replacement)') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Repeat Schedule') }}</label>
                        <div class="select-wrap">
                            <select name="ulangi" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="1">{{ __('Do not repeat (One-time only)') }}</option>
                                <option value="2">{{ __('2 Consecutive Weeks') }}</option>
                                <option value="3">{{ __('3 Consecutive Weeks') }}</option>
                                <option value="4">{{ __('1 Month (4 Weeks)') }}</option>
                                <option value="8">{{ __('Half Semester (8 Weeks)') }}</option>
                                <option value="14">{{ __('14 Weeks (Standard Semester)') }}</option>
                                <option value="16">{{ __('Full Semester (16 Weeks)') }}</option>
                            </select>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5 italic">{{ __('*Schedules are automatically duplicated at the same day and time for consecutive weeks.') }}</p>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3"><button type="button" @click="showModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ __('Cancel') }}</button><button type="submit" class="px-4 py-2.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">{{ __('Save Schedule') }}</button></div>
                </form>
            </div>
        </div>
    </div>
    <div x-show="showDetailModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto overflow-hidden flex flex-col max-h-[90vh]" @click.away="showDetailModal = false">
            <div class="p-5 overflow-y-auto flex-1">
                <div class="flex justify-between items-center mb-5"><h3 class="text-lg font-black text-gray-800">{{ __('Schedule Details') }}</h3><button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-800 text-2xl">&times;</button></div>
                
                <div class="space-y-4">
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 text-center">
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mb-1">{{ __('Booking Time') }}</p>
                        <p class="text-sm font-bold text-blue-800 mb-0.5" x-text="selectedBookingTanggalFormat"></p>
                        <p class="text-xl lg:text-2xl font-mono font-black text-blue-700" x-text="((selectedBooking.waktu_mulai || selectedBooking.start_time || '').substring(0,5)) + ' - ' + ((selectedBooking.waktu_selesai || selectedBooking.end_time || '').substring(0,5))"></p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div><p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-1">{{ __('Activity / Class') }}</p><p class="text-sm font-bold text-gray-800" x-text="(selectedBooking.mata_kuliah?.nama_matkul || selectedBooking.course?.name || selectedBooking.keperluan_bebas || selectedBooking.general_purpose || '-') + ((selectedBooking.kelas?.nama_kelas || selectedBooking.course_class?.name || selectedBooking.courseClass?.name) ? ' - ' + '{{ __('Class') }} ' + (selectedBooking.kelas?.nama_kelas || selectedBooking.course_class?.name || selectedBooking.courseClass?.name) : '')"></p></div>
                        <div><p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-1">{{ __('Borrower') }}</p><p class="text-sm font-bold text-gray-800" x-text="selectedBooking.dosen?.nama_dosen || selectedBooking.lecturer?.name || selectedBooking.nama_peminjam_bebas || selectedBooking.general_borrower_name || '-'"></p></div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col space-y-3">
                    <div x-data="{ konfirmasi: false, dalamDuaMenit: function() { if (!selectedBooking.created_at) return false; return (Date.now() - new Date(selectedBooking.created_at).getTime()) < 120000; } }">
                        @if($isAdmin)
                            <form :action="'{{ url('/booking') }}/' + selectedBooking.id" method="POST" class="m-0">
                                @csrf @method('DELETE')
                                <div x-show="!konfirmasi" class="flex space-x-3"><button type="button" @click="openEdit(selectedBooking, 'detail')" class="flex-1 bg-blue-50 text-blue-600 font-bold py-3 rounded-xl hover:bg-blue-100 transition border border-blue-200">✏️ {{ __('Edit') }}</button><button @click="konfirmasi = true" type="button" class="flex-1 bg-red-50 text-red-600 font-bold py-3 rounded-xl hover:bg-red-100 transition border border-red-200">🗑️ {{ __('Delete') }}</button></div>
                                <div x-show="konfirmasi" x-transition class="p-4 bg-red-50 border border-red-300 rounded-xl" x-cloak><p class="text-red-800 font-bold text-sm text-center mb-3">⚠️ {{ __('Delete this schedule?') }}</p><div class="flex space-x-2"><button type="button" @click="konfirmasi = false" class="flex-1 py-2 bg-white text-gray-700 font-bold rounded-lg border border-gray-300 hover:bg-gray-100 transition">{{ __('No') }}</button><button type="submit" class="flex-1 py-2 font-bold rounded-lg text-white bg-red-600 transition hover:bg-red-700">{{ __('Yes, Delete!') }}</button></div></div>
                            </form>
                        @else
                            <template x-if="dalamDuaMenit()">
                                <form :action="'{{ url('/booking') }}/' + selectedBooking.id" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <div x-show="!konfirmasi" class="flex space-x-3"><button type="button" @click="openEdit(selectedBooking, 'detail')" class="flex-1 bg-blue-50 text-blue-600 font-bold py-3 rounded-xl hover:bg-blue-100 transition border border-blue-200">✏️ {{ __('Edit') }}</button><button @click="konfirmasi = true" type="button" class="flex-1 bg-red-50 text-red-600 font-bold py-3 rounded-xl hover:bg-red-100 transition border border-red-200">🗑️ {{ __('Delete') }}</button></div>
                                    <div x-show="konfirmasi" x-transition class="p-4 bg-red-50 border border-red-300 rounded-xl" x-cloak><p class="text-red-800 font-bold text-sm text-center mb-3">⚠️ {{ __('Delete this schedule?') }}</p><div class="flex space-x-2"><button type="button" @click="konfirmasi = false" class="flex-1 py-2 bg-white text-gray-700 font-bold rounded-lg border border-gray-300 hover:bg-gray-100 transition">{{ __('No') }}</button><button type="submit" class="flex-1 py-2 font-bold rounded-lg text-white bg-red-600 transition hover:bg-red-700">{{ __('Yes, Delete!') }}</button></div></div>
                                </form>
                            </template>
                            <template x-if="!dalamDuaMenit()"><div class="w-full bg-yellow-50 border border-yellow-200 text-yellow-800 font-medium py-3 px-4 rounded-xl text-xs lg:text-sm text-center leading-relaxed">⏰ {{ __('Edit/delete window has expired.') }}<br><span class="font-bold">{{ __('Contact Lab Administrator') }}{{ $kontakLaboran ? ' : ' . $kontakLaboran : '' }}</span> {{ __('to modify this schedule.') }}</div></template>
                        @endif
                    </div>
                    <button type="button" @click="showDetailModal = false" class="w-full py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition text-sm">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <div x-show="showKelolaModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-2 lg:p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[95vh] lg:h-[85vh] flex flex-col overflow-hidden mx-auto" @click.away="showKelolaModal = false" x-data="{ selectedIds: [], toggleAll(ids) { this.selectedIds = this.selectedIds.length === ids.length ? [] : [...ids]; }, showBulkConfirm: false }">
            <div class="p-4 lg:p-6 border-b border-gray-200 flex flex-col lg:flex-row justify-between items-start lg:items-center bg-gray-50 gap-4 lg:gap-0 shrink-0">
                <div class="flex justify-between items-center w-full lg:w-auto"><div><h3 class="text-lg lg:text-xl font-black text-gray-800">{{ __('Manage Schedules') }}</h3><p class="text-xs lg:text-sm text-gray-500 mt-1"><span class="font-bold text-blue-600">{{ $ruanganAktif }}</span> | {{ $bulanTampil }}</p></div><button @click="showKelolaModal = false" class="lg:hidden text-gray-400 hover:text-red-500 text-2xl bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center">&times;</button></div>
                <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-end">
                    <div x-show="selectedIds.length > 0" x-cloak>
                        <button x-show="!showBulkConfirm" @click="showBulkConfirm = true" type="button" class="bg-red-600 hover:bg-red-700 text-white border-none rounded-lg px-3 py-2 text-xs lg:text-sm font-bold flex items-center gap-2 transition">🗑️ {{ __('Delete Selected') }} (<span x-text="selectedIds.length"></span>)</button>
                        <div x-show="showBulkConfirm" x-cloak class="flex items-center gap-2"><span class="text-xs font-bold text-red-600">{{ __('Delete selected?') }}</span><button type="button" @click="showBulkConfirm = false" class="bg-gray-200 text-gray-700 rounded-md px-3 py-1.5 text-xs font-bold hover:bg-gray-300">{{ __('Cancel') }}</button>
                            <form action="{{ url('/booking-bulk') }}" method="POST" class="m-0" id="bulk-delete-form">
                                @csrf @method('DELETE') <template x-for="id in selectedIds" :key="id"><input type="hidden" name="ids[]" :value="id"></template><button type="submit" class="bg-red-600 text-white rounded-md px-3 py-1.5 text-xs font-bold hover:bg-red-700">{{ __('Yes, Delete!') }}</button>
                            </form>
                        </div>
                    </div>
                    <button @click="showKelolaModal = false" class="hidden lg:block text-gray-400 hover:text-red-500 text-3xl transition">&times;</button>
                </div>
            </div>
            <div class="flex-1 overflow-auto bg-white relative w-full">
                @php $semuaId = $semuaJadwal->pluck('id')->toArray(); @endphp
                <table class="w-full text-left border-collapse text-sm min-w-[800px]">
                    <thead class="bg-gray-50 sticky top-0 z-20 shadow-sm outline outline-1 outline-gray-200"><tr><th class="py-4 px-4 border-b text-center w-12"><input type="checkbox" @change="toggleAll({{ json_encode($semuaId) }})" :checked="selectedIds.length === {{ count($semuaId) }} && {{ count($semuaId) }} > 0" class="w-4 h-4 cursor-pointer accent-blue-600"></th><th class="py-4 px-4 border-b text-gray-500 font-bold uppercase text-[10px] tracking-wider text-center">{{ __('Date') }}</th><th class="py-4 px-4 border-b text-gray-500 font-bold uppercase text-[10px] tracking-wider text-center">{{ __('Time') }}</th><th class="py-4 px-4 border-b text-gray-500 font-bold uppercase text-[10px] tracking-wider text-center">{{ __('Activity / Course') }}</th><th class="py-4 px-4 border-b text-gray-500 font-bold uppercase text-[10px] tracking-wider text-center">{{ __('Lecturer / Borrower') }}</th><th class="py-4 px-4 border-b text-gray-500 font-bold uppercase text-[10px] tracking-wider text-center w-28">{{ __('Action') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($semuaJadwal->sortBy('tanggal')->sortBy('waktu_mulai') as $j)
                        <tr class="transition group" :class="selectedIds.includes({{ $j->id }}) ? 'bg-red-50' : 'hover:bg-blue-50/40'" x-data="{ konfirmHapus: false }">
                            <td class="py-3 px-4 text-center"><input type="checkbox" :checked="selectedIds.includes({{ $j->id }})" @change="selectedIds.includes({{ $j->id }}) ? selectedIds = selectedIds.filter(i => i !== {{ $j->id }}) : selectedIds.push({{ $j->id }})" class="w-4 h-4 cursor-pointer accent-blue-600"></td>
                            <td class="py-3 px-4 font-medium text-gray-800 whitespace-nowrap text-center">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d M Y') }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-blue-600 whitespace-nowrap text-center">{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }}</td>
                            <td class="py-3 px-4 text-xs">{{ $j->mata_kuliah->nama_matkul ?? $j->keperluan_bebas }} {{ $j->kelas ? ' - ' . __('Class') . ' ' . $j->kelas->nama_kelas : '' }}</td>
                            <td class="py-3 px-4 text-xs text-gray-600">{{ $j->dosen->nama_dosen ?? $j->nama_peminjam_bebas }}</td>
                            <td class="py-3 px-4 text-center">
                                <div x-show="!konfirmHapus" class="flex items-center justify-center space-x-3"><button type="button" @click="openEdit({{ json_encode($j) }})" class="text-blue-500 hover:text-blue-700 hover:scale-110 transition-transform"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button><button type="button" @click="konfirmHapus = true" class="text-red-500 hover:text-red-700 hover:scale-110 transition-transform"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></div>
                                <div x-show="konfirmHapus" class="flex space-x-1 justify-center items-center" x-cloak><button type="button" @click="konfirmHapus = false" class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-[10px] font-bold hover:bg-gray-300">{{ __('Cancel') }}</button>
                                    <form action="{{ url('/booking/' . $j->id) }}" method="POST" class="m-0">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 bg-red-600 text-white rounded text-[10px] font-bold hover:bg-red-700 shadow-sm">{{ __('Yes!') }}</button></form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-10 px-6 text-center text-gray-400 italic">{{ __('No schedules found for this room & month.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="showEditModal" class="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto overflow-hidden flex flex-col max-h-[90vh]" @click.away="showEditModal = false">
            <div class="flex border-b border-gray-200 bg-gray-50 shrink-0">
                <button type="button" @click="activeEditTab = 'dosen'" :class="activeEditTab === 'dosen' ? 'bg-white font-bold text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 font-medium hover:text-gray-800 hover:bg-gray-100 border-b-2 border-transparent'" class="flex-1 py-3 lg:py-4 text-sm transition text-center">{{ __('Lecture / Academic') }}</button>
                <button type="button" @click="activeEditTab = 'umum'" :class="activeEditTab === 'umum' ? 'bg-white font-bold text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 font-medium hover:text-gray-800 hover:bg-gray-100 border-b-2 border-transparent'" class="flex-1 py-3 lg:py-4 text-sm transition text-center">{{ __('General Event') }}</button>
            </div>
            <div class="overflow-y-auto flex-1 p-4 lg:p-6">
                <form :action="'{{ url('/booking') }}/' + editBookingData.id" method="POST">
                    @csrf @method('PUT') <input type="hidden" name="kategori_peminjam" :value="activeEditTab === 'dosen' ? 'Dosen' : 'Umum'">
                    
                    <div class="mb-5 p-3 bg-blue-50 rounded-lg border border-blue-100 text-xs lg:text-sm text-blue-700 font-bold flex justify-center items-center gap-2">
                        📅 <span x-text="editBookingData.tanggal || editBookingData.date"></span> <span class="text-blue-300">|</span> 🕐 <span x-text="(editBookingData.waktu_mulai || editBookingData.start_time || '').substring(0,5)"></span> - <span x-text="(editBookingData.waktu_selesai || editBookingData.end_time || '').substring(0,5)"></span>
                    </div>
                    
                    <div x-show="activeEditTab === 'dosen'" class="space-y-4">
                        <div x-data="{ openDosen: false, searchDosen: '' }">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Lecturer Name') }}</label><input type="hidden" name="dosen_id" :value="editBookingData.dosen_id">
                            <div class="relative"><button type="button" @click="openDosen = !openDosen" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm text-left flex justify-between items-center focus:ring-blue-500 focus:border-blue-500"><span :class="(editBookingData.dosen_id || editBookingData.lecturer_id) ? 'text-gray-800' : 'text-gray-400'" x-text="(editBookingData.dosen ? (editBookingData.dosen.nama_dosen || editBookingData.dosen.name) : '') || '{{ __('Select Lecturer...') }}'"></span><svg style="width:16px; height:16px; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                                <div x-show="openDosen" @click.away="openDosen = false" x-cloak style="position:absolute; top:100%; left:0; right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); margin-top:4px;"><div style="padding:8px;"><input type="text" x-model="searchDosen" placeholder="{{ __('Search lecturer...') }}" @click.stop style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;"></div><div style="overflow-y:auto; max-height:200px;"><div @click="editBookingData.dosen_id=''; editBookingData.dosen=null; openDosen=false" style="padding:8px 12px; font-size:13px; color:#9ca3af; cursor:pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">{{ __('Select Lecturer...') }}</div>@foreach($daftarDosen as $dosen)<div x-show="'{{ strtolower(addslashes($dosen->nama_dosen)) }}'.includes(searchDosen.toLowerCase())" @click="editBookingData.dosen_id='{{ $dosen->id }}'; editBookingData.dosen={nama_dosen: '{{ addslashes($dosen->nama_dosen) }}'}; openDosen=false; searchDosen=''" style="padding:8px 12px; font-size:13px; color:#374151; cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">{{ $dosen->nama_dosen }}</div>@endforeach</div></div>
                            </div>
                        </div>

                        <div x-data="{ openMatkul: false, searchMatkul: '' }">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Course / Subject') }}</label><input type="hidden" name="mata_kuliah_id" :value="editBookingData.mata_kuliah_id">
                            <div class="relative"><button type="button" @click="openMatkul = !openMatkul" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm text-left flex justify-between items-center focus:ring-blue-500 focus:border-blue-500"><span :class="(editBookingData.mata_kuliah_id || editBookingData.course_id) ? 'text-gray-800' : 'text-gray-400'" x-text="(editBookingData.mata_kuliah ? (editBookingData.mata_kuliah.nama_matkul || editBookingData.mata_kuliah.name) : '') || '{{ __('Select Course...') }}'"></span><svg style="width:16px; height:16px; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                                <div x-show="openMatkul" @click.away="openMatkul = false" x-cloak style="position:absolute; top:100%; left:0; right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); margin-top:4px;"><div style="padding:8px;"><input type="text" x-model="searchMatkul" placeholder="{{ __('Search course...') }}" @click.stop style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;"></div><div style="overflow-y:auto; max-height:200px;"><div @click="editBookingData.mata_kuliah_id=''; editBookingData.mata_kuliah=null; openMatkul=false" style="padding:8px 12px; font-size:13px; color:#9ca3af; cursor:pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">{{ __('Select Course...') }}</div>@foreach($daftarMatkul as $matkul)<div x-show="'{{ strtolower(addslashes($matkul->nama_matkul)) }}'.includes(searchMatkul.toLowerCase())" @click="editBookingData.mata_kuliah_id='{{ $matkul->id }}'; editBookingData.mata_kuliah={nama_matkul: '{{ addslashes($matkul->nama_matkul) }}'}; openMatkul=false; searchMatkul=''" style="padding:8px 12px; font-size:13px; color:#374151; cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">{{ $matkul->nama_matkul }}</div>@endforeach</div></div>
                            </div>
                        </div>

                        <div x-data="{ openKelas: false, searchKelas: '' }">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Class') }}</label><input type="hidden" name="kelas_id" :value="editBookingData.kelas_id">
                            <div class="relative"><button type="button" @click="openKelas = !openKelas" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm text-left flex justify-between items-center focus:ring-blue-500 focus:border-blue-500"><span :class="(editBookingData.kelas_id || editBookingData.course_class_id) ? 'text-gray-800' : 'text-gray-400'" x-text="(editBookingData.kelas ? (editBookingData.kelas.nama_kelas || editBookingData.kelas.name) : '') || '{{ __('Select Class...') }}'"></span><svg style="width:16px; height:16px; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                                <div x-show="openKelas" @click.away="openKelas = false" x-cloak style="position:absolute; top:100%; left:0; right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); margin-top:4px;"><div style="padding:8px;"><input type="text" x-model="searchKelas" placeholder="{{ __('Search class...') }}" @click.stop style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;"></div><div style="overflow-y:auto; max-height:200px;"><div @click="editBookingData.kelas_id=''; editBookingData.kelas=null; openKelas=false" style="padding:8px 12px; font-size:13px; color:#9ca3af; cursor:pointer;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">{{ __('Select Class...') }}</div>@foreach($daftarKelas as $kelas)<div x-show="'{{ strtolower(addslashes($kelas->nama_kelas)) }}'.includes(searchKelas.toLowerCase())" @click="editBookingData.kelas_id='{{ $kelas->id }}'; editBookingData.kelas={nama_kelas: '{{ addslashes($kelas->nama_kelas) }}'}; openKelas=false; searchKelas=''" style="padding:8px 12px; font-size:13px; color:#374151; cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">{{ $kelas->nama_kelas }}</div>@endforeach</div></div>
                            </div>
                        </div>
                    </div>
                    
                    <div x-show="activeEditTab === 'umum'" class="space-y-4" x-cloak>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Borrower / Organization') }}</label><input type="text" name="nama_peminjam_bebas" x-model="editBookingData.nama_peminjam_bebas" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Purpose / Event') }}</label><input type="text" name="keperluan_bebas" x-model="editBookingData.keperluan_bebas" class="w-full bg-gray-50 border border-gray-200 rounded-md p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">{{ __('Schedule Type') }}</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center text-sm cursor-pointer">
                                <input type="radio" name="jenis_jadwal" value="Tetap" class="mr-2" x-model="editBookingData.jenis_jadwal">
                                <span class="font-bold text-blue-700">{{ __('Regular (Weekly)') }}</span>
                            </label>
                            <label class="flex items-center text-sm cursor-pointer">
                                <input type="radio" name="jenis_jadwal" value="Insidental" class="mr-2" x-model="editBookingData.jenis_jadwal">
                                <span class="font-medium text-gray-700">{{ __('Incidental (Replacement/One-off)') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3"><button type="button" @click="showEditModal = false; editSource === 'kelola' ? showKelolaModal = true : showDetailModal = true" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ __('Cancel') }}</button><button type="submit" class="px-4 py-2.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">{{ __('Save Changes') }}</button></div>
                </form>
            </div>
        </div>
    </div>
{{-- MODAL CHAT AI (FIXED WIDTH & DRAGGABLE) --}}
    <div x-show="showAiChat" 
         class="fixed z-[100] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-200"
         :style="`transform: translate(${dragX}px, ${dragY}px); width: 380px; height: 550px; bottom: 24px; right: 24px;`"
         @mousemove.window="doDrag"
         @mouseup.window="stopDrag"
         x-transition 
         x-cloak
         x-data="{ 
            pertanyaan: '', 
            loading: false,
            
            // Variabel Fitur Drag dengan persistence
            dragX: (function() { try { return parseInt(localStorage.getItem('maki_ai_drag_x') || '0', 10); } catch(e) { return 0; } })(),
            dragY: (function() { try { return parseInt(localStorage.getItem('maki_ai_drag_y') || '0', 10); } catch(e) { return 0; } })(),
            startX: 0,
            startY: 0,
            dragging: false,
            
            startDrag(e) {
                this.dragging = true;
                this.startX = e.clientX - this.dragX;
                this.startY = e.clientY - this.dragY;
            },
            doDrag(e) {
                if (this.dragging) {
                    this.dragX = e.clientX - this.startX;
                    this.dragY = e.clientY - this.startY;
                }
            },
            stopDrag() {
                if (this.dragging) {
                    this.dragging = false;
                    try {
                        localStorage.setItem('maki_ai_drag_x', this.dragX);
                        localStorage.setItem('maki_ai_drag_y', this.dragY);
                    } catch(e) {}
                }
            },

            // Riwayat Chat dengan persistence di localStorage
            chats: (function() {
                try {
                    const saved = localStorage.getItem('maki_ai_chats');
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
                    }
                } catch(e) {}
                return [
                    { role: 'bot', text: '{{ __('Hello! How can I help you regarding Maki Space schedules for the upcoming week?') }}' }
                ];
            })(),

            saveChats() {
                try {
                    localStorage.setItem('maki_ai_chats', JSON.stringify(this.chats));
                } catch(e) {}
            },

            clearChat() {
                this.chats = [
                    { role: 'bot', text: '{{ __('Hello! How can I help you regarding Maki Space schedules for the upcoming week?') }}' }
                ];
                try {
                    localStorage.removeItem('maki_ai_chats');
                } catch(e) {}
                this.scrollToBottom();
            },

            closeChat() {
                this.showAiChat = false;
                try {
                    localStorage.setItem('maki_ai_open', 'false');
                } catch(e) {}
            },

            init() {
                if (this.showAiChat) {
                    this.$nextTick(() => { this.scrollToBottom(); });
                }
                this.$watch('showAiChat', value => {
                    if (value) { this.$nextTick(() => { this.scrollToBottom(); }); }
                });
            },

            tanyaAi() {
                if(!this.pertanyaan.trim()) return;
                
                let userTanya = this.pertanyaan;
                this.chats.push({ role: 'user', text: userTanya });
                this.saveChats();
                
                this.pertanyaan = ''; 
                this.loading = true;
                this.scrollToBottom();
                
                fetch('{{ url('/api/ask-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ pertanyaan: userTanya })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        // Formatting bold (**) and newlines (\n) from AI response
                        let jawabanHtml = data.jawaban.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
                        this.chats.push({ role: 'bot', text: jawabanHtml });
                    } else {
                        this.chats.push({ role: 'bot', text: 'Sorry, a server error occurred: ' + (data.pesan || '') });
                    }
                    this.saveChats();
                })
                .catch(err => {
                    this.chats.push({ role: 'bot', text: 'Failed to connect to the server.' });
                    this.saveChats();
                })
                .finally(() => {
                    this.loading = false;
                    this.scrollToBottom();
                });
            },
            scrollToBottom() {
                setTimeout(() => {
                    let chatBox = document.getElementById('chat-container');
                    if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                }, 50);
            }
        }">
        
        {{-- Header Chat (Area Drag) --}}
        <div @mousedown="startDrag" 
             style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important; color: #ffffff !important;"
             class="px-4 py-3 flex justify-between items-center shrink-0 shadow-sm z-10 cursor-grab active:cursor-grabbing select-none"
             title="Drag to move this window">
            <div class="flex items-center gap-2.5 pointer-events-none" style="color: #ffffff !important;">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-sm shadow-inner" style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.25);">
                    ✨
                </div>
                <div>
                    <h3 class="font-bold text-sm leading-tight" style="color: #ffffff !important;">Maki AI Assistant</h3>
                    <p class="text-[10px] leading-none mt-0.5 font-medium" style="color: rgba(255, 255, 255, 0.8) !important;">Maki Space</p>
                </div>
            </div>
            <!-- Tombol Action: Clear & Close -->
            <div class="flex items-center gap-1.5">
                <button type="button" @mousedown.stop @click="clearChat()" title="{{ __('Clear Chat History') }}" 
                        class="w-7 h-7 rounded-lg flex items-center justify-center transition cursor-pointer hover:scale-105"
                        style="color: #ffffff !important; background-color: rgba(255, 255, 255, 0.16) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <button type="button" @mousedown.stop @click="closeChat()" title="{{ __('Close') }}" 
                        class="w-7 h-7 rounded-lg flex items-center justify-center transition cursor-pointer hover:scale-105" 
                        style="color: #ffffff !important; background-color: rgba(255, 255, 255, 0.16) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Area Chat (Riwayat Pesan) --}}
        <div id="chat-container" class="flex-1 p-4 overflow-y-auto flex flex-col gap-3 text-sm scroll-smooth" style="background-color: #f8fafc;">
            <template x-for="(chat, index) in chats" :key="index">
                <div :class="chat.role === 'user' ? 'flex gap-2 justify-end' : 'flex gap-2'">
                    <!-- Avatar Bot -->
                    <div x-show="chat.role === 'bot'" 
                         style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb;" 
                         class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 shadow-sm text-xs">🤖</div>
                    
                    <!-- Balon Teks -->
                    <div :class="chat.role === 'user' 
                            ? 'font-medium p-3 rounded-2xl rounded-tr-none shadow-sm max-w-[85%]' 
                            : 'p-3 rounded-2xl rounded-tl-none shadow-sm max-w-[85%] leading-relaxed'" 
                         :style="chat.role === 'user' 
                            ? 'background-color: #2563eb !important; color: #ffffff !important; border: 1px solid #1d4ed8 !important;' 
                            : 'background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #e2e8f0 !important;'"
                          x-html="chat.text">
                    </div>

                    <!-- Avatar User -->
                    <div x-show="chat.role === 'user'" 
                         style="background-color: #dbeafe; border: 1px solid #93c5fd; color: #1e40af;" 
                         class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 shadow-sm text-xs">👤</div>
                </div>
            </template>

            <!-- Loading Animasi -->
            <div x-show="loading" class="flex gap-2 mt-auto">
                <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb;" 
                     class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 shadow-sm text-xs">🤖</div>
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0;" 
                     class="p-3 rounded-2xl rounded-tl-none shadow-sm flex gap-1 items-center h-9">
                    <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #3b82f6;"></span>
                    <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #3b82f6; animation-delay: 0.2s"></span>
                    <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #3b82f6; animation-delay: 0.4s"></span>
                </div>
            </div>
        </div>

        {{-- Form Input Bawah --}}
        <div class="p-3 bg-white border-t border-gray-200 shrink-0">
            <form @submit.prevent="tanyaAi" class="flex items-center gap-2">
                <input type="text" x-model="pertanyaan" placeholder="{{ __('Ask a question about schedules...') }}" 
                       class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition-all" 
                       style="background-color: #f8fafc; color: #1e293b;"
                       :disabled="loading" autocomplete="off">
                
                <button type="submit" 
                        class="px-4 py-2 rounded-xl font-bold transition-all shrink-0 shadow-sm text-xs cursor-pointer hover:opacity-95" 
                        style="background-color: #2563eb !important; color: #ffffff !important;"
                        :disabled="loading || !pertanyaan.trim()">
                    {{ __('Send') }} 🚀
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let targetElement = null;
            @if(session('new_booking_ids')) targetElement = document.getElementById("booking-{{ session('new_booking_ids')[0] }}");
            @elseif(session('conflicting_id')) targetElement = document.getElementById("booking-{{ session('conflicting_id') }}");
            @elseif(session('cleared_slot')) @php $jamSlots = ['07:00', '08:40', '10:20', '12:30', '14:10', '16:00', '18:00']; $indexJam = array_search(session('cleared_slot')['waktu'], $jamSlots); @endphp targetElement = document.getElementById("cell-{{ session('cleared_slot')['tanggal'] }}-{{ $indexJam }}");
            @endif
            
            if (!targetElement) { targetElement = document.getElementById('hari-ini'); }
            
            if (targetElement) {
                setTimeout(() => {
                    const container = document.querySelector('main > div.overflow-auto'); 
                    if (container) {
                        const elementRect = targetElement.getBoundingClientRect();
                        const containerRect = container.getBoundingClientRect();
                        const scrollTopPos = elementRect.top - containerRect.top + container.scrollTop - (container.clientHeight / 2) + (elementRect.height / 2);
                        container.scrollTo({ top: scrollTopPos, behavior: 'smooth' });
                    } else { targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                    
                    if(targetElement.id === 'hari-ini') {
                        targetElement.classList.add('bg-blue-100', 'transition-all', 'duration-500');
                        setTimeout(() => { targetElement.classList.remove('bg-blue-100'); }, 1500);
                    }
                }, 400); 
            }
        });
    </script>
    {{-- ═══════════════════════ MODAL USER GUIDE ═══════════════════════ --}}
    <div x-show="showGuide"
         style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.5); padding: 16px;"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-auto overflow-hidden max-h-[90vh] flex flex-col"
             @click.away="showGuide = false">
            
            {{-- Header Modal --}}
            <div class="p-4 lg:p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📖</span>
                    <div>
                        <h3 class="text-base lg:text-lg font-black text-gray-800 leading-tight">{{ __('User Guide') }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $namaLab }} {{ __('Scheduling System') }}</p>
                    </div>
                </div>
                <button type="button" @click="showGuide = false"
                        class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 text-2xl font-bold rounded-lg w-8 h-8 flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            {{-- Guide Content (Scrollable) --}}
            <div class="overflow-y-auto flex-1 p-4 lg:p-6 space-y-4 text-sm text-gray-700">

                {{-- Quick Overview Banner --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 lg:p-4 flex items-center gap-3">
                    <span class="text-2xl">💡</span>
                    <div>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wide m-0">{{ __('Quick Overview') }}</p>
                        <p class="text-xs text-blue-800 mt-0.5">This system manages laboratory room bookings, regular lectures, incidental sessions, and real-time room availability monitoring.</p>
                    </div>
                </div>

                {{-- Step 1: Booking a Room --}}
                <div class="border border-gray-200 rounded-xl p-4 bg-white shadow-sm space-y-2">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <span>📅</span> {{ __('How to Book a Room') }}
                    </h4>
                    <ol class="list-decimal list-inside space-y-1.5 text-xs text-gray-600 pl-1 leading-relaxed">
                        <li>Select your desired <strong>Room</strong> from the left sidebar.</li>
                        <li>Hover over and click on any <strong>available slot cell</strong> for your preferred date and time.</li>
                        <li>Choose the <strong>"Lecture / Academic"</strong> tab for courses, or <strong>"General Event"</strong> for other activities.</li>
                        <li>Select the <strong>Schedule Type</strong> (Regular weekly or Incidental replacement).</li>
                        <li>Use <strong>"Repeat Schedule"</strong> if you need to repeat the session up to a full semester automatically.</li>
                        <li>Click <strong>"Save Schedule"</strong>.</li>
                    </ol>
                </div>

                {{-- Step 2: Split View --}}
                <div class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-4 shadow-sm space-y-2">
                    <h4 class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2.5" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18"/></svg>
                        {{ __('Room Split View Feature') }}
                    </h4>
                    <p class="text-xs text-emerald-800 leading-relaxed">
                        Click the <strong>"Split View"</strong> toggle in the top toolbar to compare all room schedules simultaneously on a single date.
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-xs text-emerald-700 pl-1">
                        <li>Dashed green slots marked <strong>✨ AVAILABLE</strong> indicate vacant slots ready to be booked.</li>
                        <li>Use the date stepper buttons in the toolbar to switch between days.</li>
                        <li>Use room filter pills to focus on specific rooms.</li>
                    </ul>
                </div>

                {{-- Step 3: Edit & Delete --}}
                <div class="border border-amber-200 bg-amber-50/40 rounded-xl p-4 shadow-sm space-y-2">
                    <h4 class="font-bold text-amber-900 text-sm flex items-center gap-2">
                        <span>✏️</span> {{ __('Editing & Canceling Schedules') }}
                    </h4>
                    <ul class="list-disc list-inside space-y-1.5 text-xs text-amber-800 pl-1 leading-relaxed">
                        <li>Click directly on any <strong>schedule card</strong> in the calendar to view its details.</li>
                        <li><strong>Standard Users:</strong> Have a <strong>2-minute grace period</strong> after creation to edit or delete their booking.</li>
                        <li>After 2 minutes, please contact <strong>Lab Administrator{{ $kontakLaboran ? ' (' . $kontakLaboran . ')' : '' }}</strong> for modifications.</li>
                        <li><strong>Admin:</strong> Can edit or delete any schedule at any time without time limits.</li>
                    </ul>
                </div>

                {{-- Step 4: AI Assistant --}}
                <div class="border border-purple-200 bg-purple-50/40 rounded-xl p-4 shadow-sm space-y-2">
                    <h4 class="font-bold text-purple-900 text-sm flex items-center gap-2">
                        <span>✨</span> {{ __('Smart AI Assistant') }}
                    </h4>
                    <p class="text-xs text-purple-800 leading-relaxed">
                        Click <strong>"✨ Ask Maki"</strong> in the top header to query schedules interactively. Powered by <strong>Google Gemini</strong>, you can ask questions like:
                    </p>
                    <div class="bg-white/80 rounded-lg p-2.5 text-[11px] text-purple-900 font-mono space-y-1 border border-purple-200">
                        <p>💬 "Which rooms are available tomorrow at 10 AM?"</p>
                        <p>💬 "When are Dr. Smith's lecture schedules this week?"</p>
                        <p>💬 "What time slots are free in Coding Lab on Thursday?"</p>
                    </div>
                </div>

                {{-- Contact --}}
                @if($kontakLaboran)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-600 flex items-center justify-between">
                    <span>📞 Need technical assistance or schedule changes?</span>
                    <strong class="text-blue-700 font-mono">{{ $kontakLaboran }}</strong>
                </div>
                @endif

            </div>

            {{-- Footer Modal --}}
            <div class="p-4 border-t border-gray-200 bg-gray-50 shrink-0 flex justify-end space-x-3">
                <button type="button" @click="showGuide = false"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg transition shadow-sm">
                    {{ __('Close Guide') }}
                </button>
            </div>
        </div>
    </div>

</body>
</html>