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

        async _loadScannerLib() {
            if (window.ZXing) return true;

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
                    console.warn('[BarcodeScanner] CDN failed:', url, e.message);
                }
            }
            throw new Error('Gagal memuat library ZXing. Periksa koneksi internet.');
        },

        stop() {
            this.isScanning = false;
            if (this.html5Qrcode) {
                try {
                    this.html5Qrcode.reset();
                } catch(e) {}
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
                    ring.offsetHeight; // trigger reflow
                    ring.style.animation = 'pulse-glow 0.6s ease-out';
                    setTimeout(() => { ring.style.display = 'none'; }, 600);
                }
                const video = document.getElementById('barcode-video-element');
                if (video && video.srcObject) {
                    const track = video.srcObject.getVideoTracks()[0];
                    if (track && track.applyConstraints) {
                        try {
                            // Tickle the hardware to trigger autofocus
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
                    <div id="barcode-scanner-video-container" onclick="window.triggerScannerFocus()" style="width:100%; max-width:400px; height:300px; margin:0 auto; border-radius:var(--radius-md); overflow:hidden; background:#000; position:relative; cursor:pointer;">
                        <video id="barcode-video-element" style="width:100%; height:100%; object-fit:cover;"></video>
                        <!-- Larger targeting box for tilted barcodes -->
                        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:90%; height:65%; border:2px solid rgba(45, 211, 111, 0.6); border-radius:12px; box-shadow:0 0 0 4000px rgba(0,0,0,0.3); pointer-events:none;"></div>
                        <!-- Tap to focus ring -->
                        <div id="scannerFocusRing" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:70px; height:70px; border:2px solid #fff; border-radius:50%; pointer-events:none; box-shadow: 0 0 10px rgba(255,255,255,0.5);"></div>
                        <div style="position:absolute; bottom:12px; left:0; width:100%; text-align:center; pointer-events:none;">
                            <span style="background:rgba(0,0,0,0.5); color:#fff; font-size:10px; padding:4px 8px; border-radius:12px; backdrop-filter:blur(4px);">Ketuk area kamera untuk fokus</span>
                        </div>
                    </div>
                    <p id="scanStatus" style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:12px; margin-bottom:8px;">
                        <i class="bi bi-camera-video"></i> Memuat kamera ZXing tingkat lanjut...
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
     * Public API: Open camera scanner modal and scan barcode into input element
     */
    async scanBarcode(inputEl, onScanned) {
        try {
            const modalPromise = this._showScannerModal(inputEl, onScanned);
            
            await this.scanner._loadScannerLib();
            
            if (!window.ZXing) {
                throw new Error('Library ZXing gagal dimuat');
            }
            
            await new Promise(r => setTimeout(r, 300));
            
            const status = document.getElementById('scanStatus');
            const success = document.getElementById('scanSuccess');
            const successText = document.getElementById('scanSuccessText');

            if (status) {
                status.innerHTML = '<i class="bi bi-camera-video"></i> Arahkan kamera ke barcode...';
            }

            // Configure ZXing Hints for maximum sensitivity
            const hints = new Map();
            hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                ZXing.BarcodeFormat.CODE_128,
                ZXing.BarcodeFormat.EAN_13,
                ZXing.BarcodeFormat.EAN_8,
                ZXing.BarcodeFormat.CODE_39,
                ZXing.BarcodeFormat.ITF,
                ZXing.BarcodeFormat.UPC_A,
                ZXing.BarcodeFormat.UPC_E
            ]);
            // ENABLE TRY_HARDER: Forces deep analysis of the image for blurry/folded barcodes
            hints.set(ZXing.DecodeHintType.TRY_HARDER, true);

            const codeReader = new ZXing.BrowserMultiFormatReader(hints);
            this.scanner.html5Qrcode = codeReader;
            this.scanner.isScanning = true;

            const videoConstraints = {
                video: {
                    facingMode: "environment",
                    // Soft request for HD resolution to massively improve detection of very small barcodes
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            };

            try {
                codeReader.decodeFromConstraints(videoConstraints, 'barcode-video-element', (result, err) => {
                    if (result && this.scanner.isScanning) {
                        this.scanner.isScanning = false;
                        const decodedText = result.getText();
                        
                        inputEl.value = decodedText;
                        inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                        
                        if (status) status.style.display = 'none';
                        if (success) {
                            success.style.display = 'flex';
                            if (successText) successText.textContent = `Terdeteksi: ${decodedText}`;
                        }
                        
                        showToast(`Barcode terdeteksi: ${decodedText}`, 'success');
                        
                        // Stop reader
                        setTimeout(() => {
                            this.scanner.stop();
                            AppModal.close('scanned');
                            if (typeof onScanned === 'function') onScanned(decodedText);
                        }, 800);
                    }
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.debug("ZXing error:", err);
                    }
                });
            } catch (startError) {
                const errMsg = startError?.message || String(startError);
                console.error('Start camera error:', errMsg);
                throw new Error(`Gagal memulai kamera: ${errMsg}`);
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
                status.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Gagal memuat kamera: ${errorMsg}`;
                status.style.color = 'var(--danger)';
            } else {
                showToast(errorMsg || 'Gagal memuat scanner', 'error');
            }
        }
    },
};
