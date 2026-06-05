<?php
/**
 * AiChatController - UI Chat + Endpoint OpenRouter RAG + Knowledge Base
 */
class AiChatController extends Controller
{
    /** @var AiChatModel */
    private $aiChatModel;

    /** @var SettingModel */
    private $settingModel;

    /** Pola kalimat koreksi dari user (bahasa Indonesia) */
    private const CORRECTION_PATTERNS = [
        '/yang benar(nya)?\s+(adalah|:)/i',
        '/seharusnya\s+/i',
        '/koreksi\s*:/i',
        '/salah[,.]?\s+(yang benar|sebenarnya)/i',
        '/bukan\s+(itu|begitu)[,.]?\s+/i',
        '/perbaiki\s*:/i',
        '/maksudku\s+/i',
        '/tolong\s+perbaiki/i',
        '/faktanya\s+/i',
        '/informasi yang tepat/i',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->aiChatModel  = new AiChatModel();
        $this->settingModel = new SettingModel();
    }

    // ============================================================
    // HALAMAN CHAT
    // ============================================================

    public function index(): void
    {
        $user = AuthController::currentUser();
        if (!$user) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $chatEnabled = $this->settingModel->get('ai_chat_enabled', '1');
        if ($chatEnabled === '0') {
            $_SESSION['flash_message'] = 'Fitur AI Chat sedang dinonaktifkan oleh Admin.';
            $_SESSION['flash_type']    = 'warning';
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->view('chat/index', [
            'title'       => 'AI Assistant - AlfarezMart',
            'active_menu' => 'chat',
        ]);
    }

    // ============================================================
    // API: KIRIM PESAN
    // ============================================================

    public function sendMessage(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->validateCSRF();

        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $message   = trim((string)$this->input('message'));
        $sessionId = trim((string)$this->input('session_id'));

        if (empty($message) || empty($sessionId)) {
            echo json_encode(['success' => false, 'error' => 'Pesan dan Session ID tidak boleh kosong.']);
            exit;
        }

        // API Key
        $apiKey = $this->settingModel->get('ai_chat_api_key') ?: $this->settingModel->get('ai_api_key');
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'error' => 'API Key OpenRouter belum dikonfigurasi. Hubungi Admin.']);
            exit;
        }

        $model = $this->settingModel->get('ai_chat_model', 'openrouter/auto');

        try {
            // Auto-detect koreksi dari user → simpan ke knowledge base
            $this->autoSaveCorrection($message, (int)$user['id'], $sessionId);

            // 1. Simpan pesan user
            $this->aiChatModel->saveMessage((int)$user['id'], $sessionId, 'user', $message);

            // 2. Riwayat chat (max 10 pesan)
            $history = $this->aiChatModel->getHistory((int)$user['id'], $sessionId, 10);

            // 3. Bangun context RAG (internal first)
            $systemPrompt = 'Kamu adalah AI Asisten toko AlfarezMart. Jawab dalam Bahasa Indonesia.';
            try {
                $contextBuilder = new AiContextBuilder();
                $systemPrompt   = $contextBuilder->buildSystemPrompt($message);
            } catch (Throwable $ctxErr) {
                // Fallback jika RAG gagal
            }

            // 4. Susun messages untuk OpenRouter
            $messages = [['role' => 'system', 'content' => $systemPrompt]];
            foreach ($history as $h) {
                $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            }

            // 5. Kirim ke OpenRouter (Agentic Loop max 2 pass)
            $url      = 'https://openrouter.ai/api/v1/chat/completions';
            $maxPasses = 2;
            $currentPass = 1;
            $aiResponse = '';
            $totalTokens = 0;

            while ($currentPass <= $maxPasses) {
                $postData = [
                    'model'       => $model,
                    'messages'    => $messages,
                    'temperature' => 0.2,   // Lebih rendah = lebih faktual
                    'max_tokens'  => 1024,
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
                curl_setopt($ch, CURLOPT_POST,            true);
                curl_setopt($ch, CURLOPT_POSTFIELDS,      json_encode($postData));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  10);
                curl_setopt($ch, CURLOPT_TIMEOUT,         60);
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
                $totalTokens += $resData['usage']['total_tokens'] ?? 0;

                // Cek apakah ada SQL Query
                if (preg_match('/\[SQL_QUERY\](.*?)\[\/SQL_QUERY\]/is', $aiResponse, $matches)) {
                    $sqlQuery = trim($matches[1]);
                    
                    // Keamanan: Validasi hanya boleh SELECT
                    if (stripos($sqlQuery, 'SELECT') !== 0 || preg_match('/(?:INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|REPLACE|GRANT|REVOKE)\b/i', $sqlQuery)) {
                        $sqlResult = "ERROR: Keamanan ditolak. Hanya query SELECT yang diizinkan.";
                    } else {
                        // Tambahkan LIMIT jika belum ada
                        if (stripos($sqlQuery, 'LIMIT') === false) {
                            $sqlQuery .= " LIMIT 50";
                        }
                        
                        try {
                            $db = Database::getInstance()->getConnection();
                            $stmt = $db->query($sqlQuery);
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $sqlResult = json_encode($results, JSON_UNESCAPED_UNICODE);
                        } catch (Throwable $e) {
                            $sqlResult = "ERROR SQL: " . $e->getMessage();
                        }
                    }

                    // Append the AI's partial response and the SQL result
                    $messages[] = ['role' => 'assistant', 'content' => $aiResponse];
                    $messages[] = ['role' => 'user', 'content' => "[SQL_RESULT]\n" . $sqlResult . "\n[/SQL_RESULT]\nSekarang jawab pertanyaan saya berdasarkan data di atas. Jangan tampilkan query-nya lagi ke user."];
                    
                    $currentPass++;
                } else {
                    // No SQL query, break the loop
                    break;
                }
            }

            // 6. Simpan respons AI
            if (!empty($aiResponse)) {
                $this->aiChatModel->saveMessage((int)$user['id'], $sessionId, 'assistant', $aiResponse, $totalTokens);
            }

            echo json_encode([
                'success' => true,
                'data'    => ['response' => $aiResponse, 'tokens' => $totalTokens],
            ]);

        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
        }
    }

    // ============================================================
    // API: RIWAYAT CHAT
    // ============================================================

    public function getHistory(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

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

    // ============================================================
    // API: HAPUS RIWAYAT
    // ============================================================

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

    // ============================================================
    // API: SIMPAN FEEDBACK / KOREKSI MANUAL
    // ============================================================

    /**
     * Endpoint: POST /api/chat/feedback
     * Body JSON: { topic, correction, session_id }
     *
     * Menyimpan koreksi eksplisit user sebagai knowledge baru.
     */
    public function saveFeedback(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->validateCSRF();

        $user = AuthController::currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $topic      = trim((string)$this->input('topic'));
        $correction = trim((string)$this->input('correction'));
        $context    = trim((string)$this->input('context', ''));

        if (empty($correction)) {
            echo json_encode(['success' => false, 'error' => 'Koreksi tidak boleh kosong.']);
            exit;
        }

        // Buat topic otomatis jika tidak disediakan
        if (empty($topic)) {
            $topic = mb_substr($correction, 0, 80);
        }

        // Susun konten yang akan disimpan
        $content = $correction;
        if (!empty($context)) {
            $content = "Konteks pertanyaan: \"{$context}\"\nFakta yang benar: {$correction}";
        }

        try {
            $saved = $this->aiChatModel->saveKnowledge($topic, $content, 'user_feedback');
            if ($saved) {
                echo json_encode(['success' => true, 'message' => 'Terima kasih! Koreksi Anda sudah disimpan dan akan digunakan untuk meningkatkan AI.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menyimpan koreksi.']);
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // ============================================================
    // INTERNAL: AUTO-DETECT KOREKSI
    // ============================================================

    /**
     * Mendeteksi apakah pesan user mengandung koreksi.
     * Jika ya, simpan otomatis ke knowledge base.
     */
    private function autoSaveCorrection(string $message, int $userId, string $sessionId): void
    {
        $isCorrection = false;
        foreach (self::CORRECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $message)) {
                $isCorrection = true;
                break;
            }
        }
        if (!$isCorrection) return;

        // Cari pesan terakhir dari assistant di sesi ini sebagai konteks topik
        $history = $this->aiChatModel->getHistory($userId, $sessionId, 6);
        $lastAiMsg = '';
        foreach (array_reverse($history) as $h) {
            if ($h['role'] === 'assistant') {
                // Ambil 120 karakter pertama sebagai ringkasan topik
                $lastAiMsg = mb_substr(strip_tags($h['content']), 0, 120);
                break;
            }
        }

        $topic   = $lastAiMsg ?: mb_substr($message, 0, 100);
        $content = "Koreksi dari pengguna: {$message}";
        if ($lastAiMsg) {
            $content = "AI sebelumnya menjawab: \"{$lastAiMsg}...\"\nKoreksi pengguna: {$message}";
        }

        $this->aiChatModel->saveKnowledge($topic, $content, 'auto_correction');
    }
}
