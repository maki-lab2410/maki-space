<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $namaLab }} - {{ __('Schedule') }}</title>

    <link rel="icon" type="image/png" href="{{ !empty($logoPath) ? asset($logoPath) : asset('images/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #FFFFFF;
            -webkit-tap-highlight-color: transparent;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-user-select: none;
            user-select: none;
        }
        [x-cloak] { display: none !important; }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Kartu Jadwal ala Apple Calendar */
        .cal-event-card {
            border-radius: 8px;
            padding: 5px 7px 5px 9px;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            text-align: left;
            cursor: pointer;
            transition: transform 0.1s ease, opacity 0.1s ease;
            position: relative;
        }
        .cal-event-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            border-radius: 8px 0 0 8px;
        }
        .cal-event-card:active {
            transform: scale(0.97);
            opacity: 0.85;
        }

        /* Baris slot waktu dengan tinggi tetap */
        .grid-row-slot {
            height: 88px;
            border-bottom: 1px solid #F1F5F9;
        }

        /* ── Segmented Control (persis seperti Calendar / Split View di desktop) ── */
        .phone-seg-track {
            background-color: #F1F3F7;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 3px 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .phone-seg-track .seg-btn {
            flex: 1;
            border: none;
            outline: none;
            background-color: transparent;
            box-shadow: none;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            height: 34px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12.5px;
            line-height: 1;
            white-space: nowrap;
            border-radius: 9px;
            transition: all 0.15s ease-in-out;
            font-family: inherit;
        }
        .phone-seg-track .seg-btn:hover {
            color: #0F172A;
        }
        .phone-seg-track .seg-btn.seg-active {
            background-color: #FFFFFF;
            color: #2563EB;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.08), 0 1px 2px 0 rgba(0,0,0,0.04);
            font-weight: 700;
        }
        .phone-seg-track .seg-btn.seg-active svg {
            color: #2563EB;
        }
        .phone-seg-track .seg-btn:not(.seg-active) svg {
            color: #94A3B8;
        }
    </style>
</head>

