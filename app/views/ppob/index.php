

<!-- Custom CSS for PPOB -->
<style>
    .ppob-header {
        background: var(--gradient-primary);
        border-radius: var(--radius-lg);
        padding: 20px;
        margin-bottom: 24px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ppob-balance {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .category-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    .category-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
        border-color: var(--primary);
    }
    .category-icon {
        font-size: 24px;
        margin-bottom: 8px;
    }
    .category-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-color);
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }
    .product-card {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--surface);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 100px;
    }
    .product-card:hover {
        border-color: var(--primary);
        background: rgba(var(--bs-primary-rgb, 13,110,253), 0.05);
    }
    .product-name {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 8px;
    }
    .product-price {
        font-size: 14px;
        font-weight: bold;
        color: var(--primary);
    }
    .input-group-custom {
        position: relative;
        margin-bottom: 16px;
    }
    .input-group-custom input {
        width: 100%;
        padding: 12px 16px;
        padding-right: 40px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 16px;
        background: var(--surface);
        color: var(--text-color);
    }
    .input-group-custom input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb, 13,110,253), 0.1);
    }
    .input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    #ppob-view { display: block; }
    #category-view { display: none; }
</style>

<div class="page-section">
    <!-- Main PPOB View -->
    <div id="ppob-view">
        <div class="ppob-header">
            <div>
                <h4 style="margin:0;font-weight:700;">Produk Digital</h4>
                <div style="font-size:12px;opacity:0.9;">Layanan PPOB Digiflazz</div>
            </div>
            <div class="ppob-balance">
                <i class="bi bi-wallet2"></i>
                <span id="digi-balance">Loading...</span>
            </div>
        </div>

        <div class="section-title">Pilih Layanan</div>
        <div class="category-grid">
            <div class="category-card" onclick="openCategory('pulsa', 'Pulsa')">
                <div class="category-icon" style="color: #ef4444;"><i class="bi bi-phone"></i></div>
                <div class="category-label">Pulsa</div>
            </div>
            <div class="category-card" onclick="openCategory('data', 'Paket Data')">
                <div class="category-icon" style="color: #3b82f6;"><i class="bi bi-wifi"></i></div>
                <div class="category-label">Data</div>
            </div>
            <div class="category-card" onclick="openCategory('pln', 'Token PLN')">
                <div class="category-icon" style="color: #eab308;"><i class="bi bi-lightning-charge"></i></div>
                <div class="category-label">PLN</div>
            </div>
            <div class="category-card" onclick="openCategory('ewallet', 'E-Wallet')">
                <div class="category-icon" style="color: #06b6d4;"><i class="bi bi-wallet"></i></div>
                <div class="category-label">E-Wallet</div>
            </div>
            <div class="category-card" onclick="openCategory('bpjs', 'BPJS')">
                <div class="category-icon" style="color: #10b981;"><i class="bi bi-hospital"></i></div>
                <div class="category-label">BPJS</div>
            </div>
            <div class="category-card" onclick="openCategory('game', 'Voucher Game')">
                <div class="category-icon" style="color: #8b5cf6;"><i class="bi bi-controller"></i></div>
                <div class="category-label">Game</div>
            </div>
            <div class="category-card" onclick="openCategory('multifinance', 'Angsuran')">
                <div class="category-icon" style="color: #f97316;"><i class="bi bi-building"></i></div>
                <div class="category-label">Angsuran</div>
            </div>
            <div class="category-card" onclick="openCategory('bank', 'Transfer Bank')">
                <div class="category-icon" style="color: #64748b;"><i class="bi bi-bank"></i></div>
                <div class="category-label">Transfer</div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Transaksi Terakhir</div>
            <a href="<?= BASE_URL ?>ppob/history" class="text-primary text-decoration-none" style="font-size:12px;font-weight:600;">Lihat Semua <i class="bi bi-chevron-right"></i></a>
        </div>
        
        <!-- Riwayat Singkat -->
        <div class="card card-custom p-0 overflow-hidden">
            <ul class="list-group list-group-flush" id="recent-transactions">
                <li class="list-group-item text-center text-muted py-3" style="font-size: 13px;">
                    Memuat transaksi...
                </li>
            </ul>
        </div>
    </div>

    <!-- Category Detail View -->
    <div id="category-view">
        <div class="d-flex align-items-center mb-3">
            <button class="btn btn-icon me-2" onclick="closeCategory()" style="background:var(--surface-3);color:var(--text-color);">
                <i class="bi bi-arrow-left"></i>
            </button>
            <h5 class="m-0 fw-bold" id="category-title">Nama Kategori</h5>
        </div>

        <div class="card card-custom mb-3">
            <div class="input-group-custom">
                <input type="text" id="customer-no" placeholder="Masukkan Nomor Tujuan" oninput="handleCustomerNoInput()">
                <i class="bi bi-person-lines-fill input-icon" onclick="openContacts()" style="cursor:pointer;" title="Pilih dari kontak"></i>
            </div>
            
            <!-- Provider Auto Detect / Inquiry Result -->
            <div id="inquiry-result" style="display:none; background:var(--info-bg); color:var(--info); padding:12px; border-radius:var(--radius-md); font-size:12px; margin-bottom:16px;">
                <div class="fw-bold mb-1" id="inquiry-title">Provider</div>
                <div id="inquiry-desc">Memeriksa...</div>
            </div>

            <!-- Brand Selection (E-Wallet, Game, dll) -->
            <div id="brand-selection" style="display:none; margin-bottom: 16px;">
                <label class="form-label" style="font-size:12px;font-weight:600;">Pilih Provider / Layanan</label>
                <select class="form-select" id="brand-select" onchange="loadProducts()">
                    <option value="">Pilih...</option>
                </select>
            </div>
        </div>

        <!-- Product Loading -->
        <div id="product-loading" class="text-center py-4" style="display:none;">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <div class="mt-2 text-muted" style="font-size:12px;">Memuat produk...</div>
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="product-list">
            <!-- Products will be injected here -->
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Transaksi -->
<div class="modal fade" id="trxModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-lg);overflow:hidden;">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title fs-6 fw-bold">Konfirmasi Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="fw-bold" id="confirm-product-name" style="font-size:18px;">Product Name</div>
                    <div class="text-muted" style="font-size:14px;" id="confirm-brand">Brand</div>
                </div>

                <div class="p-3 mb-3" style="background:var(--surface-3);border-radius:var(--radius-md);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted" style="font-size:13px;">No. Tujuan</span>
                        <span class="fw-bold" id="confirm-target" style="font-size:13px;">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" id="confirm-name-row" style="display:none !important;">
                        <span class="text-muted" style="font-size:13px;">Nama Pelanggan</span>
                        <span class="fw-bold text-end" id="confirm-name" style="font-size:13px;">-</span>
                    </div>
                    <hr style="margin:10px 0; border-color:var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size:13px;">Total Bayar</span>
                        <span class="fw-bold text-primary" id="confirm-price" style="font-size:18px;">Rp0</span>
                    </div>
                </div>

                <button class="btn btn-primary w-100" id="btn-process-trx" onclick="processTransaction()" style="padding:12px;font-weight:700;border-radius:var(--radius-md);">
                    Proses Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hasil Transaksi -->
