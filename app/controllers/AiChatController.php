<?php
/**
 * AiChatController v5.0 - Smart AI Chat with Auto-Learning
 *
 * Features:
 * - SQL-First agentic loop (max 3 passes)
 * - Auto-learning: saves successful SQL results as facts
 * - Smart history trimming (max 6 messages)
 * - Token-efficient (max_tokens: 800)
 * - User correction auto-detection
 * - .env fallback for API key
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

        // API Key: Settings UI > .env > fail
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'error' => 'API Key OpenRouter belum dikonfigurasi. Masukkan di Pengaturan > Aplikasi > AI Chat.']);
            exit;
        }

        $model = $this->settingModel->get('ai_chat_model', 'openrouter/free');
        if (empty($model) || in_array($model, ['openrouter/auto', 'deepseek/deepseek-chat:free', 'meta-llama/llama-3.3-70b-instruct:free'])) {
            $model = 'openrouter/free';
        }

        try {
            // Cleanup expired facts periodically (1 in 10 chance)
            if (mt_rand(1, 10) === 1) {
                $this->aiChatModel->cleanupExpiredFacts();
            }

            // Auto-detect correction from user → save to knowledge base
            $this->autoSaveCorrection($message, (int)$user['id'], $sessionId);

            // 1. Save user message
            $this->aiChatModel->saveMessage((int)$user['id'], $sessionId, 'user', $message);

            // 2. Get chat history (max 6 messages for token efficiency)
            $history = $this->aiChatModel->getHistory((int)$user['id'], $sessionId, 6);

            // 3. Build smart context
            $contextBuilder = new AiContextBuilder();
            $systemPrompt   = $contextBuilder->buildSystemPrompt($message, $user);

            // 4. Build messages array for OpenRouter
            $messages = [['role' => 'system', 'content' => $systemPrompt]];
            foreach ($history as $h) {
                $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            }

            // 5. Agentic SQL Loop (max 3 passes)
            $url         = 'https://openrouter.ai/api/v1/chat/completions';
            $maxPasses   = 3;
            $currentPass = 1;
            $aiResponse  = '';
            $totalTokens = 0;

            // List of reliable 100% FREE models to try sequentially if primary model fails
            $fallbackModels = [
                $model,
                'openrouter/free',
                'google/gemma-4-26b-a4b-it:free',
                'nvidia/nemotron-nano-12b-v2-vl:free',
                'openai/gpt-oss-20b:free',
                'cohere/north-mini-code:free',
            ];
            $fallbackModels = array_values(array_unique(array_filter($fallbackModels)));

            while ($currentPass <= $maxPasses) {
                $rawContent = '';
                $requestSuccess = false;
                $lastErrMsg = '';

                foreach ($fallbackModels as $currentModel) {
                    $postData = [
                        'model'       => $currentModel,
                        'messages'    => $messages,
                        'temperature' => 0.15,
                        'max_tokens'  => 2500,
                    ];

                    $ch = curl_init($url);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => json_encode($postData),
                        CURLOPT_CONNECTTIMEOUT => 15,
                        CURLOPT_TIMEOUT        => 60,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_HTTPHEADER     => [
                            'Authorization: Bearer ' . $apiKey,
                            'Content-Type: application/json',
                            'HTTP-Referer: https://alfarezmart.com',
                            'X-Title: AlfarezMart AI',
                        ],
                    ]);

                    $response  = curl_exec($ch);
                    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);

                    if ($response === false) {
                        $lastErrMsg = 'Koneksi cURL gagal: ' . $curlError;
                        continue;
                    }

                    $resData = json_decode($response, true);
                    if ($httpCode >= 400 || isset($resData['error'])) {
                        $lastErrMsg = $resData['error']['message'] ?? ('HTTP ' . $httpCode);
                        continue;
                    }

                    $choiceMsg  = $resData['choices'][0]['message'] ?? [];
                    $rawContent = $choiceMsg['content'] ?? '';

                    // Strip any internal <think>...</think> tags if present
                    if (!empty($rawContent)) {
                        $rawContent = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $rawContent);
                        $rawContent = trim($rawContent);
                    }

                    // Only use reasoning if it contains an actual [SQL_QUERY] tag
                    if (empty($rawContent) && !empty($choiceMsg['reasoning'])) {
                        if (stripos($choiceMsg['reasoning'], '[SQL_QUERY]') !== false) {
                            $rawContent = trim($choiceMsg['reasoning']);
                        }
                    }

                    // Filter out raw English internal thinking logs (e.g. "We need to query...", "I think...", "The user is asking...")
                    if (preg_match('/^(?:I think|The user|Let\'s|First,|Looking at|To answer|The rule|We need|We should|We must|Query the|Step \d|Here is|Based on)/i', $rawContent)) {
                        // If it doesn't contain [SQL_QUERY], ignore this raw English thinking log
                        if (stripos($rawContent, '[SQL_QUERY]') === false) {
                            $rawContent = '';
                        }
                    }

                    if (!empty($rawContent)) {
                        $totalTokens += $resData['usage']['total_tokens'] ?? 0;
                        $requestSuccess = true;
                        break; // Success!
                    }
                }

                if (!$requestSuccess) {
                    echo json_encode([
                        'success' => false,
                        'error'   => 'AI Error: ' . ($lastErrMsg ?: 'Model AI tidak memberikan respon text. Coba ganti model di Pengaturan AI Chat.')
                    ]);
                    exit;
                }

                $aiResponse = $rawContent;

                // Check for SQL Query in response
                if (preg_match('/\[SQL_QUERY\](.*?)\[\/SQL_QUERY\]/is', $aiResponse, $matches)) {
                    $sqlQuery = trim($matches[1]);

                    // Security: Only SELECT allowed
                    if (stripos(ltrim($sqlQuery), 'SELECT') !== 0 ||
                        preg_match('/\b(?:INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|REPLACE|GRANT|REVOKE|CREATE)\b/i', $sqlQuery)) {
                        $sqlResult = "ERROR: Hanya query SELECT yang diizinkan.";
                    } else {
                        if (stripos($sqlQuery, 'LIMIT') === false) {
                            $sqlQuery .= " LIMIT 50";
                        }

                        try {
                            if (!Database::getInstance()->ping()) {
                                Database::getInstance()->reconnect();
                            }
                            $db = Database::getInstance()->getConnection();
                            $stmt = $db->query($sqlQuery);
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $sqlResult = json_encode($results, JSON_UNESCAPED_UNICODE);
                        } catch (Throwable $e) {
                            $sqlResult = "ERROR SQL: " . $e->getMessage();
                        }
                    }

                    // Append results to conversation
                    $messages[] = ['role' => 'assistant', 'content' => $aiResponse];

                    if (strpos($sqlResult, 'ERROR') !== false) {
                        $messages[] = ['role' => 'user', 'content' => "[SQL_ERROR]\n{$sqlResult}\n[/SQL_ERROR]\nPerbaiki query SQL-mu dan coba lagi."];
                    } elseif ($sqlResult === '[]') {
                        $messages[] = ['role' => 'user', 'content' => "[SQL_RESULT]\n[]\n[/SQL_RESULT]\nData spesifik tidak ditemukan pada query ini. DILARANG memberikan jawaban mati seperti 'data tidak ditemukan' saja!\n- Jika ini pass 1 atau 2: Lakukan pencarian [SQL_QUERY] yang lebih luas (broad search) menggunakan nama brand/merek utama atau variasi kata kunci lain.\n- Jika ini pass terakhir: Tampilkan varian/produk serupa yang ada di database toko dan pandu pengguna dengan pertanyaan klarifikasi/rekomendasi selanjutnya."];
                    } else {
                        $messages[] = ['role' => 'user', 'content' => "[SQL_RESULT]\n{$sqlResult}\n[/SQL_RESULT]\nJawab pertanyaan berdasarkan data di atas. Jangan tampilkan query SQL ke user."];

                        // AUTO-LEARN: Save successful SQL result as a learned fact
                        $this->autoLearnFromResult($message, $sqlResult, $contextBuilder);
                    }

                    $currentPass++;
                } else {
                    // No SQL query needed, break loop
                    break;
                }
            }

            // Clean up leftover SQL tags from response
            if (preg_match('/\[SQL_QUERY\]/is', $aiResponse)) {
                $aiResponse = preg_replace('/\[SQL_QUERY\].*?\[\/SQL_QUERY\]/is', '', $aiResponse);
                $aiResponse = trim($aiResponse);
                if (empty($aiResponse)) {
                    $aiResponse = "Maaf, saya tidak berhasil menemukan data yang diminta dalam database.";
                }
            }

            // Save AI response
            if (!empty($aiResponse)) {
                try {
                    if (!Database::getInstance()->ping()) {
                        Database::getInstance()->reconnect();
                        $this->aiChatModel = new AiChatModel();
                    }
                    $this->aiChatModel->saveMessage((int)$user['id'], $sessionId, 'assistant', $aiResponse, $totalTokens);
                } catch (Throwable $dbErr) {}
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

        if (empty($topic)) {
            $topic = mb_substr($correction, 0, 80);
        }

        $content = $correction;
        if (!empty($context)) {
            $content = "Konteks pertanyaan: \"{$context}\"\nFakta yang benar: {$correction}";
        }

        try {
            $saved = $this->aiChatModel->saveKnowledge($topic, $content, 'user_feedback');

            // Also save as a learned fact (permanent, no expiry)
            $this->aiChatModel->saveFact(
                mb_substr($topic, 0, 255),
                $correction,
                'tutorial',  // 'tutorial' category = never expires
                'user_correction'
            );

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

        $history = $this->aiChatModel->getHistory($userId, $sessionId, 6);
        $lastAiMsg = '';
        foreach (array_reverse($history) as $h) {
            if ($h['role'] === 'assistant') {
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

    // ============================================================
    // INTERNAL: AUTO-LEARN FROM SQL RESULTS
    // ============================================================

    /**
     * When AI successfully queries and gets data, save key info as a learned fact.
     * Next time a similar question is asked, AI can answer from cached facts.
     */
    private function autoLearnFromResult(string $userMessage, string $sqlResult, AiContextBuilder $contextBuilder): void
    {
        try {
            $category = $contextBuilder->categorizeQuestion($userMessage);
            $factKey  = mb_substr($userMessage, 0, 200);

            // Truncate large results to save space
            $factValue = mb_substr($sqlResult, 0, 500);

            $this->aiChatModel->saveFact($factKey, $factValue, $category, 'sql_result');
        } catch (Throwable $e) {
            // Silently fail — learning is non-critical
        }
    }

    // ============================================================
    // INTERNAL: GET API KEY (Settings UI > .env > fail)
    // ============================================================

    private function getApiKey(): string
    {
        // Priority 1: Settings UI (app_settings table)
        $apiKey = $this->settingModel->get('ai_chat_api_key');
        if (!empty($apiKey)) {
            return $apiKey;
        }

        // Priority 2: Legacy key name
        $apiKey = $this->settingModel->get('ai_api_key');
        if (!empty($apiKey)) {
            return $apiKey;
        }

        // Priority 3: .env file
        if (defined('AI_CHAT_API_KEY') && !empty(AI_CHAT_API_KEY)) {
            return AI_CHAT_API_KEY;
        }

        return '';
    }
}
