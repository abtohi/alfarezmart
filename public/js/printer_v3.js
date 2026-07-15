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
     * Helper to safely connect and find characteristic with retries and specific UUID querying
     * to prevent GATT Server crashes on cheap thermal printers (like RPP02N)
     */
    async _connectAndFindCharacteristic(device) {
        let server = null;
        let characteristic = null;
        
        let retries = 3;
        while (retries > 0) {
            try {
                server = await device.gatt.connect();
                // Tunggu 1000ms agar GATT Server benar-benar stabil sebelum meminta services
                await new Promise(r => setTimeout(r, 1000));
                
                // Coba ambil spesifik service UUID alih-alih mengambil semua service (yang sering membuat RPP02N crash)
                const uuidsToTry = [
                    '000018f0-0000-1000-8000-00805f9b34fb',
                    'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455'
                ];
                
                for (const uuid of uuidsToTry) {
                    try {
                        const service = await server.getPrimaryService(uuid);
                        const characteristics = await service.getCharacteristics();
                        for (const char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                characteristic = char;
                                break;
                            }
                        }
                    } catch (e) {
                        // ignore error if this specific service doesn't exist
                    }
                    if (characteristic) break;
                }
                
                if (characteristic) {
                    return { server, characteristic };
                } else {
                    // Fallback to old method just in case
                    const services = await server.getPrimaryServices();
                    for (const service of services) {
                        const characteristics = await service.getCharacteristics();
                        for (const char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                characteristic = char;
                                break;
                            }
                        }
                        if (characteristic) break;
                    }
                    if (characteristic) return { server, characteristic };
                    throw new Error('Printer tidak mendukung penulisan data.');
                }
            } catch (err) {
                console.warn(`[ThermalPrinter] GATT connect attempt failed, retries left: ${retries - 1}`, err);
                retries--;
                if (retries === 0) throw err;
                await new Promise(r => setTimeout(r, 200));
            }
        }
        throw new Error('Gagal menghubungkan ke GATT Server setelah retries.');
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
                const result = await this._connectAndFindCharacteristic(this.device);
                this.server = result.server;
                this.characteristic = result.characteristic;
                this._autoReconnected = true;
                this.device.addEventListener('gattserverdisconnected', this._disconnectHandler);
                console.log('[ThermalPrinter] Reconnected to:', this.device.name);
                return true;
            } catch (e) {
                console.warn(`[ThermalPrinter] Direct reconnect attempt failed:`, e);
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

                    // Try GATT connect with retries
                    try {
                        const result = await this._connectAndFindCharacteristic(found);
                        this.server = result.server;
                        this.characteristic = result.characteristic;
                        this._autoReconnected = true;
                        found.addEventListener('gattserverdisconnected', this._disconnectHandler);
                        console.log('[ThermalPrinter] Auto-reconnected to:', found.name);
                        return true;
                    } catch (err) {
                        console.warn(`[ThermalPrinter] Auto-reconnect GATT attempt failed:`, err);
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

            const result = await this._connectAndFindCharacteristic(this.device);
            this.server = result.server;
            this.characteristic = result.characteristic;
            this.saveLastDevice(this.device);
            this.device.addEventListener('gattserverdisconnected', this._disconnectHandler);
            console.log('[ThermalPrinter] Printer terhubung:', this.device.name);
            return true;
        } catch (e) {
            console.error('[ThermalPrinter] Connection error:', e);
            if (e.name === 'NotFoundError') {
                throw new Error('Tidak ada printer Bluetooth ditemukan atau dibatalkan pengguna.');
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
            'setengah': 'stg', 'seperempat': '1/4', 'papan': 'ppn', 'bungkusan': 'bks'
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

    buildReceipt(cart, total, invoiceNumber, paymentMethod = 'Tunai', options = {}) {
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

            let unitPrice = parseFloat(item.unit_price) || 0;
            const qty = item.quantity || 0;
            const unitAbbr = item.unit_abbr || this.abbreviateUnit(item.unit_name || 'pcs');
            let itemTotal = parseFloat(item.total) || (qty * unitPrice);
            let discountLine = '';

            if (options.saleMode === 'mix' && options.mixInfo && options.mixInfo.items) {
                const mixItemInfo = options.mixInfo.items.find(x => x.name === name);
                if (mixItemInfo && mixItemInfo.discount > 0) {
                    itemTotal = mixItemInfo.retailTotal;
                    unitPrice = mixItemInfo.retailTotal / qty;
                    discountLine = this.padLine('', `Diskon: -${this._formatPrice(mixItemInfo.discount)}`, width) + LF;
                }
            }

            // Format: "  1bks x Rp17.000"  (space after x)
            const left = `  ${qty}${unitAbbr} x ${this._formatPrice(unitPrice)}`;
            cmds += this.padLine(left, this._formatPrice(itemTotal), width) + LF;
            if (discountLine) cmds += discountLine;
            
            // Add a slight gap between products (print and feed 12 dots ≈ 1.5mm)
            cmds += ESC + 'J' + String.fromCharCode(12);
        });

        // TOTAL
        cmds += this.separator(width) + LF;
        cmds += ESC + 'E\x01';
        if (options.saleMode === 'mix' && options.mixInfo && options.mixInfo.totalDiscount > 0) {
            const subtotal = total + options.mixInfo.totalDiscount;
            cmds += ESC + 'E\x00'; // Normal weight for subtotal
            cmds += this.padLine('Subtotal', this._formatPrice(subtotal), width) + LF;
            cmds += this.padLine('Total Diskon', `-${this._formatPrice(options.mixInfo.totalDiscount)}`, width) + LF;
            cmds += ESC + 'E\x01'; // Bold for grand total
            cmds += this.padLine('TOTAL BAYAR', this._formatPrice(total), width) + LF;
        } else {
            cmds += this.padLine('TOTAL', this._formatPrice(total), width) + LF;
        }
        cmds += ESC + 'E\x00';

        // Footer from settings — use ESC/POS center align command (\x01)
        // Do NOT use centerLine() padding here as ESC a\x01 handles centering natively
        let footerText = store.receipt_footer || '';
        if (options.saleMode === 'mix' && options.mixInfo && options.mixInfo.totalDiscount > 0) {
            footerText += `\n\nTerima kasih telah berbelanja,\nAnda menghemat ${this._formatPrice(options.mixInfo.totalDiscount)} hari ini! 🎉`;
        }

        if (footerText) {
            cmds += LF;
            cmds += ESC + 'a\x01'; // center align via ESC/POS command
            this.wrapMultiline(footerText, width).forEach(line => {
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

    async printDigitalReceipt(transaction, options = {}, storeSettings = null) {
        if (!this.characteristic) {
            throw new Error('Printer belum terhubung. Hubungkan printer terlebih dahulu.');
        }

        if (storeSettings) {
            this.storeSettings = storeSettings;
        } else {
            await this.loadStoreSettings();
        }

        const dateStr = transaction.created_at || new Date().toISOString().slice(0, 19).replace('T', ' ');
        const storeName = this.storeSettings?.store_name || 'AlfarezMart';
        const storeAddr = this.storeSettings?.store_address || 'Toko Kami';
        const width = this.getLineWidth();

        // Helper functions
        const padRight = (str, len) => {
            if (!str) return ' '.repeat(len);
            const s = String(str);
            return s.length >= len ? s.substring(0, len) : s + ' '.repeat(len - s.length);
        };
        
        const padLeft = (str, len) => {
            if (!str) return ' '.repeat(len);
            const s = String(str);
            return s.length >= len ? s.substring(0, len) : ' '.repeat(len - s.length) + s;
        };

        const detectProductTypeStr = (d) => {
            const name  = (d.product_name  || '').toLowerCase();
            const sku   = (d.buyer_sku_code || '').toLowerCase();
            const sn    = (d.sn            || '');
            const hasSN = sn && sn !== '-';

            if (name.includes('pln') || sku.includes('pln') || (hasSN && sn.split('/').length >= 4)) return 'pln';
            if (name.includes('dana') || sku.includes('dana') || name.includes('dnid')) return 'dana';
            if (name.includes('shopeepay') || name.includes('shopee') || sku.includes('shopee') || sku.includes('spay')) return 'shopee';
            if (name.includes('gopay') || sku.includes('gopay') || sku.includes('gpay')) return 'gopay';
            if (name.includes('ovo') || sku.includes('ovo')) return 'ovo';
            if (name.includes('link aja') || name.includes('linkaja') || sku.includes('linkaja')) return 'linkaja';
            if (name.includes('bpjs') || sku.includes('bpjs')) return 'bpjs';
            if (name.includes('pdam') || sku.includes('pdam')) return 'pdam';
            if (name.includes('telkom') || name.includes('indihome') || sku.includes('telkom')) return 'telkom';
            if (name.includes('paket data') || name.includes('paket internet') || sku.includes('data')) return 'paket';
            if (name.includes('pulsa') || name.includes('prabayar') || /^(xl|tsel|isat|axis|tri|hnet|smartfren)/i.test(sku)) return 'pulsa';
            if (name.includes('voucher') || name.includes('game')) return 'voucher';
            return 'other';
        };

        const getProductLabelInfo = (type) => {
            const themes = {
                pln:     'PLN PREPAID',
                dana:    'DANA',
                shopee:  'SHOPEEPAY',
                gopay:   'GOPAY',
                ovo:     'OVO',
                linkaja: 'LINKAJA',
                bpjs:    'BPJS KESEHATAN',
                pdam:    'PDAM AIR',
                telkom:  'TELKOM / INDIHOME',
                paket:   'PAKET DATA',
                pulsa:   'PULSA',
                voucher: 'VOUCHER GAME',
                other:   'PRODUK DIGITAL',
            };
            return themes[type] || themes.other;
        };

        const typeStr = detectProductTypeStr(transaction);
        const labelInfo = getProductLabelInfo(typeStr);
        const isPln = typeStr === 'pln';

        // Build string manually
        let data = '';

        // Header
        data += '\x1B@'; // Init
        data += '\x1Ba\x01'; // Align Center
        data += '\x1BE\x01'; // Bold On
        data += storeName.toUpperCase() + '\n';
        data += '\x1BE\x00'; // Bold Off
        data += 'Pusat Pembayaran\nProduk Digital\n';
        data += '-'.repeat(width) + '\n';

        // Details
        data += '\x1Ba\x00'; // Align Left
        data += '\x1B!\x01'; // Font B
        let d = new Date(dateStr);
        let formattedDate = dateStr;
        if (!isNaN(d.getTime())) {
            formattedDate = ('0'+d.getDate()).slice(-2)+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+d.getFullYear()+' '+('0'+d.getHours()).slice(-2)+':'+('0'+d.getMinutes()).slice(-2);
        }
        
        data += padRight('TGL', 4) + ': ' + formattedDate + '\n';
        data += padRight('REF', 4) + ': ' + (transaction.ref_id || '-') + '\n';
        data += padRight('TRX', 4) + ': ' + (transaction.digiflazz_trx_id || transaction.trx_id || '-') + '\n';
        data += '\x1B!\x00'; // Font A
        data += '-'.repeat(width) + '\n';

        // Product Category Badge
        data += '\x1Ba\x01'; // Align Center
        data += '\x1BE\x01'; // Bold On
        data += `=== ${labelInfo} ===\n`;
        data += 'TRANSAKSI BERHASIL\n';
        data += '\x1BE\x00'; // Bold Off
        data += '-'.repeat(width) + '\n';

        // Info Body
        data += '\x1Ba\x00'; // Align Left
        data += '\x1BE\x01'; // Bold On
        data += 'PRODUK :\n';
        data += '\x1BE\x00'; // Bold Off
        data += transaction.product_name + '\n\n';

        data += '\x1BE\x01'; // Bold On
        if (isPln) {
            data += 'ID PELANGGAN :\n';
        } else {
            data += 'TUJUAN :\n';
        }
        data += '\x1BE\x00'; // Bold Off
        data += transaction.customer_no + '\n';

        // Additional Customer / Account Info
        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        let hasNamePrinted = false;

        if (transaction.sn && transaction.sn !== '-') {
            if (isPln) {
                if (transaction.sn.includes('/')) {
                    const parts = transaction.sn.split('/');
                    if (parts.length >= 4) {
                        const plnName = parts[1] || '';
                        const plnTarifPower = parts.length > 4 ? `${parts[2]}/${parts[3]}` : parts[2];
                        const plnKwh = parts.length > 4 ? parts[4] : parts[3];
                        
                        if (plnName) data += plnName + '\n';
                        data += `${plnTarifPower} - ${plnKwh} kWh\n`;
                        hasNamePrinted = true;
                    }
                }
            } else if (ewallets.includes(typeStr)) {
                if (transaction.sn.toUpperCase().includes('NAMA:') && transaction.sn.toUpperCase().includes('REFF:')) {
                    const namaMatch = transaction.sn.match(/NAMA:\s*([^,]+)/i);
                    if (namaMatch && namaMatch[1]) {
                        data += namaMatch[1].trim() + '\n';
                        hasNamePrinted = true;
                    }
                }
            }
        }
        
        if (!hasNamePrinted && transaction.customer_name && !ewallets.includes(typeStr) && !isPln) {
            data += transaction.customer_name + '\n';
        }

        data += '\n'; // spacing

        // SN / REF / TOKEN
        if (transaction.sn && transaction.sn !== '-') {
            if (isPln) {
                data += '\x1Ba\x01'; // Align Center
                data += '\x1BE\x01'; // Bold On
                data += 'TOKEN PLN :\n';
                
                const parts = transaction.sn.split('/');
                const snValue = parts[0];
                let cleanToken = snValue.replace(/[^0-9]/g, '');
                let tokenOutput = snValue;
                if (cleanToken.length === 20) {
                    let t1 = cleanToken.substring(0, 4) + ' ' + cleanToken.substring(4, 8) + ' ' + cleanToken.substring(8, 12);
                    let t2 = cleanToken.substring(12, 16) + ' ' + cleanToken.substring(16, 20);
                    tokenOutput = t1 + '\n' + t2;
                }
                
                data += '\x1B!\x30'; // Double height and width
                data += tokenOutput + '\n';
                data += '\x1B!\x00'; // Reset size
                data += '\x1BE\x00'; // Bold Off
                data += '\x1Ba\x00'; // Align Left
            } else {
                let reffValue = transaction.sn;
                if (transaction.sn.toUpperCase().includes('NAMA:') && transaction.sn.toUpperCase().includes('REFF:')) {
                    const reffMatch = transaction.sn.match(/REFF:\s*([^,]+)/i);
                    if (reffMatch && reffMatch[1]) {
                        reffValue = reffMatch[1].trim();
                    }
                }
                
                data += '\x1BE\x01'; // Bold On
                data += 'SN / REF :\n';
                data += '\x1BE\x00'; // Bold Off
                data += reffValue + '\n';
            }
        }
        
        data += '-'.repeat(width) + '\n';
        
        // Total row
        const price = parseInt(transaction.sell_price).toLocaleString('id-ID');
        const totalText = `Rp ${price}`;
        const totalLabel = 'TOTAL BAYAR';
        
        data += '\x1BE\x01'; // Bold On
        data += '\x1B!\x10'; // Double height
        
        const spaceCount = width - totalLabel.length - totalText.length;
        if (spaceCount > 0) {
            data += totalLabel + ' '.repeat(spaceCount) + totalText + '\n';
        } else {
            data += totalLabel + '\n' + padLeft(totalText, width) + '\n';
        }
        data += '\x1B!\x00'; // Reset size
        data += '\x1BE\x00'; // Bold Off
        data += '-'.repeat(width) + '\n';
        
        // Footer
        data += '\x1Ba\x01'; // Align Center
        data += 'Struk ini merupakan bukti\n';
        data += 'transaksi yang sah dan resmi\n';
        data += `dari ${storeName}.\n\n`;
        
        data += 'Terima kasih telah berbelanja\n';
        data += '= Semoga Berkah =\n\n\n\n';

        const encoder = new TextEncoder();
        let payload = encoder.encode(data);

        // Inject logo if available
        if (this.storeSettings?.store_logo) {
            try {
                const logoBytes = await this._buildLogoRaster(this.storeSettings.store_logo);
                if (logoBytes && logoBytes.length > 0) {
                    const prefix = encoder.encode('\x1B@\x1Ba\x01');
                    const restPayload = payload.slice(2); // Skip the first \x1B@
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

        // Send payload in chunks
        const CHUNK_SIZE = 64;
        for (let i = 0; i < payload.length; i += CHUNK_SIZE) {
            const chunk = payload.slice(i, i + CHUNK_SIZE);
            if (chunk.length === 0) continue;

            try {
                if (this.characteristic.properties.writeWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                } else if (this.characteristic.properties.write) {
                    await this.characteristic.writeValue(chunk);
                } else {
                    throw new Error('Printer characteristic tidak support write operations');
                }
                // Small delay to prevent buffer overflow
                await new Promise(r => setTimeout(r, 20));
            } catch (err) {
                console.error('[ThermalPrinter] Chunk write failed', err);
                throw err;
            }
        }
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
            options.paymentMethod || 'Tunai',
            options
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
            const unitAbbr = item.unit_abbr || this.abbreviateUnit(item.unit_name || 'pcs');
            
            let displayUnitPrice = parseFloat(item.unit_price) || 0;
            let displayItemTotal = parseFloat(item.total) || 0;
            let discountRowHtml = '';

            if (options.saleMode === 'mix' && options.mixInfo && options.mixInfo.items) {
                const mixItemInfo = options.mixInfo.items.find(x => x.name === name);
                if (mixItemInfo && mixItemInfo.discount > 0) {
                    displayItemTotal = mixItemInfo.retailTotal;
                    displayUnitPrice = mixItemInfo.retailTotal / item.quantity;
                    discountRowHtml = `<tr class="item-detail"><td colspan="3" class="right" style="padding-bottom:6px; font-size:10px; color:#333;">Diskon: -${this._formatPrice(mixItemInfo.discount)}</td></tr>`;
                }
            }

            itemsHtml += `<tr><td colspan="3" class="item-name">${this._escapeHtml(name)}</td></tr>`;
            itemsHtml += `<tr class="item-detail" style="${discountRowHtml ? '' : 'padding-bottom: 6px;'}">
                <td>${item.quantity} ${this._escapeHtml(unitAbbr)}</td>
                <td>x ${this._formatPrice(displayUnitPrice)}</td>
                <td class="right">${this._formatPrice(displayItemTotal)}</td>
            </tr>`;
            itemsHtml += discountRowHtml;
        });

        let totalsHtml = '';
        if (options.saleMode === 'mix' && options.mixInfo && options.mixInfo.totalDiscount > 0) {
            const subtotal = total + options.mixInfo.totalDiscount;
            totalsHtml = `
                <table class="total-row">
                    <tr style="font-size:11px;font-weight:normal;"><td>Subtotal</td><td class="right">${this._formatPrice(subtotal)}</td></tr>
                    <tr style="font-size:11px;font-weight:normal;"><td>Total Diskon</td><td class="right">-${this._formatPrice(options.mixInfo.totalDiscount)}</td></tr>
                    <tr><td style="padding-top:4px;">TOTAL BAYAR</td><td class="right" style="padding-top:4px;">${this._formatPrice(total)}</td></tr>
                </table>
            `;
        } else {
            totalsHtml = `<table class="total-row"><tr><td>TOTAL</td><td class="right">${this._formatPrice(total)}</td></tr></table>`;
        }

        let footerHtml = store.receipt_footer ? `<hr class="divider"><div class="center" style="font-size:10px;white-space:pre-line;">${this._escapeHtml(store.receipt_footer)}</div>` : '';
        if (options.saleMode === 'mix' && options.mixInfo && options.mixInfo.totalDiscount > 0) {
            footerHtml += `<div class="center" style="font-size:11px;font-weight:bold;margin-top:8px;">Terima kasih telah berbelanja di Alfarez Mart, Anda telah menghemat belanjaan sebesar ${this._formatPrice(options.mixInfo.totalDiscount)} 🎉</div>`;
        }

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
                ${totalsHtml}
                ${footerHtml}
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

/**
 * Global app-styled chooser untuk pemilihan printer thermal.
 * Mengganti dialog Web Bluetooth mentah dengan UI konsisten ala AlfarezMart.
 *
 * Pemakaian:
 *   const ok = await openPrinterChooser(thermalPrinter);
 *   if (ok) showToast('Printer terhubung: ' + thermalPrinter.device?.name, 'success');
 *
 * @returns {Promise<boolean>} true bila printer berhasil terhubung
 */
window.openPrinterChooser = function (tp) {
    return new Promise((resolve) => {
        if (!tp) { resolve(false); return; }

        // Cleanup existing overlay (kalau dibuka 2x cepat)
        const old = document.getElementById('printerChooserOverlay');
        if (old) old.remove();

        const saved = tp.lastConnectedDevice;
        const overlay = document.createElement('div');
        overlay.id = 'printerChooserOverlay';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:2000;display:flex;align-items:flex-end;justify-content:center;padding:0;font-family:Inter,-apple-system,sans-serif;animation:fadeIn .25s ease;';

        overlay.innerHTML = `
            <div style="background:#16213e;border:1px solid #2a2a4a;border-top-left-radius:20px;border-top-right-radius:20px;width:100%;max-width:480px;padding:20px;box-shadow:0 -10px 40px rgba(0,0,0,0.6);animation:slideUp .3s cubic-bezier(.16,.86,.43,1);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(76,201,240,0.15);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-bluetooth" style="color:#4cc9f0;font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#fff;font-size:0.95rem;">Pilih Printer Thermal</div>
                            <div style="font-size:0.7rem;color:#94a3b8;">Bluetooth · Cetak Struk POS</div>
                        </div>
                    </div>
                    <button id="pcCloseBtn" style="background:none;border:none;color:#94a3b8;font-size:1.4rem;cursor:pointer;padding:4px 8px;">&times;</button>
                </div>

                ${saved ? `
                <div style="background:rgba(46,196,182,0.08);border:1px solid rgba(46,196,182,0.4);border-radius:12px;padding:14px;margin-bottom:12px;display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(46,196,182,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-printer-fill" style="color:#2ec4b6;font-size:1.1rem;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;color:#fff;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${saved.name || 'Printer Tersimpan'}</div>
                        <div style="font-size:0.7rem;color:#2ec4b6;">Tersimpan di perangkat ini</div>
                    </div>
                </div>
                <button id="pcReconnectBtn" style="width:100%;padding:14px;background:linear-gradient(135deg,#2ec4b6,#1a9d92);color:#fff;border:none;border-radius:12px;font-weight:600;font-size:0.9rem;cursor:pointer;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="bi bi-arrow-clockwise"></i> Hubungkan ke Printer Tersimpan
                </button>
                ` : `
                <div style="background:rgba(255,183,3,0.08);border:1px solid rgba(255,183,3,0.3);border-radius:12px;padding:14px;margin-bottom:12px;text-align:center;color:#ffb703;font-size:0.8rem;">
                    <i class="bi bi-info-circle"></i> Belum ada printer tersimpan. Klik tombol di bawah untuk memilih printer pertama kali.
                </div>
                `}

                ${saved ? '' : `
                <button id="pcScanNewBtn" style="width:100%;padding:14px;background:linear-gradient(135deg,#e63946,#b8202e);border:none;color:#fff;border-radius:12px;font-weight:600;font-size:0.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="bi bi-search"></i> Cari & Hubungkan Printer Bluetooth
                </button>
                `}

                ${saved ? `
                <button id="pcForgetBtn" style="width:100%;margin-top:10px;padding:10px;background:none;border:none;color:#e63946;font-size:0.78rem;cursor:pointer;text-decoration:underline;">
                    Lupakan printer tersimpan
                </button>
                ` : ''}

                <div style="margin-top:14px;padding-top:14px;border-top:1px solid #2a2a4a;font-size:0.7rem;color:#94a3b8;line-height:1.5;">
                    <i class="bi bi-shield-check" style="color:#4cc9f0;"></i> AlfarezMart hanya menampilkan dialog perangkat Bluetooth bawaan sistem saat mencari printer baru. Pemilihan dilakukan oleh OS Anda.
                </div>
            </div>
        `;

        // Inject animasi (sekali saja)
        if (!document.getElementById('printerChooserStyle')) {
            const style = document.createElement('style');
            style.id = 'printerChooserStyle';
            style.textContent = '@keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes slideUp{from{transform:translateY(100%)}to{transform:translateY(0)}}';
            document.head.appendChild(style);
        }

        document.body.appendChild(overlay);

        const cleanup = () => { try { overlay.remove(); } catch (e) {} };

        document.getElementById('pcCloseBtn').onclick = () => { cleanup(); resolve(false); };
        overlay.addEventListener('click', (e) => { if (e.target === overlay) { cleanup(); resolve(false); } });

        const btnReconnect = document.getElementById('pcReconnectBtn');
        if (btnReconnect) {
            btnReconnect.onclick = async () => {
                btnReconnect.disabled = true;
                btnReconnect.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghubungkan...';
                try {
                    const ok = await tp.tryAutoReconnect();
                    if (ok) { cleanup(); resolve(true); return; }
                    // Fallback: trigger picker
                    btnReconnect.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Auto-reconnect gagal, buka pemilih...';
                    await tp.connect(false);
                    cleanup(); resolve(true);
                } catch (e) {
                    if (typeof showToast === 'function') showToast(e.message || 'Gagal menghubungkan', 'error');
                    cleanup(); resolve(false);
                }
            };
        }
        const btnScanNew = document.getElementById('pcScanNewBtn');
        if (btnScanNew) {
            btnScanNew.onclick = async () => {
                btnScanNew.disabled = true;
                btnScanNew.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Membuka pemilih Bluetooth...';
                try {
                    await tp.connect(true);
                    cleanup(); resolve(true);
                } catch (e) {
                    if (typeof showToast === 'function') showToast(e.message || 'Gagal menghubungkan', 'error');
                    cleanup(); resolve(false);
                }
            };
        }
        const btnForget = document.getElementById('pcForgetBtn');
        if (btnForget) {
            btnForget.onclick = () => {
                tp.clearLastDevice();
                cleanup();
                if (typeof showToast === 'function') showToast('Printer tersimpan dihapus.', 'info');
                resolve(false);
            };
        }
    });
};
