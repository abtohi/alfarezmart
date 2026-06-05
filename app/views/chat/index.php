<!-- AI Chat Interface -->
<div class="chat-container">
    <div class="chat-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;background:var(--primary-bg);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;">
                🤖
            </div>
            <div>
                <h2 style="font-size:var(--font-size-md);font-weight:700;margin:0;">AlfarezMart AI</h2>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;align-items:center;gap:4px;">
                    <span style="width:6px;height:6px;background:var(--success);border-radius:50%;display:inline-block;"></span> Online
                </div>
            </div>
        </div>
        <button onclick="clearChatHistory()" class="btn-icon" title="Bersihkan Riwayat">
            <i class="bi bi-trash3"></i>
        </button>
    </div>

    <!-- Suggested Prompts -->
    <div class="chat-suggestions" id="chatSuggestions">
        <div class="suggestion-chip" onclick="sendSuggested('Berapa omzet dan pengeluaran hari ini?')">
            📊 Rekap Keuangan Hari Ini
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Tampilkan 5 produk yang stoknya hampir habis')">
            📦 Cek Stok Menipis
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Apa saja 5 produk paling laku bulan ini?')">
            🏆 Top Produk Laris
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Kapan jadwal kunjungan sales terdekat?')">
            👤 Jadwal Kunjungan Sales
        </div>
        <div class="suggestion-chip" onclick="sendSuggested('Produk apa yang tidak laku (belum restock dalam 30 hari)?')">
            ⚠️ Produk Kurang Laku
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages" id="chatMessages">
        <!-- Initial greeting -->
        <div class="message ai">
            <div class="message-bubble">
                Halo! Saya asisten AI pintar AlfarezMart 👋<br>
                Saya dapat membantu Anda mengecek stok, menganalisis keuangan, melihat laporan produk, atau menjawab pertanyaan lain seputar toko Anda. Ada yang bisa saya bantu?
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="chat-input-area">
        <form id="chatForm" onsubmit="handleChatSubmit(event)">
            <input type="hidden" id="chatCsrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <textarea id="chatInput" rows="1" placeholder="Tanya sesuatu tentang toko..." required></textarea>
                <button type="submit" class="btn-send" id="btnSend">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
        <div style="font-size:10px;text-align:center;color:var(--text-muted);margin-top:8px;">
            AI dapat melakukan kesalahan. Harap periksa kembali informasi penting.
        </div>
    </div>
</div>

<style>
/* Chat Layout */
body {
    background: var(--bg-primary); /* Dark theme base */
}
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
.btn-icon:hover {
    color: var(--danger);
    background: rgba(230, 57, 70, 0.1);
}

.chat-suggestions {
    display: flex;
    overflow-x: auto;
    gap: 8px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-primary);
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.chat-suggestions::-webkit-scrollbar {
    display: none;
}

.suggestion-chip {
    white-space: nowrap;
    padding: 6px 12px;
    border-radius: 16px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
}
.suggestion-chip:hover {
    background: var(--primary-bg);
    color: var(--primary);
    border-color: var(--primary);
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

.message.user {
    align-self: flex-end;
}

.message.ai {
    align-self: flex-start;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 16px;
    font-size: var(--font-size-sm);
    line-height: 1.5;
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

/* Markdown Styles inside AI bubble */
.message.ai .message-bubble p { margin-bottom: 8px; }
.message.ai .message-bubble p:last-child { margin-bottom: 0; }
.message.ai .message-bubble strong { color: #fff; font-weight: 700; }
.message.ai .message-bubble ul, .message.ai .message-bubble ol { padding-left: 20px; margin-bottom: 8px; }
.message.ai .message-bubble li { margin-bottom: 4px; }
.message.ai .message-bubble table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 12px; }
.message.ai .message-bubble th, .message.ai .message-bubble td { padding: 6px; border: 1px solid var(--border-color); }
.message.ai .message-bubble th { background: var(--surface-2); text-align: left; }

.chat-input-area {
    padding: 12px 16px;
    background: var(--surface-1);
    border-top: 1px solid var(--border-color);
    position: sticky;
    bottom: 0;
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
#chatInput:focus {
    outline: none;
    border-color: var(--primary);
}

.btn-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: transform 0.2s, background 0.2s;
    flex-shrink: 0;
}
.btn-send:hover {
    transform: scale(1.05);
    background: #c82333;
}
.btn-send:disabled {
    background: var(--surface-2);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
}

/* Typing Indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
}
.typing-dot {
    width: 6px;
    height: 6px;
    background: var(--text-muted);
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out both;
}
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
.typing-dot:nth-child(3) { animation-delay: 0s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Fix main content padding for chat page */
.app-content { padding-top: 0 !important; padding-bottom: 0 !important; }
#appHeader { display: none; } /* Hide default header since we have custom chat header */
</style>

<!-- Marked.js for Markdown parsing -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="<?= BASE_URL ?>public/js/chat.js?v=1.0"></script>
