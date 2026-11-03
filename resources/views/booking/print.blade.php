<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Schedule - {{ $ruanganAktif }}</title>
    @vite(['resources/css/app.css'])
    <style>
        /* ======================================================================
           PAGE LOGIC & CSS COMPRESSION (Single-page fit guarantee)
           ====================================================================== */
        @if(isset($jenis) && ($jenis == 'Tetap' || $jenis == 'Regular'))
            @page { size: A4 portrait; margin: 0.4cm; }
            table { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
            th, td { border: 1px solid black; padding: 1.5px 3px; vertical-align: middle; line-height: 1.1; }
            .judul-utama { font-size: 10pt !important; line-height: 1; }
            .sub-judul { font-size: 8pt !important; line-height: 1; }
            .logo-lab { width: 2.5rem; height: 2.5rem; }
            .text-footer { font-size: 8pt !important; line-height: 1.1; }
            .kolom-jam { white-space: nowrap; width: 75px; text-align: center; font-weight: bold; font-family: monospace; }
        @else
            @page { size: A4 landscape; margin: 0.5cm; }
            table { width: 100%; border-collapse: collapse; font-size: 9pt; }
            th, td { border: 1px solid black; padding: 3px 5px; vertical-align: middle; line-height: 1.2; }
            .judul-utama { font-size: 12pt !important; line-height: 1.2; }
            .sub-judul { font-size: 10pt !important; line-height: 1.2; }
            .logo-lab { width: 3rem; height: 3rem; }
            .text-footer { font-size: 9pt !important; }
            .kolom-jam { white-space: nowrap; text-align: center; font-weight: bold; font-family: monospace; }
        @endif

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: white !important; }
            thead { display: table-header-group; }   
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }         
        }
    </style>
