<!-- AI Chat Interface v5.0 -->
<div class="chat-container" id="chatContainer">
    <div class="chat-header">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="chat-avatar">
                <i class="bi bi-stars"></i>
            </div>
            <div>
                <h2 style="font-size:var(--font-size-md);font-weight:700;margin:0;">AlfarezMart AI</h2>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                    <span class="status-dot"></span>
                    <span>Online · Belajar dari setiap percakapan</span>
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <button onclick="startNewChat()" class="btn-icon" title="Sesi Baru">
                <i class="bi bi-plus-circle"></i>
            </button>
            <button onclick="clearChatHistory()" class="btn-icon btn-icon-danger" title="Hapus Riwayat">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    </div>

    <!-- Suggested Prompts -->
    <div class="chat-suggestions" id="chatSuggestions">
        <div class="suggestion-chip" onclick="sendSuggested('Tampilkan list 10 produk pertama dalam tabel dengan kolom Kode, Nama, Harga Eceran, Harga Grosir, dan Stok')">
            📋 List Produk (Tabel Mobile)
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Tampilkan produk di toko yang memiliki foto lengkap dengan gambar, harga eceran, dan stoknya')">
            📷 Tampilkan Foto Produk
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Berapa omzet dan profit hari ini?')">
            📊 Rekap Keuangan Hari Ini
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Tampilkan 5 produk yang stoknya hampir habis')">
            📦 Cek Stok Menipis
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Berapa total hutang toko dan piutang pelanggan saat ini?')">
            💳 Rekap Hutang & Piutang
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages" id="chatMessages">
        <!-- Initial greeting -->
        <div class="message ai">
            <div class="message-bubble">
                Halo! Saya <strong>AlfarezMart AI</strong> 👋<br>
                Saya memiliki akses langsung ke seluruh data toko: <strong>produk, harga, stok, keuangan, hutang, supplier, penjualan,</strong> dan lainnya.<br><br>
                Tanya apa saja — saya akan mencari jawabannya langsung dari database! 🔍<br>
                <em style="font-size:11px;opacity:0.7;">💡 Klik ✏️ pada jawaban saya jika kurang tepat — saya akan belajar dari koreksi Anda.</em>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="chat-input-area">
        <form id="chatForm" onsubmit="handleChatSubmit(event)">
            <input type="hidden" id="chatCsrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="chat-input-wrapper">
                <textarea id="chatInput" rows="1" placeholder="Tanya seputar data toko, harga, stok, keuangan..." required></textarea>
                <button type="submit" class="btn-send" id="btnSend" disabled>
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
        <div class="chat-input-footer">
            AI menggunakan data internal toko. Klik ✏️ untuk koreksi · Powered by OpenRouter
        </div>
    </div>
</div>

<!-- Modal Koreksi / Feedback -->
<div id="feedbackModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--surface-1);border-radius:16px;padding:24px;max-width:480px;width:90%;border:1px solid var(--border-color);">
        <h3 style="margin:0 0 8px;font-size:var(--font-size-md);display:flex;align-items:center;gap:8px;">
            ✏️ Beri Koreksi ke AI
        </h3>
        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin:0 0 16px;">
            Koreksi Anda akan disimpan sebagai pengetahuan permanen — AI akan lebih pintar untuk pertanyaan serupa.
        </p>
        <div id="feedbackContext" style="background:var(--surface-2);border-radius:8px;padding:10px;margin-bottom:12px;font-size:var(--font-size-xs);color:var(--text-secondary);display:none;"></div>
        <textarea id="feedbackText" rows="4" placeholder="Tulis koreksi atau informasi yang benar di sini..."
            style="width:100%;background:var(--bg-primary);border:1px solid var(--border-color);border-radius:10px;
                   padding:10px 14px;color:var(--text-primary);font-size:var(--font-size-sm);resize:none;
                   font-family:var(--font-family);box-sizing:border-box;"></textarea>
        <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end;">
            <button onclick="closeFeedbackModal()" style="padding:8px 16px;border-radius:8px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-secondary);cursor:pointer;font-size:var(--font-size-sm);">
                Batal
            </button>
            <button onclick="submitFeedback()" style="padding:8px 20px;border-radius:8px;background:var(--primary);border:none;color:white;cursor:pointer;font-size:var(--font-size-sm);font-weight:600;">
                💾 Simpan Koreksi
            </button>
        </div>
    </div>
</div>

<style>
/* Chat Layout */
body { background: var(--bg-primary); }
.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - var(--header-height) - var(--bottom-nav-height));
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}

.chat-header {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--surface-1);
    z-index: 10;
}

.chat-avatar {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--primary), #ff6b6b);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.status-dot {
    width: 7px;
    height: 7px;
    background: var(--success);
    border-radius: 50%;
    display: inline-block;
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.btn-icon {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s;
}
.btn-icon:hover { color: var(--primary); background: var(--primary-bg); }
.btn-icon-danger:hover { color: var(--danger); background: rgba(230,57,70,0.1); }

.chat-suggestions {
    display: flex;
    overflow-x: auto;
    gap: 8px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-primary);
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.chat-suggestions::-webkit-scrollbar { display: none; }

.suggestion-chip {
    white-space: nowrap;
    padding: 7px 14px;
    border-radius: 20px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}
