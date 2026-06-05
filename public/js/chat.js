/**
 * Chat Logic for AlfarezMart AI
 */

let chatSessionId = localStorage.getItem('alfarezmart_chat_session');
if (!chatSessionId) {
    chatSessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    localStorage.setItem('alfarezmart_chat_session', chatSessionId);
}

const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const btnSend = document.getElementById('btnSend');
const chatCsrf = document.getElementById('chatCsrf') ? document.getElementById('chatCsrf').value : '';

document.addEventListener('DOMContentLoaded', () => {
    // Auto resize textarea
    if (chatInput) {
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight < 120 ? this.scrollHeight : 120) + 'px';
            
            // Toggle button state
            btnSend.disabled = this.value.trim().length === 0;
        });
        
        // Handle Enter to send (Shift+Enter for new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length > 0) {
                    document.getElementById('chatForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            }
        });
    }

    // Load history
    loadChatHistory();
});

function generateBubbleHTML(role, content) {
    const isUser = role === 'user';
    
    let htmlContent = content;
    if (!isUser) {
        // Parse markdown for AI response
        if (typeof marked !== 'undefined') {
            htmlContent = marked.parse(content);
        } else {
            // Fallback simple parsing
            htmlContent = content.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        }
    } else {
        htmlContent = escapeHtml(content).replace(/\n/g, '<br>');
    }

    return `
        <div class="message ${isUser ? 'user' : 'ai'}">
            <div class="message-bubble">
                ${htmlContent}
            </div>
        </div>
    `;
}

function appendMessage(role, content) {
    if (!chatMessages) return;
    
    // Remove typing indicator if exists
    const indicator = document.getElementById('typingIndicator');
    if (indicator) indicator.remove();

    chatMessages.insertAdjacentHTML('beforeend', generateBubbleHTML(role, content));
    scrollToBottom();
}

function showTypingIndicator() {
    if (!chatMessages) return;
    const html = `
        <div class="message ai" id="typingIndicator">
            <div class="message-bubble" style="padding: 12px 16px;">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

function scrollToBottom() {
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

function sendSuggested(text) {
    if (chatInput) {
        chatInput.value = text;
        chatInput.style.height = 'auto';
        btnSend.disabled = false;
        document.getElementById('chatForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }
}

async function handleChatSubmit(e) {
    e.preventDefault();
    if (!chatInput || !chatInput.value.trim()) return;

    const message = chatInput.value.trim();
    
    // Clear input
    chatInput.value = '';
    chatInput.style.height = 'auto';
    btnSend.disabled = true;

    // Append user message instantly
    appendMessage('user', message);

    // Hide suggestions after first interaction
    const suggestions = document.getElementById('chatSuggestions');
    if (suggestions) suggestions.style.display = 'none';

    // Show typing
    showTypingIndicator();

    try {
        const response = await fetch(`${BASE_URL}api/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': chatCsrf
            },
            body: JSON.stringify({
                message: message,
                session_id: chatSessionId
            })
        });

        const data = await response.json();
        
        if (data.success && data.data && data.data.response) {
            appendMessage('assistant', data.data.response);
        } else {
            const errorIndicator = document.getElementById('typingIndicator');
            if (errorIndicator) errorIndicator.remove();
            
            appendMessage('assistant', `⚠️ Maaf, terjadi kesalahan: ${data.error || 'Gagal terhubung ke AI'}`);
        }
    } catch (err) {
        console.error('Chat error:', err);
        const errorIndicator = document.getElementById('typingIndicator');
        if (errorIndicator) errorIndicator.remove();
        
        if (!navigator.onLine) {
            appendMessage('assistant', '📶 Anda sedang offline. Fitur AI Chat membutuhkan koneksi internet.');
        } else {
            appendMessage('assistant', '⚠️ Gagal terhubung ke server. Silakan coba lagi nanti.');
        }
    }
}

async function loadChatHistory() {
    try {
        const response = await fetch(`${BASE_URL}api/chat/history?session_id=${chatSessionId}`);
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            // Hide suggestions if history exists
            const suggestions = document.getElementById('chatSuggestions');
            if (suggestions) suggestions.style.display = 'none';
            
            // Clear default greeting
            chatMessages.innerHTML = '';
            
            // Append history
            data.data.forEach(msg => {
                chatMessages.insertAdjacentHTML('beforeend', generateBubbleHTML(msg.role, msg.content));
            });
            scrollToBottom();
        }
    } catch (err) {
        console.error('Failed to load chat history:', err);
    }
}

async function clearChatHistory() {
    if (!confirm('Yakin ingin menghapus riwayat chat ini?')) return;
    
    try {
        const response = await fetch(`${BASE_URL}api/chat/clear`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': chatCsrf
            },
            body: JSON.stringify({ session_id: chatSessionId })
        });
        
        const data = await response.json();
        if (data.success) {
            // Generate new session ID
            chatSessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
            localStorage.setItem('alfarezmart_chat_session', chatSessionId);
            
            // Reload page
            window.location.reload();
        }
    } catch (err) {
        alert('Gagal membersihkan histori');
    }
}
