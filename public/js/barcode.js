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
        quaggaActive: false,
        isScanning: false,

        /**
         * Detect if running on iOS (Safari / all iOS browsers use WebKit)
         */
        _isIOS() {
            return /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        },

        async _loadScannerLib() {
            if (window.Html5Qrcode) return true;

            // CDN list: UMD builds that set window.Html5Qrcode correctly
            const cdnUrls = [
                'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
                'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js',
            ];

            for (const url of cdnUrls) {
                try {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = url;
                        script.type = 'text/javascript';
                        script.onload = () => {
                            if (window.Html5Qrcode) {
                                resolve(true);
                            } else {
                                reject(new Error('Html5Qrcode global not available'));
                            }
                        };
                        script.onerror = () => reject(new Error('CDN load failed'));
                        document.head.appendChild(script);
                    });
                    return true;
                } catch (e) {
                    console.warn('[BarcodeScanner] CDN failed:', url, e.message);
                }
            }
            throw new Error('Gagal memuat library scanner. Periksa koneksi internet.');
        },

        /**
         * Load Quagga2 library (for iOS) - better 1D barcode support on WebKit
         */
        async _loadQuaggaLib() {
            if (window.Quagga) return true;

            const cdnUrls = [
                'https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js',
                'https://unpkg.com/@ericblade/quagga2/dist/quagga.min.js',
            ];

            for (const url of cdnUrls) {
                try {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = url;
                        script.type = 'text/javascript';
                        script.onload = () => {
                            if (window.Quagga) resolve(true);
                            else reject(new Error('Quagga global not available'));
                        };
                        script.onerror = () => reject(new Error('Quagga CDN load failed: ' + url));
                        document.head.appendChild(script);
                    });
                    return true;
                } catch (e) {
                    console.warn('[BarcodeScanner] Quagga CDN failed:', url, e.message);
                }
            }
            throw new Error('Gagal memuat library Quagga2. Periksa koneksi internet.');
        },

        stop() {
            this.isScanning = false;

            // Stop html5-qrcode (Android path)
            if (this.html5Qrcode) {
                try {
                    this.html5Qrcode.stop().catch(e => console.warn(e));
                } catch(e) {}
                this.html5Qrcode = null;
            }

            // Stop Quagga2 (iOS path)
            if (this.quaggaActive && window.Quagga) {
                try {
                    Quagga.stop();
                } catch(e) {}
                this.quaggaActive = false;
            }
        }
    },

    /**
     * Show scanner modal UI
     */
    _showScannerModal(inputEl, onScanned) {
        return AppModal.show({
            title: 'Scan Barcode',
            subtitle: 'Arahkan kamera ke barcode',
            icon: 'bi-upc-scan',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: `
                <div style="text-align:center;">
                    <div id="barcode-scanner-video-container" style="width:100%; max-width:400px; margin:0 auto; border-radius:var(--radius-md); overflow:hidden; background:#000; position:relative;"></div>
                    <p id="scanStatus" style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:12px; margin-bottom:8px;">
                        <i class="bi bi-camera-video"></i> Memuat kamera scanner...
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
     * iOS-specific barcode scanner using Quagga2
     * Quagga2 handles 1D barcodes far better on iOS WebKit than html5-qrcode/ZXing
     */
    async _startQuaggaScanner(containerId, onDetected, onError) {
        await this.scanner._loadQuaggaLib();

        if (!window.Quagga) {
            throw new Error('Library Quagga2 gagal dimuat');
        }

        await new Promise((resolve, reject) => {
            Quagga.init({
                inputStream: {
                    name: 'Live',
                    type: 'LiveStream',
                    target: document.getElementById(containerId),
                    constraints: {
                        facingMode: 'environment',  // Rear camera
                        // Increase ideal resolution for better barcode clarity
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    // Removed area restrictions so it scans the entire frame regardless of orientation
                },
                locator: {
                    patchSize: 'large', // Better for varying distances and blurry barcodes
                    halfSample: false,  // Disabled for higher accuracy (reads smaller/tilted barcodes better)
                },
                numOfWorkers: 0,        // Use 0 for iOS
                frequency: 20,          // Maximize scan attempts per second
                decoder: {
                    readers: [
                        'code_128_reader',
                        'ean_reader',
                        'ean_8_reader',
                        'upc_reader',
                        'upc_e_reader',
                        'code_39_reader',
                        'code_93_reader',
                        'i2of5_reader',
                    ],
                    multiple: false     // Focus on one barcode for speed
                },
                locate: true,           // Crucial for finding tilted/rotated barcodes
            }, function(err) {
                if (err) {
                    reject(err);
                    return;
                }
                Quagga.start();
                resolve(true);
            });
        });

        this.scanner.quaggaActive = true;

        // Overlay scan line CSS into the Quagga container
        const container = document.getElementById(containerId);
        if (container) {
            container.style.position = 'relative';
            // Hide Quagga's debug canvas overlay (drawingBuffer) visually while keeping processing
            const canvases = container.querySelectorAll('canvas');
            canvases.forEach(c => {
                if (c.className && c.className.includes('drawingBuffer')) {
                    c.style.display = 'none';
                }
            });
        }

        // Listen for detections
        const _self = this;
        Quagga.onDetected(function(result) {
            if (!_self.scanner.isScanning) return;

            const code = result && result.codeResult && result.codeResult.code;
            if (!code) return;

            // Filter out low-confidence reads (error correction)
            const errors = result.codeResult.decodedCodes
                .filter(x => x.error !== undefined)
                .map(x => x.error);
            if (errors.length > 0) {
                const avgError = errors.reduce((a, b) => a + b, 0) / errors.length;
                // Relaxed error threshold from 0.30 to 0.40 to allow slightly tilted/blurry reads
                if (avgError > 0.40) {
                    console.debug('[Quagga] Low confidence read, ignoring. Error:', avgError, 'Code:', code);
                    return;
                }
            }

            console.log('[Quagga] Barcode detected:', code);
            onDetected(code);
        });

        // Error handler for Quagga process errors
        Quagga.onProcessed(function(result) {
            // Intentionally minimal - process errors are normal for frames with no barcode
        });
    },

    /**
     * Public API: Open camera scanner modal and scan barcode into input element
     * Automatically selects the best scanning engine per platform:
     * - iOS: Quagga2 (better 1D barcode support on WebKit/Safari)
     * - Android/Desktop: html5-qrcode (existing, working implementation)
     */
    async scanBarcode(inputEl, onScanned) {
        const isIOS = this.scanner._isIOS();
        console.log('[BarcodeScanner] Platform:', isIOS ? 'iOS' : 'Android/Other');

        try {
            const modalPromise = this._showScannerModal(inputEl, onScanned);

            // Wait for modal DOM to render
            await new Promise(r => setTimeout(r, 350));

            const status = document.getElementById('scanStatus');
            const success = document.getElementById('scanSuccess');
            const successText = document.getElementById('scanSuccessText');

            const _handleDetected = (decodedText) => {
                if (!this.scanner.isScanning) return;
                this.scanner.isScanning = false;

                inputEl.value = decodedText;
                inputEl.dispatchEvent(new Event('input', { bubbles: true }));

                if (status) status.style.display = 'none';
                if (success) {
                    success.style.display = 'flex';
                    if (successText) successText.textContent = `Terdeteksi: ${decodedText}`;
                }

                showToast(`Barcode terdeteksi: ${decodedText}`, 'success');

                setTimeout(() => {
                    this.scanner.stop();
                    AppModal.close('scanned');
                    if (typeof onScanned === 'function') onScanned(decodedText);
                }, 800);
            };

            if (isIOS) {
                // ── iOS path: use Quagga2 ──────────────────────────────────────
                if (status) {
                    status.innerHTML = '<i class="bi bi-camera-video"></i> Memuat kamera (iOS)...';
                }

                this.scanner.isScanning = true;

                try {
                    await this._startQuaggaScanner(
                        'barcode-scanner-video-container',
                        _handleDetected,
                        (err) => console.warn('[Quagga] process error:', err)
                    );

                    if (status) {
                        status.innerHTML = '<i class="bi bi-camera-video-fill"></i> Arahkan kamera ke barcode...';
                    }
                } catch (quaggaErr) {
                    console.error('[iOS] Quagga start error:', quaggaErr);
                    throw new Error(`Gagal memulai kamera di iOS: ${quaggaErr?.message || quaggaErr}`);
                }

            } else {
                // ── Android / Desktop path: use html5-qrcode (unchanged) ──────
                await this.scanner._loadScannerLib();

                if (!window.Html5Qrcode) {
                    throw new Error('Library scanner gagal dimuat');
                }

                if (status) {
                    status.innerHTML = '<i class="bi bi-camera-video"></i> Arahkan kamera ke barcode...';
                }

                // Create Html5Qrcode instance
                this.scanner.html5Qrcode = new Html5Qrcode("barcode-scanner-video-container");

                // Android-optimised config:
                // - No aspectRatio (can cause issues on some devices too)
                // - useBarCodeDetectorIfSupported: true works on Chrome Android
                const qrConfig = {
                    fps: 20,
                    qrbox: { width: 280, height: 160 },
                    // aspectRatio intentionally omitted – let browser choose native ratio
                    disableFlip: false,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                };

                const cameraConstraints = { facingMode: 'environment' };

                this.scanner.isScanning = true;

                try {
                    await this.scanner.html5Qrcode.start(
                        cameraConstraints,
                        qrConfig,
                        (decodedText) => _handleDetected(decodedText),
                        (errorMessage) => {
                            if (errorMessage && !errorMessage.includes('No barcode or QR code detected')) {
                                console.debug('Scan error:', errorMessage);
                            }
                        }
                    );
                } catch (startError) {
                    const errMsg = startError?.message || String(startError);
                    console.error('Start camera error:', errMsg);
                    throw new Error(`Gagal memulai kamera: ${errMsg}`);
                }
            }

            // Wait for modal to close (either by user or by successful scan)
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
                status.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Gagal memuat kamera: ${errorMsg}`;
                status.style.color = 'var(--danger)';
            } else {
                showToast(errorMsg || 'Gagal memuat scanner', 'error');
            }
        }
    },
};