</head>
<body class="bg-white text-black" onload="window.print()">

    <table>
        <thead>
            <tr>
                <th colspan="5" class="border-0 p-0">
                    <div class="flex justify-between items-center border-b-[2px] border-black pb-0.5 mb-1 mt-0.5">
                        <div class="flex items-center">
                            <img src="{{ !empty($logoPath) ? asset($logoPath) : asset('images/logo.png') }}" alt="Logo" class="logo-lab object-contain mr-2">
                            <div class="text-left">
                                @if(!empty($namaInstansi))
                                    <div class="text-[7.5pt] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">{{ $namaInstansi }}</div>
                                @endif
                                <h1 class="judul-utama font-black text-gray-900 uppercase tracking-tight m-0">
                                    LABORATORY SCHEDULE
                                    @if(isset($jenis) && ($jenis == 'Tetap' || $jenis == 'Regular'))
                                        <span class="ml-1">(REGULAR)</span>
                                    @elseif(isset($jenis) && ($jenis == 'Insidental' || $jenis == 'Incidental'))
                                        <span class="ml-1">(INCIDENTAL)</span>
                                    @endif
                                </h1>
                                <h2 class="sub-judul font-bold text-blue-700 mt-0.5">{{ $ruanganAktif }}</h2>
                                <p class="sub-judul font-bold text-gray-600 mt-0.5">{{ $teksSemester }}</p>
                            </div>
                        </div>

                        <div class="text-left text-footer font-medium text-gray-800 space-y-0.5">
                            @if(!isset($jenis) || ($jenis != 'Tetap' && $jenis != 'Regular'))
                                <div class="flex items-center"><span class="mr-1">📅</span> Month: {{ $bulanTampil }}</div>
                            @endif
                            <div class="flex items-center"><span class="mr-1">🖨️</span> Printed: {{ \Carbon\Carbon::now()->translatedFormat('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </th>
            </tr>
            
            <tr class="bg-gray-100 text-center font-bold @if(isset($jenis) && ($jenis == 'Tetap' || $jenis == 'Regular')) text-[8pt] @else text-[10pt] @endif">
                <th class="py-0.5 w-14">Day {{ (!isset($jenis) || ($jenis != 'Tetap' && $jenis != 'Regular')) ? '/ Date' : '' }}</th>
                <th class="py-0.5 kolom-jam">Time Slot</th>
                <th class="py-0.5">Course / Purpose</th>
                <th class="py-0.5 w-14">Class</th>
                <th class="py-0.5 w-40">Lecturer / Reserved By</th>
            </tr>
        </thead>

        <tbody>
            @if(isset($jenis) && ($jenis == 'Tetap' || $jenis == 'Regular'))
                
                @php
                    $hariKerja = [
                        ['name' => 'Monday', 'alt' => 'Senin'],
                        ['name' => 'Tuesday', 'alt' => 'Selasa'],
                        ['name' => 'Wednesday', 'alt' => 'Rabu'],
                        ['name' => 'Thursday', 'alt' => 'Kamis'],
                        ['name' => 'Friday', 'alt' => 'Jumat'],
                    ];
                    $slotWaktu = [];
                    if(isset($daftarJamSlot) && $daftarJamSlot->count() > 0) {
                        for ($si = 0; $si < count($daftarJamSlot); $si++) {
                            $curStart = substr($daftarJamSlot[$si]->jam_mulai, 0, 5);
                            $nextStart = isset($daftarJamSlot[$si + 1]) ? substr($daftarJamSlot[$si + 1]->jam_mulai, 0, 5) : '18:00';
                            $slotWaktu[] = ['start' => $curStart, 'end' => $nextStart];
                        }
                    } else {
                        $slotWaktu = [
                            ['start' => '07:00', 'end' => '08:40'],
                            ['start' => '08:40', 'end' => '10:20'],
                            ['start' => '10:20', 'end' => '12:00'],
                            ['start' => '12:30', 'end' => '14:10'],
                            ['start' => '14:10', 'end' => '15:50'],
                            ['start' => '16:00', 'end' => '17:40'],
                        ];
                    }
                @endphp

                @foreach($hariKerja as $hari)
                    @php
                        $daftarHariJadwal = $jadwalTetapMingguan[$hari['name']] ?? $jadwalTetapMingguan[$hari['alt']] ?? collect();
                    @endphp
                    @foreach($slotWaktu as $index => $slot)
                        @php
                            $jadwalSlot = null;
                            if($daftarHariJadwal && count($daftarHariJadwal) > 0) {
                                foreach($daftarHariJadwal as $j) {
                                    $jm = substr($j->start_time ?? $j->waktu_mulai, 0, 5);
                                    if($jm >= $slot['start'] && $jm < $slot['end']) {
                                        $jadwalSlot = $j;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            @if($index === 0)
                                <td rowspan="{{ count($slotWaktu) }}" class="text-center font-bold align-middle bg-gray-50/50">
                                    {{ $hari['name'] }}
                                </td>
                            @endif
                            <td class="kolom-jam">{{ $slot['start'] }} - {{ $slot['end'] }}</td>
                            
                            @if($jadwalSlot)
                                <td class="text-gray-900 uppercase leading-tight">{{ $jadwalSlot->course->name ?? $jadwalSlot->mata_kuliah->nama_matkul ?? $jadwalSlot->general_purpose ?? $jadwalSlot->keperluan_bebas }}</td>
                                <td class="text-center font-bold text-gray-900">{{ $jadwalSlot->courseClass->class_code ?? $jadwalSlot->kelas->nama_kelas ?? '-' }}</td>
                                <td class="text-gray-800 leading-tight">{{ $jadwalSlot->lecturer->name ?? $jadwalSlot->dosen->nama_dosen ?? $jadwalSlot->general_borrower_name ?? $jadwalSlot->nama_peminjam_bebas }}</td>
                            @else
                                <td class="text-center text-gray-300">-</td>
                                <td class="text-center text-gray-300">-</td>
                                <td class="text-center text-gray-300">-</td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach

            @else
                
                @php
                    $jadwalGrouped = $semuaJadwal->groupBy(function($item) {
                        return $item->date ?? $item->tanggal;
                    });
                @endphp

                @forelse($jadwalGrouped as $tanggal => $jadwals)
                    @foreach($jadwals as $index => $j)
                        <tr>
                            @if($index === 0)
                                <td rowspan="{{ count($jadwals) }}" class="text-center font-bold align-top bg-gray-50/50">
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l') }}<br>
                                    <span class="text-[8pt] font-normal text-gray-600">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('M d, Y') }}</span>
                                </td>
                            @endif
                            <td class="kolom-jam">{{ substr($j->start_time ?? $j->waktu_mulai, 0, 5) }} - {{ substr($j->end_time ?? $j->waktu_selesai, 0, 5) }}</td>
                            <td class="text-gray-900 uppercase">{{ $j->course->name ?? $j->mata_kuliah->nama_matkul ?? $j->general_purpose ?? $j->keperluan_bebas }}</td>
                            <td class="text-center font-bold text-gray-900">{{ $j->courseClass->class_code ?? $j->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-gray-800">{{ $j->lecturer->name ?? $j->dosen->nama_dosen ?? $j->general_borrower_name ?? $j->nama_peminjam_bebas }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500 italic">No bookings scheduled for this month.</td>
                    </tr>
                @endforelse

            @endif
        </tbody>
    </table>

    {{-- SIGNATURE & NOTES AREA --}}
    <div class="mt-4 w-full px-1" style="page-break-inside: avoid;">
        {{-- Signature Row: Authority (Left) & Lab PIC (Right) --}}
        <div class="flex justify-between items-start text-footer w-full mb-3">
            {{-- Left Column: Approving Authority / Department Head --}}
            <div class="text-center w-5/12">
                <p class="text-[8.5pt]">Acknowledged by,</p>
                <p class="font-bold text-[8.5pt] mt-0.5">{{ $jabatanAtasan ?: 'Head of Department' }}</p>
                <div style="height: 45px;"></div>
                <p class="font-bold underline text-[8.5pt]">{{ $namaAtasan ?: '(___________________________)' }}</p>
                @if($nipAtasan)
                    <p class="text-[7.5pt] text-gray-700 mt-0.5">NIP / ID. {{ $nipAtasan }}</p>
                @endif
            </div>

            {{-- Right Column: Lab PIC --}}
            <div class="text-center w-5/12">
                <p class="text-[8.5pt]">{{ \Carbon\Carbon::now()->translatedFormat('F d, Y') }}</p>
                <p class="font-bold text-[8.5pt] mt-0.5">{{ $jabatanPj ?: ($namaLab ?? 'Laboratory') . ' Person in Charge' }}</p>
                <div style="height: 45px;"></div>
                <p class="font-bold underline text-[8.5pt]">{{ $namaPj ?: '(___________________________)' }}</p>
                @if($nipPj)
                    <p class="text-[7.5pt] text-gray-700 mt-0.5">NIP / ID. {{ $nipPj }}</p>
                @endif
            </div>
        </div>

        {{-- Footer Note & Contact --}}
        <div class="pt-2 border-t border-gray-300 text-left text-[7pt] text-gray-600 flex justify-between items-end">
            <div>
                <p class="italic mb-0.5">(*For room relocation, practicum rescheduling, or additional classes, please contact laboratory staff at the coordinator desk)</p>
                @if($kontakLaboran)
                    <p class="font-bold text-gray-800">Scheduling Coordinator: {{ $kontakLaboran }}</p>
                @endif
            </div>
            <div class="text-right text-[7pt] text-gray-400 whitespace-nowrap pl-4">
                © {{ date('Y') }} Maki
            </div>
        </div>
    </div>
</body>
</html>