.suggestion-chip:hover {
    background: var(--primary-bg);
    color: var(--primary);
    border-color: var(--primary);
    transform: translateY(-1px);
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    scroll-behavior: smooth;
}

.message {
    display: flex;
    flex-direction: column;
    max-width: 85%;
    animation: fadeIn 0.3s ease-out forwards;
}
.message.user  { align-self: flex-end; }
.message.ai    { align-self: flex-start; }

.message-bubble {
    padding: 12px 16px;
    border-radius: 16px;
    font-size: var(--font-size-sm);
    line-height: 1.6;
    word-break: break-word;
}
.message.user .message-bubble {
    background: var(--primary);
    color: white;
    border-bottom-right-radius: 4px;
}
.message.ai .message-bubble {
    background: var(--surface-1);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-bottom-left-radius: 4px;
}

/* Markdown & Table Responsiveness in AI bubbles */
.message.ai.has-table {
    max-width: 100%;
    width: 100%;
}
.message.ai.has-table .message-bubble {
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
}

.chat-table-wrapper {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 10px 0;
    border-radius: var(--radius-sm, 8px);
    border: 1px solid var(--border-color);
    background: var(--surface-1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.chat-table-wrapper table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin: 0;
}

.chat-table-wrapper th, .chat-table-wrapper td {
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-color);
    border-right: 1px solid var(--border-color);
    white-space: nowrap;
    vertical-align: middle;
}

.chat-table-wrapper th:last-child, .chat-table-wrapper td:last-child {
    border-right: none;
}

.chat-table-wrapper tr:last-child td {
    border-bottom: none;
}

.chat-table-wrapper th {
    background: var(--surface-2);
    color: var(--text-primary);
    font-weight: 700;
    text-align: left;
}

.chat-table-wrapper tr:nth-child(even) td {
    background: rgba(255, 255, 255, 0.02);
}

/* Chat Product Images */
.chat-product-img {
    max-width: 160px;
    max-height: 160px;
    object-fit: contain;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    background: var(--surface-2);
    margin: 6px 0;
    cursor: zoom-in;
    display: inline-block;
    vertical-align: middle;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.chat-product-img:hover {
    transform: scale(1.04);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
}

.chat-table-wrapper .chat-product-img {
    width: 44px;
    height: 44px;
    max-width: 44px;
    max-height: 44px;
    margin: 0 auto;
    border-radius: 6px;
    display: block;
}

.message.ai .message-bubble p { margin-bottom: 8px; }
.message.ai .message-bubble p:last-child { margin-bottom: 0; }
.message.ai .message-bubble strong { color: var(--text-primary); font-weight: 700; }
.message.ai .message-bubble ul, .message.ai .message-bubble ol { padding-left: 20px; margin-bottom: 8px; }
.message.ai .message-bubble li { margin-bottom: 4px; }
.message.ai .message-bubble code {
    background: var(--surface-2);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.88em;
}

/* Actions below bubbles (Copy & Feedback) */
.message-actions {
    display: flex;
    gap: 6px;
    margin-top: 4px;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.message.user .message-actions {
    justify-content: flex-end;
}
.message:hover .message-actions {
    opacity: 1;
}

.btn-chat-action {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 3px 10px;
    font-size: 11px;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-family);
}
.btn-chat-action:hover {
    background: var(--primary-bg);
    color: var(--primary);
    border-color: var(--primary);
}
.btn-chat-action.copied {
    background: rgba(46, 196, 182, 0.15);
    color: var(--success);
    border-color: var(--success);
    font-weight: 600;
}

.chat-input-area {
    padding: 12px 16px;
    background: var(--surface-1);
    border-top: 1px solid var(--border-color);
    position: sticky;
    bottom: 0;
}

.chat-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

#chatInput {
    flex: 1;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 10px 16px;
    color: var(--text-primary);
    font-size: var(--font-size-sm);
    resize: none;
    max-height: 120px;
    font-family: var(--font-family);
    line-height: 1.4;
}
#chatInput:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(230,57,70,0.1); }

.btn-send {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--primary);
    color: white; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; cursor: pointer;
    transition: transform 0.2s, background 0.2s;
    flex-shrink: 0;
}
.btn-send:hover { transform: scale(1.05); background: #c82333; }
.btn-send:disabled { background: var(--surface-2); color: var(--text-muted); cursor: not-allowed; transform: none; }

.chat-input-footer {
    font-size: 10px;
    text-align: center;
    color: var(--text-muted);
    margin-top: 8px;
    opacity: 0.7;
}

/* Typing Indicator */
.typing-indicator { display:flex;align-items:center;gap:4px;padding:8px 12px; }
.typing-dot { width:6px;height:6px;background:var(--text-muted);border-radius:50%;animation:typing 1.4s infinite ease-in-out both; }
.typing-dot:nth-child(1){animation-delay:-0.32s;}
.typing-dot:nth-child(2){animation-delay:-0.16s;}
.typing-dot:nth-child(3){animation-delay:0s;}

@keyframes typing { 0%,80%,100%{transform:scale(0)} 40%{transform:scale(1)} }
@keyframes fadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* Fix main content padding */
.app-content { padding-top: 0 !important; padding-bottom: 0 !important; }
#appHeader { display: none; }
</style>

<!-- Marked.js for Markdown -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="<?= BASE_URL ?>public/js/chat.js?v=5.0"></script>
