
function showComingSoon(title, desc, icon) {
    AppModal.show({
        title: title,
        subtitle: desc,
        icon: icon || 'bi-clock',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: `
            <div style="text-align:center;padding:20px 0;">
                <div style="width:80px;height:80px;background:var(--warning-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="${icon || 'bi-clock'}" style="font-size:2rem;color:var(--warning);"></i>
                </div>
                <h3 style="font-size:var(--font-size-md);font-weight:700;margin-bottom:8px;">${title}</h3>
                <p style="color:var(--text-muted);font-size:var(--font-size-sm);margin-bottom:16px;">${desc}</p>
                <div style="background:var(--surface-2);border-radius:var(--radius-lg);padding:16px;">
                    <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin:0;">Fitur ini sedang dalam tahap pengembangan dan akan segera tersedia di pembaruan berikutnya. Nantikan! 🚀</p>
                </div>
            </div>
        `,
        submitText: 'Oke, Mengerti',
        hideCancel: true,
        onSubmit: async () => true
    });
}

// Modal Export JS Logic
let exportSupplierData = [];
let exportProductData = [];
async function openExportModal() {
    const html = `
        <style>
            .export-tab { padding: 8px; font-size: 11px; font-weight: 600; text-align: center; border-radius: var(--radius-md); cursor: pointer; flex: 1; transition: 0.2s; }
            .export-tab.active { background: var(--primary); color: white; }
            .export-tab.inactive { background: var(--surface-2); color: var(--text-muted); }
            .export-panel { display: none; margin-top: 15px; }
            .export-panel.active { display: block; }
        </style>
        <div style="display: flex; gap: 8px; margin-bottom: 10px;">
            <div id="tabExport1" class="export-tab active" onclick="switchExportTab(1)">By Supplier</div>
            <div id="tabExport2" class="export-tab inactive" onclick="switchExportTab(2)">By Produk</div>
        </div>
        
        <div id="panelExport1" class="export-panel active">
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Pilih Supplier *</label>
                <div id="exportSupplierSearchContainer1"></div>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                <div class="modal-form-group" style="flex: 1; text-align: left;">
                    <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Tgl Dari (Opsional)</label>
                    <input type="date" id="exportDateFrom1" class="form-control-dark" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="modal-form-group" style="flex: 1; text-align: left;">
                    <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Tgl Sampai (Opsional)</label>
                    <input type="date" id="exportDateTo1" class="form-control-dark" value="${new Date().toISOString().split('T')[0]}">
                </div>
            </div>
            <button class="btn-primary-custom" onclick="executeExport(1)" style="width: 100%; padding: 10px; border-radius: var(--radius-md);"><i class="bi bi-download"></i> Download .xlsx</button>
        </div>
        
        <div id="panelExport2" class="export-panel">
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Cari Nama Produk (Kosong = Semua)</label>
                <div id="exportProductSearchContainer"></div>
            </div>
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Filter Supplier (Opsional)</label>
                <div id="exportSupplierSearchContainer2"></div>
            </div>
            <button class="btn-primary-custom" onclick="executeExport(2)" style="width: 100%; padding: 10px; border-radius: var(--radius-md);"><i class="bi bi-download"></i> Download .xlsx</button>
        </div>
    `;

    AppModal.show({
        title: 'Export Data Produk',
        bodyHTML: html,
        hideFooter: true
    });

    try {
        const res = await api(`${BASE_URL}api/suppliers`);
        exportSupplierData = res.success ? res.data : (Array.isArray(res) ? res : []);
    } catch (e) { console.error("Gagal load supplier", e); }
    
    try {
        const res = await api(`${BASE_URL}api/products`);
        exportProductData = res.success ? res.data : (Array.isArray(res) ? res : []);
    } catch (e) { console.error("Gagal load produk", e); }

    const supOptions = exportSupplierData.map(s => ({ value: s.id.toString(), label: s.name }));
    const prodOptions = exportProductData.map(p => ({ value: p.name, label: p.name }));
    
    window.exportSearchBox1 = new SearchBox(document.getElementById('exportSupplierSearchContainer1'), {
        options: supOptions, placeholder: '-- Ketik/Pilih Supplier --', name: 'exportSupplier1', icon: 'bi-truck'
    });

    window.exportSearchBox2 = new SearchBox(document.getElementById('exportSupplierSearchContainer2'), {
        options: supOptions, placeholder: '-- Semua Supplier --', name: 'exportSupplier2', icon: 'bi-truck'
    });
    
    window.exportProductBox = new SearchBox(document.getElementById('exportProductSearchContainer'), {
        options: prodOptions, placeholder: '-- Ketik Nama Produk --', name: 'exportProductName', icon: 'bi-box'
    });
}

window.switchExportTab = function(tabIdx) {
    document.getElementById('tabExport1').className = (tabIdx === 1) ? 'export-tab active' : 'export-tab inactive';
    document.getElementById('tabExport2').className = (tabIdx === 2) ? 'export-tab active' : 'export-tab inactive';
    document.getElementById('panelExport1').className = (tabIdx === 1) ? 'export-panel active' : 'export-panel';
    document.getElementById('panelExport2').className = (tabIdx === 2) ? 'export-panel active' : 'export-panel';
};

window.executeExport = async function(mode) {
    let payload = { mode: mode };
    if (mode === 1) {
        const supId = document.querySelector('input[name="exportSupplier1"]').value;
        if (!supId) {
            showToast("Pilih supplier terlebih dahulu!", "warning");
            return;
        }
        payload.supplier_id = supId;
        payload.date_from = document.getElementById('exportDateFrom1').value;
        payload.date_to = document.getElementById('exportDateTo1').value;
    } else {
        payload.product_name = document.getElementById('exportProductName').value.trim();
        const supId2 = document.querySelector('input[name="exportSupplier2"]').value;
        if(supId2) payload.supplier_id = supId2;
    }

    try {
        const query = new URLSearchParams(payload).toString();
        showToast("Mempersiapkan data ekspor...", "info");
        const res = await api(`${BASE_URL}api/products/export?${query}`);
        
        if (res.success && res.data && res.data.length > 0) {
            if (typeof XLSX === 'undefined') {
                showToast("Library XLSX belum termuat. Pastikan koneksi internet aktif untuk mendownload library.", "error");
                return;
            }
            const ws = XLSX.utils.json_to_sheet(res.data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data Produk");
            
            const filename = `Export_Produk_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename);
            showToast("Berhasil didownload!", "success");
            AppModal.close();
        } else {
            showToast(res.message || "Tidak ada data ditemukan untuk kriteria ini.", "warning");
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
};

