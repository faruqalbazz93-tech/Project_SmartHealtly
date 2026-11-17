<?php

namespace App\Services;

use App\Config\App;
use App\Core\Auth;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\HealthService;
use App\Models\HealthArticle;
use App\Models\Question;
use App\Models\Registration;
use App\Models\Schedule;
use Exception;

class ChatService
{
    private AiService $aiService;
    private ChatConversation $conversationModel;
    private ChatMessage $messageModel;

    public function __construct()
    {
        $this->aiService = new AiService();
        $this->conversationModel = new ChatConversation();
        $this->messageModel = new ChatMessage();
    }

    /**
     * Get active conversation for current user or session
     */
    public function getActiveConversation(string $sessionId): array
    {
        if (Auth::check()) {
            return $this->conversationModel->getOrCreateForUser((int)Auth::id());
        }

        return $this->conversationModel->getOrCreateForSession($sessionId);
    }

    /**
     * Get messages history for current conversation
     */
    public function getConversationHistory(string $sessionId, int $limit = 40): array
    {
        $conversation = $this->getActiveConversation($sessionId);
        $messages = $this->messageModel->getMessages((int)$conversation['id'], $limit);

        // Sanitize messages for output
        return array_map(function ($msg) {
            return [
                'id'         => (int)$msg['id'],
                'role'       => $msg['role'],
                'message'    => $msg['message'],
                'created_at' => $msg['created_at'],
                'time_formatted' => date('H:i', strtotime($msg['created_at']))
            ];
        }, $messages);
    }

    /**
     * Clear chat conversation for current user or session
     */
    public function clearConversation(string $sessionId): bool
    {
        if (Auth::check()) {
            return $this->conversationModel->clearUserConversation((int)Auth::id());
        }

        return $this->conversationModel->clearSessionConversation($sessionId);
    }

    /**
     * Process an incoming user message and generate AI/Mock response
     */
    public function processMessage(string $sessionId, string $userMessage): array
    {
        $cleanMessage = trim($userMessage);
        if ($cleanMessage === '') {
            throw new Exception("Pesan tidak boleh kosong.");
        }

        if (mb_strlen($cleanMessage) > 500) {
            throw new Exception("Pesan terlalu panjang (maksimal 500 karakter).");
        }

        // 1. Get or create conversation
        $conversation = $this->getActiveConversation($sessionId);
        $convId = (int)$conversation['id'];

        // 2. Save user message to database
        $userMsgId = $this->messageModel->addMessage($convId, 'user', $cleanMessage);
        $this->conversationModel->touchConversation($convId);

        // 3. Check for acute emergency keywords first (Medical Safety Triage)
        $emergencyResponse = $this->checkEmergencyMedicalTriage($cleanMessage);
        if ($emergencyResponse !== null) {
            $this->messageModel->addMessage($convId, 'assistant', $emergencyResponse);
            return [
                'success' => true,
                'role'    => 'assistant',
                'message' => $emergencyResponse,
                'time'    => date('H:i'),
                'is_emergency' => true,
                'provider' => 'emergency_triage'
            ];
        }

        // 4. Fetch context data from database
        $systemContext = $this->buildSystemContext();

        // 5. Check if real AI is configured or use smart Mock Service
        if (!$this->aiService->isConfigured()) {
            $aiReply = $this->generateSmartMockResponse($cleanMessage);
        } else {
            // Load previous history for multi-turn coherence
            $history = $this->messageModel->getMessages($convId, 8);
            
            try {
                $aiReply = $this->aiService->chat($systemContext, $history, $cleanMessage);
            } catch (Exception $e) {
                // If API fails or times out, gracefully fallback to friendly message
                $aiReply = "Mohon maaf, layanan AI saat ini mengalami kendala koneksi (" . htmlspecialchars($e->getMessage()) . "). "
                    . "Namun Anda tetap dapat melihat jadwal poli Puskesmas, mendaftar antrean di menu Pendaftaran, atau menghubungi Hotline kami di " 
                    . htmlspecialchars(App::puskesmas()['hotline']) . ".";
            }
        }

        // 6. Save assistant reply to database
        $this->messageModel->addMessage($convId, 'assistant', $aiReply);
        $this->conversationModel->touchConversation($convId);

        return [
            'success'  => true,
            'role'     => 'assistant',
            'message'  => $aiReply,
            'time'     => date('H:i'),
            'provider' => $this->aiService->getProvider()
        ];
    }

