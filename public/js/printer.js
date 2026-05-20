/**
 * Bluetooth Thermal Printer Utility (Web Bluetooth API / ESC-POS)
 * Optimized for 58mm thermal printers with proper Bluetooth connection handling
 * Dengan fallback ke AirPrint untuk iOS
 */
class ThermalPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.characteristic = null;
        this.storeSettings = null;
        this.lastConnectedDevice = this.loadLastDevice();
        this._autoReconnected = false;
        this._disconnectHandler = this.handleDisconnect.bind(this);
        this.isIOS = this._detectIOS();
        this.hasBluetoothAPI = this._detectBluetoothAPI();
        this.printerWidth = 58; // Default 58mm thermal printer
        this.connectionRetries = 0;
        this.maxConnectionRetries = 3;
    }

    _detectIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }

    _detectBluetoothAPI() {
        return !!(navigator.bluetooth && navigator.bluetooth.requestDevice);
    }

    /**
     * Check if platform supports Bluetooth printing
     */
    isBluetoothSupported() {
        return this.hasBluetoothAPI && !this.isIOS;
    }

    /**
     * Check if platform should use browser print (iOS/fallback)
     */
    isBrowserPrintSupported() {
        return this.isIOS || !this.hasBluetoothAPI;
    }

    handleDisconnect() {
        console.log('[ThermalPrinter] Device disconnected via event');
        this.characteristic = null;
        this.server = null;
        // Note: keep this.device so we can reconnect without dialog
    }

    loadLastDevice() {
        try {
            const stored = localStorage.getItem('alfarezmart_printer_device');
            return stored ? JSON.parse(stored) : null;
        } catch (e) {
            return null;
        }
    }

    saveLastDevice(device) {
        try {
            localStorage.setItem('alfarezmart_printer_device', JSON.stringify({
                id: device.id,
                name: device.name,
            }));
            this.lastConnectedDevice = { id: device.id, name: device.name };
        } catch (e) {
            console.error('Error saving device:', e);
        }
    }

    clearLastDevice() {
        localStorage.removeItem('alfarezmart_printer_device');
        this.lastConnectedDevice = null;
    }

    /** Check if printer is currently connected */
    isConnected() {
        return !!(this.device && this.device.gatt && this.device.gatt.connected && this.characteristic);
    }

    /** Check if we have a previously saved device */
    hasSavedDevice() {
        return !!this.lastConnectedDevice;
    }

    async loadStoreSettings() {
        try {
            const res = await fetch(`${BASE_URL}api/settings/receipt`);
            this.storeSettings = await res.json();
        } catch (e) {
            this.storeSettings = {
                store_name: 'AlfarezMart', store_address: '', store_phone: '',
                thermal_printer_width: 58, receipt_header: '', receipt_footer: '', store_logo: ''
            };
        }
        return this.storeSettings;
    }

    setStoreSettings(settings) {
        this.storeSettings = settings;
    }

    /**
     * Try silent auto-reconnect using getDevices() API (Chrome 85+)
     * This does NOT show a device chooser dialog
     */
    async tryAutoReconnect() {
        if (this.isConnected()) return true;
        if (!navigator.bluetooth) return false;

        // If we still have a device object from a previous session, try reconnecting directly
        if (this.device) {
            try {
                console.log('[ThermalPrinter] Reconnecting to existing device:', this.device.name);
                this.server = await this.device.gatt.connect();
                const services = await this.server.getPrimaryServices();
                for (const service of services) {
                    const characteristics = await service.getCharacteristics();
                    for (const char of characteristics) {
                        if (char.properties.write || char.properties.writeWithoutResponse) {
                            this.characteristic = char;
                            this._autoReconnected = true;
                            this.device.addEventListener('gattserverdisconnected', this._disconnectHandler);
                            console.log('[ThermalPrinter] Reconnected to:', this.device.name);
                            return true;
                        }
                    }
                }
            } catch (e) {
                console.log('[ThermalPrinter] Direct reconnect failed:', e.message);
                this.server = null;
                this.characteristic = null;
            }
        }

        if (!this.lastConnectedDevice) return false;

        try {
            // Use getDevices() for silent reconnection (no dialog)
            if (typeof navigator.bluetooth.getDevices === 'function') {
                const devices = await navigator.bluetooth.getDevices();
                const saved = this.lastConnectedDevice;
                const found = devices.find(d => d.id === saved.id || d.name === saved.name);

                if (found) {
                    console.log('[ThermalPrinter] Found saved device via getDevices():', found.name);
                    this.device = found;

                    // Listen for advertisement to reconnect
                    if (found.watchAdvertisements) {
                        const abortCtrl = new AbortController();
                        const timeout = setTimeout(() => abortCtrl.abort(), 5000);

                        try {
                            await found.watchAdvertisements({ signal: abortCtrl.signal });
                            // Wait briefly for advertisement
                            await new Promise(r => setTimeout(r, 1500));
                        } catch (e) {
                            // watchAdvertisements may not be supported
                        }
                        clearTimeout(timeout);
                    }

                    // Try GATT connect
                    this.server = await found.gatt.connect();
                    const services = await this.server.getPrimaryServices();

                    for (const service of services) {
                        const characteristics = await service.getCharacteristics();
                        for (const char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                this.characteristic = char;
                                this._autoReconnected = true;
                                found.addEventListener('gattserverdisconnected', this._disconnectHandler);
                                console.log('[ThermalPrinter] Auto-reconnected to:', found.name);
                                return true;
                            }
                        }
                    }
                }
            }
        } catch (e) {
            console.log('[ThermalPrinter] Auto-reconnect failed:', e.message);
            this.server = null;
            this.characteristic = null;
        }
        return false;
    }

    async connect(forceNew = false) {
        if (!navigator.bluetooth) {
            throw new Error('Web Bluetooth tidak didukung. Gunakan Chrome/Edge di Android.');
        }

        if (this.isIOS) {
            throw new Error('iOS tidak mendukung Bluetooth printing. Silakan gunakan Cetak Browser (AirPrint).');
        }

        // Already connected
        if (this.isConnected()) return true;

        // Try silent reconnect first if not forced to find new
        if (!forceNew && this.hasSavedDevice()) {
            try {
                console.log('[ThermalPrinter] Attempting silent reconnect before picker...');
                const autoConnected = await this.tryAutoReconnect();
                if (autoConnected) {
                    return true;
                }
                console.log('[ThermalPrinter] Silent reconnect failed, showing picker.');
            } catch (e) {
                console.log('[ThermalPrinter] Auto-reconnect exception:', e);
            }
        }

        try {
            // Show device picker - filter by common thermal printer services
            this.device = await navigator.bluetooth.requestDevice({
                filters: [
                    { services: ['000018f0-0000-1000-8000-00805f9b34fb'] },
                ],
                optionalServices: [
                    '000018f0-0000-1000-8000-00805f9b34fb',
                    'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                ],
            });

            this.server = await this.device.gatt.connect();
            const services = await this.server.getPrimaryServices();

            for (const service of services) {
                const characteristics = await service.getCharacteristics();
                for (const char of characteristics) {
                    if (char.properties.write || char.properties.writeWithoutResponse) {
                        this.characteristic = char;
                        this.saveLastDevice(this.device);
                        this.device.addEventListener('gattserverdisconnected', this._disconnectHandler);
                        console.log('[ThermalPrinter] Printer terhubung:', this.device.name);
                        return true;
                    }
                }
            }
            throw new Error('Printer tidak mendukung penulisan data.');
        } catch (e) {
            console.error('[ThermalPrinter] Connection error:', e);
            if (e.name === 'NotFoundError') {
                throw new Error('Tidak ada printer Bluetooth ditemukan. Pastikan printer sudah dinyalakan.');
            }
            throw e;
        }
    }

    disconnect() {
        if (this.device) {
            this.device.removeEventListener('gattserverdisconnected', this._disconnectHandler);
        }
        if (this.device?.gatt?.connected) {
            try { this.device.gatt.disconnect(); } catch (e) {}
        }
        this.device = null;
        this.server = null;
        this.characteristic = null;
    }

    /**
     * Soft disconnect: clears GATT connection but keeps device reference.
     * Allows reconnect on next print without showing a Bluetooth picker dialog.
     */
    softDisconnect() {
        if (this.device?.gatt?.connected) {
            try { this.device.gatt.disconnect(); } catch (e) {}
        }
        this.server = null;
        this.characteristic = null;
        // Keep this.device so tryAutoReconnect can use it directly
        console.log('[ThermalPrinter] Soft-disconnected. Device ref preserved for reconnect.');
    }

    /** Chars per line: 58mm ≈ 32 chars, 80mm ≈ 48 chars */
    getLineWidth() {
        const w = this.storeSettings?.thermal_printer_width || this.printerWidth || 58;
        return w >= 80 ? 48 : 32;
    }

    /**
     * Abbreviate common unit names for compact receipt output.
     * e.g. "Bungkus" → "bks", "Karton" → "ktn"
     */
    abbreviateUnit(unit) {
        const u = String(unit || 'pcs').toLowerCase().trim();
        const map = {
            'bungkus': 'bks', 'buah': 'bh', 'karton': 'ktn', 'dus': 'dus',
            'lusin': 'lsn', 'renceng': 'rnc', 'pcs': 'pcs', 'pak': 'pak',
            'keping': 'kpg', 'lembar': 'lbr', 'batang': 'btg', 'botol': 'btl',
            'sachet': 'sct', 'kaleng': 'klg', 'liter': 'ltr', 'mililiter': 'ml',
            'kilogram': 'kg', 'gram': 'gr', 'meter': 'mtr', 'roll': 'roll',
            'gross': 'grs', 'kodi': 'kdi', 'ikat': 'ikt', 'slop': 'slp',
            'box': 'box', 'pack': 'pack', 'set': 'set', 'unit': 'unit',
        };
        return map[u] || u.substring(0, 5);
    }

    padLine(left, right, width) {
        left = String(left || '');
        right = String(right || '');
        const spaces = width - left.length - right.length;
        if (spaces < 1) {
            return left.substring(0, width - right.length - 1) + ' ' + right;
        }
        return left + ' '.repeat(spaces) + right;
    }

    wrapText(text, width) {
        const words = String(text || '').split(/\s+/);
        const lines = [];
        let line = '';
        words.forEach(word => {
            if (!word) return;
            const test = line ? `${line} ${word}` : word;
            if (test.length <= width) {
                line = test;
            } else {
                if (line) lines.push(line);
                line = word.length > width ? word.substring(0, width) : word;
            }
        });
        if (line) lines.push(line);
        return lines.length ? lines : [''];
    }

    /** Wrap text that may contain literal newlines, respecting each line break */
    wrapMultiline(text, width) {
        const out = [];
        String(text || '').split(/\r?\n/).forEach(segment => {
            this.wrapText(segment.trimEnd(), width).forEach(l => out.push(l));
        });
        return out.length ? out : [''];
    }

    centerLine(text, width) {
        text = String(text || '');
        if (text.length >= width) return text.substring(0, width);
        const pad = Math.floor((width - text.length) / 2);
        return ' '.repeat(pad) + text;
    }

    separator(width, char = '-') {
        return char.repeat(width);
    }

    buildReceipt(cart, total, invoiceNumber, paymentMethod = 'Tunai') {
        const store = this.storeSettings || {};
        const width = this.getLineWidth();
        const ESC = '\x1B';
        const GS = '\x1D';
        const LF = '\x0A';

        let cmds = '';

        // Initialize printer
        cmds += ESC + '@';

        cmds += ESC + '!' + String.fromCharCode(0);

        cmds += ESC + 't' + String.fromCharCode(28);
        cmds += ESC + '3' + String.fromCharCode(30);

        // FAKTUR BELANJA + Store name (centered, bold)
        cmds += ESC + 'a\x01'; // ESC/POS hardware center align
        cmds += ESC + 'E\x01';
        cmds += 'FAKTUR BELANJA' + LF;
        cmds += (store.store_name || 'AlfarezMart').toUpperCase() + LF;
        cmds += ESC + 'E\x00';

        // Address + Phone (centered via ESC/POS hardware — no manual padding)
        if (store.store_address) {
            this.wrapMultiline(store.store_address, width).forEach(l => { cmds += l.trimEnd() + LF; });
        }
        if (store.store_phone) {
            cmds += store.store_phone + LF;
        }

        // Header from settings (centered via ESC/POS hardware)
        if (store.receipt_header) {
            this.wrapMultiline(store.receipt_header, width).forEach(line => { cmds += line.trimEnd() + LF; });
        }

        cmds += ESC + 'a\x00'; // Switch back to left align
        cmds += this.separator(width) + LF;

        // Invoice + Date
        const now = new Date();
        const tgl = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const jam = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        cmds += this.padLine('No:', invoiceNumber, width) + LF;
        cmds += this.padLine('Tgl:', `${tgl} ${jam}`, width) + LF;
        cmds += this.separator(width) + LF;

        // Items (compact: name then detail line)
        cart.forEach(item => {
            const name = String(item.print_name || item.name || 'Item').trim();
            this.wrapText(name, width).forEach(line => { cmds += line + LF; });

            const unitPrice = parseFloat(item.unit_price) || 0;
            const qty = item.quantity || 0;
            const unitAbbr = this.abbreviateUnit(item.unit_name || 'pcs');
            const itemTotal = parseFloat(item.total) || (qty * unitPrice);

            // Format: "  1bks x Rp17.000"  (space after x)
            const left = `  ${qty}${unitAbbr} x ${this._formatPrice(unitPrice)}`;
            cmds += this.padLine(left, this._formatPrice(itemTotal), width) + LF;
        });

        // TOTAL
        cmds += this.separator(width) + LF;
        cmds += ESC + 'E\x01';
        cmds += this.padLine('TOTAL', this._formatPrice(total), width) + LF;
        cmds += ESC + 'E\x00';

        // Footer from settings — use ESC/POS center align command (\x01)
        // Do NOT use centerLine() padding here as ESC a\x01 handles centering natively
        if (store.receipt_footer) {
            cmds += LF;
            cmds += ESC + 'a\x01'; // center align via ESC/POS command
            this.wrapMultiline(store.receipt_footer, width).forEach(line => {
                cmds += line.trimEnd() + LF; // Raw line, let printer center it
            });
            cmds += ESC + 'a\x00'; // back to left align
        }

        cmds += ESC + 'a\x00';

        // Feed paper and cut
        cmds += LF + LF + LF;
        
        // Paper cut command (partial cut for 58mm printers)
        // GS V m: m=0 (Full cut), m=1 (Partial cut)
        cmds += GS + 'V' + String.fromCharCode(1); // Partial cut
        cmds += LF;

        return cmds;
    }

    /** Helper to escape HTML in strings */
    _escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /** Format price without using formatRupiah (which may not encode well for thermal) */
    _formatPrice(num) {
        if (!num && num !== 0) return 'Rp0';
        return 'Rp' + Math.round(num).toLocaleString('id-ID');
    }

    _getReceiptDateTime() {
        const now = new Date();
        return {
            dateStr: now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
            timeStr: now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
        };
    }

    /** Build ESC/POS raster image bytes for Bluetooth Thermal Print */
    async _buildLogoRaster(imgSrc) {
        if (!imgSrc) return null;
        return new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = "Anonymous";
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Max width for 58mm printer is typically 384 dots. We use 200 for a decent sized logo.
                const maxWidth = 200;
                let width = img.width;
                let height = img.height;
                if (width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }
                // Width must be a multiple of 8 for ESC/POS raster
                width = Math.floor(width / 8) * 8;
                if (width === 0) return resolve(null);
                
                canvas.width = width;
                canvas.height = height;
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);
                
                const imgData = ctx.getImageData(0, 0, width, height);
                const data = imgData.data;
                const bytes = new Uint8Array((width / 8) * height);
                
                for (let y = 0; y < height; y++) {
                    for (let x = 0; x < width; x++) {
                        const idx = (y * width + x) * 4;
                        const r = data[idx];
                        const g = data[idx+1];
                        const b = data[idx+2];
                        const a = data[idx+3];
                        
                        // Monochrome conversion
                        const isBlack = (a > 128) && (r * 0.299 + g * 0.587 + b * 0.114 < 128);
                        if (isBlack) {
                            bytes[(y * (width / 8)) + Math.floor(x / 8)] |= (1 << (7 - (x % 8)));
                        }
                    }
                }
                
                // GS v 0 m xL xH yL yH [data]
                const xL = (width / 8) & 0xFF;
                const xH = ((width / 8) >> 8) & 0xFF;
                const yL = height & 0xFF;
                const yH = (height >> 8) & 0xFF;
                
                const cmdPrefix = new Uint8Array([0x1D, 0x76, 0x30, 0x00, xL, xH, yL, yH]);
                
                const result = new Uint8Array(cmdPrefix.length + bytes.length);
                result.set(cmdPrefix, 0);
                result.set(bytes, cmdPrefix.length);
                
                resolve(result);
            };
            img.onerror = () => resolve(null);
            img.src = imgSrc;
        });
    }

    async print(cart, total, invoiceNumber, options = {}) {
        if (!this.characteristic) {
            throw new Error('Printer belum terhubung. Hubungkan printer terlebih dahulu.');
        }

        await this.loadStoreSettings();
        if (options.storeSettings) {
            this.setStoreSettings(options.storeSettings);
        }

        // Debug: Log cart data
        console.log('[ThermalPrinter] Cart data:', JSON.stringify(cart, null, 2));
        console.log('[ThermalPrinter] Total:', total);
        console.log('[ThermalPrinter] Invoice:', invoiceNumber);
        console.log('[ThermalPrinter] Printer Width:', this.getLineWidth(), 'chars for', (this.storeSettings?.thermal_printer_width || 58) + 'mm');

        const enrichedCart = cart.map(item => ({
            ...item,
            print_name: item.print_name || item.name || 'Item',
        }));

        const data = this.buildReceipt(
            enrichedCart,
            total,
            invoiceNumber,
            options.paymentMethod || 'Tunai'
        );

        console.log('[ThermalPrinter] Receipt output length:', data.length, 'bytes');
        console.log('[ThermalPrinter] Receipt preview (first 200 chars):', data.substring(0, 200));

        const encoder = new TextEncoder();
        let payload = encoder.encode(data);

        // Inject logo if available
        if (this.storeSettings?.store_logo) {
            try {
                const logoBytes = await this._buildLogoRaster(this.storeSettings.store_logo);
                if (logoBytes && logoBytes.length > 0) {
                    // Prepend ESC @ and ESC a 1 (center align), then logo, then the rest
                    const prefix = encoder.encode('\x1B@\x1Ba\x01');
                    // Skip the very first \x1B@ (2 chars) in the `data` text
                    const restPayload = payload.slice(2);
                    
                    const combined = new Uint8Array(prefix.length + logoBytes.length + restPayload.length);
                    combined.set(prefix, 0);
                    combined.set(logoBytes, prefix.length);
                    combined.set(restPayload, prefix.length + logoBytes.length);
                    payload = combined;
                }
            } catch(e) {
                console.error('[ThermalPrinter] Logo generation error:', e);
            }
        }

        console.log('[ThermalPrinter] Encoded payload length:', payload.length, 'bytes');

        // Optimal chunk size for Bluetooth thermal printers (20-128 bytes)
        const CHUNK_SIZE = 64;
        let sentBytes = 0;

        for (let i = 0; i < payload.length; i += CHUNK_SIZE) {
            const chunk = payload.slice(i, i + CHUNK_SIZE);
            if (chunk.length === 0) continue;

            try {
                // Use writeValueWithoutResponse for faster, streaming-like behavior
                if (this.characteristic.properties.writeWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                    console.log(`[ThermalPrinter] Sent chunk ${Math.floor(i/CHUNK_SIZE) + 1}: ${chunk.length} bytes`);
                } else if (this.characteristic.properties.write) {
                    await this.characteristic.writeValue(chunk);
                    console.log(`[ThermalPrinter] Sent (with response) chunk ${Math.floor(i/CHUNK_SIZE) + 1}: ${chunk.length} bytes`);
                } else {
                    throw new Error('Printer characteristic tidak support write operations');
                }
            } catch (e) {
                console.error('[ThermalPrinter] Write chunk error:', e);
                this.handleDisconnect();
                throw new Error(`Gagal mengirim data ke printer: ${e.message}`);
            }
            
            sentBytes += chunk.length;
            
            // Small delay between chunks to allow printer processing
            await new Promise(r => setTimeout(r, 40));
        }

        console.log(`[ThermalPrinter] Total bytes sent: ${sentBytes} / ${payload.length}`);
        
        // Wait for printer to finish processing
        await new Promise(r => setTimeout(r, 1000));
        
        // Soft-disconnect: release GATT but keep device reference for next print
        // This prevents OS Bluetooth stack from hanging on subsequent prints
        this.softDisconnect();
        console.log('[ThermalPrinter] Soft-disconnected after print. Ready for next print.');
    }

    /**
     * Fallback untuk iOS / Safari dimana Web Bluetooth tidak didukung.
     * Membuat receipt HTML dan memanggil window.print() untuk AirPrint.
     */
    async printBrowser(cart, total, invoiceNumber, options = {}) {
        await this.loadStoreSettings();
        if (options.storeSettings) {
            this.setStoreSettings(options.storeSettings);
        }

        const store = this.storeSettings || {};
        const logoHtml = store.store_logo
            ? `<div class="center" style="margin-bottom:4px;"><img src="${store.store_logo}" style="max-width:50px;max-height:50px;object-fit:contain;"></div>`
            : `<div class="center" style="margin-bottom:4px;"><img src="${typeof BASE_URL !== 'undefined' ? BASE_URL : '/'}public/images/Icon.png" style="max-width:40px;max-height:40px;object-fit:contain;" onerror="this.style.display='none'"></div>`;

        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit' });
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        let itemsHtml = '';
        cart.forEach(item => {
            const name = item.print_name || item.name || 'Item';
            const unitPrice = parseFloat(item.unit_price) || 0;
            const itemTotal = parseFloat(item.total) || 0;
            const unitAbbr = this.abbreviateUnit(item.unit_name || 'pcs');
            itemsHtml += `<tr><td colspan="3" class="item-name">${this._escapeHtml(name)}</td></tr>`;
            itemsHtml += `<tr class="item-detail">
                <td>${item.quantity} ${this._escapeHtml(unitAbbr)}</td>
                <td>x ${this._formatPrice(unitPrice)}</td>
                <td class="right">${this._formatPrice(itemTotal)}</td>
            </tr>`;
        });

        let html = `
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Struk Penjualan</title>
                <style>
                    @page { margin: 0; size: 80mm auto; }
                    body { font-family: 'Courier New', monospace; padding: 6px 8px; width: 76mm; margin: 0 auto; color: black; font-size: 11px; line-height: 1.4; background: white; text-align: center; }
                    .center { text-align: center; }
                    .left { text-align: left; }
                    .bold { font-weight: bold; }
                    .title { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
                    .store { font-size: 13px; font-weight: bold; margin-bottom: 1px; }
                    .divider { border: none; border-top: 1px dashed #000; margin: 4px 0; }
                    table { width: 100%; border-collapse: collapse; }
                    td { vertical-align: top; font-size: 11px; padding: 1px 0; }
                    .right { text-align: right; }
                    .item-name { font-weight: 600; padding-bottom: 1px; }
                    .item-detail { color: #333; }
                    .total-row td { font-size: 13px; font-weight: bold; padding-top: 4px; }
                    @media print { body { padding: 0; margin: 0; } .no-print { display: none !important; } }
                </style>
            </head>
            <body>
                <div class="center title">FAKTUR BELANJA</div>
                ${logoHtml}
                <div class="center store">${this._escapeHtml(store.store_name || 'AlfarezMart').toUpperCase()}</div>
                ${store.store_address ? `<div class="center" style="font-size:10px;white-space:pre-line;">${this._escapeHtml(store.store_address)}</div>` : ''}
                ${store.store_phone ? `<div class="center" style="font-size:10px;">${this._escapeHtml(store.store_phone)}</div>` : ''}
                ${store.receipt_header ? `<div class="center" style="font-size:10px;white-space:pre-line;">${this._escapeHtml(store.receipt_header)}</div>` : ''}
                <hr class="divider">
                <table class="left" style="text-align:left;">
                    <tr><td>No</td><td>:</td><td style="font-family:monospace;font-size:10px;">${this._escapeHtml(invoiceNumber)}</td></tr>
                    <tr><td>Tgl</td><td>:</td><td>${dateStr} ${timeStr}</td></tr>
                </table>
                <hr class="divider">
                <table style="text-align:left;">${itemsHtml}</table>
                <hr class="divider">
                <table class="total-row"><tr><td>TOTAL</td><td class="right">${this._formatPrice(total)}</td></tr></table>
                ${store.receipt_footer ? `<hr class="divider"><div class="center" style="font-size:10px;white-space:pre-line;">${this._escapeHtml(store.receipt_footer)}</div>` : ''}
                <div class="no-print" style="margin-top:16px;text-align:center;padding:16px;">
                    <button onclick="window.print()" style="padding:10px 24px;font-size:14px;cursor:pointer;margin:0 4px;background:#007bff;color:white;border:none;border-radius:4px;">Cetak</button>
                    <button onclick="window.close()" style="padding:10px 24px;font-size:14px;cursor:pointer;margin:0 4px;background:#6c757d;color:white;border:none;border-radius:4px;">Tutup</button>
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() { window.print(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `;

        const printWin = window.open('', '_blank');
        if (!printWin) {
            throw new Error("Harap izinkan Pop-up untuk mencetak struk.");
        }
        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();
    }
}

const thermalPrinter = new ThermalPrinter();
window.thermalPrinter = thermalPrinter;
