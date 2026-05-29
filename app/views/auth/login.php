<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="AlfarezMart - Login">
    <meta name="theme-color" content="#1a1a2e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>Login - AlfarezMart</title>
    
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>public/images/mobile_icon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>public/images/mobile_icon.png">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/variables.css">
    
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: var(--font-family);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
        }

        /* Animated background particles */
        .bg-particles {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .bg-particles::before,
        .bg-particles::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        .bg-particles::before {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(230,57,70,0.08) 0%, transparent 70%);
            top: -50px; right: -80px;
        }
        .bg-particles::after {
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(76,201,240,0.06) 0%, transparent 70%);
            bottom: -30px; left: -60px;
            animation-delay: -4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .login-logo {
            width: 72px; height: 72px;
            border-radius: 20px;
            margin-bottom: 16px;
            box-shadow: 0 8px 32px rgba(230,57,70,0.3);
            animation: logoEntry 0.6s ease-out;
        }
        @keyframes logoEntry {
            from { transform: scale(0.5) translateY(30px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
        .login-header h1 {
            font-size: 1.6rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 4px;
        }
        .login-header p {
            font-size: var(--font-size-sm);
            color: var(--text-muted);
        }

        .login-card {
            background: var(--gradient-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 28px 24px;
            box-shadow: var(--shadow-lg);
            animation: cardSlide 0.5s ease-out 0.2s both;
        }
        @keyframes cardSlide {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: var(--font-size-xs);
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: color var(--transition-fast);
        }
        .input-wrapper input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: var(--font-family);
            font-size: var(--font-size-base);
            outline: none;
            transition: all var(--transition-fast);
        }
        .input-wrapper input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(230,57,70,0.15);
        }
        .input-wrapper input:focus + i,
        .input-wrapper input:focus ~ i {
            color: var(--primary);
        }
        .input-wrapper input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            transition: color var(--transition-fast);
        }
        .toggle-password:hover {
            color: var(--text-primary);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-family: var(--font-family);
            font-size: var(--font-size-base);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: 0 4px 20px rgba(230,57,70,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(230,57,70,0.45);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            background: var(--danger-bg);
            color: var(--danger);
            font-size: var(--font-size-sm);
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
            display: none;
            align-items: center;
            gap: 8px;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: var(--font-size-xs);
            color: var(--text-muted);
        }
        .login-footer span {
            color: var(--primary);
            font-weight: 600;
        }

        /* Loading spinner in button */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
    </style>
</head>
<body>
    <div class="bg-particles"></div>

    <div class="login-container">
        <div class="login-header">
            <img src="<?= BASE_URL ?>public/images/Icon.png" alt="AlfarezMart" class="login-logo">
            <h1>AlfarezMart</h1>
            <p>Sistem Manajemen Stok Toko</p>
        </div>

        <div class="login-card">
            <div class="error-message" id="errorMsg">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span id="errorText"></span>
            </div>

            <form id="loginForm" onsubmit="handleLogin(event)">
                <input type="hidden" name="csrf_token" id="csrfToken" value="<?= $csrfToken ?>">
                
                <div class="form-group">
                    <label for="credential">Email atau Nomor HP</label>
                    <div class="input-wrapper">
                        <input type="text" id="credential" name="credential" 
                               placeholder="contoh@email.com atau 08xx" 
                               autocomplete="username" required>
                        <i class="bi bi-person"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" 
                               placeholder="Masukkan password" 
                               autocomplete="current-password" required>
                        <i class="bi bi-lock"></i>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>&copy; 2026 <span>AlfarezMart</span> · v1.0</p>
        </div>
    </div>

    <script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        const eye = document.getElementById('eyeIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            eye.className = 'bi bi-eye-slash';
        } else {
            pwd.type = 'password';
            eye.className = 'bi bi-eye';
        }
    }

    function showError(msg) {
        const el = document.getElementById('errorMsg');
        document.getElementById('errorText').textContent = msg;
        el.style.display = 'flex';
    }

    function hideError() {
        document.getElementById('errorMsg').style.display = 'none';
    }

    async function _sha256(text) {
        try {
            const buf = new TextEncoder().encode(text);
            const hash = await crypto.subtle.digest('SHA-256', buf);
            return Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2, '0')).join('');
        } catch (e) {
            return null;
        }
    }

    async function tryOfflineLogin(credential, password) {
        // Hanya bekerja untuk superadmin: aplikasi sangat krusial — pemilik wajib bisa pantau & cek harga
        // walau offline. Kredensial pernah dipakai login sukses online → disimpan sebagai hash di device.
        try {
            const raw = localStorage.getItem('alfarezmart_offline_creds');
            if (!raw) return { ok: false, reason: 'Belum pernah login online di perangkat ini.' };
            const creds = JSON.parse(raw);
            if (!creds || creds.level !== 'superadmin') {
                return { ok: false, reason: 'Login offline hanya tersedia untuk Superadmin.' };
            }
            const hash = await _sha256((credential || '') + '|' + (password || ''));
            if (!hash || hash !== creds.hash) {
                return { ok: false, reason: 'Email/No HP atau Password offline tidak cocok.' };
            }
            // Set hint supaya app.js skip redirect ke login
            localStorage.setItem('alfarezmart_logged_in', 'true');
            localStorage.setItem('alfarezmart_user', JSON.stringify({
                id: creds.id,
                name: creds.name,
                email: creds.email,
                level: creds.level,
                login_time: new Date().toISOString(),
                offline: true,
            }));
            return { ok: true, user: creds };
        } catch (e) {
            return { ok: false, reason: 'Gagal memproses kredensial offline.' };
        }
    }

    async function handleLogin(e) {
        e.preventDefault();
        hideError();

        const btn = document.getElementById('btnLogin');
        const prevHTML = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memverifikasi...';
        btn.disabled = true;

        const credential = document.getElementById('credential').value.trim();
        const password = document.getElementById('password').value;
        const csrfToken = document.getElementById('csrfToken').value;

        if (!credential || !password) {
            showError('Silakan isi semua field');
            btn.innerHTML = prevHTML;
            btn.disabled = false;
            return;
        }

        // Jika offline, langsung coba jalur login offline (superadmin)
        if (!navigator.onLine) {
            const offlineRes = await tryOfflineLogin(credential, password);
            if (offlineRes.ok) {
                btn.innerHTML = '<i class="bi bi-wifi-off"></i> Login Offline OK';
                btn.style.background = 'var(--gradient-success)';
                setTimeout(() => { window.location.href = '<?= BASE_URL ?>'; }, 500);
                return;
            }
            showError('Mode Offline: ' + offlineRes.reason);
            btn.innerHTML = prevHTML;
            btn.disabled = false;
            return;
        }

        try {
            const res = await fetch('<?= BASE_URL ?>api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ credential, password })
            });

            const data = await res.json();

            if (data.success) {
                // Store user info untuk auto-login & offline access
                localStorage.setItem('alfarezmart_user', JSON.stringify({
                    id: data.user?.id,
                    name: data.user?.name,
                    email: data.user?.email,
                    level: data.user?.level,
                    login_time: new Date().toISOString(),
                }));
                localStorage.setItem('alfarezmart_logged_in', 'true');

                // Cache kredensial offline KHUSUS untuk superadmin (aplikasi krusial)
                if ((data.user?.level || '') === 'superadmin') {
                    const hash = await _sha256(credential + '|' + password);
                    if (hash) {
                        localStorage.setItem('alfarezmart_offline_creds', JSON.stringify({
                            id: data.user.id,
                            name: data.user.name,
                            email: data.user.email,
                            level: data.user.level,
                            hash: hash,
                            saved_at: new Date().toISOString(),
                        }));
                    }
                } else {
                    // Hapus jejak superadmin lain jika sekarang login non-superadmin di device sama
                    localStorage.removeItem('alfarezmart_offline_creds');
                }

                btn.innerHTML = '<i class="bi bi-check-circle"></i> Berhasil!';
                btn.style.background = 'var(--gradient-success)';
                setTimeout(() => {
                    window.location.href = data.redirect || '<?= BASE_URL ?>';
                }, 500);
            } else {
                showError(data.error || 'Login gagal');
                btn.innerHTML = prevHTML;
                btn.disabled = false;
            }
        } catch (err) {
            // Server tak terjangkau → coba offline login otomatis
            const offlineRes = await tryOfflineLogin(credential, password);
            if (offlineRes.ok) {
                btn.innerHTML = '<i class="bi bi-wifi-off"></i> Login Offline OK';
                btn.style.background = 'var(--gradient-success)';
                setTimeout(() => { window.location.href = '<?= BASE_URL ?>'; }, 500);
                return;
            }
            showError('Tidak bisa terhubung ke server. ' + offlineRes.reason);
            btn.innerHTML = prevHTML;
            btn.disabled = false;
        }
    }

    // Auto-focus first input
    document.addEventListener('DOMContentLoaded', () => {
        <?php if (isset($_GET['error'])): ?>
        showError(<?= json_encode($_GET['error']) ?>);
        <?php endif; ?>
        
        document.getElementById('credential').focus();
    });
    </script>
</body>
</html>