<body style="height: 100dvh; display: flex; flex-direction: column; overflow: hidden; background: #FFFFFF;"
      x-data="{
          selectedDate: '{{ request('date', date('Y-m-d')) }}',
          selectedRuangan: '{{ $ruanganAktif }}',
          showBookingModal: {{ $errors->any() ? 'true' : 'false' }},
          showDetailModal: false,
          showAiChat: false,
          showRoomModal: false,
          selectedBooking: {},
          selectedTanggalFormat: '',
          selectedWaktuMulai: '07:00',
          selectedWaktuMulaiBebas: '07:00',
          selectedWaktuSelesaiBebas: '08:40',
          selectedSks: 2,
          activeTab: 'dosen',
          touchStartX: 0,
          touchEndX: 0,
          
          hitungJamSelesai(waktuMulai, sks) {
              if (!waktuMulai) return '-';
              let parts = waktuMulai.split(':');
              let h = parseInt(parts[0], 10) || 0;
              let m = parseInt(parts[1], 10) || 0;
              let durasi = (parseInt(sks, 10) || 2) * 50;
              let totalM = h * 60 + m + durasi;
              let endH = Math.floor(totalM / 60) % 24;
              let endM = totalM % 60;
              return String(endH).padStart(2, '0') + ':' + String(endM).padStart(2, '0');
          },
          openBooking(tgl, tglFormat, waktu) {
              this.selectedDate = tgl;
              this.selectedTanggalFormat = tglFormat;
              let jam = (waktu || '07:00').substring(0, 5);
              this.selectedWaktuMulai = jam;
              this.selectedWaktuMulaiBebas = jam;
              this.selectedSks = 2;
              this.selectedWaktuSelesaiBebas = this.hitungJamSelesai(jam, 2);
              this.showBookingModal = true;
          },
          openDetail(booking, tglFormat) {
              let b = JSON.parse(JSON.stringify(booking));
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
              this.selectedTanggalFormat = tglFormat;
              this.showDetailModal = true;
          },
          handleSwipe() {
              let diff = this.touchEndX - this.touchStartX;
              if (Math.abs(diff) < 60) return;
              if (diff < 0) {
                  window.location.href = '{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->copy()->addDays(2)->format('Y-m-d') }}';
              } else {
                  window.location.href = '{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->copy()->subDays(2)->format('Y-m-d') }}';
              }
          }
      }"
      @touchstart.passive="touchStartX = $event.changedTouches[0].screenX"
      @touchend.passive="touchEndX = $event.changedTouches[0].screenX; handleSwipe()">

    {{-- ═══ FLOATING NOTIFICATION TOAST (Success, Error, Validation) ═══ --}}
    @if(session('success') || session('error') || $errors->any())
    <div x-data="{ showToast: true }" 
         x-init="setTimeout(() => showToast = false, 7000)" 
         x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         style="position: fixed; top: 14px; left: 14px; right: 14px; z-index: 99999; pointer-events: auto;">
        
        @if(session('success'))
        <div style="background: #10B981; color: white; border-radius: 14px; padding: 12px 16px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">✅</span>
                <span style="font-size: 13px; font-weight: 700; line-height: 1.4;">{{ session('success') }}</span>
            </div>
            <button @click="showToast = false" style="background: transparent; border: none; color: white; font-size: 18px; cursor: pointer; padding: 0 4px;">&times;</button>
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div style="background: #EF4444; color: white; border-radius: 14px; padding: 12px 16px; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-top: 6px;">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <span style="font-size: 20px; line-height: 1;">⚠️</span>
                <div>
                    @if(session('error'))
                        <div style="font-size: 13px; font-weight: 700; line-height: 1.4;">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div style="font-size: 12px; font-weight: 600; line-height: 1.4; margin-top: 2px;">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
            </div>
            <button @click="showToast = false" style="background: transparent; border: none; color: white; font-size: 18px; cursor: pointer; padding: 0 4px;">&times;</button>
        </div>
        @endif
    </div>
    @endif

    @php
        $selectedDateStr = request('date', date('Y-m-d'));
        $carbonDate = \Carbon\Carbon::parse($selectedDateStr);
        
        // ── 2 Hari yang Aktif Ditampilkan ──────────────────────────────────
        $day1 = $carbonDate->copy();
        $day2 = $carbonDate->copy()->addDay();
        
        $day1Str = $day1->format('Y-m-d');
        $day2Str = $day2->format('Y-m-d');
        
        $isDay1Today = ($day1Str === date('Y-m-d'));
        $isDay2Today = ($day2Str === date('Y-m-d'));

        // Format Label Header
        $mapHari = ['Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab', 'Sun' => 'Min'];
        $mapBulanShort = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        
        $day1NamaHari  = $mapHari[$day1->format('D')] ?? $day1->format('D');
        $day1BulanStr  = $mapBulanShort[$day1->month] ?? $day1->format('M');
        $day1Header    = "{$day1NamaHari} – {$day1->format('j')} {$day1BulanStr}";

        $day2NamaHari  = $mapHari[$day2->format('D')] ?? $day2->format('D');
        $day2BulanStr  = $mapBulanShort[$day2->month] ?? $day2->format('M');
        $day2Header    = "{$day2NamaHari} – {$day2->format('j')} {$day2BulanStr}";

        // Label Bulan untuk tombol navigasi kiri (< Oktober)
        $bulanNavLabel = $mapBulanShort[$day1->month] ?? $day1->format('M');

        // Navigasi 2 hari
        $prev2Days = $day1->copy()->subDays(2)->format('Y-m-d');
        $next2Days = $day1->copy()->addDays(2)->format('Y-m-d');

        // Pita 7 hari (Senin s/d Minggu dari pekan day1)
        $startOfWeek = $day1->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $dt   = $startOfWeek->copy()->addDays($i);
            $wStr = $dt->format('Y-m-d');
            $weekDays[] = [
                'date'       => $wStr,
                'dayLetter'  => strtoupper(substr($dt->format('D'), 0, 1)),
                'dayNum'     => $dt->format('j'),
                'isToday'    => ($wStr === date('Y-m-d')),
                'isDay1'     => ($wStr === $day1Str),
                'isDay2'     => ($wStr === $day2Str),
                'isSelected' => ($wStr === $day1Str || $wStr === $day2Str),
            ];
        }

        // Ruangan Aktif
        $ruangAktifModel = $daftarRuanganDb->firstWhere('nama_ruangan', $ruanganAktif);
        $warnaRuang      = $ruangAktifModel->warna_hex ?? '#2563EB';

        // Array slot waktu mulai untuk filter presisi
        $jamSlotArr = $daftarJamSlot->pluck('jam_mulai')->map(fn($j) => substr($j, 0, 5))->values()->toArray();
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 1. TOP HEADER (APPLE CALENDAR STYLE)                               --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <header style="flex-shrink: 0; position: sticky; top: 0; z-index: 30; background: rgba(255,255,255,0.97); border-bottom: 1px solid #E5E5EA; padding-top: env(safe-area-inset-top, 6px);">
        
        {{-- Baris 1: < Bulan | Ruangan | > + --}}
        <div style="padding: 8px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
            
            {{-- Kiri: < Bulan --}}
            <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ $prev2Days }}" 
               style="text-decoration: none; display: flex; align-items: center; gap: 2px; color: #FF3B30; font-weight: 700; font-size: 15px; flex-shrink: 0; min-width: 72px;">
                <svg style="width: 17px; height: 17px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>{{ $bulanNavLabel }}</span>
            </a>

            {{-- Tengah: Selector Ruangan (Pill) --}}
            <button type="button" @click="showRoomModal = true" 
                    style="display: flex; align-items: center; gap: 6px; padding: 6px 13px; border-radius: 9999px; border: 1.5px solid #E5E5EA; background: #F8FAFC; font-size: 11.5px; font-weight: 700; color: #1C1C1E; flex: 1; max-width: 180px; cursor: pointer; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $warnaRuang }}; flex-shrink: 0; display: inline-block;"></span>
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; text-align: left;">{{ $ruanganAktif }}</span>
                <svg style="width: 11px; height: 11px; color: #8E8E93; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Kanan: > & + --}}
            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ $next2Days }}" 
                   style="text-decoration: none; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; color: #6B7280; border-radius: 50%;">
                    <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <button type="button" 
                        @click="openBooking('{{ $day1Str }}', '{{ $day1->translatedFormat('l, d F Y') }}', '{{ $jamSlotArr[0] ?? '07:00' }}')" 
                        style="width: 30px; height: 30px; border-radius: 50%; background: #FFE5E5; color: #FF3B30; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 300; border: none; cursor: pointer; line-height: 1;">
                    +
                </button>
            </div>
        </div>

        {{-- Baris 2: Pita 7 Hari --}}
        <div style="padding: 2px 6px 8px 6px; display: flex; align-items: center; justify-content: space-around; background: #FFFFFF;">
            @foreach($weekDays as $wDay)
                @php
                    $isSel  = $wDay['isSelected'];
                    $isD1   = $wDay['isDay1'];
                    $isD2   = $wDay['isDay2'];
                    $isTod  = $wDay['isToday'];
                    
                    // Gaya pill untuk pasangan 2 hari aktif agar menyambung
                    $pillBg = $isSel ? '#EFEFF4' : 'transparent';
                    if ($isD1 && !$isD2) {
                        $pillRadius = '9999px 0 0 9999px';
                    } elseif ($isD2 && !$isD1) {
                        $pillRadius = '0 9999px 9999px 0';
                    } else {
                        $pillRadius = '9999px';
                    }
                @endphp
                <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ $wDay['date'] }}" 
                   style="text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 40px; height: 48px; background: {{ $pillBg }}; border-radius: {{ $pillRadius }}; transition: all 0.15s;">
                    <span style="font-size: 9.5px; font-weight: 700; color: {{ $isTod ? '#FF3B30' : '#8E8E93' }}; margin-bottom: 2px; letter-spacing: 0.02em;">
                        {{ $wDay['dayLetter'] }}
                    </span>
                    @if($isTod)
                        <span style="width: 26px; height: 26px; border-radius: 50%; background: #FF3B30; color: #FFFFFF; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center;">
                            {{ $wDay['dayNum'] }}
                        </span>
                    @else
                        <span style="width: 26px; height: 26px; font-weight: {{ $isSel ? '800' : '500' }}; font-size: 13px; display: flex; align-items: center; justify-content: center; color: {{ $isSel ? '#1C1C1E' : '#3C3C43' }};">
                            {{ $wDay['dayNum'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 2. GRID 2-HARI PERSIS APPLE CALENDAR (TABEL DENGAN ROW SEJAJAR)   --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div style="flex: 1; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; padding-bottom: 100px; background: #FFFFFF;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            
            {{-- Header 2 Kolom Hari --}}
            <thead style="position: sticky; top: 0; z-index: 20; background: #FFFFFF;">
                <tr style="border-bottom: 1.5px solid #E5E5EA;">
                    <th style="width: 46px; padding: 7px 4px; text-align: right; font-size: 9.5px; font-weight: 600; color: #8E8E93; border-right: 1px solid #E5E5EA; background: #FFFFFF;">
                        Waktu
                    </th>
                    <th style="padding: 7px 6px; text-align: center; font-size: 12px; font-weight: 700; border-right: 1px solid #E5E5EA; background: #FFFFFF; color: {{ $isDay1Today ? '#FF3B30' : '#1C1C1E' }};">
                        {{ $day1Header }}
                    </th>
                    <th style="padding: 7px 6px; text-align: center; font-size: 12px; font-weight: 700; background: #FFFFFF; color: {{ $isDay2Today ? '#FF3B30' : '#1C1C1E' }};">
                        {{ $day2Header }}
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($daftarJamSlot as $slotIndex => $jamSlotItem)
                    @php
                        $waktuSlot        = substr($jamSlotItem->jam_mulai, 0, 5);
                        $waktuSlotSelesai = $jamSlotArr[$slotIndex + 1] ?? '23:59';

                        // Ambil jadwal di hari 1 untuk slot ini
                        $jadwalDay1 = $semuaJadwal->filter(function($j) use ($day1Str, $waktuSlot, $waktuSlotSelesai) {
                            $tgl = $j->tanggal ?? $j->date ?? '';
                            $wm  = substr($j->waktu_mulai ?? $j->start_time ?? '', 0, 5);
                            return $tgl == $day1Str && $wm >= $waktuSlot && $wm < $waktuSlotSelesai;
                        });

                        // Ambil jadwal di hari 2 untuk slot ini
                        $jadwalDay2 = $semuaJadwal->filter(function($j) use ($day2Str, $waktuSlot, $waktuSlotSelesai) {
                            $tgl = $j->tanggal ?? $j->date ?? '';
                            $wm  = substr($j->waktu_mulai ?? $j->start_time ?? '', 0, 5);
                            return $tgl == $day2Str && $wm >= $waktuSlot && $wm < $waktuSlotSelesai;
                        });

                        // Palet warna pastel ala Apple Calendar (berdasar id matkul)
                        $paletWarna = [
                            ['bg' => '#EEF2FF', 'border' => '#818CF8', 'text' => '#3730A3'], // Indigo
                            ['bg' => '#F0FDF4', 'border' => '#4ADE80', 'text' => '#166534'], // Green
                            ['bg' => '#FFF7ED', 'border' => '#FB923C', 'text' => '#9A3412'], // Orange
                            ['bg' => '#FDF4FF', 'border' => '#C084FC', 'text' => '#6B21A8'], // Violet
                            ['bg' => '#FFF1F2', 'border' => '#FB7185', 'text' => '#9F1239'], // Rose
                            ['bg' => '#EFF6FF', 'border' => '#60A5FA', 'text' => '#1E40AF'], // Blue
                            ['bg' => '#ECFEFF', 'border' => '#22D3EE', 'text' => '#155E75'], // Cyan
                        ];
                    @endphp

                    <tr class="grid-row-slot">
                        
                        {{-- Kolom Waktu (Kiri, Sticky) --}}
                        <td style="width: 46px; vertical-align: top; padding: 5px 5px 0 0; text-align: right; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 10px; font-weight: 600; color: #8E8E93; border-right: 1px solid #E5E5EA; background: #FFFFFF;">
                            {{ $waktuSlot }}
                        </td>

                        {{-- Kolom Hari 1 --}}
                        <td style="vertical-align: top; padding: 3px 3px; border-right: 1px solid #F1F5F9; background: {{ $isDay1Today ? '#FFFBF5' : '#FFFFFF' }};">
                            @if($jadwalDay1->count() > 0)
                                @foreach($jadwalDay1 as $j)
                                    @php
                                        $pid   = ($j->mata_kuliah_id ?? $j->id ?? 0) % 7;
                                        $warna = $paletWarna[$pid];
                                        $judul    = $j->mata_kuliah->nama_matkul ?? $j->keperluan_bebas ?? 'Kegiatan Lab';
                                        $pengajar = $j->dosen->nama_dosen ?? $j->nama_peminjam_bebas ?? '-';
                                        $jamTampil = substr($j->waktu_mulai, 0, 5) . ' – ' . substr($j->waktu_selesai, 0, 5);
                                        $kelasTampil = $j->kelas->nama_kelas ?? '';
                                    @endphp
                                    <div @click.stop="openDetail({{ json_encode($j) }}, '{{ $day1Header }}')"
                                         class="cal-event-card"
                                         style="background: {{ $warna['bg'] }}; border-left: 3.5px solid {{ $warna['border'] }}; color: {{ $warna['text'] }};">
                                        <div style="font-weight: 800; font-size: 10px; line-height: 1.25; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ $judul }}
                                        </div>
                                        <div style="font-size: 9px; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                                            {{ $pengajar }}
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 8px; font-weight: 700; margin-top: 2px; opacity: 0.75;">
                                            <span>{{ $jamTampil }}</span>
                                            <span>{{ $kelasTampil }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                {{-- Slot Kosong: Klik untuk Pinjam --}}
                                <div @click.stop="openBooking('{{ $day1Str }}', '{{ $day1Header }}', '{{ $waktuSlot }}')"
                                     style="width: 100%; height: 100%; border-radius: 6px; cursor: pointer; transition: background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'" onmouseout="this.style.background='transparent'">
                                </div>
                            @endif
                        </td>

                        {{-- Kolom Hari 2 --}}
                        <td style="vertical-align: top; padding: 3px 3px; background: {{ $isDay2Today ? '#FFFBF5' : '#FFFFFF' }};">
                            @if($jadwalDay2->count() > 0)
                                @foreach($jadwalDay2 as $j)
                                    @php
                                        $pid   = ($j->mata_kuliah_id ?? $j->id ?? 0) % 7;
                                        $warna = $paletWarna[$pid];
                                        $judul    = $j->mata_kuliah->nama_matkul ?? $j->keperluan_bebas ?? 'Kegiatan Lab';
                                        $pengajar = $j->dosen->nama_dosen ?? $j->nama_peminjam_bebas ?? '-';
                                        $jamTampil = substr($j->waktu_mulai, 0, 5) . ' – ' . substr($j->waktu_selesai, 0, 5);
                                        $kelasTampil = $j->kelas->nama_kelas ?? '';
                                    @endphp
                                    <div @click.stop="openDetail({{ json_encode($j) }}, '{{ $day2Header }}')"
                                         class="cal-event-card"
                                         style="background: {{ $warna['bg'] }}; border-left: 3.5px solid {{ $warna['border'] }}; color: {{ $warna['text'] }};">
                                        <div style="font-weight: 800; font-size: 10px; line-height: 1.25; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ $judul }}
                                        </div>
                                        <div style="font-size: 9px; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                                            {{ $pengajar }}
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 8px; font-weight: 700; margin-top: 2px; opacity: 0.75;">
                                            <span>{{ $jamTampil }}</span>
                                            <span>{{ $kelasTampil }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                {{-- Slot Kosong: Klik untuk Pinjam --}}
                                <div @click.stop="openBooking('{{ $day2Str }}', '{{ $day2Header }}', '{{ $waktuSlot }}')"
                                     style="width: 100%; height: 100%; border-radius: 6px; cursor: pointer; transition: background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'" onmouseout="this.style.background='transparent'">
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 3. FLOATING ACTIONS (PERSIS SEPERTI GAMBAR APPLE CALENDAR)         --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}

    {{-- Tombol Kiri Bawah: Today --}}
    <a href="{{ url('/') }}?ruangan={{ urlencode($ruanganAktif) }}&date={{ date('Y-m-d') }}" 
       style="position: fixed; bottom: calc(22px + env(safe-area-inset-bottom, 0px)); left: 16px; z-index: 40; text-decoration: none; display: flex; align-items: center; gap: 6px; padding: 8px 18px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid #E5E5EA; border-radius: 9999px; box-shadow: 0 2px 12px rgba(0,0,0,0.10);">
        <span style="width: 7px; height: 7px; border-radius: 50%; background: #FF3B30; display: inline-block; flex-shrink: 0;"></span>
        <span style="font-weight: 700; font-size: 13px; color: #1C1C1E;">Today</span>
    </a>

    {{-- Tombol Kanan Bawah: Tanya Maki & Desktop --}}
    <div style="position: fixed; bottom: calc(22px + env(safe-area-inset-bottom, 0px)); right: 16px; z-index: 40; display: flex; align-items: center; gap: 8px;">
        <button type="button" @click="showAiChat = true" 
                style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid #E5E5EA; border-radius: 9999px; box-shadow: 0 2px 12px rgba(0,0,0,0.10); font-weight: 700; font-size: 12.5px; color: #2563EB; cursor: pointer;">
            <span>✨</span>
            <span>Tanya Maki</span>
        </button>
        <a href="{{ url('/') }}?mode=desktop" 
           style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid #E5E5EA; box-shadow: 0 2px 12px rgba(0,0,0,0.10); display: flex; align-items: center; justify-content: center; text-decoration: none; color: #6B7280;" 
           title="Versi Desktop">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect width="20" height="14" x="2" y="3" rx="2"/>
                <path d="M8 21h8m-4-4v4"/>
            </svg>
        </a>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 4. MODAL PILIH RUANGAN                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="showRoomModal" x-cloak style="position: fixed; inset: 0; z-index: 50; overflow: hidden;" x-transition.opacity>
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" @click="showRoomModal = false"></div>
        <div style="position: fixed; top: 72px; left: 14px; right: 14px; max-height: 72vh; z-index: 10; overflow-y: auto; background: #FFFFFF; border-radius: 20px; padding: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.18);">
            <h3 style="font-size: 11px; font-weight: 800; color: #8E8E93; text-transform: uppercase; letter-spacing: 0.06em; margin: 0 0 12px 2px;">
                Pilih Ruangan Lab
            </h3>
            <div style="display: flex; flex-direction: column; gap: 7px;">
                @foreach($daftarRuanganDb as $rDb)
                    @php $isRActive = ($ruanganAktif == $rDb->nama_ruangan); @endphp
                    <a href="{{ url('/') }}?ruangan={{ urlencode($rDb->nama_ruangan) }}&date={{ $selectedDateStr }}" 
                       style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border-radius: 12px; font-size: 13px; {{ $isRActive ? 'background: #EFF6FF; border: 1.5px solid #3B82F6; color: #1D4ED8; font-weight: 800;' : 'background: #F8FAFC; border: 1px solid #F1F5F9; color: #1C1C1E; font-weight: 500;' }}">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: {{ $rDb->warna_hex }}; display: inline-block; flex-shrink: 0;"></span>
                            <span>{{ $rDb->nama_ruangan }}</span>
                        </div>
                        @if($isRActive)
                            <span style="color: #2563EB; font-size: 15px;">✓</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 5. MODAL FORM PINJAM RUANGAN (BOTTOM SHEET)                        --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="showBookingModal" x-cloak style="position: fixed; inset: 0; z-index: 50; overflow: hidden;" x-transition.opacity>
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" @click="showBookingModal = false"></div>
        <div style="position: fixed; bottom: 0; left: 0; right: 0; max-height: 92vh; background: #FFFFFF; border-radius: 24px 24px 0 0; box-shadow: 0 -4px 24px rgba(0,0,0,0.18); display: flex; flex-direction: column; overflow: hidden;"
             x-transition:enter="transition ease-out duration-300 transform" 
             x-transition:enter-start="translate-y-full" 
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform" 
             x-transition:leave-start="translate-y-0" 
             x-transition:leave-end="translate-y-full">
            
            {{-- Handle & Judul --}}
            <div style="padding: 12px 16px 10px; border-bottom: 1px solid #F1F5F9; text-align: center; position: relative; flex-shrink: 0;">
                <div style="width: 36px; height: 4px; background: #CBD5E1; border-radius: 9999px; margin: 0 auto 8px auto;"></div>
                <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: #0F172A;">Pinjam Ruangan</h3>
                <p style="font-size: 11.5px; font-weight: 700; color: #2563EB; margin: 3px 0 0 0;">
                    🏠 <span x-text="selectedRuangan"></span> &bull; 📅 <span x-text="selectedDate"></span>
                </p>
                <button type="button" @click="showBookingModal = false" 
                        style="position: absolute; right: 14px; top: 14px; font-size: 22px; color: #8E8E93; background: none; border: none; cursor: pointer; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                    &times;
                </button>
            </div>

            <form action="{{ route('booking.store') }}" method="POST" 
                  style="flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; padding: 14px 16px; display: flex; flex-direction: column; gap: 12px;">
                @csrf
                <input type="hidden" name="kategori_peminjam" :value="activeTab === 'dosen' ? 'Dosen' : 'Umum'">

                @if($errors->any())
                <div style="background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 12px; padding: 10px 14px; color: #DC2626; font-size: 12px;">
                    <div style="font-weight: 700; margin-bottom: 4px;">⚠️ Mohon periksa inputan:</div>
                    <ul style="margin: 0; padding-left: 18px; line-height: 1.5;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Pilihan Ruangan & Tanggal --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">
                            Ruangan <span style="color: #EF4444;">*</span>
                        </label>
                        <select name="ruangan" x-model="selectedRuangan" 
                                style="width: 100%; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 12.5px; color: #1C1C1E; -webkit-appearance: auto;">
                            @foreach($daftarRuanganDb as $rDb)
                                <option value="{{ $rDb->nama_ruangan }}">{{ $rDb->nama_ruangan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">
                            Tanggal <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="date" name="tanggal" x-model="selectedDate" 
                               style="width: 100%; box-sizing: border-box; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 8px 10px; font-size: 12.5px; color: #1C1C1E; -webkit-appearance: auto;">
                    </div>
                </div>

                {{-- Tab Kategori (segmented control identik dengan Calendar/Split View di desktop) --}}
                <div class="phone-seg-track">

                    {{-- Tab Kuliah / Praktikum --}}
                    <button type="button"
                            @click="activeTab = 'dosen'"
                            class="seg-btn"
                            :class="activeTab === 'dosen' ? 'seg-active' : ''">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                        <span>Kuliah / Praktikum</span>
                    </button>

                    {{-- Tab Umum / Kegiatan --}}
                    <button type="button"
                            @click="activeTab = 'umum'"
                            class="seg-btn"
                            :class="activeTab === 'umum' ? 'seg-active' : ''">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            <line x1="12" y1="12" x2="12" y2="17"/>
                            <line x1="9.5" y1="14.5" x2="14.5" y2="14.5"/>
                        </svg>
                        <span>Umum / Kegiatan</span>
                    </button>
                </div>

                {{-- Form Dosen --}}
                <div x-show="activeTab === 'dosen'" style="display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Nama Dosen <span style="color: #EF4444;">*</span></label>
                        <select name="dosen_id" style="width: 100%; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                            <option value="">-- Pilih Dosen... --</option>
                            @foreach($daftarDosen as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->nama_dosen }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Mata Kuliah <span style="color: #EF4444;">*</span></label>
                        <select name="mata_kuliah_id" style="width: 100%; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                            <option value="">-- Pilih Mata Kuliah... --</option>
                            @foreach($daftarMatkul as $matkul)
                                <option value="{{ $matkul->id }}">{{ $matkul->nama_matkul }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Kelas <span style="color: #EF4444;">*</span></label>
                        <select name="kelas_id" style="width: 100%; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                            <option value="">-- Pilih Kelas... --</option>
                            @foreach($daftarKelas as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jam Mulai & Durasi SKS (Kuliah) --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Jam Mulai <span style="color: #EF4444;">*</span></label>
                            <select name="waktu_mulai" x-model="selectedWaktuMulai" 
                                    style="width: 100%; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                                @foreach($daftarJamSlot as $js)
                                    @php $jm = substr($js->jam_mulai, 0, 5); @endphp
                                    <option value="{{ $jm }}">{{ $jm }} {{ !empty($js->label) ? '('.$js->label.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Durasi SKS <span style="color: #EF4444;">*</span></label>
                            <select name="sks" x-model="selectedSks" 
                                    style="width: 100%; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                                <option value="2">2 SKS (100 menit)</option>
                                <option value="3">3 SKS (150 menit)</option>
                                <option value="4">4 SKS (200 menit)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Info Jam Selesai Terkalkulasi Otomatis --}}
                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 9px 12px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11.5px; font-weight: 600; color: #1E40AF;">🕐 Jam Pelaksanaan:</span>
                        <span style="font-size: 13px; font-weight: 800; color: #1D4ED8; font-family: monospace;" 
                              x-text="selectedWaktuMulai + ' – ' + hitungJamSelesai(selectedWaktuMulai, selectedSks)"></span>
                    </div>
                </div>

                {{-- Form Umum --}}
                <div x-show="activeTab === 'umum'" style="display: flex; flex-direction: column; gap: 10px;" x-cloak>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Nama Peminjam / Organisasi <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="nama_peminjam_bebas" placeholder="Nama Anda / Organisasi" 
                               style="width: 100%; box-sizing: border-box; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Keperluan / Acara <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="keperluan_bebas" placeholder="Contoh: Rapat Koordinasi / Workshop" 
                               style="width: 100%; box-sizing: border-box; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E;">
                    </div>

                    {{-- Jam Mulai & Jam Selesai untuk Umum --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Jam Mulai <span style="color: #EF4444;">*</span></label>
                            <input type="time" name="waktu_mulai_bebas" x-model="selectedWaktuMulaiBebas" 
                                   style="width: 100%; box-sizing: border-box; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Jam Selesai <span style="color: #EF4444;">*</span></label>
                            <input type="time" name="waktu_selesai_bebas" x-model="selectedWaktuSelesaiBebas" 
                                   style="width: 100%; box-sizing: border-box; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 10px; font-size: 13px; color: #1C1C1E; -webkit-appearance: auto;">
                        </div>
                    </div>
                </div>

                {{-- Jenis Jadwal --}}
                <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 12px; border: 1px solid #E5E5EA;">
                    <label style="display: block; font-size: 10px; font-weight: 800; color: #8E8E93; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Jenis Jadwal</label>
                    <div style="display: flex; gap: 20px; font-size: 13px; font-weight: 500;">
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #1C1C1E;">
                            <input type="radio" name="jenis_jadwal" value="Tetap" checked> Tetap (Rutin)
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #1C1C1E;">
                            <input type="radio" name="jenis_jadwal" value="Insidental"> Insidental
                        </label>
                    </div>
                </div>

                {{-- Ulangi Jadwal --}}
                <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 12px; border: 1px solid #E5E5EA;">
                    <label style="display: block; font-size: 10px; font-weight: 800; color: #8E8E93; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Ulangi Jadwal (Opsional)</label>
                    <select name="ulangi" style="width: 100%; background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 8px 10px; font-size: 12.5px; color: #1C1C1E; -webkit-appearance: auto;">
                        <option value="1">Tidak berulang (Hanya tanggal ini)</option>
                        <option value="2">2 Minggu berturut-turut</option>
                        <option value="3">3 Minggu berturut-turut</option>
                        <option value="4">1 Bulan (4 Minggu)</option>
                        <option value="8">Setengah Semester (8 Minggu)</option>
                        <option value="14">1 Semester Penuh (14 Minggu)</option>
                    </select>
                </div>

                <button type="submit" 
                        style="width: 100%; padding: 13px; background: #2563EB; color: #FFFFFF; border: none; border-radius: 13px; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 2px 8px rgba(37,99,235,0.3); margin-top: 4px; margin-bottom: env(safe-area-inset-bottom, 8px);">
                    Simpan Jadwal Booking
                </button>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 6. MODAL DETAIL JADWAL (BOTTOM SHEET)                              --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="showDetailModal" x-cloak style="position: fixed; inset: 0; z-index: 50; overflow: hidden;" x-transition.opacity>
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" @click="showDetailModal = false"></div>
        <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #FFFFFF; border-radius: 24px 24px 0 0; padding: 16px 16px calc(16px + env(safe-area-inset-bottom, 0px)); box-shadow: 0 -4px 24px rgba(0,0,0,0.18);"
             x-transition:enter="transition ease-out duration-300 transform" 
             x-transition:enter-start="translate-y-full" 
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform" 
             x-transition:leave-start="translate-y-0" 
             x-transition:leave-end="translate-y-full">
            
            <div style="width: 36px; height: 4px; background: #CBD5E1; border-radius: 9999px; margin: 0 auto 14px auto;"></div>
            
            {{-- Judul Jadwal --}}
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 14px; padding: 13px; text-align: center; margin-bottom: 12px;">
                <span style="font-size: 10px; font-weight: 800; color: #1D4ED8; text-transform: uppercase; letter-spacing: 0.05em;" x-text="selectedBooking.ruangan"></span>
                <h4 style="font-size: 15px; font-weight: 800; color: #1E3A8A; margin: 4px 0 0 0;" 
                    x-text="selectedBooking.mata_kuliah?.nama_matkul || selectedBooking.keperluan_bebas || '-'"></h4>
            </div>

            {{-- Detail Jadwal --}}
            <div style="background: #F8FAFC; border: 1px solid #E5E5EA; border-radius: 12px; overflow: hidden; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #F1F5F9;">
                    <span style="font-size: 12px; color: #64748B;">Dosen / Peminjam</span>
                    <span style="font-size: 12px; font-weight: 700; color: #0F172A; text-align: right; max-width: 60%;" 
                          x-text="selectedBooking.dosen?.nama_dosen || selectedBooking.nama_peminjam_bebas || '-'"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #F1F5F9;" x-show="selectedBooking.kelas?.nama_kelas">
                    <span style="font-size: 12px; color: #64748B;">Kelas</span>
                    <span style="font-size: 12px; font-weight: 700; color: #0F172A;" x-text="selectedBooking.kelas?.nama_kelas"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #F1F5F9;">
                    <span style="font-size: 12px; color: #64748B;">Tanggal</span>
                    <span style="font-size: 12px; font-weight: 700; color: #0F172A;" x-text="selectedBooking.tanggal"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px;">
                    <span style="font-size: 12px; color: #64748B;">Waktu</span>
                    <span style="font-size: 13px; font-weight: 800; color: #2563EB; font-family: monospace;" 
                          x-text="(selectedBooking.waktu_mulai?.substring(0,5) || '') + ' – ' + (selectedBooking.waktu_selesai?.substring(0,5) || '')"></span>
                </div>
            </div>

            <button type="button" @click="showDetailModal = false" 
                    style="width: 100%; padding: 12px; background: #F1F5F9; color: #334155; border: none; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 7. MODAL TANYA MAKI AI (BOTTOM SHEET)                              --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="showAiChat" x-cloak 
         style="position: fixed; inset: 0; z-index: 50; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end;"
         x-data="{
             pertanyaan: '',
             loading: false,
             chats: [{ role: 'bot', text: 'Halo! Ada yang bisa Maki bantu seputar jadwal lab minggu ini?' }],
             tanyaAi() {
                 if(!this.pertanyaan.trim()) return;
                 let q = this.pertanyaan;
                 this.chats.push({ role: 'user', text: q });
                 this.pertanyaan = '';
                 this.loading = true;
                 fetch('{{ url('/api/ask-ai') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                     },
                     body: JSON.stringify({ pertanyaan: q })
                 })
                 .then(res => res.json())
                 .then(d => {
                     this.loading = false;
                     this.chats.push({ role: 'bot', text: d.answer || 'Maaf, Maki belum bisa menjawab.' });
                 })
                 .catch(() => {
                     this.loading = false;
                     this.chats.push({ role: 'bot', text: 'Koneksi terganggu. Silakan coba lagi.' });
                 });
             }
         }">
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" @click="showAiChat = false"></div>
        <div style="position: relative; z-index: 10; background: #FFFFFF; border-radius: 24px 24px 0 0; height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 -4px 24px rgba(0,0,0,0.18);">
            
            {{-- Header Chat --}}
            <div style="padding: 13px 16px; background: #2563EB; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">✨</span>
                    <div>
                        <h4 style="font-weight: 800; font-size: 14px; margin: 0; color: #FFFFFF;">Tanya Maki</h4>
                        <p style="font-size: 10px; color: #BFDBFE; margin: 0;">Asisten Jadwal Lab AI</p>
                    </div>
                </div>
                <button type="button" @click="showAiChat = false" style="background: none; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>

            {{-- Chat Bubbles --}}
            <div style="flex: 1; padding: 14px; overflow-y: auto; -webkit-overflow-scrolling: touch; display: flex; flex-direction: column; gap: 8px; background: #F8FAFC;">
                <template x-for="(c, i) in chats" :key="i">
                    <div :style="c.role === 'user' ? 'align-self: flex-end; max-width: 82%;' : 'align-self: flex-start; max-width: 82%;'">
                        <div :style="c.role === 'user' 
                                ? 'background: #2563EB; color: white; border-radius: 16px 16px 4px 16px; box-shadow: 0 1px 3px rgba(37,99,235,0.3);' 
                                : 'background: white; color: #1E293B; border-radius: 16px 16px 16px 4px; border: 1px solid #E5E5EA; box-shadow: 0 1px 2px rgba(0,0,0,0.05);'"
                             style="padding: 10px 13px; font-size: 13px; line-height: 1.45;" x-text="c.text">
                        </div>
                    </div>
                </template>
                <div x-show="loading" style="font-style: italic; color: #94A3B8; font-size: 11.5px; align-self: flex-start;">Maki sedang mengetik...</div>
            </div>

            {{-- Input --}}
            <div style="padding: 10px 12px; padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px)); background: #FFFFFF; border-top: 1px solid #E5E5EA; flex-shrink: 0;">
                <form @submit.prevent="tanyaAi()" style="display: flex; gap: 8px;">
                    <input type="text" x-model="pertanyaan" placeholder="Tanyakan jadwal..." 
                           style="flex: 1; background: #F1F5F9; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 9px 12px; font-size: 13px; color: #1C1C1E;"
                           @keydown.enter.prevent="tanyaAi()">
                    <button type="submit" 
                            style="padding: 9px 18px; background: #2563EB; color: #FFFFFF; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">
                        Kirim
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