    /**
     * Emergency medical keyword triage filter
     */
    private function checkEmergencyMedicalTriage(string $message): ?string
    {
        $lower = strtolower($message);
        $emergencyKeywords = [
            'nyeri dada hebat', 'sesak napas parah', 'tidak bisa bernapas', 'henti napas',
            'pendarahan hebat', 'kejang', 'pingsan', 'tidak sadar', 'hilang kesadaran',
            'muntah darah', 'batuk darah parah', 'keracunan', 'overdosis', 'tersedak anak',
            'lumpuh mendadak', 'stroke mendadak', 'bicara pelo mendadak'
        ];

        foreach ($emergencyKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $puskesmas = App::puskesmas();
                return "🚨 **PERHATIAN MEDIS GAWAT DARURAT** 🚨\n\n"
                    . "Gejala yang Anda sebutkan (*{$kw}*) berpotensi merupakan kondisi gawat darurat yang membutuhkan penanganan medis segera!\n\n"
                    . "Mohon segera lakukan langkah berikut:\n"
                    . "1. Segera datangi **IGD 24 Jam {$puskesmas['name']}** beralamat di: *{$puskesmas['address']}*, atau Rumah Sakit terdekat.\n"
                    . "2. Hubungi Hotline Darurat/IGD kami di **{$puskesmas['hotline']}** atau telepon **{$puskesmas['phone']}**.\n"
                    . "3. Anda juga dapat menghubungi Layanan Ambulans Gawat Darurat Nasional di **119**.\n\n"
                    . "*Asisten AI SmartHealthy bukan dokter dan tidak dapat memberikan tindakan pada keadaan gawat darurat.*";
            }
        }

