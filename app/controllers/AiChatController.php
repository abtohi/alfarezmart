<?php
/**
 * AiChatController - Menangani UI Chat dan Endpoint OpenRouter RAG
 */
class AiChatController extends Controller
{
    private $aiChatModel;
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

        $data = [
            'title' => 'AI Assistant - AlfarezMart',
            'currentUser' => $user,
            'csrfToken' => CsrfHelper::generateToken()
        ];
        
        $this->view('layouts/app', [
            'title' => $data['title'],
            'content' => $this->view('chat/index', $data, true),
            'currentUser' => $data['currentUser'],
            'active_menu' => 'chat'
        ]);
    }

    /**
     * API: Kirim pesan ke OpenRouter dengan RAG Context
     */
    public function sendMessage()
    {
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

        // Cek API Key khusus Chat
        $apiKey = $this->settingModel->get('ai_chat_api_key');
        if (empty($apiKey)) {
            // Fallback ke API Key utama jika chat key kosong
            $apiKey = $this->settingModel->get('ai_api_key');
            if (empty($apiKey)) {
                echo json_encode(['success' => false, 'error' => 'API Key OpenRouter belum dikonfigurasi. Hubungi Admin.']);
                exit;
            }
        }

        $model = $this->settingModel->get('ai_chat_model', 'openrouter/auto');

        // 1. Simpan pesan user
        $this->aiChatModel->saveMessage($user['id'], $sessionId, 'user', $message);

        // 2. Ambil riwayat chat sebelumnya (Max 10 pesan terakhir)
        $history = $this->aiChatModel->getHistory($user['id'], $sessionId, 10);

        // 3. Bangun Context (RAG)
        $contextBuilder = new AiContextBuilder();
        $systemPrompt = $contextBuilder->buildSystemPrompt($message);

        // 4. Susun pesan untuk OpenRouter
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Tambahkan histori chat (tidak termasuk system prompt)
        foreach ($history as $h) {
            $messages[] = [
                'role' => $h['role'],
                'content' => $h['content']
            ];
        }

        // Pastikan pesan terbaru ada di akhir (opsional, karena histori sudah urut dari lama ke baru dan mencakup pesan user terbaru yang baru di-save)

        // 5. Kirim ke OpenRouter via cURL
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        
        $postData = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.3 // Rendah agar jawaban lebih faktual sesuai data
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . BASE_URL,
            'X-Title: AlfarezMart AI Chat'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['success' => false, 'error' => 'Koneksi ke OpenRouter gagal: ' . $curlError]);
            exit;
        }

        $resData = json_decode($response, true);

        if ($httpCode >= 400 || isset($resData['error'])) {
            $errMsg = isset($resData['error']['message']) ? $resData['error']['message'] : 'Gagal memproses request AI (HTTP ' . $httpCode . ')';
            echo json_encode(['success' => false, 'error' => $errMsg]);
            exit;
        }

        $aiResponse = $resData['choices'][0]['message']['content'] ?? '';
        $tokens = $resData['usage']['total_tokens'] ?? 0;

        // 6. Simpan respon AI
        if (!empty($aiResponse)) {
            $this->aiChatModel->saveMessage($user['id'], $sessionId, 'assistant', $aiResponse, $tokens);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'response' => $aiResponse,
                'tokens' => $tokens
            ]
        ]);
    }

    /**
     * API: Ambil histori chat
     */
    public function getHistory()
    {
        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $sessionId = trim((string)$this->input('session_id', 'get'));
        
        if (empty($sessionId)) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        $history = $this->aiChatModel->getHistory($user['id'], $sessionId, 50); // ambil 50 pesan terakhir
        
        echo json_encode(['success' => true, 'data' => $history]);
    }

    /**
     * API: Hapus histori chat
     */
    public function clearHistory()
    {
        $this->validateCSRF();
        
        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $sessionId = trim((string)$this->input('session_id'));
        
        if (!empty($sessionId)) {
            $this->aiChatModel->clearHistory($user['id'], $sessionId);
        }
        
        echo json_encode(['success' => true, 'message' => 'Riwayat chat dibersihkan']);
    }
}
