<?php
/**
 * AiChatController - Menangani UI Chat dan Endpoint OpenRouter RAG
 */
class AiChatController extends Controller
{
    /** @var AiChatModel */
    private $aiChatModel;

    /** @var SettingModel */
    private $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->aiChatModel = new AiChatModel();
        $this->settingModel = new SettingModel();
    }

    /**
     * Tampilkan halaman Chat UI
     */
    public function index()
    {
        $user = AuthController::currentUser();
        if (!$user) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Cek jika fitur chat aktif
        $chatEnabled = $this->settingModel->get('ai_chat_enabled', '1');
        if ($chatEnabled === '0') {
            $_SESSION['flash_message'] = 'Fitur AI Chat sedang dinonaktifkan oleh Admin.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->view('chat/index', [
            'title' => 'AI Assistant - AlfarezMart',
            'active_menu' => 'chat'
        ]);
    }

    /**
     * API: Kirim pesan ke OpenRouter dengan RAG Context
     */
    public function sendMessage(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->validateCSRF();

        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $message = trim((string)$this->input('message'));
        $sessionId = trim((string)$this->input('session_id'));

        if (empty($message) || empty($sessionId)) {
            echo json_encode(['success' => false, 'error' => 'Pesan dan Session ID tidak boleh kosong.']);
            exit;
        }

        // Cek API Key khusus Chat, fallback ke API Key utama
        $apiKey = $this->settingModel->get('ai_chat_api_key');
        if (empty($apiKey)) {
            $apiKey = $this->settingModel->get('ai_api_key');
            if (empty($apiKey)) {
                echo json_encode(['success' => false, 'error' => 'API Key OpenRouter belum dikonfigurasi. Hubungi Admin.']);
                exit;
            }
        }

        $model = $this->settingModel->get('ai_chat_model', 'openrouter/auto');

        try {
            // 1. Simpan pesan user
            $this->aiChatModel->saveMessage((int)$user['id'], $sessionId, 'user', $message);

            // 2. Ambil riwayat chat sebelumnya (Max 10 pesan terakhir)
            $history = $this->aiChatModel->getHistory((int)$user['id'], $sessionId, 10);

            // 3. Bangun Context (RAG) — bungkus agar SQL error di AiContextBuilder tidak crash
            $systemPrompt = '';
            try {
                $contextBuilder = new AiContextBuilder();
                $systemPrompt = $contextBuilder->buildSystemPrompt($message);
            } catch (Throwable $ctxErr) {
                // Fallback system prompt jika RAG gagal
                $systemPrompt = "Kamu adalah AI Asisten untuk toko AlfarezMart. Jawab pertanyaan pengguna dalam Bahasa Indonesia secara ringkas dan profesional. (Catatan: data konteks toko tidak tersedia saat ini.)";
            }

            // 4. Susun pesan untuk OpenRouter
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];
            foreach ($history as $h) {
                $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            }

            // 5. Kirim ke OpenRouter via cURL
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $postData = [
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => 0.3,
                'max_tokens'  => 1024, // Batasi respons agar tidak melebihi kredit gratis
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
            curl_setopt($ch, CURLOPT_POST,            true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,      json_encode($postData));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  10);  // max 10 detik untuk koneksi
            curl_setopt($ch, CURLOPT_TIMEOUT,         60);  // max 60 detik untuk respons
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . BASE_URL,
                'X-Title: AlfarezMart AI Chat',
            ]);

            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                echo json_encode(['success' => false, 'error' => 'Koneksi ke OpenRouter gagal: ' . $curlError]);
                exit;
            }

            $resData = json_decode($response, true);

            if ($httpCode >= 400 || isset($resData['error'])) {
                $errMsg = $resData['error']['message'] ?? ('Gagal memproses request AI (HTTP ' . $httpCode . ')');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }

            $aiResponse = $resData['choices'][0]['message']['content'] ?? '';
            $tokens     = $resData['usage']['total_tokens'] ?? 0;

            // 6. Simpan respons AI
            if (!empty($aiResponse)) {
                $this->aiChatModel->saveMessage((int)$user['id'], $sessionId, 'assistant', $aiResponse, $tokens);
            }

            echo json_encode([
                'success' => true,
                'data'    => ['response' => $aiResponse, 'tokens' => $tokens],
            ]);

        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Ambil histori chat
     */
    public function getHistory(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // session_id dikirim via query string (?session_id=...) bukan POST body
        $sessionId = trim((string)$this->query('session_id'));

        if (empty($sessionId)) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        try {
            $history = $this->aiChatModel->getHistory((int)$user['id'], $sessionId, 50);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Gagal memuat riwayat: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Hapus histori chat
     */
    public function clearHistory(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->validateCSRF();

        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $sessionId = trim((string)$this->input('session_id'));

        try {
            if (!empty($sessionId)) {
                $this->aiChatModel->clearHistory((int)$user['id'], $sessionId);
            }
            echo json_encode(['success' => true, 'message' => 'Riwayat chat dibersihkan']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Gagal menghapus riwayat: ' . $e->getMessage()]);
        }
    }
}
