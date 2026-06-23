/**
 * BarcodeUtil - Generate & print product barcodes (CODE128)
 */
const BarcodeUtil = {
    async generate() {
        const res = await fetch(`${BASE_URL}api/barcode/generate`);
        const data = await res.json();
        if (!data.success || !data.barcode) {
            throw new Error(data.error || 'Gagal generate barcode');
        }
        return data.barcode;
    },

    /**
     * Print barcode label(s) in a new window
     * @param {Array<{code:string, title:string, subtitle?:string}>|{code,title,subtitle}} labels
     */
    print(labels) {
        const items = Array.isArray(labels) ? labels : [labels];
        const valid = items.filter(l => l && l.code);
        if (valid.length === 0) {
            showToast('Barcode kosong, tidak bisa dicetak', 'warning');
            return;
        }

        const win = window.open('', '_blank', 'width=420,height=640');
        if (!win) {
            showToast('Izinkan pop-up untuk mencetak barcode', 'warning');
            return;
        }

        const labelsHtml = valid.map((item, idx) => {
            const title = this._escape(item.title || '');
            const subHtml = item.subtitle
                ? `<div class="subtitle">${this._escape(item.subtitle)}</div>`
                : '';
            return `<div class="label"><div class="title">${title}</div>${subHtml}<svg id="bc-print-${idx}"></svg></div>`;
        }).join('');

        const codesJson = JSON.stringify(valid.map(l => String(l.code)));

        win.document.write(`<!DOCTYPE html>
<html><head>
<meta charset="UTF-8">
<title>Cetak Barcode</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; padding: 12px; }
  .label {
    border: 1px dashed #ccc;
    padding: 12px;
    margin-bottom: 16px;
    text-align: center;
    max-width: 320px;
  }
  .title { font-size: 11px; font-weight: 700; margin-bottom: 2px; line-height: 1.3; }
  .subtitle { font-size: 10px; color: #555; margin-bottom: 8px; }
  svg { max-width: 100%; height: auto; }
  @media print {
    body { padding: 0; }
    .label { border: none; margin-bottom: 8mm; }
    .no-print { display: none !important; }
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
</head><body>
${labelsHtml}
<div class="no-print" style="margin-top:16px;text-align:center;">
  <button onclick="window.print()" style="padding:10px 24px;font-size:14px;cursor:pointer;">Cetak</button>
  <button onclick="window.close()" style="padding:10px 16px;font-size:14px;cursor:pointer;margin-left:8px;">Tutup</button>
</div>
<script>
  const codes = ${codesJson};
  codes.forEach((code, i) => {
    JsBarcode("#bc-print-" + i, code, {
      format: "CODE128", width: 2, height: 55, displayValue: true, fontSize: 13, margin: 6
    });
  });
<\/script>
</body></html>`);
        win.document.close();
    },

    async fillInput(inputEl, btnEl) {
        if (!inputEl) return;
        const prev = btnEl ? btnEl.innerHTML : '';
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        }
        try {
            const code = await this.generate();
            inputEl.value = code;
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
            showToast('Barcode berhasil digenerate', 'success');
        } catch (e) {
            showToast(e.message, 'error');
        } finally {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = prev;
            }
        }
    },

    printFromInput(inputEl, title, subtitle) {
        const code = inputEl?.value?.trim();
        if (!code) {
            showToast('Isi atau generate barcode terlebih dahulu', 'warning');
            return;
        }
        this.print({ code, title: title || code, subtitle });
    },

    async generateAllEmpty(selector = 'input.barcode-field') {
        const inputs = document.querySelectorAll(selector);
        let count = 0;
        for (const input of inputs) {
            if (!input.value.trim()) {
                input.value = await this.generate();
                input.dispatchEvent(new Event('input', { bubbles: true }));
                count++;
            }
        }
        if (count > 0) {
            showToast(`${count} barcode berhasil digenerate`, 'success');
        } else {
            showToast('Semua level sudah memiliki barcode', 'info');
        }
    },

    printAllFilled(selector = 'input.barcode-field', getMeta) {
        const labels = [];
        document.querySelectorAll(selector).forEach((input, i) => {
            const code = input.value?.trim();
            if (!code) return;
            const row = input.closest('.packaging-level') || input.closest('.pkg-barcode-row');
            const meta = getMeta ? getMeta(input, row, i) : {};
            labels.push({
                code,
                title: meta.title || code,
                subtitle: meta.subtitle || '',
            });
        });
        this.print(labels);
    },

    _escape(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    },

    // Barcode Scanner with Camera
    scanner: {
        html5Qrcode: null,
        isScanning: false,
        _stream: null,

        // Pre-load ZXing library in the BACKGROUND on page load.
        // This way it's already cached when the user opens the scanner.
        _zxingReady: false,
        _zxingLoading: false,

        async _preloadZXing() {
            if (window.ZXing || this._zxingReady || this._zxingLoading) return;
            // Only preload if native BarcodeDetector is NOT available
            if ('BarcodeDetector' in window) return;
            
            this._zxingLoading = true;
            const cdnUrls = [
                'https://unpkg.com/@zxing/library@latest/umd/index.min.js',
                'https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js',
            ];
            for (const url of cdnUrls) {
                try {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = url;
                        script.type = 'text/javascript';
                        script.onload = () => {
                            if (window.ZXing) { this._zxingReady = true; resolve(true); }
                            else reject(new Error('ZXing global not available'));
                        };
                        script.onerror = () => reject(new Error('CDN load failed'));
                        document.head.appendChild(script);
                    });
                    this._zxingLoading = false;
                    return;
                } catch (e) {
                    // silently try next CDN
                }
            }
            this._zxingLoading = false;
        },

        async _loadScannerLib() {
            if (window.ZXing) return true;
            // If preload already started, wait for it
            if (this._zxingLoading) {
                for (let i = 0; i < 100; i++) {
                    await new Promise(r => setTimeout(r, 100));
                    if (window.ZXing) return true;
                }
            }
            // Direct load as last resort
            const cdnUrls = [
                'https://unpkg.com/@zxing/library@latest/umd/index.min.js',
                'https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js',
            ];
            for (const url of cdnUrls) {
                try {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = url;
                        script.type = 'text/javascript';
                        script.onload = () => {
                            if (window.ZXing) resolve(true);
                            else reject(new Error('ZXing global not available'));
                        };
                        script.onerror = () => reject(new Error('CDN load failed'));
                        document.head.appendChild(script);
                    });
                    return true;
                } catch (e) {
                    // try next
                }
            }
            throw new Error('Gagal memuat library ZXing. Periksa koneksi internet.');
        },

        stop() {
            this.isScanning = false;
            if (this._stream) {
                this._stream.getTracks().forEach(t => t.stop());
                this._stream = null;
            }
            if (this.html5Qrcode) {
                try { this.html5Qrcode.reset(); } catch(e) {}
                this.html5Qrcode = null;
            }
        }
    },

    /**
     * Show scanner modal UI
     */
    _showScannerModal(inputEl, onScanned) {
        // Add global focus handler if it doesn't exist
        if (!window.triggerScannerFocus) {
            window.triggerScannerFocus = async function() {
                const ring = document.getElementById('scannerFocusRing');
                if (ring) {
                    ring.style.display = 'block';
                    ring.style.animation = 'none';
                    ring.offsetHeight;
                    ring.style.animation = 'pulse-glow 0.6s ease-out';
                    setTimeout(() => { ring.style.display = 'none'; }, 600);
                }
                const video = document.getElementById('barcode-video-element');
                if (video && video.srcObject) {
                    const track = video.srcObject.getVideoTracks()[0];
                    if (track && track.applyConstraints) {
                        try {
                            await track.applyConstraints({ advanced: [{ focusMode: "continuous" }] });
                        } catch (e) {
                            try { await track.applyConstraints({}); } catch(e2) {}
                        }
                    }
                }
            };
        }

        return AppModal.show({
            title: 'Scan Barcode',
            subtitle: 'Arahkan kamera ke barcode',
            icon: 'bi-upc-scan',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: `
                <div style="text-align:center;">
                    <div id="barcode-scanner-video-container" onclick="window.triggerScannerFocus()" style="width:100%; max-width:400px; height:300px; margin:0 auto; border-radius:var(--radius-md); overflow:hidden; background:var(--surface-2); position:relative; cursor:pointer;">
                        <!-- Loading spinner (visible while camera boots) -->
                        <div id="barcode-loading-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; background:var(--surface-2); color:var(--text-secondary); z-index:5;">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <span style="font-size:12px;">Mempersiapkan kamera...</span>
                        </div>
                        <video id="barcode-video-element" autoplay playsinline muted style="width:100%; height:100%; object-fit:cover; position:relative; z-index:1;"></video>
                        <!-- Targeting box -->
                        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:90%; height:65%; border:2px solid rgba(45, 211, 111, 0.6); border-radius:12px; box-shadow:0 0 0 4000px rgba(0,0,0,0.3); pointer-events:none; z-index:3;"></div>
                        <!-- Focus ring -->
                        <div id="scannerFocusRing" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:70px; height:70px; border:2px solid #fff; border-radius:50%; pointer-events:none; box-shadow: 0 0 10px rgba(255,255,255,0.5); z-index:4;"></div>
                        <div style="position:absolute; bottom:12px; left:0; width:100%; text-align:center; pointer-events:none; z-index:4;">
                            <span style="background:rgba(0,0,0,0.6); color:#fff; font-size:10px; padding:4px 8px; border-radius:12px; backdrop-filter:blur(4px);">Ketuk untuk autofokus</span>
                        </div>
                    </div>
                    <p id="scanStatus" style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:12px; margin-bottom:8px;">
                        <i class="bi bi-camera-video"></i> Mempersiapkan kamera...
                    </p>
                    <div style="background:var(--success-bg); color:var(--success); padding:10px 14px; border-radius:var(--radius-sm); font-size:var(--font-size-xs); display:none; align-items:center; gap:6px; justify-content:center; margin-top:8px;" id="scanSuccess">
                        <i class="bi bi-check-circle-fill"></i> <span id="scanSuccessText">Barcode terdeteksi</span>
                    </div>
                </div>
            `,
            submitText: 'Tutup',
            hideFooter: false,
            cancelText: 'Batal',
            onSubmit: async () => {
                this.scanner.stop();
                return true;
            },
        });
    },

    /**
     * Public API: Open camera scanner modal and scan barcode into input element.
     * Strategy: Use native BarcodeDetector API if available (instant), 
     * otherwise fall back to ZXing (pre-loaded in background).
     */
    async scanBarcode(inputEl, onScanned) {
        try {
            const modalPromise = this._showScannerModal(inputEl, onScanned);

            // Let the browser paint the modal + spinner before doing anything heavy
            await new Promise(r => requestAnimationFrame(() => setTimeout(r, 16)));

            const status = document.getElementById('scanStatus');
            const success = document.getElementById('scanSuccess');
            const successText = document.getElementById('scanSuccessText');
            const loadingOverlay = document.getElementById('barcode-loading-overlay');

            // === 1. OPEN CAMERA WITH ZERO CONSTRAINTS (fastest possible) ===
            let stream;
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "environment" }
                });
            } catch(camErr) {
                throw new Error(`Gagal akses kamera: ${camErr.message}`);
            }

            this.scanner._stream = stream;
            const videoEl = document.getElementById('barcode-video-element');
            if (!videoEl) {
                stream.getTracks().forEach(t => t.stop());
                throw new Error('Elemen video tidak ditemukan');
            }
            videoEl.srcObject = stream;
            videoEl.setAttribute('playsinline', 'true');
            
            // Wait for actual video frames to appear
            await new Promise((resolve, reject) => {
                const timeout = setTimeout(() => resolve(), 3000); // safety timeout
                videoEl.onloadeddata = () => { clearTimeout(timeout); resolve(); };
                videoEl.play().catch(reject);
            });

            // Hide the loading spinner - camera is now live
            if (loadingOverlay) loadingOverlay.style.display = 'none';

            // === 2. CHOOSE DETECTION ENGINE ===
            const useNative = 'BarcodeDetector' in window;

            if (useNative) {
                // ========== NATIVE BarcodeDetector (Chrome Android 83+, Safari 17.2+) ==========
                if (status) status.innerHTML = '<i class="bi bi-lightning-charge-fill"></i> Arahkan kamera ke barcode...';
                
                const detector = new BarcodeDetector({
                    formats: ['code_128', 'ean_13', 'ean_8', 'code_39', 'itf', 'upc_a', 'upc_e']
                });
                this.scanner.isScanning = true;

                const scanNative = async () => {
                    if (!this.scanner.isScanning) return;
                    if (videoEl.readyState < videoEl.HAVE_CURRENT_DATA) {
                        setTimeout(scanNative, 100);
                        return;
                    }

                    try {
                        const barcodes = await detector.detect(videoEl);
                        if (barcodes.length > 0 && this.scanner.isScanning) {
                            const decoded = barcodes[0].rawValue;
                            this._onBarcodeFound(decoded, inputEl, onScanned, stream, videoEl, status, success, successText);
                            return;
                        }
                    } catch(e) {
                        // detection failed on this frame, try again
                    }

                    if (this.scanner.isScanning) {
                        setTimeout(scanNative, 120); // ~8 FPS is plenty for barcode detection
                    }
                };
                scanNative();

            } else {
                // ========== ZXING FALLBACK (older browsers) ==========
                if (!window.ZXing) {
                    if (status) status.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> Mengunduh mesin pemindai...';
                    await this.scanner._loadScannerLib();
                }
                if (!window.ZXing) throw new Error('Library ZXing gagal dimuat');

                if (status) status.innerHTML = '<i class="bi bi-camera-video"></i> Arahkan kamera ke barcode...';

                const hints = new Map();
                hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                    ZXing.BarcodeFormat.CODE_128,
                    ZXing.BarcodeFormat.EAN_13,
                    ZXing.BarcodeFormat.EAN_8,
                    ZXing.BarcodeFormat.CODE_39
                ]);
                hints.set(ZXing.DecodeHintType.TRY_HARDER, true);

                const codeReader = new ZXing.BrowserMultiFormatReader(hints);
                this.scanner.html5Qrcode = codeReader;
                this.scanner.isScanning = true;

                // Create canvas ONCE with willReadFrequently
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                // Set canvas size once based on video dimensions
                let canvasReady = false;

                const drawFrame = (angleDeg, contrast) => {
                    const vw = videoEl.videoWidth;
                    const vh = videoEl.videoHeight;
                    if (!vw || !vh) return false;
                    
                    const rad = angleDeg * Math.PI / 180;
                    const sin = Math.abs(Math.sin(rad));
                    const cos = Math.abs(Math.cos(rad));
                    const cw = Math.round(vw * cos + vh * sin);
                    const ch = Math.round(vw * sin + vh * cos);
                    
                    if (canvas.width !== cw) canvas.width = cw;
                    if (canvas.height !== ch) canvas.height = ch;
                    
                    ctx.save();
                    ctx.translate(cw / 2, ch / 2);
                    ctx.rotate(rad);
                    ctx.drawImage(videoEl, -vw / 2, -vh / 2, vw, vh);
                    ctx.restore();

                    if (contrast > 1) {
                        const intercept = 128 * (1 - contrast);
                        const imgData = ctx.getImageData(0, 0, cw, ch);
                        const d = imgData.data;
                        for (let i = 0; i < d.length; i += 4) {
                            d[i]   = Math.min(255, Math.max(0, d[i]   * contrast + intercept));
                            d[i+1] = Math.min(255, Math.max(0, d[i+1] * contrast + intercept));
                            d[i+2] = Math.min(255, Math.max(0, d[i+2] * contrast + intercept));
                        }
                        ctx.putImageData(imgData, 0, 0);
                    }
                    return true;
                };

                const tryDecode = () => {
                    // Completely silence ALL console output from ZXing during decode
                    const _log = console.log, _warn = console.warn, _err = console.error;
                    console.log = console.warn = console.error = () => {};
                    let result = null;
                    try {
                        const src = new ZXing.HTMLCanvasElementLuminanceSource(canvas);
                        const bmp = new ZXing.BinaryBitmap(new ZXing.HybridBinarizer(src));
                        const r = codeReader.decodeBitmap(bmp);
                        result = r ? r.getText() : null;
                    } catch(e) {
                        result = null;
                    } finally {
                        console.log = _log; console.warn = _warn; console.error = _err;
                    }
                    return result;
                };

                // Scan configs for multi-angle detection
                const CONFIGS = [
                    { deg: 0, ct: 1 }, { deg: 90, ct: 1 }, { deg: 180, ct: 1 },
                    { deg: 45, ct: 1 }, { deg: 135, ct: 1 }, { deg: 270, ct: 1 },
                    { deg: 0, ct: 1.5 }, { deg: 90, ct: 1.5 }, { deg: 180, ct: 1.5 }
                ];
                let cfgIdx = 0;

                const scanZXing = () => {
                    if (!this.scanner.isScanning) return;
                    if (videoEl.readyState < videoEl.HAVE_CURRENT_DATA) {
                        setTimeout(scanZXing, 100);
                        return;
                    }

                    const cfg = CONFIGS[cfgIdx];
                    if (drawFrame(cfg.deg, cfg.ct)) {
                        const decoded = tryDecode();
                        if (decoded && this.scanner.isScanning) {
                            this._onBarcodeFound(decoded, inputEl, onScanned, stream, videoEl, status, success, successText);
                            return;
                        }
                    }

                    cfgIdx = (cfgIdx + 1) % CONFIGS.length;
                    if (this.scanner.isScanning) {
                        setTimeout(scanZXing, 80); // ~12 FPS
                    }
                };
                scanZXing();
            }

            // Wait for modal to close
            try {
                await modalPromise;
            } finally {
                this.scanner.stop();
            }

        } catch (e) {
            this.scanner.stop();
            const status = document.getElementById('scanStatus');
            const errorMsg = e?.message || String(e) || 'Unknown error';
            console.error('Barcode scanner error:', e);
            if (status) {
                status.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Gagal: ${errorMsg}`;
                status.style.color = 'var(--danger)';
            } else {
                showToast(errorMsg || 'Gagal memuat scanner', 'error');
            }
        }
    },

    /**
     * Internal: Handle successful barcode detection
     */
    _onBarcodeFound(decoded, inputEl, onScanned, stream, videoEl, status, success, successText) {
        this.scanner.isScanning = false;
        
        // Stop camera
        if (stream) stream.getTracks().forEach(t => t.stop());
        if (videoEl) videoEl.srcObject = null;

        // Fill input
        if (inputEl) {
            inputEl.value = decoded;
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // Update UI
        if (status) status.style.display = 'none';
        if (success) {
            success.style.display = 'flex';
            if (successText) successText.textContent = `Terdeteksi: ${decoded}`;
        }

        showToast(`Barcode terdeteksi: ${decoded}`, 'success');

        setTimeout(() => {
            AppModal.close('scanned');
            if (typeof onScanned === 'function') onScanned(decoded);
        }, 800);
    },
};

// === PRE-LOAD ZXing library in background on page load ===
// This ensures ZXing is ready BEFORE the user opens the scanner.
document.addEventListener('DOMContentLoaded', () => {
    // Delay preload by 3 seconds so it doesn't compete with page resources
    setTimeout(() => {
        if (typeof BarcodeUtil !== 'undefined') {
            BarcodeUtil.scanner._preloadZXing();
        }
    }, 3000);
});

