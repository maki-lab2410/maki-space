<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\Room;
use App\Models\TimeSlot;
use Carbon\Carbon;

class AiController extends Controller
{
    public function askAi(Request $request)
    {
        // ── Validasi & sanitasi input dari user (fix: support key 'pertanyaan' dan 'question') ──
        $request->validate([
            'pertanyaan' => 'nullable|string|max:1500',
            'question'   => 'nullable|string|max:1500',
        ]);

        $userMessage = trim(
            $request->input('pertanyaan')
            ?? $request->input('question')
            ?? ''
        );

        if (empty($userMessage)) {
            return response()->json([
                'status' => 'error',
                'jawaban' => 'Pertanyaan tidak boleh kosong.',
                'answer'  => 'Question cannot be empty.',
            ], 422);
        }

        // Sanitasi: hapus karakter kontrol berbahaya, batasi panjang
        $userMessage = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $userMessage);
        $userMessage = mb_substr($userMessage, 0, 1000);
        
        // 1. FILTER DATA JADWAL (Mencakup 7 hari lalu hingga 45 hari ke depan)
        $startDate = Carbon::today()->subDays(7)->format('Y-m-d');
        $endDate   = Carbon::today()->addDays(45)->format('Y-m-d');

        // Ambil daftar ruangan resmi
        $daftarRuangan = Room::where('is_active', true)->orderBy('order')->pluck('name')->toArray();
        if (empty($daftarRuangan)) {
            $daftarRuangan = ['Koding dan Reimbursement', 'Statistik dan Pelaporan', 'Anatomi Fisiologi', 'Ruang Serbaguna'];
        }

        // Ambil daftar jam slot resmi
        $daftarSlotDb = TimeSlot::orderBy('order')->get();
        if ($daftarSlotDb->count() > 0) {
            $daftarSlot = $daftarSlotDb->map(function($s) {
                return $s->label ?: (substr($s->start_time, 0, 5) . ' - selesai');
            })->toArray();
        } else {
            $daftarSlot = [
                '07.00 - 08.40',
                '08.40 - 10.20',
                '10.20 - 12.00',
                '12.30 - 14.10',
                '14.10 - 15.50',
                '16.00 - 17.40',
                'LEBIH > 18.00'
            ];
        }

