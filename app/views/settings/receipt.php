<!-- Receipt Settings View -->
<?php
$settingModel = new \App\Models\SettingModel();
$storeName = $settingModel->get('store_name', 'AlfarezMart');
$storeAddress = $settingModel->get('store_address', '');
$storePhone = $settingModel->get('store_phone', '');
$printerWidth = $settingModel->get('thermal_printer_width', '58');
$header = $settingModel->get('receipt_header', '');
$footer = $settingModel->get('receipt_footer', '');
$logo = $settingModel->get('store_logo', '');
?>
<div class="page-section">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Pengaturan Struk</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Atur nama toko, alamat, header/footer kustom, dan lebar printer</p>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>" />

    <!-- Settings Form -->
    <form id="receipt-settings-form">
        <!-- Toko Info Section -->
        <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
            <div style="font-weight:600; margin-bottom:12px; color:var(--text-primary);"><i class="bi bi-shop" style="color:var(--primary); margin-right:8px;"></i> Informasi Toko</div>
            
            <div style="margin-bottom:12px;text-align:center;">
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:8px; text-align:left;">Logo Toko (Muncul di Cetak Browser/iOS)</label>
                <img id="logo_preview" src="<?= $logo ? $logo : BASE_URL . 'public/images/Icon.png' ?>" style="max-width:80px;max-height:80px;border-radius:8px;margin-bottom:8px;border:1px solid var(--border-color);object-fit:contain;">
                <input type="file" id="store_logo_input" accept="image/*" style="display:block;width:100%;font-size:12px;" onchange="previewLogo(this)">
                <input type="hidden" id="store_logo_base64" name="store_logo" value="<?= htmlspecialchars($logo) ?>">
            </div>
            
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Nama Toko</label>
                <input id="store_name" name="store_name" type="text" value="<?= htmlspecialchars($storeName) ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="Nama Toko" required />
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Alamat Toko</label>
                <textarea id="store_address" name="store_address" rows="2" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); resize:none;" placeholder="Alamat Toko"><?= htmlspecialchars($storeAddress) ?></textarea>
            </div>

            <div>
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Telepon Toko</label>
                <input id="store_phone" name="store_phone" type="text" value="<?= htmlspecialchars($storePhone) ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="08xxxxxxx" />
            </div>
        </div>

        <!-- Printer Settings Section -->
        <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
            <div style="font-weight:600; margin-bottom:12px; color:var(--text-primary);"><i class="bi bi-printer" style="color:var(--success); margin-right:8px;"></i> Pengaturan Printer</div>
            
            <div>
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Lebar Printer (mm)</label>
                <select id="thermal_printer_width" name="thermal_printer_width" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);">
                    <option value="58" <?= $printerWidth == '58' ? 'selected' : '' ?>>58mm (32 karakter)</option>
                    <option value="80" <?= $printerWidth == '80' ? 'selected' : '' ?>>80mm (48 karakter)</option>
                </select>
            </div>
        </div>

        <!-- Custom Text Section -->
        <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
            <div style="font-weight:600; margin-bottom:12px; color:var(--text-primary);"><i class="bi bi-file-text" style="color:var(--warning); margin-right:8px;"></i> Teks Kustom Struk</div>
            
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Header Struk (opsional)</label>
                <textarea id="receipt_header" name="receipt_header" rows="3" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); resize:none;" placeholder="Tambahkan pesan atau judul di atas detail transaksi"><?= htmlspecialchars($header) ?></textarea>
                <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Ditampilkan setelah nama toko</small>
            </div>

            <div>
                <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Footer Struk (opsional)</label>
                <textarea id="receipt_footer" name="receipt_footer" rows="3" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); resize:none;" placeholder="Catatan atau syarat &amp; ketentuan di bawah total"><?= htmlspecialchars($footer) ?></textarea>
                <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Ditampilkan setelah total pembayaran</small>
            </div>
        </div>

        <button type="submit" class="btn-primary-custom" style="width:100%; padding:12px; font-weight:600; margin-bottom:8px;">💾 Simpan Pengaturan</button>
        <a href="<?= BASE_URL ?>settings" class="btn-outline-custom" style="width:100%; padding:12px; text-align:center; display:block; font-weight:600;">Kembali ke Pengaturan</a>
    </form>

    <!-- Live Preview Struk Thermal -->
    <div style="margin-top:24px;">
        <div style="font-weight:700; font-size:var(--font-size-md); margin-bottom:12px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-eye" style="color:var(--info);"></i> Preview Struk Thermal
        </div>
        <div style="background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-lg); max-width:320px; margin:0 auto; box-shadow:var(--shadow-md); overflow:hidden; text-align:center;">
            <div id="receiptPreview" style="display:inline-block; font-family:'Courier New', monospace; font-size:11px; line-height:1.5; color:#000; padding:16px 12px; white-space:pre-wrap; word-break:break-word; text-align:left;">
                <!-- Rendered by JS -->
            </div>
        </div>
        <p style="text-align:center; font-size:var(--font-size-xs); color:var(--text-muted); margin-top:8px;">
            <i class="bi bi-info-circle"></i> Preview simulasi struk printer thermal Bluetooth (bukan versi web/AirPrint)
        </p>
    </div>
</div>

