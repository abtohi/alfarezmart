function openUnifiedContactModal(customer = null, defaultType = 'hp', defaultNo = '') {
    const isEdit = !!customer;
    const services = ['Dana', 'GoPay', 'OVO', 'ShopeePay', 'LinkAja'];
    
    // Inject dynamic CSS to fix disabled select background
    if (!document.getElementById('ppob-contacts-css')) {
        const style = document.createElement('style');
        style.id = 'ppob-contacts-css';
        style.innerHTML = `
            select.glass-input:disabled {
                background-color: var(--surface-2) !important;
                color: var(--text-muted) !important;
                opacity: 0.7 !important;
                cursor: not-allowed !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Parse ewallet accounts if available
    let ewallets = {};
    if (customer && customer.ewallet_accounts) {
        try { ewallets = JSON.parse(customer.ewallet_accounts); } catch(e) {}
    }

    let servicesHtml = services.map(s => `
        <div class="mb-2">
            <div class="form-check custom-checkbox d-flex align-items-center">
                <input class="form-check-input ewallet-check me-2" type="checkbox" value="${s}" id="chk_${s}" ${ewallets[s] ? 'checked' : ''} onchange="toggleEwalletInput('${s}')" style="cursor:pointer; width:18px; height:18px;">
                <label class="form-check-label fw-bold" for="chk_${s}" style="cursor:pointer; color:var(--text-primary);">${s}</label>
            </div>
            <div id="input_wrapper_${s}" style="display: ${ewallets[s] ? 'block' : 'none'}; margin-top: 8px; margin-left: 26px;">
                <input type="text" class="form-control glass-input ewallet-input" id="input_${s}" placeholder="Nama Akun ${s} (Opsional)" value="${ewallets[s] || ''}" style="font-size:13px;">
            </div>
        </div>
    `).join('');

    AppModal.show({
        title: isEdit ? 'Edit Pelanggan' : 'Simpan Pelanggan Baru',
        icon: isEdit ? 'bi-pencil-square' : 'bi-person-plus-fill',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: `
            <input type="hidden" id="uc_id" value="${customer ? customer.id : ''}">
            <div class="mb-3">
                <label class="form-label fw-bold small" style="color:var(--text-secondary);">Tipe Pelanggan</label>
                <select class="form-select glass-input fw-bold" id="uc_type" onchange="toggleUnifiedContactType()" ${isEdit ? 'disabled' : ''}>
                    <option value="hp" ${(customer ? customer.type : defaultType) === 'hp' ? 'selected' : ''}>Nomor HP (Pulsa/Data/E-Wallet)</option>
                    <option value="pln" ${(customer ? customer.type : defaultType) === 'pln' ? 'selected' : ''}>PLN (Token/Tagihan)</option>
                    <option value="game" ${(customer ? customer.type : defaultType) === 'game' ? 'selected' : ''}>Voucher Game</option>
                    <option value="tv" ${(customer ? customer.type : defaultType) === 'tv' ? 'selected' : ''}>TV Voucher</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small" style="color:var(--text-secondary);">Nama Alias <span class="text-danger">*</span></label>
                <input type="text" class="form-control glass-input" id="uc_name" placeholder="Contoh: Budi Utama" value="${customer ? (customer.customer_name || '') : ''}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small" style="color:var(--text-secondary);">Nomor Tujuan / ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control glass-input fw-bold font-monospace" id="uc_no" placeholder="Nomor HP / Meter / ID" value="${customer ? customer.customer_no : defaultNo}" style="letter-spacing:1px; color:var(--primary);">
            </div>
            
            <div id="uc_pln_section" style="display: none; animation: fadeIn 0.3s;">
                <div class="mb-3">
                    <label class="form-label fw-bold small" style="color:var(--text-secondary);">Nama PLN Asli</label>
                    <input type="text" class="form-control glass-input" id="uc_pln_name" placeholder="Nama meteran PLN" value="${customer ? (customer.pln_name || '') : ''}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small" style="color:var(--text-secondary);">Daya (VA)</label>
                    <input type="text" class="form-control glass-input" id="uc_pln_power" placeholder="Contoh: R1M/900" value="${customer ? (customer.pln_power || '') : ''}">
                </div>
            </div>

            <div id="uc_ewallet_section" style="display: none; background: var(--surface-2); padding: 16px; border-radius: 16px; border: 1px solid var(--border-color); animation: fadeIn 0.3s; margin-top: 10px;">
                <label class="form-label fw-bold small mb-2" style="color:var(--primary);"><i class="bi bi-wallet2 me-1"></i> Dukungan E-Wallet</label>
                <p class="small mb-3" style="color:var(--text-muted); font-size:11px;">Centang layanan E-Wallet yang didukung nomor ini, isi nama akun untuk validasi silang.</p>
                <div class="ewallet-list">
                    ${servicesHtml}
                </div>
            </div>
            <style>
                @keyframes fadeIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }
                .custom-checkbox input:checked { background-color: var(--primary); border-color: var(--primary); }
                .ewallet-input { transition: all 0.2s; }
                .ewallet-input:focus { transform: translateX(5px); }
            </style>
        `,
        submitText: 'Simpan',
        onShown: () => {
            toggleUnifiedContactType();
        },
        onSubmit: async () => {
            const type = document.getElementById('uc_type').value;
            const name = document.getElementById('uc_name').value.trim();
            const no = document.getElementById('uc_no').value.trim();
            
            if (!no) {
                showToast('Nomor tujuan wajib diisi', 'warning');
                return false;
            }

            let data = {
                id: document.getElementById('uc_id').value,
                type: type,
                customer_name: name,
                customer_no: no,
                csrf_token: document.getElementById('csrfToken')?.value || (typeof csrfVal !== 'undefined' ? csrfVal : '')
            };

            if (type === 'pln') {
                data.pln_name = document.getElementById('uc_pln_name').value.trim();
                data.pln_power = document.getElementById('uc_pln_power').value.trim();
            } else if (type === 'hp') {
                let ew = {};
                document.querySelectorAll('.ewallet-check:checked').forEach(el => {
                    const s = el.value;
                    const val = document.getElementById(`input_${s}`).value.trim();
                    ew[s] = val;
                });
                data.ewallet_accounts = JSON.stringify(ew);
            }

            try {
                const endpoint = data.id ? 'ppob-customers/update' : 'ppob-customers/create';
                const res = await api(`${BASE_URL}${endpoint}`, 'POST', data);
                if (res.success) {
                    showToast(res.message, 'success');
                    if (typeof loadContacts === 'function') loadContacts();
                    if (typeof filterCustomers === 'function') filterCustomers('all');
                    if (window.location.href.includes('ppob') && !window.location.href.includes('ppob-customers')) {
                        if (typeof initContacts === 'function') initContacts();
                    }
                    return true;
                } else {
                    showToast(res.message, 'error');
                    return false;
                }
            } catch (e) {
                showToast(e.message, 'error');
                return false;
            }
        }
    });
}

function toggleUnifiedContactType() {
    const type = document.getElementById('uc_type').value;
    const plnSec = document.getElementById('uc_pln_section');
    const ewSec = document.getElementById('uc_ewallet_section');
    if(plnSec) plnSec.style.display = (type === 'pln') ? 'block' : 'none';
    if(ewSec) ewSec.style.display = (type === 'hp') ? 'block' : 'none';
}

function toggleEwalletInput(service) {
    const checked = document.getElementById(`chk_${service}`).checked;
    const wrapper = document.getElementById(`input_wrapper_${service}`);
    if (checked) {
        wrapper.style.display = 'block';
        setTimeout(() => document.getElementById(`input_${service}`).focus(), 100);
    } else {
        wrapper.style.display = 'none';
    }
}