        // Ambil jadwal lengkap dengan jam mulai dan jam selesai
        $jadwal = Booking::with(['lecturer', 'course', 'courseClass'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function($j) {
                return [
                    'tanggal'     => $j->date,
                    'hari'        => Carbon::parse($j->date)->isoFormat('dddd'),
                    'jam_mulai'   => substr($j->start_time, 0, 5),
                    'jam_selesai' => substr($j->end_time, 0, 5),
                    'ruangan'     => $j->room,
                    'kegiatan'    => $j->course->name ?? $j->general_purpose ?? '-',
                    'dosen'       => $j->lecturer->name ?? $j->general_borrower_name ?? '-',
                    'kelas'       => $j->courseClass->name ?? '-',
                ];
            });

        $dataJadwalJson = json_encode($jadwal);

        // 2. Ambil pengaturan Gemini dari database (fallback ke .env)
        $apiKey     = Setting::get('gemini_api_key') ?: trim(env('GEMINI_API_KEY', ''));
        $rawModel   = Setting::get('gemini_model', 'gemini-2.5-flash');
        $rawTokens  = (int) Setting::get('gemini_max_tokens', 2048);
        $maxTokens  = max($rawTokens, 2048);
        $temp       = (float) Setting::get('gemini_temperature', 0.1);
        $namaLab    = Setting::get('nama_lab', 'Maki Space');

        if (empty($apiKey)) {
            return response()->json([
                'status' => 'error',
                'pesan'  => 'Google Gemini API Key has not been configured. Please configure your API key in Admin Panel → AI & System Settings.'
            ]);
        }

        $todayDate = Carbon::today()->format('Y-m-d');
        $todayDay  = Carbon::today()->isoFormat('dddd');

        // 3. SYSTEM INSTRUCTION BERBASIS LOGIKA SLOT PRESISI
        $systemPrompt = "You are Maki, the intelligent laboratory scheduling assistant for {$namaLab}.\n" .
            "Today's date is: {$todayDay}, {$todayDate}.\n" .
            "Official Lab Rooms: " . implode(', ', $daftarRuangan) . ".\n" .
            "Standard Lab Time Slots: " . implode(' | ', $daftarSlot) . ".\n\n" .
            "CRITICAL RULES FOR CHECKING SCHEDULES & AVAILABLE / EMPTY SLOTS:\n" .
            "1. Each item in SCHEDULE DATA contains: tanggal (YYYY-MM-DD), hari, jam_mulai, jam_selesai, ruangan, kegiatan, dosen, kelas.\n" .
            "2. A slot is BUSY (TERISI) if there is an activity in that room overlapping with the slot's time range (between jam_mulai and jam_selesai).\n" .
            "3. A slot is AVAILABLE / EMPTY (KOSONG) if NO activity occupies that slot in that room on that date.\n" .
            "4. STEP-BY-STEP REASONING FOR CHECKING EMPTY TIMES ON A DATE:\n" .
            "   - Step A: Identify the requested date and room(s).\n" .
            "   - Step B: Check each standard slot one by one against the existing schedules:\n" .
            "     * Slot 07:00 - 08:40: If no schedule starts at 07:00, it is EMPTY (07:00 - 08:40).\n" .
            "     * Slot 08:40 - 10:20: If there is a schedule (e.g. 08:40 - 10:20), it is BUSY. DO NOT report 08:40 - 10:20 as empty!\n" .
            "     * Slot 10:20 - 12:00: If there is a schedule (e.g. 10:20 - 12:00), it is BUSY. DO NOT report 10:20 - 12:00 as empty!\n" .
            "     * Slots after 12:30 (12:30 - 14:10, 14:10 - 15:50, etc.): If no schedule exists after 12:00, report as EMPTY from 12:30 onwards (e.g. '12:30 - seterusnya').\n" .
            "   - Step C: NEVER combine an empty slot with a busy slot! For example, NEVER say '07:00 - 10:20 is empty' when 08:40 - 10:20 is busy!\n" .
            "5. If a room has NO schedules at all on the requested date, clearly state that the room is COMPLETELY EMPTY (kosong sepanjang hari).\n" .
            "6. CONCISENESS & COMPLETENESS (VERY IMPORTANT):\n" .
            "   - When asked about available/empty slots (jadwal kosong), focus directly on listing the EMPTY times for each room.\n" .
            "   - Do NOT write lengthy paragraphs or elaborate academic titles for busy slots. You can simply note: '*(Terisi: 08:40 - 12:00)*'.\n" .
            "   - Ensure your answer covers all relevant rooms completely without stopping mid-sentence or cutting off.\n" .
            "7. Always answer politely, clearly, and accurately in the user's language (Indonesian or English).\n" .
            "8. Use bold (**) formatting for dates, room names, times, and slot availability.\n\n" .
            "SCHEDULE DATA (JSON):\n" . $dataJadwalJson;

        // 4. CANDIDATE MODELS WITH RESILIENT FALLBACK
        // Auto-upgrade obsolete 1.5 models that return 404
        $primaryModel = trim($rawModel);
        if (in_array($primaryModel, ['gemini-1.5-flash', 'gemini-1.5-pro', ''])) {
            $primaryModel = 'gemini-2.5-flash';
        }

        // Ordered list of candidate models supported in current Gemini tier free
        $candidates = array_unique(array_filter([
            $primaryModel,
            'gemini-2.5-flash',
            'gemini-3.5-flash-lite',
            'gemini-2.5-flash-lite',
            'gemini-3.1-flash-lite',
            'gemini-2.0-flash',
            'gemini-3-flash',
            'gemini-2.5-pro',
        ]));

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $userMessage]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => $temp,
                'maxOutputTokens' => $maxTokens,
            ]
        ];
        $payloadJson = json_encode($payload);

        $lastError    = 'Unknown error occurred while contacting Gemini API';
        $lastHttpCode = 500;
        $successResult = null;
        $workingModel  = null;

        foreach ($candidates as $candidateModel) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$candidateModel}:generateContent?key=" . urlencode($apiKey);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
            // ── SSL: AKTIFKAN verifikasi untuk mencegah MITM attack ──
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                $lastError = 'Connection failed: ' . $err;
                continue;
            }

            $result = json_decode($response, true);

            // If 404 (model not found / deprecated), fallback to next candidate
            if ($httpCode === 404 || (isset($result['error']['code']) && $result['error']['code'] == 404)) {
                $lastError = $result['error']['message'] ?? "Model {$candidateModel} not found (404)";
                $lastHttpCode = 404;
                continue;
            }

            if (isset($result['error'])) {
                $lastError = $result['error']['message'] ?? json_encode($result['error']);
                $lastHttpCode = $httpCode;
                // If rate limited (429), try next candidate
                if ($httpCode === 429) {
                    continue;
                }
                break;
            }

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $successResult = $result;
                $workingModel  = $candidateModel;
                break;
            }
        }

        if (!$successResult) {
            return response()->json([
                'status' => 'error',
                'pesan'  => 'Google Gemini API error (Code ' . $lastHttpCode . '): ' . $lastError
            ]);
        }

        // Auto-persist working model if changed from obsolete model
        if ($workingModel && $workingModel !== $rawModel) {
            try {
                Setting::set('gemini_model', $workingModel);
            } catch (\Throwable $e) {
                // Silently continue if database is read-only
            }
        }

        $jawabanAi = $successResult['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$jawabanAi) {
            $finishReason = $successResult['candidates'][0]['finishReason'] ?? 'UNKNOWN';
            $jawabanAi = "Sorry, no response could be generated (Status: {$finishReason}). Please try asking in a different way.";
        }

        // Sanitasi terhadap karakter berbahaya HTML untuk mencegah injeksi XSS pada antarmuka
        $jawabanAi = htmlspecialchars($jawabanAi, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return response()->json([
            'status'  => 'success',
            'jawaban' => $jawabanAi
        ]);
    }
}