        return null;
    }

    /**
     * Build rich, factual grounding system prompt from database
     */
    private function buildSystemContext(): string
    {
        $puskesmas = App::puskesmas();

        // 1. Puskesmas info
        $context = "Anda adalah SmartHealthy AI Assistant, asisten virtual resmi untuk {$puskesmas['name']}, {$puskesmas['district']}, {$puskesmas['regency']}.\n";
        $context .= "Alamat: {$puskesmas['address']}. Telepon: {$puskesmas['phone']}. Hotline IGD/WA: {$puskesmas['hotline']}. Email: {$puskesmas['email']}.\n";
        $context .= "Aplikasi SmartHealthy adalah sistem antrean dan pendaftaran Puskesmas gratis berbasis web.\n\n";

        // 2. Active Health Services & Doctors
        $serviceModel = new HealthService();
        $services = $serviceModel->getActiveServices();
        $context .= "LAYANAN POLI AKTIF DI PUSKESMAS ASTANAJAPURA:\n";
        foreach ($services as $s) {
            $context .= "- {$s['name']} (Kode: {$s['code']}) | Dokter/Penanggung Jawab: {$s['doctor_name']} | Kuota Harian: {$s['quota_daily']} pasien\n";
        }
        $context .= "\n";

        // 3. User Authentication & Active Registrations Context
        if (Auth::check()) {
            $user = Auth::user();
            $context .= "STATUS PENGGUNA SAAT INI: TERAUTENTIKASI (LOGIN)\n";
            $context .= "Nama Pasien: {$user['name']}\n";
            $context .= "Desa: " . ($user['village_name'] ?? 'Wilayah Astanajapura') . "\n";
            $context .= "NIK: " . substr($user['nik'] ?? '----------------', 0, 6) . "******\n";

            // Get registrations
            $regModel = new Registration();
            $registrations = $regModel->getByUserFiltered((int)$user['id']);

            if (!empty($registrations)) {
                $context .= "DAFTAR PENDAFTARAN ANTREAN MILIK PASIEN INI:\n";
                foreach (array_slice($registrations, 0, 5) as $r) {
                    $context .= "- Kode: {$r['registration_code']} | Poli: {$r['service_name']} | No. Antrean: {$r['queue_number']} | Tanggal: {$r['registration_date']} | Status: {$r['status']} | Keluhan: {$r['complaint']}\n";
                }
            } else {
                $context .= "Pasien ini belum memiliki riwayat atau antrean aktif di Puskesmas.\n";
            }
        } else {
            $context .= "STATUS PENGGUNA SAAT INI: PENGUNJUNG TAMU (BELUM LOGIN)\n";
            $context .= "Pengguna belum login. Untuk melihat status pendaftaran pribadi atau membuat tiket antrean, arahkan pengguna untuk Login di menu 'Masuk' (/login) atau Daftar Akun (/register).\n";
        }

        // 4. Articles
        try {
            $articleModel = new HealthArticle();
            $articles = $articleModel->getPublished(4);
            if (!empty($articles)) {
                $context .= "\nMATERI EDUKASI KESEHATAN TERSEDIA:\n";
                foreach ($articles as $art) {
                    $context .= "- {$art['title']} (Kategori: {$art['category_name']}): {$art['summary']}\n";
                }
            }
        } catch (\Throwable $e) {}

        // 5. Medical & Operational Rules
        $context .= "\nATURAN PERILAKU DAN KEBIJAKAN MEDIS (MUTLAK):\n";
        $context .= "1. Jawab ramah, sopan, mengayomi, dan gunakan Bahasa Indonesia yang mudah dipahami warga.\n";
        $context .= "2. DILARANG MEMBERIKAN DIAGNOSIS MEDIS DEFINITIF ATAU RESEP OBAT KERAS.\n";
        $context .= "3. Chatbot BUKAN pengganti dokter. Selalu berikan anjuran untuk berkonsultasi langsung dengan dokter di Puskesmas jika memerlukan pemeriksaan fisik.\n";
        $context .= "4. JANGAN PERNAH MENGARANG informasi poli, jadwal, atau kuota di luar data di atas.\n";
        $context .= "5. JANGAN KLAIM bahwa Anda telah mendaftarkan antrean jika form belum disubmit oleh pasien di sistem. Arahkan pasien ke menu Pendaftaran (/pendaftaran/baru).\n";
        $context .= "6. Untuk pendaftaran antrean, pasien hanya perlu login, pilih poli, tanggal kunjungan, dan masukkan keluhan singkat untuk mendapatkan nomor antrean digital.\n";

        return $context;
    }

    /**
     * Smart Mock Service for development / offline use
     */
    private function generateSmartMockResponse(string $message): string
    {
        $lower = strtolower($message);
        $puskesmas = App::puskesmas();

        $prefix = "[Mode Simulasi / Development Puskesmas - AI API Key belum disetel]\n\n";

        // Query: Cara mendaftar
        if (str_contains($lower, 'daftar') || str_contains($lower, 'mendaftar') || str_contains($lower, 'booking')) {
            return $prefix 
                . "Untuk mendaftar antrean layanan kesehatan di **{$puskesmas['name']}**, ikuti 4 langkah mudah berikut:\n\n"
                . "1. **Login atau Buat Akun**: Masuk menggunakan NIK/Email terdaftar di menu [Masuk](/login). Jika belum memiliki akun, daftar di menu [Daftar Pasien Baru](/register).\n"
                . "2. **Pilih Layanan**: Masuk ke menu **Pendaftaran Baru**, pilih poli layanan yang Anda butuhkan (misalnya: Poli Umum, Gigi, KIA & KB, dll).\n"
                . "3. **Tentukan Tanggal & Keluhan**: Pilih tanggal rencana kunjungan dan ketik keluhan kesehatan Anda secara singkat.\n"
                . "4. **Dapatkan E-Ticket**: Sistem akan secara otomatis menerbitkan Nomor Antrean dan Tiket Digital yang dapat Anda simpan atau cetak langsung.";
        }

        // Query: Layanan yang tersedia
        if (str_contains($lower, 'layanan') || str_contains($lower, 'poli') || str_contains($lower, 'dokter') || str_contains($lower, 'tersedia')) {
            $serviceModel = new HealthService();
            $services = $serviceModel->getActiveServices();
            
            $text = $prefix . "Berikut adalah layanan Poli dan Tenaga Medis yang siap melayani Anda di **{$puskesmas['name']}**:\n\n";
            foreach ($services as $s) {
                $text .= "• **{$s['name']}** — Dokter/PJ: {$s['doctor_name']} (Kuota harian: {$s['quota_daily']} pasien)\n";
            }
            $text .= "\nSemua layanan untuk warga ber-KTP/BPJS adalah **100% Gratis**. Pendaftaran dapat dilakukan secara online melalui website ini.";
            return $text;
        }

        // Query: Status pendaftaran
        if (str_contains($lower, 'status') || str_contains($lower, 'tiket') || str_contains($lower, 'antrean') || str_contains($lower, 'nomor antrean')) {
            if (Auth::check()) {
                $user = Auth::user();
                $regModel = new Registration();
                $registrations = $regModel->getByUserFiltered((int)$user['id']);

                if (!empty($registrations)) {
                    $latest = $registrations[0];
                    return $prefix 
                        . "Halo, **{$user['name']}**! Berikut adalah status pendaftaran antrean terbaru Anda:\n\n"
                        . "• **Kode Booking**: `{$latest['registration_code']}`\n"
                        . "• **Layanan**: {$latest['service_name']}\n"
                        . "• **Nomor Antrean**: #{$latest['queue_number']}\n"
                        . "• **Tanggal Kunjungan**: {$latest['registration_date']}\n"
                        . "• **Status**: **{$latest['status']}**\n\n"
                        . "Anda dapat melihat tiket lengkap atau mencetaknya di menu [Riwayat Pendaftaran](/pendaftaran/riwayat).";
                } else {
                    return $prefix . "Halo, **{$user['name']}**! Anda saat ini belum memiliki pendaftaran antrean aktif. Anda dapat membuat pendaftaran baru di menu [Pendaftaran Layanan](/pendaftaran/baru).";
                }
            } else {
                return $prefix 
                    . "Untuk melihat status pendaftaran pribadi dan nomor antrean Anda, silakan **[Masuk ke Akun Anda](/login)** terlebih dahulu.\n\n"
                    . "Jika Anda sudah memegang kode booking pendaftaran, Anda juga dapat mengecek status panggilan nomor antrean tanpa login di halaman **[Cek Nomor Antrean Publik](/cek-antrean)**.";
            }
        }

        // Query: Edukasi & Belajar Kesehatan
        if (str_contains($lower, 'belajar') || str_contains($lower, 'edukasi') || str_contains($lower, 'artikel') || str_contains($lower, 'stunting') || str_contains($lower, 'dbd') || str_contains($lower, 'hipertensi')) {
            return $prefix 
                . "SmartHealthy menyediakan modul **Edukasi & Promosi Kesehatan (Promkes)** untuk warga Astanajapura, meliputi:\n\n"
                . "1. **Pencegahan Stunting 1000 HPK**: Pentingnya pemenuhan gizi seimbang sejak masa kehamilan hingga anak berusia 2 tahun.\n"
                . "2. **Gerakan 3M Plus DBD**: Menguras penampungan air, menutup rapat wadah air, mengubur barang bekas, dan pencegahan gigitan nyamuk Aedes aegypti.\n"
                . "3. **Pengendalian Hipertensi & CERDIK**: Cek kesehatan berkala, enyahkan asap rokok, rajin olahraga, diet seimbang, istirahat cukup, dan kelola stres.\n\n"
                . "Seluruh artikel lengkap dapat Anda baca secara bebas di halaman **[Edukasi Kesehatan](/artikel)**.";
        }

        // Query: Kuis kesehatan
        if (str_contains($lower, 'kuis') || str_contains($lower, 'soal') || str_contains($lower, 'paham') || str_contains($lower, 'tes')) {
            return $prefix 
                . "Anda dapat menguji pemahaman kesehatan Anda melalui fitur **Kuis Kesehatan Interaktif SmartHealthy**!\n\n"
                . "• Dapatkan 5 soal pilihan ganda acak seputar PHBS, nutrisi, dan pencegahan penyakit.\n"
                . "• Skor dinilai secara otomatis dan instan.\n"
                . "• Dilengkapi dengan fitur *'Jelaskan dengan AI'* untuk mendapatkan penjelasan medis mendalam atas setiap soal.\n\n"
                . "Ayo coba kuisnya di menu **[Kuis Kesehatan](/kuis)**!";
        }

        // Default response
        return $prefix 
            . "Halo! Saya asisten virtual **SmartHealthy {$puskesmas['name']}**.\n\n"
            . "Saya dapat membantu Anda dengan:\n"
            . "• Penjelasan cara mendaftar poli antrean gratis\n"
            . "• Informasi dokter, poli, dan jadwal operasional\n"
            . "• Pengecekan status pendaftaran antrean Anda\n"
            . "• Informasi promosi kesehatan dan pembahasan kuis\n\n"
            . "Silakan klik salah satu pertanyaan cepat di bawah atau ketikkan pertanyaan Anda!";
    }
}