<div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-lg);overflow:hidden;">
            <div class="modal-body p-4 text-center">
                <div id="result-icon" style="font-size:48px;margin-bottom:16px;"></div>
                <h4 class="fw-bold mb-2" id="result-title">Status</h4>
                <p class="text-muted mb-4" id="result-desc" style="font-size:13px;">Message</p>
                
                <div class="p-3 mb-4 text-start" style="background:var(--surface-3);border-radius:var(--radius-md);">
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Serial Number / Token</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-bold" id="result-sn" style="font-size:15px;word-break:break-all;">-</div>
                        <button class="btn btn-sm btn-light" onclick="copySN()" title="Copy SN"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-light flex-1" data-bs-dismiss="modal" onclick="closeCategory()">Tutup</button>
                    <button class="btn btn-primary flex-1" id="btn-print-receipt" onclick="printReceipt()"><i class="bi bi-printer"></i> Cetak Struk</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentCategory = '';
    let currentProducts = [];
    let selectedProduct = null;
    let customerName = null;
    let debounceTimer;
    let isProcessing = false;
    let lastTransaction = null;
    const trxModal = new bootstrap.Modal(document.getElementById('trxModal'));
    const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

    // Prefix Data for Auto-Detect
    const OPERATOR_PREFIX = {
        'telkomsel': ['0811','0812','0813','0821','0822','0823','0851','0852','0853'],
        'indosat':   ['0814','0815','0816','0855','0856','0857','0858'],
        'xl':        ['0817','0818','0819','0859','0877','0878'],
        'axis':      ['0838','0831','0832','0833'],
        'tri':       ['0895','0896','0897','0898','0899'],
        'smartfren': ['0881','0882','0883','0884','0885','0886','0887','0888','0889']
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadBalance();
        loadRecentTransactions();
    });

    async function loadBalance() {
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
            const data = await res.json();
            if (data.success && data.data && data.data.deposit !== undefined) {
                document.getElementById('digi-balance').textContent = 'Rp ' + parseInt(data.data.deposit).toLocaleString('id-ID');
            } else {
                document.getElementById('digi-balance').textContent = 'Error';
            }
        } catch (e) {
            document.getElementById('digi-balance').textContent = 'Offline';
        }
    }

    async function loadRecentTransactions() {
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/transactions?limit=5');
            const data = await res.json();
            const list = document.getElementById('recent-transactions');
            list.innerHTML = '';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(trx => {
                    let statusColor = trx.status === 'success' ? 'success' : (trx.status === 'pending' ? 'warning' : 'danger');
                    let statusIcon = trx.status === 'success' ? 'check-circle-fill' : (trx.status === 'pending' ? 'clock-fill' : 'x-circle-fill');
                    
                    list.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div class="d-flex flex-column" style="min-width:0;">
                                <span class="fw-bold text-truncate" style="font-size:12px;">${trx.product_name}</span>
                                <span class="text-muted" style="font-size:10px;">${trx.customer_no} • ${trx.created_at}</span>
                            </div>
                            <div class="text-end ms-2 flex-shrink-0">
                                <div class="text-${statusColor}" style="font-size:11px;font-weight:600;">
                                    <i class="bi bi-${statusIcon}"></i> ${trx.status.toUpperCase()}
                                </div>
                                <div class="fw-bold" style="font-size:12px;">Rp${parseInt(trx.sell_price).toLocaleString('id-ID')}</div>
                            </div>
                        </li>
                    `;
                });
            } else {
                list.innerHTML = '<li class="list-group-item text-center text-muted py-3" style="font-size: 13px;">Belum ada transaksi</li>';
            }
        } catch (e) {
            console.error(e);
        }
    }

    function openCategory(cat, title) {
        currentCategory = cat;
        document.getElementById('category-title').textContent = title;
        document.getElementById('ppob-view').style.display = 'none';
        document.getElementById('category-view').style.display = 'block';
        document.getElementById('customer-no').value = '';
        document.getElementById('product-list').innerHTML = '';
        document.getElementById('inquiry-result').style.display = 'none';
        document.getElementById('brand-selection').style.display = 'none';
        customerName = null;

        // Custom logic based on category
        if (cat === 'ewallet' || cat === 'game' || cat === 'multifinance') {
            loadBrands(cat);
        } else if (cat === 'pulsa' || cat === 'data') {
            document.getElementById('customer-no').placeholder = 'Masukkan Nomor HP';
        } else if (cat === 'pln') {
            document.getElementById('customer-no').placeholder = 'Masukkan No. Meter / ID Pelanggan';
        } else {
            loadProducts();
        }
    }

    function closeCategory() {
        document.getElementById('category-view').style.display = 'none';
        document.getElementById('ppob-view').style.display = 'block';
        loadBalance();
        loadRecentTransactions();
    }

    async function loadBrands(cat) {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/brands/${cat}`);
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('brand-select');
                select.innerHTML = '<option value="">Pilih...</option>';
                data.data.forEach(brand => {
                    select.innerHTML += `<option value="${brand}">${brand}</option>`;
                });
                document.getElementById('brand-selection').style.display = 'block';
            }
        } catch (e) {
            console.error(e);
        }
    }

    function handleCustomerNoInput() {
        clearTimeout(debounceTimer);
        const no = document.getElementById('customer-no').value.replace(/\D/g, '');
        
        if (currentCategory === 'pulsa' || currentCategory === 'data') {
            if (no.length >= 4) {
                const prefix = no.substring(0, 4);
                let detectedBrand = null;
                for (const [brand, prefixes] of Object.entries(OPERATOR_PREFIX)) {
                    if (prefixes.includes(prefix)) {
                        detectedBrand = brand.toUpperCase();
                        break;
                    }
                }
                
                const inqRes = document.getElementById('inquiry-result');
                if (detectedBrand) {
                    inqRes.style.display = 'block';
                    document.getElementById('inquiry-title').textContent = detectedBrand;
                    document.getElementById('inquiry-desc').textContent = 'Operator ditemukan';
                    loadProducts(detectedBrand);
                } else {
                    inqRes.style.display = 'none';
                    document.getElementById('product-list').innerHTML = '';
                }
            } else {
                document.getElementById('inquiry-result').style.display = 'none';
                document.getElementById('product-list').innerHTML = '';
            }
        } else if (currentCategory === 'pln') {
            if (no.length >= 11) {
                debounceTimer = setTimeout(() => {
                    inquiryPLN(no);
                }, 1000);
            }
        }
    }

    async function inquiryPLN(no) {
        const inqRes = document.getElementById('inquiry-result');
        inqRes.style.display = 'block';
        inqRes.className = 'text-info';
        document.getElementById('inquiry-title').textContent = 'Mengecek ID Pelanggan...';
        document.getElementById('inquiry-desc').textContent = 'Mohon tunggu';

        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/inquiry-pln', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ customer_no: no })
            });
            const data = await res.json();
            
            if (data.success && data.data && data.data.name) {
                customerName = data.data.name;
                inqRes.style.background = 'var(--success-bg)';
                inqRes.style.color = 'var(--success)';
                document.getElementById('inquiry-title').textContent = customerName;
                document.getElementById('inquiry-desc').innerHTML = `
                    Daya: ${data.data.segment_power || '-'} <br>
                    ID: ${data.data.subscriber_id || no}
                `;
                loadProducts('PLN'); // Load token list
            } else {
                customerName = null;
                inqRes.style.background = 'var(--danger-bg)';
                inqRes.style.color = 'var(--danger)';
                document.getElementById('inquiry-title').textContent = 'ID Tidak Ditemukan';
                document.getElementById('inquiry-desc').textContent = 'Silakan periksa kembali nomor pelanggan';
                document.getElementById('product-list').innerHTML = '';
            }
        } catch (e) {
            inqRes.style.background = 'var(--warning-bg)';
            inqRes.style.color = 'var(--warning)';
            document.getElementById('inquiry-title').textContent = 'Gagal Cek';
            document.getElementById('inquiry-desc').textContent = 'Coba lagi nanti atau langsung pilih nominal token.';
            loadProducts('PLN');
        }
    }

    async function loadProducts(brandOverride = null) {
        let brand = brandOverride;
        if (!brand && document.getElementById('brand-selection').style.display !== 'none') {
            brand = document.getElementById('brand-select').value;
            if (!brand) {
                document.getElementById('product-list').innerHTML = '';
                return;
            }
        }

        document.getElementById('product-loading').style.display = 'block';
        document.getElementById('product-list').innerHTML = '';

        try {
            let url = `<?= BASE_URL ?>api/ppob/products/${currentCategory}`;
            if (brand) url += `?brand=${encodeURIComponent(brand)}`;
            
            const res = await fetch(url);
            const data = await res.json();
            
            document.getElementById('product-loading').style.display = 'none';
            
            if (data.success && data.data.length > 0) {
                currentProducts = data.data;
                const list = document.getElementById('product-list');
                data.data.forEach(p => {
                    const price = parseInt(p.sell_price).toLocaleString('id-ID');
                    list.innerHTML += `
                        <div class="product-card" onclick='confirmTransaction(${JSON.stringify(p).replace(/'/g, "\\'")})'>
                            <div class="product-name">${p.product_name}</div>
                            <div class="product-price">Rp${price}</div>
                        </div>
                    `;
                });
            } else {
                document.getElementById('product-list').innerHTML = `
                    <div class="text-center text-muted" style="grid-column:1/-1;padding:20px;font-size:13px;">
                        Produk tidak tersedia untuk saat ini
                    </div>
                `;
            }
        } catch (e) {
            console.error(e);
            document.getElementById('product-loading').style.display = 'none';
        }
    }

    function confirmTransaction(product) {
        const no = document.getElementById('customer-no').value;
        if (!no) {
            alert('Silakan isi nomor tujuan terlebih dahulu');
            document.getElementById('customer-no').focus();
            return;
        }

        selectedProduct = product;
        document.getElementById('confirm-product-name').textContent = product.product_name;
        document.getElementById('confirm-brand').textContent = product.brand;
        document.getElementById('confirm-target').textContent = no;
        document.getElementById('confirm-price').textContent = 'Rp ' + parseInt(product.sell_price).toLocaleString('id-ID');

        const nameRow = document.getElementById('confirm-name-row');
        if (customerName) {
            document.getElementById('confirm-name').textContent = customerName;
            nameRow.style.setProperty('display', 'flex', 'important');
        } else {
            nameRow.style.setProperty('display', 'none', 'important');
        }

        trxModal.show();
    }

    async function processTransaction() {
        if (isProcessing) return;
        
        const no = document.getElementById('customer-no').value;
        const btn = document.getElementById('btn-process-trx');
        
        isProcessing = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
        btn.disabled = true;

        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/transaction', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sku: selectedProduct.buyer_sku_code,
                    customer_no: no,
                    customer_name: customerName
                })
            });
            const data = await res.json();
            
            trxModal.hide();
            
            // Build result view
            const rIcon = document.getElementById('result-icon');
            const rTitle = document.getElementById('result-title');
            
            if (data.success && data.data) {
                lastTransaction = {
                    ...data.data,
                    product_name: selectedProduct.product_name,
                    customer_no: no,
                    customer_name: customerName,
                    sell_price: data.data.sell_price || selectedProduct.sell_price
                };

                const status = data.data.status ? data.data.status.toLowerCase() : '';
                
                if (status === 'sukses') {
                    rIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                    rTitle.textContent = 'Transaksi Sukses';
                } else if (status === 'pending') {
                    rIcon.innerHTML = '<i class="bi bi-clock-fill text-warning"></i>';
                    rTitle.textContent = 'Transaksi Diproses';
                } else {
                    rIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                    rTitle.textContent = 'Transaksi Gagal';
                }
                
                document.getElementById('result-desc').textContent = data.data.message || '';
                document.getElementById('result-sn').textContent = data.data.sn || '-';
                
            } else {
                rIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i>';
                rTitle.textContent = 'Gagal';
                document.getElementById('result-desc').textContent = data.message || 'Terjadi kesalahan sistem';
                document.getElementById('result-sn').textContent = '-';
                lastTransaction = null;
            }
            
            resultModal.show();
            
        } catch (e) {
            console.error(e);
            alert('Gagal memproses transaksi. Cek koneksi internet.');
        } finally {
            isProcessing = false;
            btn.innerHTML = 'Proses Sekarang';
            btn.disabled = false;
        }
    }

    function copySN() {
        const sn = document.getElementById('result-sn').textContent;
        if (sn && sn !== '-') {
            navigator.clipboard.writeText(sn);
            alert('SN berhasil disalin!');
        }
    }

    // Call native Android contact picker if in WebView, else fallback
    function openContacts() {
        if (window.AndroidInterface && window.AndroidInterface.pickContact) {
            window.AndroidInterface.pickContact();
        } else {
            alert('Fitur ambil dari kontak hanya tersedia di aplikasi Android.');
        }
    }

    // Callback from Android Interface
    function onContactPicked(number) {
        let no = number.replace(/\D/g, '');
        if (no.startsWith('62')) no = '0' + no.substring(2);
        document.getElementById('customer-no').value = no;
        handleCustomerNoInput();
    }

    async function printReceipt() {
        if (!lastTransaction) return;
        
        const btn = document.getElementById('btn-print-receipt');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        try {
            // Need to fetch settings first for printer configuration
            const res = await fetch('<?= BASE_URL ?>api/settings/receipt');
            const data = await res.json();
            const storeSettings = {
                name: '<?= htmlspecialchars($_ENV['STORE_NAME'] ?? 'AlfarezMart') ?>',
                address: '<?= htmlspecialchars($_ENV['STORE_ADDRESS'] ?? '') ?>',
                phone: '<?= htmlspecialchars($_ENV['STORE_PHONE'] ?? '') ?>'
            };
            
            // Assume printDigitalReceipt exists in window.Printer from printer.js
            if (window.Printer && typeof window.Printer.printDigitalReceipt === 'function') {
                await window.Printer.printDigitalReceipt(lastTransaction, data.data || {}, storeSettings);
            } else {
                alert('Fungsi cetak Bluetooth belum tersedia atau printer.js belum termuat.');
            }
        } catch (e) {
            console.error('Print error:', e);
            alert('Gagal mencetak struk: ' + e.message);
        } finally {
            btn.innerHTML = '<i class="bi bi-printer"></i> Cetak Struk';
        }
    }
</script>


