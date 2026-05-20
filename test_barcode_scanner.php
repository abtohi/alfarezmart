<?php
/**
 * Barcode Scanner Test - Verify camera scanning functionality
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Scan Barcode Kamera</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .test-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .test-section h2 {
            font-size: 16px;
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .status.pass {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.fail {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.loading {
            background: #cfe2ff;
            color: #084298;
            border: 1px solid #b6d4fe;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            display: none;
        }
        .result.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .result.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .code-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .log-box {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="bi bi-upc-scan"></i> Test Scan Barcode</h1>
        <p class="subtitle">Verifikasi fitur kamera scanner barcode bekerja dengan baik</p>

        <div class="test-section">
            <h2><i class="bi bi-gear"></i> Pre-Flight Check</h2>
            <div id="browsers" class="status loading">
                <span><i class="bi bi-info-circle"></i> Memeriksa browser support...</span>
            </div>
            <div id="camera" class="status loading">
                <span><i class="bi bi-info-circle"></i> Memeriksa akses kamera...</span>
            </div>
        </div>

        <div class="test-section">
            <h2><i class="bi bi-camera-video"></i> Test Scanner</h2>
            <input type="text" id="barcodeInput" class="code-input" placeholder="Klik tombol di bawah atau scan barcode...">
            <button id="scanBtn" onclick="testScan()">
                <i class="bi bi-upc-scan"></i> Buka Kamera & Scan
            </button>
            <div id="result" class="result"></div>
        </div>

        <div class="test-section">
            <h2><i class="bi bi-file-text"></i> Debug Log</h2>
            <div class="log-box" id="debugLog">
                Siap untuk test...
            </div>
        </div>

        <div class="test-section" style="background: #fff3cd; border-color: #ffc107;">
            <p style="color: #856404; font-size: 13px;">
                <i class="bi bi-info-circle"></i> 
                <strong>Catatan:</strong> Pastikan browser sudah memberikan izin akses kamera. Jika diminta, klik "Izinkan".
            </p>
        </div>
    </div>

    <script src="public/js/app.js"></script>
    <script src="public/js/barcode.js"></script>
    <script>
        const log = (msg) => {
            const logEl = document.getElementById('debugLog');
            const timestamp = new Date().toLocaleTimeString();
            logEl.innerHTML += `\n[${timestamp}] ${msg}`;
            logEl.scrollTop = logEl.scrollHeight;
            console.log(msg);
        };

        const updateStatus = (elId, msg, type) => {
            const el = document.getElementById(elId);
            if (el) {
                el.innerHTML = msg;
                el.className = `status ${type}`;
            }
        };

        // Check browser support
        window.addEventListener('DOMContentLoaded', async () => {
            log('Halaman dimulai...');
            
            // Check browser
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                updateStatus('browsers', '<i class="bi bi-check-circle-fill"></i> Browser support: OK', 'pass');
                log('Browser mendukung getUserMedia');
            } else {
                updateStatus('browsers', '<i class="bi bi-x-circle-fill"></i> Browser tidak mendukung camera access', 'fail');
                log('ERROR: Browser tidak support getUserMedia');
            }

            // Check camera permissions
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                if (videoDevices.length > 0) {
                    updateStatus('camera', `<i class="bi bi-check-circle-fill"></i> Kamera ditemukan: ${videoDevices.length} device`, 'pass');
                    log(`Ditemukan ${videoDevices.length} kamera: ${videoDevices.map(d => d.label || 'unknown').join(', ')}`);
                } else {
                    updateStatus('camera', '<i class="bi bi-x-circle-fill"></i> Tidak ada kamera yang terdeteksi', 'fail');
                    log('WARNING: Tidak ada video device ditemukan');
                }
            } catch (e) {
                updateStatus('camera', `<i class="bi bi-exclamation-triangle"></i> ${e.message}`, 'fail');
                log(`ERROR: ${e.message}`);
            }

            log('Pre-flight check selesai');
        });

        async function testScan() {
            log('Test scan dimulai...');
            const btn = document.getElementById('scanBtn');
            const resultEl = document.getElementById('result');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Membuka kamera...';
            
            try {
                const input = document.getElementById('barcodeInput');
                
                // Check if BarcodeUtil exists
                if (typeof BarcodeUtil === 'undefined') {
                    throw new Error('BarcodeUtil tidak ditemukan - pastikan barcode.js sudah di-load');
                }
                
                if (typeof BarcodeUtil.scanBarcode !== 'function') {
                    throw new Error('scanBarcode function tidak tersedia');
                }
                
                log('Memanggil BarcodeUtil.scanBarcode()...');
                
                await BarcodeUtil.scanBarcode(input, (code) => {
                    log(`Barcode terdeteksi: ${code}`);
                    resultEl.className = 'result success';
                    resultEl.innerHTML = `<i class="bi bi-check-circle"></i> <strong>Sukses!</strong><br>Barcode terdeteksi: <code>${code}</code>`;
                    resultEl.style.display = 'block';
                });
                
            } catch (e) {
                log(`ERROR: ${e.message}`);
                resultEl.className = 'result error';
                resultEl.innerHTML = `<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong><br>${e.message}`;
                resultEl.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-upc-scan"></i> Buka Kamera & Scan';
            }
        }
    </script>
</body>
</html>