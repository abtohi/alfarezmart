/**
 * Chat Logic for AlfarezMart AI v5.0
 * - Smart session management
 * - Auto-learn feedback loop
 * - Token-efficient history
 */

let chatSessionId = localStorage.getItem('alfarezmart_chat_session');
if (!chatSessionId) {
    chatSessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    localStorage.setItem('alfarezmart_chat_session', chatSessionId);
}

const chatMessages = document.getElementById('chatMessages');
const chatInput    = document.getElementById('chatInput');
const btnSend      = document.getElementById('btnSend');
const chatCsrf     = document.getElementById('chatCsrf') ? document.getElementById('chatCsrf').value : '';

// State for feedback modal
let _feedbackContext = '';

document.addEventListener('DOMContentLoaded', () => {
    // Auto resize textarea
    if (chatInput) {
        chatInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight < 120 ? this.scrollHeight : 120) + 'px';
            btnSend.disabled = this.value.trim().length === 0;
        });

        // Enter to send, Shift+Enter for new line
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length > 0) {
                    document.getElementById('chatForm').dispatchEvent(
                        new Event('submit', { cancelable: true, bubbles: true })
                    );
                }
            }
        });
    }

    loadChatHistory();
});

// ============================================================
// BUBBLE GENERATION
// ============================================================

function generateBubbleHTML(role, content) {
    const isUser = role === 'user';
    let htmlContent = content;

    if (!isUser) {
        // Strip any residual internal thinking tags
        content = content.replace(/<think>[\s\S]*?<\/think>/gi, '').trim();
        if (typeof marked !== 'undefined') {
            htmlContent = marked.parse(content);
        } else {
            htmlContent = content.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        }
    } else {
        htmlContent = escapeHtml(content).replace(/\n/g, '<br>');
    }

    const copyBtn = `
        <button class="btn-chat-action" onclick="copyMessageText(this)" data-content="${escapeAttr(content)}" title="Salin pesan">
            📋 Salin
        </button>`;

    const feedbackBtn = (!isUser) ? `
        <button class="btn-chat-action" onclick="openFeedbackModal(this)" data-content="${escapeAttr(content)}" title="Beri koreksi / feedback">
            ✏️ Koreksi
        </button>` : '';

    return `
        <div class="message ${isUser ? 'user' : 'ai'}">
            <div class="message-bubble">${htmlContent}</div>
            <div class="message-actions">
                ${copyBtn}
                ${feedbackBtn}
            </div>
        </div>`;
}

function copyMessageText(btn) {
    const text = btn.getAttribute('data-content') || '';
    if (!text) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => showCopiedFeedback(btn)).catch(() => fallbackCopyText(text, btn));
    } else {
        fallbackCopyText(text, btn);
    }
}

function fallbackCopyText(text, btn) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showCopiedFeedback(btn);
    } catch (e) {
        alert('Gagal menyalin teks');
    }
    document.body.removeChild(textarea);
}

function showCopiedFeedback(btn) {
    const originalText = btn.innerHTML;
    btn.innerHTML = '✓ Tersalin!';
    btn.classList.add('copied');
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('copied');
    }, 2000);
}

function appendMessage(role, content) {
    if (!chatMessages) return;

    const indicator = document.getElementById('typingIndicator');
    if (indicator) indicator.remove();

    chatMessages.insertAdjacentHTML('beforeend', generateBubbleHTML(role, content));
    scrollToBottom();
}

function showTypingIndicator() {
    if (!chatMessages) return;
    chatMessages.insertAdjacentHTML('beforeend', `
        <div class="message ai" id="typingIndicator">
            <div class="message-bubble" style="padding:12px 16px;">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>`);
    scrollToBottom();
}

