function detectProductType(d) {
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
    }

    /**
     * Get theme config for each product type:
     * { accent, accentLight, accentDark, label, icon, gradient, badgeBg, badgeColor }
     */
    function getProductTheme(type) {
        const themes = {
            pln:     { accent:'#0073C6', accentLight:'#e8f4ff', accentDark:'#004f8b', gradient:'linear-gradient(135deg,#0073C6,#00b4d8)', label:'PLN Prepaid', icon:'⚡' },
            dana:    { accent:'#118EEA', accentLight:'#e5f4ff', accentDark:'#0068c2', gradient:'linear-gradient(135deg,#118EEA,#42a5f5)', label:'DANA', icon:'💙' },
            shopee:  { accent:'#EE4D2D', accentLight:'#fff1ee', accentDark:'#c73c21', gradient:'linear-gradient(135deg,#EE4D2D,#ff7043)', label:'ShopeePay', icon:'🧡' },
            gopay:   { accent:'#00AED6', accentLight:'#e6f9ff', accentDark:'#0089ab', gradient:'linear-gradient(135deg,#00AED6,#00e5ff)', label:'GoPay', icon:'💚' },
            ovo:     { accent:'#4C2A86', accentLight:'#f2eeff', accentDark:'#3a1f6a', gradient:'linear-gradient(135deg,#4C2A86,#7b5ea7)', label:'OVO', icon:'💜' },
            linkaja: { accent:'#E8192C', accentLight:'#fff0f1', accentDark:'#b91422', gradient:'linear-gradient(135deg,#E8192C,#ff5252)', label:'LinkAja', icon:'❤️' },
            bpjs:    { accent:'#00873C', accentLight:'#e6f7ee', accentDark:'#005c28', gradient:'linear-gradient(135deg,#00873C,#4caf50)', label:'BPJS Kesehatan', icon:'🏥' },
            pdam:    { accent:'#1565C0', accentLight:'#e8eeff', accentDark:'#0d3d73', gradient:'linear-gradient(135deg,#1565C0,#1e88e5)', label:'PDAM Air', icon:'💧' },
            telkom:  { accent:'#E40427', accentLight:'#fff0f1', accentDark:'#b4001e', gradient:'linear-gradient(135deg,#E40427,#f44336)', label:'Telkom/IndiHome', icon:'📡' },
            paket:   { accent:'#0277BD', accentLight:'#e6f4ff', accentDark:'#01579b', gradient:'linear-gradient(135deg,#0277BD,#29b6f6)', label:'Paket Data', icon:'📶' },
            pulsa:   { accent:'#2E7D32', accentLight:'#e8f5e9', accentDark:'#1b5e20', gradient:'linear-gradient(135deg,#2E7D32,#66bb6a)', label:'Pulsa', icon:'📱' },
            voucher: { accent:'#6A1B9A', accentLight:'#f4e6ff', accentDark:'#4a0072', gradient:'linear-gradient(135deg,#6A1B9A,#ab47bc)', label:'Voucher / Game', icon:'🎮' },
            other:   { accent:'#263238', accentLight:'#eceff1', accentDark:'#1a2327', gradient:'linear-gradient(135deg,#263238,#546e7a)', label:'Produk Digital', icon:'🔷' },
        };
        return themes[type] || themes.other;
    }

    /**
     * Parse SN field into structured data based on product type.
     * Returns { snTitle, snValue, extraRows: [{label, value}], accountName }
     */
    function parseSN(d, type) {
        const sn = (d.sn || '').trim();
        const hasSN = sn && sn !== '-';
        let result = { snTitle: 'Referensi', snValue: sn, extraRows: [], accountName: null, hasSN };

        if (!hasSN) return result;

        // PLN: parts split by '/'
        if (type === 'pln' && sn.includes('/')) {
            const parts = sn.split('/');
            if (parts.length >= 4) {
                result.snTitle  = 'Token PLN';
                result.snValue  = parts[0].trim();
                const mtrName   = parts[1]?.trim() || '';
                const tarif     = parts.length > 4 ? `${parts[2]}/${parts[3]}` : (parts[2] || '');
                const kwh       = parts.length > 4 ? parts[4] : (parts[3] || '');
                if (mtrName) result.extraRows.push({ label: 'Nama Pelanggan', value: mtrName });
                if (tarif)   result.extraRows.push({ label: 'Tarif / Daya',   value: tarif   });
                if (kwh)     result.extraRows.push({ label: 'Jumlah kWh',     value: kwh     });
            }
            return result;
        }

        // E-wallet (DANA, GoPay, ShopeePay, OVO, LinkAja): parse NAMA: ... REFF: ...
        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        if (ewallets.includes(type)) {
            result.snTitle = 'ID Referensi';
            if (sn.toUpperCase().includes('NAMA:') && sn.toUpperCase().includes('REFF:')) {
                const namaMatch = sn.match(/NAMA:\s*([^,\n]+)/i);
                const reffMatch = sn.match(/REFF:\s*([^,\n]+)/i);
                if (namaMatch?.[1]) {
                    result.accountName = namaMatch[1].trim();
                }
                result.snValue = reffMatch?.[1]?.trim() || sn;
            } else {
                // Try to detect if it's purely a ref code
                result.snValue = sn;
            }
            result.hasSN = true;
            return result;
        }

        // Pulsa / Paket Data: no structured SN usually; show as-is
        if (type === 'pulsa' || type === 'paket') {
            result.snTitle = 'Nomor SN';
            result.snValue = sn;
            return result;
        }

        // Generic NAMA: REFF: pattern
        if (sn.toUpperCase().includes('NAMA:') && sn.toUpperCase().includes('REFF:')) {
            const namaMatch = sn.match(/NAMA:\s*([^,\n]+)/i);
            const reffMatch = sn.match(/REFF:\s*([^,\n]+)/i);
            if (namaMatch?.[1]) result.accountName = namaMatch[1].trim();
            result.snValue = reffMatch?.[1]?.trim() || sn;
            return result;
        }

        return result;
    }

    /**
     * Build the elegant print-window HTML for a specific product type.
     */
    function buildPrintHTML(d, type, theme, snData) {
        const BASE = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/';
        const logoSrc = (BASE.endsWith('/') ? BASE : BASE + '/') + 'public/images/mobile_icon.png';
        const price = parseInt(d.sell_price || 0).toLocaleString('id-ID');
        const dateStr = d.created_at || '';

        // Watermark tiles — repeated logo as base64 CSS bg is too complex; use img tags in a grid
        // We use a pseudo-element approach with CSS and a data-uri trick
        const wmCount = 9; // 3x3 grid
        let wmHtml = '';
        for (let i = 0; i < wmCount; i++) {
            wmHtml += `<img src="${logoSrc}" class="wm-tile" alt="">`;
        }

        // Extra info rows from SN parsing
        let extraRowsHtml = snData.extraRows.map(r => `
            <tr class="info-row">
                <td class="info-label">${r.label}</td>
                <td class="info-value">${r.value}</td>
            </tr>
        `).join('');

        // Account name row (e-wallet)
        let accountRowHtml = '';
        if (snData.accountName) {
            accountRowHtml = `
                <tr class="info-row">
                    <td class="info-label">Nama Akun</td>
                    <td class="info-value highlight-val">${snData.accountName}</td>
                </tr>
            `;
        }

        // SN / Token box
        let snBoxHtml = '';
        if (snData.hasSN && snData.snValue) {
            const snFontSize = snData.snValue.length > 20 ? '12px' : (snData.snValue.length > 14 ? '15px' : '19px');
            snBoxHtml = `
                <div class="sn-section">
                    <div class="sn-label">${snData.snTitle}</div>
                    <div class="sn-value" style="font-size:${snFontSize}">${snData.snValue}</div>
                </div>
            `;
        }

        // Customer name — only show if not e-wallet (e-wallet shows accountName instead)
        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        const showCustomerName = d.customer_name && !ewallets.includes(type) && type !== 'pln';

        return `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk ${theme.label} — AlfarezMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px 40px;
            color: #1a1a2e;
        }

        .receipt-outer {
            width: 100%;
            max-width: 340px;
        }

        /* Main card */
        .receipt {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.07);
            position: relative;
        }

        /* Watermark layer */
        .watermark {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            align-items: center;
            justify-items: center;
            pointer-events: none;
            z-index: 0;
            padding: 20px;
            gap: 0;
        }
        .wm-tile {
            width: 62px;
            height: 62px;
            object-fit: contain;
            opacity: 0.045;
            transform: rotate(-18deg);
            filter: grayscale(100%);
            display: block;
        }

        /* Header strip */
        .receipt-header {
            background: ${theme.gradient};
            padding: 22px 20px 28px;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .header-logo-wrap {
            width: 62px;
            height: 62px;
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
            overflow: hidden;
        }
        .header-logo { width: 52px; height: 52px; object-fit: contain; }
        .header-store  { font-size: 17px; font-weight: 900; color: #fff; letter-spacing: 0.8px; text-transform: uppercase; }
        .header-tagline { font-size: 10.5px; color: rgba(255,255,255,0.82); margin-top: 3px; font-weight: 500; letter-spacing: 0.3px; }
        .header-product-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            margin-top: 10px;
            letter-spacing: 0.4px;
        }

        /* Jagged edge cut */
        .jagged {
            height: 18px;
            background: #fff;
            position: relative;
            z-index: 2;
        }
        .jagged::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 0; right: 0;
            height: 18px;
            background: radial-gradient(circle at 50% 0%, #fff 12px, transparent 13px);
            background-size: 24px 18px;
            background-repeat: repeat-x;
        }
        .jagged-top {
            height: 18px;
            background: ${theme.accent};
            position: relative;
            z-index: 2;
        }
        .jagged-top::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 18px;
            background: radial-gradient(circle at 50% 100%, #fff 12px, transparent 13px);
            background-size: 24px 18px;
            background-repeat: repeat-x;
        }

        /* Body */
        .receipt-body {
            padding: 6px 22px 20px;
            position: relative;
            z-index: 2;
        }

        /* Success badge */
        .success-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            background: ${theme.accentLight};
            border: 1.5px solid ${theme.accent};
            border-radius: 10px;
            padding: 8px 14px;
            margin-bottom: 16px;
            color: ${theme.accentDark};
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .success-dot { width: 8px; height: 8px; background: ${theme.accent}; border-radius: 50%; flex-shrink: 0; }

        /* Meta info (ref, trx, date) */
        .meta-grid {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            padding: 3px 0;
        }
        .meta-row + .meta-row { border-top: 1px solid #eee; }
        .meta-key { color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .meta-val { color: #222; font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 9px; text-align: right; }

        /* Info table */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-row td { padding: 6px 0; vertical-align: top; }
        .info-row + .info-row td { border-top: 1px dashed #eee; }
        .info-label { color: #777; font-size: 11px; font-weight: 500; width: 42%; padding-right: 8px; }
        .info-value { color: #111; font-size: 11.5px; font-weight: 700; text-align: right; word-break: break-word; }
        .highlight-val { color: ${theme.accentDark}; }

        /* Divider */
        .divider {
            border: none;
            border-top: 1.5px dashed #d4d8dd;
            margin: 14px 0;
        }

        /* SN / Token section */
        .sn-section {
            background: ${theme.accentLight};
            border: 1.5px solid ${theme.accent}33;
            border-radius: 12px;
            padding: 14px 14px;
            text-align: center;
            margin: 14px 0;
        }
        .sn-label {
            font-size: 9.5px;
            font-weight: 800;
            color: ${theme.accentDark};
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 6px;
        }
        .sn-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: #111;
            word-break: break-all;
            letter-spacing: 0.5px;
            line-height: 1.4;
        }

        /* Total row */
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: ${theme.gradient};
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 6px;
        }
        .total-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.9); text-transform: uppercase; letter-spacing: 0.5px; }
        .total-amount { font-size: 18px; font-weight: 900; color: #fff; letter-spacing: -0.5px; }

        /* Footer */
        .receipt-footer {
            text-align: center;
            padding: 16px 22px 22px;
            border-top: 1.5px dashed #e0e0e0;
            margin-top: 14px;
            position: relative;
            z-index: 2;
        }
        .footer-tagline {
            font-size: 11px;
            font-weight: 700;
            color: #333;
            margin-bottom: 3px;
        }
        .footer-sub {
            font-size: 9.5px;
            color: #999;
            font-style: italic;
        }
        .validity-note {
            font-size: 9px;
            color: ${theme.accentDark};
            background: ${theme.accentLight};
            border-radius: 6px;
            padding: 5px 10px;
            margin-top: 10px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        /* Print button */
        .print-btn {
            display: block;
            width: 100%;
            max-width: 340px;
            margin: 18px auto 0;
            padding: 14px;
            background: ${theme.gradient};
            color: #fff;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 16px ${theme.accent}44;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
        }
        .print-btn:active { opacity: 0.85; transform: scale(0.98); }

        @media print {
            body { background: #fff; padding: 0; min-height: unset; }
            .receipt { box-shadow: none; border-radius: 0; }
            .print-btn, .no-print { display: none !important; }
            .receipt-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .success-badge, .meta-grid, .sn-section, .total-section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="receipt-outer">
    <div class="receipt">
        <!-- Watermark -->
        <div class="watermark">${wmHtml}</div>

        <!-- Header -->
        <div class="receipt-header">
            <div class="header-logo-wrap">
                <img src="${logoSrc}" class="header-logo" alt="AlfarezMart">
            </div>
            <div class="header-store">AlfarezMart</div>
            <div class="header-tagline">Pusat Pembayaran Produk Digital</div>
            <div class="header-product-badge">${theme.icon} ${theme.label}</div>
        </div>
        <div class="jagged-top"></div>

        <!-- Body -->
        <div class="receipt-body">
            <!-- Success badge -->
            <div class="success-badge">
                <span class="success-dot"></span>
                Transaksi Berhasil
                <span class="success-dot"></span>
            </div>

            <!-- Meta info -->
            <div class="meta-grid">
                <div class="meta-row">
                    <span class="meta-key">No. Referensi</span>
                    <span class="meta-val">${d.ref_id || '-'}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">ID Transaksi</span>
                    <span class="meta-val">${digiTrxVal}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Tanggal & Waktu</span>
                    <span class="meta-val">${dateStr}</span>
                </div>
            </div>

            <!-- Info rows -->
            <table class="info-table">
                <tbody>
                    <tr class="info-row">
                        <td class="info-label">Produk</td>
                        <td class="info-value">${d.product_name || '-'}</td>
                    </tr>
                    <tr class="info-row">
                        <td class="info-label">Nomor / ID</td>
                        <td class="info-value">${d.customer_no || '-'}</td>
                    </tr>
                    ${accountRowHtml}
                    ${showCustomerName ? `<tr class="info-row"><td class="info-label">Nama</td><td class="info-value">${d.customer_name}</td></tr>` : ''}
                    ${extraRowsHtml}
                </tbody>
            </table>

            <!-- SN / Token -->
            ${snBoxHtml}

            <hr class="divider">

            <!-- Total -->
            <div class="total-section">
                <span class="total-label">Total Bayar</span>
                <span class="total-amount">Rp ${price}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="footer-tagline">Terima kasih telah bertransaksi</div>
            <div class="footer-sub">Struk ini merupakan bukti transaksi yang sah</div>
            <div class="validity-note">✦ Dokumen resmi AlfarezMart · Harap simpan sebagai bukti ✦</div>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">🖨️ &nbsp;Cetak Struk Sekarang</button>
</div>
</body></html>`;
    }

    function executePreviewBrowser() {
        if (!activeTrxData) return;

        const d = { ...activeTrxData };
        const customPriceInput = document.getElementById('custom-print-price');
        if (customPriceInput && customPriceInput.value) {
            d.sell_price = parseInt(customPriceInput.value) || d.sell_price;
        }

        const type  = detectProductType(d);
        const theme = getProductTheme(type);
        const snData = parseSN(d, type);

        const html = buildPrintHTML(d, type, theme, snData);
        const w = window.open('', '_blank', 'width=400,height=760');
        if (!w) { triggerToast('⚠️ Pop-up diblokir browser. Izinkan pop-up untuk halaman ini.', 'warning'); return; }
        w.document.write(html);
        w.document.close();
    }

    /**
     * Preview receipt in modal — also redesigned per product type.
     */
    function getReceiptPreviewContent(d, sellPrice) {
        const type   = detectProductType(d);
        const theme  = getProductTheme(type);
        const snData = parseSN(d, type);
        const price  = parseInt(sellPrice || d.sell_price || 0).toLocaleString('id-ID');
        const BASE   = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/';
        const logoSrc = (BASE.endsWith('/') ? BASE : BASE + '/') + 'public/images/mobile_icon.png';

        let digiTrxVal = (d.digiflazz_trx_id && d.digiflazz_trx_id !== d.ref_id) ? d.digiflazz_trx_id : ((d.trx_id && d.trx_id !== d.ref_id) ? d.trx_id : '');
        if (!digiTrxVal && d.raw_response) {
            try {
                const raw = typeof d.raw_response === 'string' ? JSON.parse(d.raw_response) : d.raw_response;
                if (raw.tr_id) digiTrxVal = String(raw.tr_id);
                else if (raw.trx_id && raw.trx_id !== d.ref_id) digiTrxVal = String(raw.trx_id);
            } catch(e) {}
        }
        if (!digiTrxVal) digiTrxVal = '-';

        // Watermark: 9 tiles
        let wmHtml = '';
        for (let i = 0; i < 9; i++) {
            wmHtml += `<img src="${logoSrc}" style="width:48px;height:48px;object-fit:contain;opacity:0.042;transform:rotate(-18deg);filter:grayscale(100%);display:block;" alt="">`;
        }

        // Extra rows from SN
        let extraRowsHtml = snData.extraRows.map(r => `
            <div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;">
                <span style="color:#888;font-size:11px;font-weight:500;">${r.label}</span>
                <span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;">${r.value}</span>
            </div>
        `).join('');

        // Account name row
        let accountRowHtml = '';
        if (snData.accountName) {
            accountRowHtml = `
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;">
                    <span style="color:#888;font-size:11px;font-weight:500;">Nama Akun</span>
                    <span style="color:${theme.accentDark};font-size:11.5px;font-weight:700;text-align:right;">${snData.accountName}</span>
                </div>
            `;
        }

        // SN box
        let snBoxHtml = '';
        if (snData.hasSN && snData.snValue) {
            const snFs = snData.snValue.length > 20 ? '11px' : (snData.snValue.length > 14 ? '14px' : '17px');
            snBoxHtml = `
                <div style="background:${theme.accentLight};border:1.5px solid ${theme.accent}33;border-radius:10px;padding:11px;text-align:center;margin:10px 0;">
                    <div style="font-size:8.5px;font-weight:800;color:${theme.accentDark};text-transform:uppercase;letter-spacing:1.2px;margin-bottom:5px;">${snData.snTitle}</div>
                    <div id="preview-sn-value" style="font-size:${snFs};font-weight:700;color:#111;word-break:break-all;font-family:'JetBrains Mono',monospace;line-height:1.4;">${snData.snValue}</div>
                </div>
            `;
        }

        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        const showCustomerName = d.customer_name && !ewallets.includes(type) && type !== 'pln';

        return `
            <div style="position:relative;background:#fff;border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;">
                <!-- Watermark -->
                <div style="position:absolute;inset:0;display:grid;grid-template-columns:repeat(3,1fr);align-items:center;justify-items:center;pointer-events:none;z-index:0;padding:16px;gap:0;">
                    ${wmHtml}
                </div>

                <!-- Header -->
                <div style="background:${theme.gradient};padding:18px 16px 22px;text-align:center;position:relative;z-index:2;">
                    <div style="width:54px;height:54px;background:rgba(255,255,255,0.95);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;box-shadow:0 4px 10px rgba(0,0,0,0.18);overflow:hidden;">
                        <img src="${logoSrc}" style="width:44px;height:44px;object-fit:contain;" alt="Logo">
                    </div>
                    <div style="font-size:15px;font-weight:900;color:#fff;letter-spacing:0.8px;text-transform:uppercase;">AlfarezMart</div>
                    <div style="font-size:9.5px;color:rgba(255,255,255,0.8);margin-top:2px;font-weight:500;">Pusat Pembayaran Produk Digital</div>
                    <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);border-radius:20px;padding:4px 12px;font-size:10px;font-weight:700;color:#fff;margin-top:8px;">${theme.icon} ${theme.label}</div>
                </div>

                <!-- Jagged edge -->
                <div style="height:14px;background:#fff;position:relative;z-index:2;">
                    <div style="position:absolute;top:-1px;left:0;right:0;height:14px;background:radial-gradient(circle at 50% 0%,#fff 10px,transparent 11px);background-size:20px 14px;background-repeat:repeat-x;"></div>
                </div>

                <!-- Body -->
                <div style="padding:4px 16px 16px;position:relative;z-index:2;">
                    <!-- Success badge -->
                    <div style="display:flex;align-items:center;justify-content:center;gap:7px;background:${theme.accentLight};border:1.5px solid ${theme.accent};border-radius:9px;padding:7px;margin-bottom:12px;color:${theme.accentDark};font-size:10.5px;font-weight:800;letter-spacing:0.8px;text-transform:uppercase;">
                        <span style="width:7px;height:7px;background:${theme.accent};border-radius:50%;flex-shrink:0;"></span>
                        Transaksi Berhasil
                        <span style="width:7px;height:7px;background:${theme.accent};border-radius:50%;flex-shrink:0;"></span>
                    </div>

                    <!-- Meta -->
                    <div style="background:#f8f9fa;border-radius:9px;padding:9px 11px;margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;padding:2px 0;">
                            <span style="color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">No. Referensi</span>
                            <span style="color:#222;font-weight:700;font-family:monospace;font-size:8.5px;text-align:right;">${d.ref_id || '-'}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;padding:2px 0;border-top:1px solid #eee;">
                            <span style="color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">ID Transaksi</span>
                            <span style="color:#222;font-weight:700;font-family:monospace;font-size:8.5px;text-align:right;">${digiTrxVal}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;padding:2px 0;border-top:1px solid #eee;">
                            <span style="color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">Tanggal</span>
                            <span style="color:#222;font-weight:700;font-family:monospace;font-size:8.5px;text-align:right;">${d.created_at || '-'}</span>
                        </div>
                    </div>

                    <!-- Info rows -->
                    <div style="display:flex;justify-content:space-between;padding:5px 0;">
                        <span style="color:#888;font-size:11px;font-weight:500;">Produk</span>
                        <span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;max-width:58%;">${d.product_name || '-'}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;">
                        <span style="color:#888;font-size:11px;font-weight:500;">Nomor / ID</span>
                        <span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;">${d.customer_no || '-'}</span>
                    </div>
                    ${accountRowHtml}
                    ${showCustomerName ? `<div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;"><span style="color:#888;font-size:11px;font-weight:500;">Nama</span><span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;">${d.customer_name}</span></div>` : ''}
                    ${extraRowsHtml}

                    <!-- SN Box -->
                    ${snBoxHtml}

                    <div style="border-top:1.5px dashed #d4d8dd;margin:12px 0;"></div>

                    <!-- Total -->
                    <div style="display:flex;justify-content:space-between;align-items:center;background:${theme.gradient};border-radius:11px;padding:12px 14px;">
                        <span style="font-size:10.5px;font-weight:700;color:rgba(255,255,255,0.9);text-transform:uppercase;letter-spacing:0.5px;">Total Bayar</span>
                        <span id="preview-total-val" style="font-size:17px;font-weight:900;color:#fff;letter-spacing:-0.5px;">Rp ${price}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div style="text-align:center;padding:12px 16px 18px;border-top:1.5px dashed #e0e0e0;position:relative;z-index:2;">
                    <div style="font-size:10.5px;font-weight:700;color:#333;margin-bottom:2px;">Terima kasih telah bertransaksi</div>
                    <div style="font-size:9px;color:#999;font-style:italic;">Struk ini merupakan bukti transaksi yang sah</div>
                    <div style="font-size:8.5px;color:${theme.accentDark};background:${theme.accentLight};border-radius:6px;padding:4px 10px;margin-top:8px;font-weight:600;">✦ Dokumen resmi AlfarezMart · Harap simpan sebagai bukti ✦</div>
                </div>
            </div>
        `;
    }