<script>
    const csrfToken = document.getElementById('csrfToken').value;

    // ── Load settings from PHP injection ────────────────────────────
    function loadReceiptSettings() {
        renderPreview();
    }

    // ── Save settings ───────────────────────────────────────────────
    async function saveReceiptSettings(event) {
        event.preventDefault();
        const form = document.getElementById('receipt-settings-form');
        const btn = form.querySelector('button[type="submit"]');
        const btnText = btn.textContent;

        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';

            const data = {
                csrf_token: csrfToken,
                store_name: document.getElementById('store_name').value,
                store_address: document.getElementById('store_address').value,
                store_phone: document.getElementById('store_phone').value,
                thermal_printer_width: document.getElementById('thermal_printer_width').value,
                receipt_header: document.getElementById('receipt_header').value,
                receipt_footer: document.getElementById('receipt_footer').value,
                store_logo: document.getElementById('store_logo_base64').value,
            };

            const result = await api('<?= BASE_URL ?>api/settings/receipt', 'POST', data);
            showToast(result.message || 'Pengaturan berhasil disimpan', 'success');
        } catch (error) {
            console.error('Error saving settings:', error);
            showToast(error.message || 'Gagal menyimpan pengaturan.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = btnText;
        }
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo_preview').src = e.target.result;
                document.getElementById('store_logo_base64').value = e.target.result;
                renderPreview();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ── Live Preview Renderer ───────────────────────────────────────
    function escLocal(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    function centerText(text, width) {
        text = String(text || '');
        if (text.length >= width) return text.substring(0, width);
        const pad = Math.floor((width - text.length) / 2);
        return '\u00A0'.repeat(pad) + text;
    }

    function padLine(left, right, width) {
        left = String(left || '');
        right = String(right || '');
        const spaces = width - left.length - right.length;
        if (spaces < 1) return left.substring(0, width - right.length - 1) + ' ' + right;
        return left + '\u00A0'.repeat(spaces) + right;
    }

    function wrapText(text, width) {
        const words = String(text || '').split(/\s+/);
        const lines = [];
        let line = '';
        words.forEach(word => {
            if (!word) return;
            const test = line ? line + ' ' + word : word;
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

    function renderPreview() {
        const storeName = document.getElementById('store_name').value || 'AlfarezMart';
        const storeAddress = document.getElementById('store_address').value || '';
        const storePhone = document.getElementById('store_phone').value || '';
        const header = document.getElementById('receipt_header').value || '';
        const footer = document.getElementById('receipt_footer').value || '';
        const printerWidth = parseInt(document.getElementById('thermal_printer_width').value) || 58;
        const width = printerWidth >= 80 ? 48 : 32;
        const sep = '-'.repeat(width);

        const now = new Date();
        const tgl = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const jam = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        let lines = [];

        // Header section (centered)
        lines.push(centerText('FAKTUR BELANJA', width));
        lines.push(centerText(storeName.toUpperCase(), width));

        if (storeAddress) {
            storeAddress.split(/\r?\n/).forEach(seg => {
                wrapText(seg.trim(), width).forEach(l => lines.push(centerText(l, width)));
            });
        }
        if (storePhone) {
            lines.push(centerText(storePhone, width));
        }
        if (header) {
            header.split(/\r?\n/).forEach(seg => {
                wrapText(seg.trim(), width).forEach(l => lines.push(centerText(l, width)));
            });
        }

        lines.push(sep);

        // Invoice info
        lines.push(padLine('No:', 'INV-20260518-001', width));
        lines.push(padLine('Tgl:', tgl + ' ' + jam, width));
        lines.push(sep);

        // Sample items
        const sampleItems = [
            { name: 'Indomie Goreng Spesial', qty: 2, unit: 'bks', price: 3500 },
            { name: 'Teh Botol Sosro 350ml', qty: 1, unit: 'btl', price: 4000 },
            { name: 'Aqua 600ml', qty: 3, unit: 'btl', price: 3000 },
        ];
        sampleItems.forEach(item => {
            wrapText(item.name, width).forEach(l => lines.push(l));
            const left = '  ' + item.qty + item.unit + ' x Rp' + item.price.toLocaleString('id-ID');
            const total = item.qty * item.price;
            lines.push(padLine(left, 'Rp' + total.toLocaleString('id-ID'), width));
        });

        lines.push(sep);

        // Total
        const grandTotal = sampleItems.reduce((s, i) => s + i.qty * i.price, 0);
        lines.push(padLine('TOTAL', 'Rp' + grandTotal.toLocaleString('id-ID'), width));

        // Footer
        if (footer) {
            lines.push('');
            footer.split(/\r?\n/).forEach(seg => {
                wrapText(seg.trim(), width).forEach(l => lines.push(centerText(l, width)));
            });
        }

        lines.push('');
        lines.push('');

        // Render into preview div
        const previewEl = document.getElementById('receiptPreview');
        previewEl.innerHTML = lines.map(l => escLocal(l)).join('\n');
    }

    // ── Event Listeners ─────────────────────────────────────────────
    document.getElementById('receipt-settings-form').addEventListener('submit', saveReceiptSettings);

    // Live preview on any form input change
    ['store_name', 'store_address', 'store_phone', 'thermal_printer_width', 'receipt_header', 'receipt_footer'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', renderPreview);
            el.addEventListener('change', renderPreview);
        }
    });

    loadReceiptSettings();
</script>