function scrollToBottom() {
    if (chatMessages) {
        requestAnimationFrame(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeAttr(str) {
    return str.replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/\n/g, ' ');
}

function sendSuggested(text) {
    if (chatInput) {
        chatInput.value = text;
        chatInput.style.height = 'auto';
        btnSend.disabled = false;
        document.getElementById('chatForm').dispatchEvent(
            new Event('submit', { cancelable: true, bubbles: true })
        );
    }
}

// ============================================================
// SEND MESSAGE
// ============================================================

async function handleChatSubmit(e) {
    e.preventDefault();
    if (!chatInput || !chatInput.value.trim()) return;

    const message = chatInput.value.trim();
    chatInput.value = '';
    chatInput.style.height = 'auto';
    btnSend.disabled = true;

    appendMessage('user', message);

    // Hide suggestions after first interaction
    const suggestions = document.getElementById('chatSuggestions');
    if (suggestions) suggestions.style.display = 'none';

    showTypingIndicator();

    try {
        const response = await fetch(`${BASE_URL}api/chat`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': chatCsrf },
            body: JSON.stringify({ message, session_id: chatSessionId })
        });

        const data = await response.json();

        if (data.success && data.data && typeof data.data.response === 'string' && data.data.response.trim().length > 0) {
            appendMessage('assistant', data.data.response);
        } else {
            const ind = document.getElementById('typingIndicator');
            if (ind) ind.remove();
            const errMsg = data.error || (data.data && data.data.response === '' ? 'Model AI tidak memberikan respon text. Silakan coba lagi.' : 'Terjadi kesalahan sistem.');
            appendMessage('assistant', `⚠️ ${errMsg}`);
        }
    } catch (err) {
        console.error('Chat error:', err);
        const ind = document.getElementById('typingIndicator');
        if (ind) ind.remove();
        appendMessage('assistant', navigator.onLine
            ? `⚠️ Gagal terhubung ke server (${err.message || 'Network Error'}). Silakan coba lagi.`
            : '📶 Anda sedang offline. Fitur AI Chat membutuhkan koneksi internet.');
    }
}

// ============================================================
// LOAD HISTORY
// ============================================================

async function loadChatHistory() {
    try {
        const response = await fetch(`${BASE_URL}api/chat/history?session_id=${chatSessionId}`);
        const data     = await response.json();

        if (data.success && data.data && data.data.length > 0) {
            const suggestions = document.getElementById('chatSuggestions');
            if (suggestions) suggestions.style.display = 'none';

            chatMessages.innerHTML = '';
            data.data.forEach(msg => {
                chatMessages.insertAdjacentHTML('beforeend', generateBubbleHTML(msg.role, msg.content));
            });
            scrollToBottom();
        }
    } catch (err) {
        console.error('Failed to load chat history:', err);
    }
}

// ============================================================
// CLEAR HISTORY
// ============================================================

async function clearChatHistory() {
    if (!confirm('Yakin ingin menghapus riwayat chat ini?')) return;

    try {
        const response = await fetch(`${BASE_URL}api/chat/clear`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': chatCsrf },
            body: JSON.stringify({ session_id: chatSessionId })
        });
        const data = await response.json();
        if (data.success) {
            chatSessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
            localStorage.setItem('alfarezmart_chat_session', chatSessionId);
            window.location.reload();
        }
    } catch (err) {
        alert('Gagal membersihkan histori');
    }
}

// ============================================================
// START NEW CHAT SESSION
// ============================================================

function startNewChat() {
    chatSessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    localStorage.setItem('alfarezmart_chat_session', chatSessionId);
    window.location.reload();
}

// ============================================================
// FEEDBACK / KOREKSI
// ============================================================

function openFeedbackModal(btn) {
    const aiContent = btn.getAttribute('data-content') || '';

    // Save AI context for feedback submission
    _feedbackContext = aiContent.substring(0, 200);

    // Show context in modal
    const ctxEl = document.getElementById('feedbackContext');
    if (ctxEl && _feedbackContext) {
        ctxEl.style.display = 'block';
        ctxEl.textContent   = '💬 Pesan AI: ' + _feedbackContext.substring(0, 100) + '...';
    }

    document.getElementById('feedbackText').value = '';
    const modal = document.getElementById('feedbackModal');
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('feedbackText').focus(), 100);
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
    _feedbackContext = '';
    const ctxEl = document.getElementById('feedbackContext');
    if (ctxEl) ctxEl.style.display = 'none';
}

async function submitFeedback() {
    const correction = document.getElementById('feedbackText').value.trim();
    if (!correction) {
        alert('Harap isi koreksi terlebih dahulu.');
        return;
    }

    const submitBtn = document.querySelector('#feedbackModal button:last-child');
    submitBtn.disabled    = true;
    submitBtn.textContent = '⏳ Menyimpan...';

    try {
        const response = await fetch(`${BASE_URL}api/chat/feedback`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': chatCsrf },
            body: JSON.stringify({
                topic:      _feedbackContext.substring(0, 80) || 'Koreksi pengguna',
                correction,
                context:    _feedbackContext,
                session_id: chatSessionId
            })
        });

        const data = await response.json();
        closeFeedbackModal();

        if (data.success) {
            // Inline notification in chat
            const note = document.createElement('div');
            note.style.cssText = 'text-align:center;font-size:11px;color:var(--success);padding:6px 0;';
            note.textContent   = '✅ Koreksi tersimpan! AI akan belajar dari masukan Anda.';
            chatMessages.appendChild(note);
            scrollToBottom();
            setTimeout(() => note.remove(), 5000);
        } else {
            alert('Gagal menyimpan koreksi: ' + (data.error || 'Error tidak diketahui'));
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan saat menyimpan feedback.');
    } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = '💾 Simpan Koreksi';
    }
}

// Close modal on backdrop click
document.getElementById('feedbackModal')?.addEventListener('click', function (e) {
    if (e.target === this) closeFeedbackModal();
});
