<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($errorTitle ?? '500 - Kesalahan Server') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-primary: #0a0e17;
            --surface-1: #131b2e;
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-muted: #94a3b8;
            --danger: #ef4444;
            --primary: #e63946;
        }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .error-card {
            background: var(--surface-1);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 36px 28px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        }
        .error-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px;
            color: var(--text-primary);
        }
        p {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0 0 20px;
        }
        .error-code-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.04);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            padding: 6px 14px;
            font-family: monospace;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #fff;
            padding: 10px 22px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            transition: opacity 0.2s;
        }
        .btn-home:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h1>Terjadi Kendala Sistem</h1>
        <p><?= htmlspecialchars($errorMessage ?? 'Sistem sedang mengalami kendala saat memproses permintaan Anda. Silakan coba kembali sesaat lagi.') ?></p>
        
        <?php if (!empty($errorId)): ?>
            <div class="error-code-badge">
                ID Insiden: <strong><?= htmlspecialchars($errorId) ?></strong>
            </div>
        <?php endif; ?>

        <div>
            <a href="/" class="btn-home">
                <i class="bi bi-house-door"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
