/**
 * AlfarezMart PWA - Utility Functions
 */

// Format currency
function formatRupiah(num, prefix = 'Rp') {
    if (!num && num !== 0) return prefix + '0';
    return prefix + Math.round(num).toLocaleString('id-ID');
}

// Format date
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Show toast notification
function showToast(message, type = 'info', duration = 3000, onClick = null) {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `app-toast ${type}`;
    toast.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i><span>${message}</span>`;
    if (onClick) {
        toast.style.cursor = 'pointer';
        toast.addEventListener('click', onClick);
    }
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(-20px)'; setTimeout(() => toast.remove(), 300); }, duration);
}

// Debounce function
function debounce(func, wait = 300) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// API call helper
async function api(endpoint, methodOrOptions = {}, data = null) {
    let options = {};
    if (typeof methodOrOptions === 'string') {
        options.method = methodOrOptions;
        if (data) options.body = JSON.stringify(data);
    } else {
        options = methodOrOptions || {};
    }
    
    const defaults = { headers: { 'Content-Type': 'application/json' } };
    const config = { ...defaults, ...options };
    
    // Add CSRF token header if exists
    const csrfToken = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';
    if (csrfToken && (!config.headers['X-CSRF-Token'])) {
        config.headers['X-CSRF-Token'] = csrfToken;
    }

    try {
        const response = await fetch(endpoint, config);
        
        // Read response as text first to avoid json parse crash on empty/invalid body
        const text = await response.text();
        
        if (!text || text.trim().length === 0) {
            throw new Error('Server mengembalikan respons kosong. Kemungkinan timeout atau error internal.');
        }
        
        let jsonData;
        try {
            jsonData = JSON.parse(text);
        } catch (parseErr) {
            console.error('Response bukan JSON valid:', text.substring(0, 500));
            // Check if it looks like an HTML error page
            if (text.includes('<br') || text.includes('<html') || text.includes('Fatal error')) {
                throw new Error('Server error (kemungkinan timeout atau kehabisan memori). Coba lagi dengan gambar yang lebih kecil.');
            }
            throw new Error('Respons server tidak valid (bukan JSON)');
        }
        
        if (!response.ok) throw new Error(jsonData.error || 'Request failed');
        return jsonData;
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

// Truncate text
function truncate(text, len = 35) {
    if (!text) return '';
    return text.length > len ? text.substring(0, len) + '...' : text;
}

// Calculate margin
function calcMargin(buy, sell) {
    if (!buy || buy <= 0 || !sell || sell <= 0) return 0;
    return ((sell - buy) / sell * 100).toFixed(1);
}
