/**
 * sales_invoice.js - Engine Pembuatan & Berbagi Struk/Invoice Gambar AlfarezMart
 * Fitur:
 * 1. Desain invoice modern, elegan, profesional dengan Logo AlfarezMart.
 * 2. Tanpa nama pelanggan pada tampilan invoice (sesuai instruksi).
 * 3. Render gambar resolusi tinggi (Retina 2x DPI, ultra-cepat & jernih).
 * 4. Berbagi langsung dalam bentuk gambar ke WhatsApp/Telegram via Web Share API Level 2 (files).
 * 5. Penanganan anti-timeout transient user activation (fresh gesture modal action).
 * 6. Fallback desktop: Salin Gambar ke Clipboard (1-click Paste Ctrl+V di WA Web) & Unduh.
 */

(function (window, document) {
    'use strict';

    // State cache sementara gambar invoice yang sudah di-generate
    const _invoiceImageCache = new Map();
    let _activeModalBlob = null;
    let _activeModalFile = null;
    let _activeShareTitle = '';
    let _activeShareText = '';

    function formatRupiah(amount) {
        return 'Rp ' + Math.round(Number(amount) || 0).toLocaleString('id-ID');
    }

    function formatDateTime(dateStr) {
        if (!dateStr) {
            return new Date().toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        const normalized = String(dateStr).replace(' ', 'T') + '+07:00';
        const d = new Date(normalized);
        if (isNaN(d.getTime())) {
            const rawD = new Date(dateStr);
            if (!isNaN(rawD.getTime())) {
                return rawD.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            return String(dateStr);
        }
        return d.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Memastikan library html2canvas siap digunakan
     */
    async function ensureHtml2Canvas() {
        if (typeof window.html2canvas === 'function') {
            return window.html2canvas;
        }
        return new Promise((resolve, reject) => {
            const baseUrl = (typeof window.BASE_URL !== 'undefined') ? window.BASE_URL : '/';
            const script = document.createElement('script');
            script.src = baseUrl + 'public/js/html2canvas.min.js';
            script.async = true;
            script.onload = () => resolve(window.html2canvas);
            script.onerror = () => reject(new Error('Gagal memuat html2canvas'));
            document.head.appendChild(script);
        });
    }

    /**
     * Render Template HTML Invoice Alfarez Mart yang Elegan, Profesional, dan Modern
     * CATATAN PENTING: Sesuai instruksi, tidak ada nama pelanggan di dalam invoice.
     */
    function renderSalesInvoiceHTML(data) {
        const storeSettings = data.storeSettings || (typeof window.STORE_SETTINGS !== 'undefined' ? window.STORE_SETTINGS : {}) || {};
        const storeName = data.store_name || storeSettings.store_name || 'Alfarez Mart';
        const storeAddress = data.store_address || storeSettings.store_address || '';
        const storePhone = data.store_phone || storeSettings.store_phone || '';
        const receiptHeader = data.receipt_header || storeSettings.receipt_header || '';
        const receiptFooter = data.receipt_footer || storeSettings.receipt_footer || '';
        const baseUrl = (typeof window.BASE_URL !== 'undefined') ? window.BASE_URL : '/';

        // Logo toko: prioritaskan store_logo atau Icon.png
        let logoSrc = data.store_logo || storeSettings.store_logo || (baseUrl + 'public/images/Icon.png');

        const invoiceNo = data.invoice_number || data.invoice || 'INV-' + Date.now();
        const dateStr = formatDateTime(data.created_at);
        const paymentMethod = data.payment_method || 'Tunai';
        const saleMode = (data.sale_mode || 'retail').toLowerCase();
        const saleModeLabel = saleMode === 'wholesale' ? 'Grosir' : (saleMode === 'mix' ? 'Mix' : 'Retail');

        const items = data.items || [];
        let totalItemsCount = 0;
        let subtotalCalculated = 0;

        const itemsHtml = items.map(item => {
            const name = item.invoice_name || item.print_name || item.short_label || item.full_name || item.name || 'Produk';
            const qty = parseFloat(item.quantity) || 1;
            totalItemsCount += qty;
            const unit = item.unit_name || item.unit || 'pcs';
            const unitPrice = parseFloat(item.unit_price) || 0;
            const itemTotal = parseFloat(item.total_price || item.total) || (qty * unitPrice);
            subtotalCalculated += itemTotal;

            const itemDiscount = parseFloat(item.discount || 0);

            return `
                <div style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0;">
                    <div style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.35; margin-bottom: 4px; word-break: break-word;">
                        ${escapeHtml(name)}
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                            <span>${qty} ${escapeHtml(unit)}</span> &times; <span>${formatRupiah(unitPrice)}</span>
                            ${itemDiscount > 0 ? `<span style="margin-left: 6px; font-size: 10px; font-weight: 700; color: #16a34a; background: #dcfce7; padding: 1px 6px; border-radius: 4px;">Disc -${formatRupiah(itemDiscount)}</span>` : ''}
                        </div>
                        <div style="font-size: 13px; font-weight: 800; color: #0f172a; text-align: right; letter-spacing: -0.2px;">
                            ${formatRupiah(itemTotal)}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        const totalAmount = parseFloat(data.total_amount) || subtotalCalculated;
        const discountTotal = parseFloat(data.discount || (data.mixInfo ? data.mixInfo.totalDiscount : 0)) || 0;

        // Tunai & Kembalian (opsional bila ada)
        let cashDetailHtml = '';
        if (data.cash_amount && parseFloat(data.cash_amount) >= totalAmount) {
            const cash = parseFloat(data.cash_amount);
            const change = cash - totalAmount;
            cashDetailHtml = `
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: #64748b; margin-top: 8px; padding-top: 6px; border-top: 1px solid #e2e8f0;">
                    <span>Tunai Dibayar</span>
                    <span style="font-weight: 600; color: #1e293b;">${formatRupiah(cash)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: #64748b; margin-top: 4px;">
                    <span>Kembalian</span>
                    <span style="font-weight: 700; color: #10b981;">${formatRupiah(change)}</span>
                </div>
            `;
        }

        return `
            <div id="sales-invoice-card" style="width: 380px; background: #ffffff; border-radius: 18px; padding: 24px 22px 20px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; box-sizing: border-box; color: #0f172a; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; position: relative;">
                
                <!-- Accent Top Glow Bar -->
                <div style="height: 4px; width: 60px; background: linear-gradient(90deg, #4f46e5, #06b6d4); border-radius: 4px; margin: 0 auto 14px;"></div>

                <!-- Store Header & Logo -->
                <div style="text-align: center; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 8px;">
                        <img src="${logoSrc}" crossorigin="anonymous" alt="Logo ${escapeHtml(storeName)}" style="max-height: 54px; max-width: 170px; object-fit: contain; display: block;" onerror="this.style.display='none'" />
                    </div>
                    <div style="font-size: 18px; font-weight: 900; color: #0f172a; letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 3px;">
                        ${escapeHtml(storeName)}
                    </div>
                    ${storeAddress ? `<div style="font-size: 11px; color: #64748b; line-height: 1.4; max-width: 320px; margin: 0 auto 3px;">${escapeHtml(storeAddress)}</div>` : ''}
                    ${storePhone && storePhone !== '-' ? `<div style="font-size: 11px; color: #475569; font-weight: 600;">Telp/WA: ${escapeHtml(storePhone)}</div>` : ''}
                    ${receiptHeader ? `<div style="font-size: 10.5px; color: #6366f1; font-weight: 600; margin-top: 5px; padding: 3px 8px; background: #eef2ff; border-radius: 6px; display: inline-block;">${escapeHtml(receiptHeader)}</div>` : ''}
                </div>

                <!-- Divider Modern Zigzag/Perforated Line -->
                <div style="border-top: 1.5px dashed #cbd5e1; margin: 14px 0 12px; position: relative;"></div>

                <!-- Invoice Meta Info (Tanpa Nama Pelanggan) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f8fafc; border: 1px solid #edf2f7; border-radius: 12px; padding: 10px 12px; margin-bottom: 14px;">
                    <div>
                        <div style="font-size: 9.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">NO. INVOICE</div>
                        <div style="font-family: monospace, monospace; font-size: 12px; font-weight: 800; color: #0f172a; margin-top: 2px;">
                            ${escapeHtml(invoiceNo)}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 9.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">WAKTU</div>
                        <div style="font-size: 11px; font-weight: 600; color: #334155; margin-top: 2px;">
                            ${escapeHtml(dateStr)}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 9.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">METODE BAYAR</div>
                        <div style="margin-top: 3px;">
                            <span style="display: inline-block; background: #e0f2fe; color: #0284c7; font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                                ${escapeHtml(paymentMethod)}
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 9.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">MODE TRANSAKSI</div>
                        <div style="margin-top: 3px;">
                            <span style="display: inline-block; background: #f1f5f9; color: #475569; font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 4px; text-transform: uppercase;">
                                ${escapeHtml(saleModeLabel)}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Title Rincian Belanja -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px;">RINCIAN BARANG</span>
                    <span style="font-size: 10.5px; font-weight: 600; color: #94a3b8;">${items.length} Item</span>
                </div>

                <!-- List Items -->
                <div style="margin-bottom: 12px;">
                    ${itemsHtml}
                </div>

                <!-- Financial Summary Box -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #475569; margin-bottom: 4px;">
                        <span>Subtotal Belanja</span>
                        <span style="font-weight: 700; color: #1e293b;">${formatRupiah(subtotalCalculated || totalAmount)}</span>
                    </div>

                    ${discountTotal > 0 ? `
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #16a34a; margin-bottom: 4px;">
                        <span style="font-weight: 600;">Diskon / Potongan</span>
                        <span style="font-weight: 700;">-${formatRupiah(discountTotal)}</span>
                    </div>` : ''}

                    <div style="margin: 8px 0; border-top: 1px dashed #cbd5e1;"></div>

                    <!-- Modern Dark Accent Total Banner -->
                    <div style="display: flex; justify-content: space-between; align-items: center; background: #0f172a; color: #ffffff; border-radius: 10px; padding: 12px 14px;">
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; color: #94a3b8;">TOTAL BAYAR</span>
                            <span style="font-size: 11px; color: #cbd5e1; font-weight: 500;">Sudah Termasuk PPN</span>
                        </div>
                        <div style="font-size: 19px; font-weight: 900; color: #38bdf8; letter-spacing: -0.3px;">
                            ${formatRupiah(totalAmount)}
                        </div>
                    </div>

                    ${cashDetailHtml}
                </div>

                <!-- Footer Section -->
                <div style="margin-top: 16px; text-align: center;">
                    <div style="border-top: 1.5px dashed #cbd5e1; margin-bottom: 12px;"></div>
                    <div style="font-size: 12px; font-weight: 800; color: #1e293b; margin-bottom: 3px;">
                        Terima Kasih Telah Berbelanja!
                    </div>
                    <div style="font-size: 10px; color: #64748b; line-height: 1.4;">
                        Barang yang sudah dibeli tidak dapat ditukar/dikembalikan
                    </div>
                    ${receiptFooter ? `<div style="font-size: 10px; color: #475569; margin-top: 4px; font-style: italic;">${escapeHtml(receiptFooter)}</div>` : ''}
                    
                    <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 12px; font-size: 9px; font-weight: 800; color: #94a3b8; letter-spacing: 0.8px; text-transform: uppercase;">
                        <span>ALFAREZ MART</span>
                        <span>•</span>
                        <span>BUKTI PEMBAYARAN SAH</span>
                    </div>
                </div>

            </div>
        `;
    }

    /**
     * Generate File Gambar PNG Invoice menggunakan html2canvas dengan Resolusi Tinggi (2x DPI Retina)
     */
    async function generateSalesInvoiceImageFile(data) {
        const h2c = await ensureHtml2Canvas();

        const invoiceNo = data.invoice_number || data.invoice || 'INV-' + Date.now();
        const cacheKey = invoiceNo + '_' + (data.total_amount || 0);

        if (_invoiceImageCache.has(cacheKey)) {
            return _invoiceImageCache.get(cacheKey);
        }

        // Render HTML ke container tersembunyi
        const container = document.createElement('div');
        container.style.position = 'fixed';
        container.style.left = '-9999px';
        container.style.top = '0';
        container.style.width = '380px';
        container.style.zIndex = '-9999';
        container.style.opacity = '0';
        container.style.pointerEvents = 'none';
        container.style.background = '#ffffff';
        container.innerHTML = renderSalesInvoiceHTML(data);
        document.body.appendChild(container);

        // Tunggu gambar (seperti logo) ter-decode sempurna
        const imgEls = Array.from(container.querySelectorAll('img'));
        if (imgEls.length > 0) {
            await Promise.all(imgEls.map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise(res => {
                    img.onload = res;
                    img.onerror = res;
                    setTimeout(res, 400);
                });
            }));
        }

        const cardNode = container.querySelector('#sales-invoice-card') || container.firstElementChild;

        // Skala 2x DPI: Menghasilkan 760px lebar, sangat jernih (Retina Quality) dan 8x lebih cepat render
        const canvas = await h2c(cardNode, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            imageTimeout: 2000
        });

        document.body.removeChild(container);

        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        if (!blob) throw new Error('Gagal merender gambar invoice');

        const cleanNo = String(invoiceNo).replace(/[^a-zA-Z0-9\-]/g, '_');
        const fileName = `Invoice_${cleanNo}.png`;
        const file = new File([blob], fileName, { type: 'image/png' });

        const result = { file, blob, fileName, invoiceNo };
        _invoiceImageCache.set(cacheKey, result);
        return result;
    }

    /**
     * Menyalin Blob Gambar ke Clipboard (Bisa langsung Ctrl+V di WhatsApp Web atau Telegram)
     */
    async function copySalesInvoiceImageBlob(blob) {
        if (!navigator.clipboard || !window.ClipboardItem) {
            throw new Error('Fitur clipboard image tidak didukung browser ini');
        }
        const item = new ClipboardItem({ 'image/png': blob });
        await navigator.clipboard.write([item]);
    }

    /**
     * Download File Gambar PNG
     */
    function downloadSalesInvoiceImageFile(blob, fileName) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
    }

    /**
     * Tampilkan Modal Share & Fallback Invoice
     * Menangani baik Web Share API (fresh user gesture) maupun fallback Desktop/Clipboard
     */
    function showSalesInvoiceShareFallbackModal(invoiceData, fileData, options = {}) {
        const invoiceNo = invoiceData.invoice_number || invoiceData.invoice || 'INV';
        const storeName = invoiceData.store_name || 'Alfarez Mart';
        const { blob, fileName, file } = fileData;

        _activeModalBlob = blob;
        _activeModalFile = file;
        _activeShareTitle = options.shareTitle || `Struk Pembelian ${storeName}`;
        _activeShareText = options.shareText || `Struk Pembelian No. ${invoiceNo} - ${storeName}`;

        const imageUrl = URL.createObjectURL(blob);
        const canShareFiles = (typeof navigator.share === 'function') && 
            (typeof navigator.canShare === 'function' ? navigator.canShare({ files: [file] }) : true);
        const isMobileOrShareable = options.isMobileShare !== undefined ? options.isMobileShare : canShareFiles;

        if (typeof window.AppModal !== 'undefined') {
            window.AppModal.show({
                title: 'Bagikan Struk / Invoice (Gambar)',
                subtitle: `Invoice #${invoiceNo} · ${storeName}`,
                icon: 'bi-image-fill',
                iconColor: 'rgba(37,211,102,0.15)',
                iconAccent: '#25D366',
                bodyHTML: `
                    <div style="text-align:center;padding:4px 0;">
                        <!-- Preview Gambar Invoice -->
                        <div style="margin-bottom:14px;background:#f8fafc;padding:12px;border-radius:12px;border:1px solid #e2e8f0;max-height:240px;overflow-y:auto;display:flex;justify-content:center;">
                            <img src="${imageUrl}" style="width:100%;max-width:240px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:block;" alt="Preview Invoice ${invoiceNo}">
                        </div>

                        ${isMobileOrShareable ? `
                        <!-- Direct Share Trigger Button (Guaranteed Fresh User Gesture) -->
                        <div style="margin-bottom:12px;">
                            <button type="button" id="btnDirectShareModal" onclick="window.shareSalesInvoiceDirectFromModal()" style="width:100%;display:flex;align-items:center;justify-content:center;gap:10px;padding:13px;background:linear-gradient(135deg, #25D366 0%, #128C7E 100%);color:white;border:none;border-radius:12px;font-weight:700;font-size:14px;box-shadow:0 4px 14px rgba(37,211,102,0.35);cursor:pointer;transition:transform 0.15s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="bi bi-whatsapp" style="font-size:1.3rem;"></i>
                                <span>Kirim Struk ke WhatsApp / Media Sosial</span>
                            </button>
                            <div style="margin-top:6px;font-size:11px;color:var(--text-muted);line-height:1.4;">
                                💡 Klik tombol hijau di atas untuk langsung memilih kontak WhatsApp atau media sosial lainnya.
                            </div>
                        </div>

                        <!-- Secondary Actions: Copy & Download -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:6px;">
                            <button type="button" onclick="window.copySalesInvoiceImageFromModal()" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-primary);border-radius:10px;font-weight:600;font-size:12px;cursor:pointer;">
                                <i class="bi bi-clipboard-check-fill" style="color:#25D366;font-size:1.1rem;"></i> Salin Gambar
                            </button>
                            <button type="button" onclick="window.downloadSalesInvoiceImageFromModal('${fileName}')" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-primary);border-radius:10px;font-weight:600;font-size:12px;cursor:pointer;">
                                <i class="bi bi-download" style="color:var(--primary);font-size:1.1rem;"></i> Unduh Gambar
                            </button>
                        </div>
                        ` : `
                        <!-- Desktop Fallback: 1-Click Copy Image Button for WA Web / Telegram -->
                        <div style="margin-bottom:12px;">
                            <button type="button" id="btnCopyInvoiceImgModal" onclick="window.copySalesInvoiceImageFromModal()" style="width:100%;display:flex;align-items:center;justify-content:center;gap:10px;padding:13px;background:linear-gradient(135deg, #25D366 0%, #128C7E 100%);color:white;border:none;border-radius:12px;font-weight:700;font-size:13.5px;box-shadow:0 4px 14px rgba(37,211,102,0.35);cursor:pointer;transition:transform 0.15s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="bi bi-clipboard-check-fill" style="font-size:1.25rem;"></i>
                                <span>Salin Gambar ke Clipboard</span>
                            </button>
                            <div style="margin-top:6px;font-size:11px;color:var(--text-muted);line-height:1.4;">
                                💡 Setelah diklik, buka <strong>WhatsApp Web</strong> atau <strong>Telegram</strong> lalu langsung tekan <strong>Paste (Ctrl+V)</strong> untuk mengirim gambar!
                            </div>
                        </div>

                        <!-- Secondary Actions: Download & Open WhatsApp -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:6px;">
                            <a href="https://web.whatsapp.com" target="_blank" rel="noopener" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-primary);border-radius:10px;text-decoration:none;font-weight:600;font-size:12px;">
                                <i class="bi bi-whatsapp" style="color:#25D366;font-size:1.1rem;"></i> Buka WA Web
                            </a>
                            <button type="button" onclick="window.downloadSalesInvoiceImageFromModal('${fileName}')" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-primary);border-radius:10px;font-weight:600;font-size:12px;cursor:pointer;">
                                <i class="bi bi-download" style="color:var(--primary);font-size:1.1rem;"></i> Unduh Gambar
                            </button>
                        </div>
                        `}
                    </div>
                `,
                submitText: null,
                cancelText: 'Tutup',
                onClose: () => {
                    setTimeout(() => URL.revokeObjectURL(imageUrl), 1000);
                }
            });
        }
    }

    /**
     * Berbagi langsung dari klik tombol modal dengan Fresh User Gesture (bebas timeout)
     */
    window.shareSalesInvoiceDirectFromModal = async function () {
        const btn = document.getElementById('btnDirectShareModal');
        if (!_activeModalFile) {
            if (typeof window.showToast === 'function') window.showToast('Gambar invoice belum siap', 'error');
            return;
        }

        if (typeof navigator.share === 'function') {
            try {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuka Aplikasi...';
                }
                await navigator.share({
                    title: _activeShareTitle || 'Struk Pembelian Alfarez Mart',
                    text: _activeShareText || '',
                    files: [_activeModalFile]
                });
                if (typeof window.showToast === 'function') {
                    window.showToast('✅ Invoice berhasil dibagikan!', 'success');
                }
                if (window.AppModal && typeof window.AppModal.close === 'function') {
                    window.AppModal.close();
                }
                return;
            } catch (err) {
                if (err.name === 'AbortError') {
                    // Pengguna membatalkan / menutup sheet share
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-whatsapp" style="font-size:1.3rem;"></i><span>Kirim Struk ke WhatsApp / Media Sosial</span>';
                    }
                    return;
                }
                console.warn('Direct share from modal failed, falling back to copy:', err);
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-whatsapp" style="font-size:1.3rem;"></i><span>Kirim Struk ke WhatsApp / Media Sosial</span>';
                }
            }
        }

        // Fallback jika tidak didukung
        window.copySalesInvoiceImageFromModal();
    };

    // Modal Helper Actions
    window.copySalesInvoiceImageFromModal = async function () {
        const btn = document.getElementById('btnCopyInvoiceImgModal');
        if (!_activeModalBlob) {
            if (typeof window.showToast === 'function') window.showToast('Gambar tidak tersedia', 'error');
            return;
        }
        try {
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyalin...';
            }
            await copySalesInvoiceImageBlob(_activeModalBlob);
            if (typeof window.showToast === 'function') {
                window.showToast('✅ Gambar invoice berhasil disalin ke clipboard! Silakan Paste (Ctrl+V) di WhatsApp / Telegram.', 'success');
            }
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Berhasil Disalin!';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-clipboard-check-fill me-1"></i> Salin Gambar ke Clipboard';
                }, 2500);
            }
        } catch (err) {
            console.error('Copy image error:', err);
            if (typeof window.showToast === 'function') {
                window.showToast('Gagal menyalin gambar otomatis. Silakan klik Unduh Gambar atau klik kanan gambar dan pilih Copy Image.', 'warning');
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-clipboard-check-fill me-1"></i> Salin Gambar ke Clipboard';
            }
        }
    };

    window.downloadSalesInvoiceImageFromModal = function (fileName) {
        if (!_activeModalBlob) {
            if (typeof window.showToast === 'function') window.showToast('Gambar tidak tersedia', 'error');
            return;
        }
        downloadSalesInvoiceImageFile(_activeModalBlob, fileName || 'Invoice_AlfarezMart.png');
        if (typeof window.showToast === 'function') {
            window.showToast('Gambar invoice berhasil diunduh', 'success');
        }
    };

    /**
     * FUNGSI UTAMA: Share Sales Invoice Langsung dalam Bentuk Gambar
     * @param {Object} options { saleId, invoiceNumber, data, buttonEl }
     */
    async function shareSalesInvoice(options = {}) {
        let { saleId, invoiceNumber, data, buttonEl } = options;

        let origBtnHtml = '';
        if (buttonEl) {
            origBtnHtml = buttonEl.innerHTML;
            buttonEl.disabled = true;
            buttonEl.innerHTML = '<i class="spinner-border spinner-border-sm" style="width:14px;height:14px;"></i>';
        }

        try {
            // Jika data belum lengkap, fetch dari API
            if (!data || !data.items || data.items.length === 0) {
                if (typeof window.showToast === 'function') window.showToast('Menyiapkan gambar invoice...', 'info');
                const fetchId = saleId || invoiceNumber;
                const baseUrl = (typeof window.BASE_URL !== 'undefined') ? window.BASE_URL : '/';
                const cleanBaseUrl = baseUrl.endsWith('/') ? baseUrl : (baseUrl + '/');
                const res = await fetch(`${cleanBaseUrl}api/sales/invoice/${encodeURIComponent(fetchId)}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Gagal mengambil data transaksi');
                const json = await res.json();
                if (!json.success) throw new Error(json.error || 'Data invoice tidak ditemukan');

                const tx = json.transaction || {};
                data = {
                    invoice_number: tx.invoice_number || invoiceNumber,
                    created_at: tx.created_at,
                    payment_method: tx.payment_method,
                    sale_mode: tx.sale_mode,
                    total_amount: tx.total_amount,
                    items: json.items || tx.items || [],
                    store_name: json.store_name,
                    store_logo: json.store_logo,
                    storeSettings: json.store_settings
                };
            }

            // Pastikan logo toko terisi
            if (!data.storeSettings && typeof window.STORE_SETTINGS !== 'undefined') {
                data.storeSettings = window.STORE_SETTINGS;
            }
            if (!data.store_logo && data.storeSettings && data.storeSettings.store_logo) {
                data.store_logo = data.storeSettings.store_logo;
            }

            // Generate file gambar invoice beresolusi tinggi (scale 2x, cepat & super tajam)
            const genResult = await generateSalesInvoiceImageFile(data);
            const { file, blob, fileName } = genResult;

            const shareTitle = `Struk Pembelian ${data.store_name || 'Alfarez Mart'}`;
            const shareText = `Struk Pembelian No. ${data.invoice_number || ''} - ${data.store_name || 'Alfarez Mart'}`;

            // LANGKAH UTAMA: Cek Web Share API Level 2 untuk Berbagi File Gambar Langsung
            const canShareFiles = (typeof navigator.canShare === 'function' && navigator.canShare({ files: [file] }));
            if (canShareFiles) {
                try {
                    await navigator.share({
                        title: shareTitle,
                        text: shareText,
                        files: [file]
                    });
                    if (typeof window.showToast === 'function') {
                        window.showToast('✅ Invoice berhasil dibagikan!', 'success');
                    }
                    return;
                } catch (shareErr) {
                    if (shareErr.name === 'AbortError') {
                        // User membatalkan dialog share - bukan error
                        return;
                    }
                    console.warn('Native share gesture expired or failed, showing action modal:', shareErr);
                    // Jika token user gesture kedaluwarsa (NotAllowedError) karena waktu render/fetch:
                    // Buka modal interaktif dengan fresh user gesture button
                    showSalesInvoiceShareFallbackModal(data, genResult, {
                        shareTitle,
                        shareText,
                        isMobileShare: true
                    });
                    return;
                }
            } else {
                // Fallback untuk desktop PC atau browser tanpa Web Share Level 2
                showSalesInvoiceShareFallbackModal(data, genResult, {
                    shareTitle,
                    shareText,
                    isMobileShare: false
                });
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('shareSalesInvoice error:', err);
                if (typeof window.showToast === 'function') {
                    window.showToast('Gagal membagikan invoice: ' + (err.message || 'Error tidak diketahui'), 'error');
                }
            }
        } finally {
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.innerHTML = origBtnHtml;
            }
        }
    }

    // Pre-initialize html2canvas saat idle agar tidak delay saat tombol share pertama kali diklik
    if (typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(() => ensureHtml2Canvas().catch(() => {}));
    } else {
        setTimeout(() => ensureHtml2Canvas().catch(() => {}), 300);
    }

    // Expose API ke Window
    window.renderSalesInvoiceHTML = renderSalesInvoiceHTML;
    window.generateSalesInvoiceImageFile = generateSalesInvoiceImageFile;
    window.copySalesInvoiceImageBlob = copySalesInvoiceImageBlob;
    window.downloadSalesInvoiceImageFile = downloadSalesInvoiceImageFile;
    window.showSalesInvoiceShareFallbackModal = showSalesInvoiceShareFallbackModal;
    window.shareSalesInvoice = shareSalesInvoice;

})(window, document);
