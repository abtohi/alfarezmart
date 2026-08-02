<!-- Load DataTables CSS before custom styles so theme overrides take priority -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
/* Modern Price List Design System */
.price-page-wrapper {
    max-width: 1300px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

.price-header-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}

.price-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, #3b82f6 50%, #8b5cf6 100%);
}

.price-stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 30px;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.price-stat-pill i {
    color: var(--primary);
    font-size: 0.95rem;
}

.category-pills-container {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 1.25rem;
    scrollbar-width: thin;
}

.category-pills-container::-webkit-scrollbar {
    height: 4px;
}
.category-pills-container::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 4px;
}

.cat-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 700;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease-in-out;
}

.cat-pill-btn:hover {
    background: var(--surface-2);
    color: var(--text-primary);
    transform: translateY(-1px);
}

.cat-pill-btn.active {
    background: linear-gradient(135deg, var(--primary) 0%, #2563eb 100%);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.price-filter-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.price-table-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

/* Dynamic Theme Adaptation Overrides for Table & Cells */
table.dataTable,
table.dataTable > tbody > tr,
table.dataTable > tbody > tr > td,
table.dataTable > thead > tr > th,
.price-table,
.price-table > tbody > tr,
.price-table > tbody > tr > td,
.price-table > thead > tr > th,
.table > :not(caption) > * > * {
    background-color: var(--surface-1) !important;
    color: var(--text-primary) !important;
    border-bottom-color: var(--border-color) !important;
    box-shadow: none !important;
}

.price-table,
table.dataTable {
    width: 100% !important;
    margin-bottom: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

.price-table thead th,
table.dataTable thead th {
    background-color: var(--surface-2) !important;
    color: var(--text-muted) !important;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    padding: 14px 16px !important;
    border-bottom: 1px solid var(--border-color) !important;
    white-space: nowrap;
}

.price-table tbody tr,
table.dataTable tbody tr {
    transition: background-color 0.15s ease;
    background-color: var(--surface-1) !important;
}

.price-table tbody tr:hover,
.price-table tbody tr:hover > td,
table.dataTable tbody tr:hover,
table.dataTable tbody tr:hover > td {
    background-color: var(--surface-2) !important;
}

.price-table tbody td,
table.dataTable tbody td {
    padding: 12px 16px !important;
    border-bottom: 1px solid var(--border-color) !important;
    color: var(--text-primary) !important;
    vertical-align: middle;
}

.sku-badge {
    font-family: var(--font-mono, SFMono-Regular, Menlo, Monaco, Consolas, monospace);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    background: var(--surface-2);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.badge-type-prepaid {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #10b981 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
}

.badge-type-postpaid {
    background: rgba(245, 158, 11, 0.15) !important;
    color: #f59e0b !important;
    border: 1px solid rgba(245, 158, 11, 0.3) !important;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
}

.profit-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.68rem;
    font-weight: 700;
    color: #10b981;
    background: rgba(16, 185, 129, 0.12);
    padding: 2px 8px;
    border-radius: 12px;
}

/* Custom DataTables Override for Dark & Light Themes */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    display: none !important;
}

.dataTables_wrapper .dataTables_info {
    font-size: 0.8rem !important;
    color: var(--text-muted) !important;
    padding: 1rem 1.25rem !important;
}

.dataTables_wrapper .dataTables_paginate {
    padding: 0.75rem 1.25rem !important;
    display: flex !important;
    gap: 4px !important;
    align-items: center !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    background: var(--surface-2) !important;
    color: var(--text-secondary) !important;
    border: 1px solid var(--border-color) !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
    background: var(--surface-3) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, var(--primary) 0%, #2563eb 100%) !important;
    color: #ffffff !important;
    border-color: transparent !important;
    font-weight: 800 !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
}

@media (max-width: 768px) {
    .price-header-card {
        padding: 1.25rem;
    }
    .price-stat-pill {
        font-size: 0.72rem;
        padding: 4px 10px;
    }
}
</style>

<div class="price-page-wrapper">
    <!-- Header Card -->
    <div class="price-header-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="fw-extrabold mb-1 d-flex align-items-center gap-2" style="color: var(--text-primary); font-weight: 800;">
                    <i class="bi bi-tags-fill text-primary"></i> Daftar Harga Produk PPOB
                </h4>
                <p class="text-muted small mb-0">
                    Katalog lengkap produk digital PPOB (Pulsa, Data, E-Wallet, PLN, Game, TV, Pasca) dengan kalkulasi margin untung otomatis.
                </p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="syncPrices()" id="btn-sync-prices">
                <i class="bi bi-arrow-repeat fs-6"></i> Sinkronkan Manual
            </button>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top border-secondary border-opacity-10">
            <div class="price-stat-pill">
                <i class="bi bi-box-seam"></i>
                <span>Total Produk: <b id="stat-total-products" style="color: var(--text-primary);">0</b></span>
            </div>
            <div class="price-stat-pill">
                <i class="bi bi-grid-fill"></i>
                <span>Kategori: <b id="stat-total-cats" style="color: var(--text-primary);">0</b></span>
            </div>
            <div class="price-stat-pill">
                <i class="bi bi-shop"></i>
                <span>Brand / Provider: <b id="stat-total-brands" style="color: var(--text-primary);">0</b></span>
            </div>
            <div class="price-stat-pill ms-auto">
                <i class="bi bi-clock-history text-success"></i>
                <span>Update Otomatis: <b style="color: var(--success);">Aktif (Cron Job)</b></span>
            </div>
        </div>
    </div>

    <!-- Category Pills Classifier -->
    <div class="category-pills-container" id="category-pills">
        <button class="cat-pill-btn active" onclick="filterCategory('', this)">
            <i class="bi bi-grid-3x3-gap-fill"></i> Semua Produk
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('pulsa', this)">
            <i class="bi bi-phone-fill"></i> Pulsa
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('data', this)">
            <i class="bi bi-wifi"></i> Data / Kuota
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('sms_nelpon', this)">
            <i class="bi bi-chat-text-fill"></i> SMS & Nelpon
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('ewallet', this)">
            <i class="bi bi-wallet2"></i> E-Wallet
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('pln', this)">
            <i class="bi bi-lightning-charge-fill"></i> PLN Token
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('game', this)">
            <i class="bi bi-controller"></i> Voucher Game
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('tv', this)">
            <i class="bi bi-tv-fill"></i> TV & Tagihan
        </button>
        <button class="cat-pill-btn" onclick="filterCategory('postpaid', this)">
            <i class="bi bi-file-earmark-text-fill"></i> Pascabayar
        </button>
    </div>

    <!-- Controls Filter Card -->
    <div class="price-filter-card">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearchInput" class="form-control glass-input ps-5 pe-4 rounded-pill" placeholder="Cari nama produk, SKU, atau brand..." style="font-size: 0.85rem;">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <select id="brandFilterSelect" class="form-select glass-input rounded-pill" style="font-size: 0.85rem;" onchange="applyCustomFilters()">
                    <option value="">Semua Brand / Provider</option>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <select id="typeFilterSelect" class="form-select glass-input rounded-pill" style="font-size: 0.85rem;" onchange="applyCustomFilters()">
                    <option value="">Semua Tipe</option>
                    <option value="prepaid">Prabayar</option>
                    <option value="postpaid">Pascabayar</option>
                </select>
            </div>
            <div class="col-md-2 col-12 ms-auto text-end">
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <span class="small text-muted" style="font-size: 0.75rem;">Tampilkan:</span>
                    <select id="pageLengthSelect" class="form-select glass-input rounded-pill w-auto py-1 px-3" style="font-size: 0.8rem;" onchange="changePageLength(this.value)">
                        <option value="15">15</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="price-table-card">
        <div class="table-responsive">
            <table class="table price-table align-middle" id="priceTable">
                <thead>
                    <tr>
                        <th>Kategori & Brand</th>
                        <th>Kode SKU & Produk</th>
                        <th>Tipe</th>
                        <th>Seller & SR</th>
                        <th class="text-end">Harga Modal</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Profit / Margin</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p class="text-muted fw-bold small mb-0">Memuat daftar harga produk digital...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0" style="background: var(--surface-1); border-radius: 20px; box-shadow: var(--shadow-lg);">
            <div class="spinner-border text-primary mx-auto mb-3" style="width: 2.8rem; height: 2.8rem;" role="status"></div>
            <h5 class="fw-bold mb-1" style="color: var(--text-primary);">Menyinkronkan...</h5>
            <p class="small text-muted mb-0" style="font-size: 0.78rem;">Menarik data produk dari server Digiflazz. Mohon tunggu beberapa saat.</p>
        </div>
    </div>
</div>

<!-- DataTables Dependencies (JS loaded after HTML) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
let dataTable;
let rawProductsData = [];
let activeCategoryFilter = '';

$(document).ready(function() {
    loadPrices();

    $('#customSearchInput').on('keyup input', function() {
        if (dataTable) {
            dataTable.search(this.value).draw();
        }
    });
});

async function loadPrices() {
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/products/all');
        const data = await res.json();
        
        const tbody = $('#priceTable tbody');
        tbody.empty();

        if (data.success && data.data && data.data.length > 0) {
            rawProductsData = data.data;

            // Populate Statistics
            $('#stat-total-products').text(rawProductsData.length.toLocaleString('id-ID'));
            const uniqueCats = [...new Set(rawProductsData.map(p => p.category))].filter(Boolean);
            const uniqueBrands = [...new Set(rawProductsData.map(p => p.brand))].filter(Boolean);
            $('#stat-total-cats').text(uniqueCats.length);
            $('#stat-total-brands').text(uniqueBrands.length);

            // Populate Brand Select Dropdown
            const brandSelect = $('#brandFilterSelect');
            brandSelect.html('<option value="">Semua Brand / Provider</option>');
            uniqueBrands.sort().forEach(b => {
                brandSelect.append(`<option value="${b}">${b}</option>`);
            });

            rawProductsData.forEach(p => {
                const isPostpaid = (p.type === 'postpaid' || p.type === 'pascabayar');
                const typeBadge = isPostpaid 
                    ? '<span class="badge-type-postpaid"><i class="bi bi-file-earmark-text-fill me-1"></i>Pascabayar</span>' 
                    : '<span class="badge-type-prepaid"><i class="bi bi-lightning-fill me-1"></i>Prabayar</span>';
                
                // Seller & SR HTML
                let sellerHtml = '<span class="text-muted" style="font-size:0.75rem;">-</span>';
                if (p.seller_name) {
                    let sellerSrHtml = '';
                    if (p.success_rate !== null && p.success_rate !== undefined) {
                        let color = p.success_rate >= 80 ? '#10b981' : (p.success_rate >= 50 ? '#f59e0b' : '#ef4444');
                        sellerSrHtml = `<span style="color: ${color}; font-weight: 700; font-size: 0.65rem;" title="SR Seller"><i class="bi bi-lightning-charge-fill"></i> ${p.success_rate}%</span>`;
                    }
                    
                    let prodSrHtml = '';
                    if (p.product_success_rate !== null && p.product_success_rate !== undefined) {
                        let pColor = p.product_success_rate >= 80 ? '#10b981' : (p.product_success_rate >= 50 ? '#f59e0b' : '#ef4444');
                        prodSrHtml = `<span style="color: ${pColor}; font-weight: 700; font-size: 0.65rem;" title="SR Produk"><i class="bi bi-shield-check"></i> ${p.product_success_rate}%</span>`;
                    }

                    let speedSrHtml = '';
                    let rawSpeed = (p.seller_avg_speed !== null && p.seller_avg_speed !== undefined) ? p.seller_avg_speed : ((p.product_avg_speed !== null && p.product_avg_speed !== undefined) ? p.product_avg_speed : null);
                    if (rawSpeed !== null) {
                        let speedVal = Math.round(parseFloat(rawSpeed));
                        let sColor = speedVal <= 5 ? '#10b981' : (speedVal <= 20 ? '#3b82f6' : (speedVal <= 60 ? '#f59e0b' : '#ef4444'));
                        let speedText = speedVal <= 59 ? `${speedVal}s` : `${Math.floor(speedVal / 60)}m, ${speedVal % 60}d`;
                        speedSrHtml = `<span style="color: ${sColor}; font-weight: 700; font-size: 0.65rem; white-space: nowrap;" title="Kecepatan Seller/Produk"><i class="bi bi-stopwatch"></i> ${speedText}</span>`;
                    }

                    let prodTrxHtml = `<span style="color: #3b82f6; font-weight: 700; font-size: 0.65rem;" title="Total Transaksi Sukses Produk Ini"><i class="bi bi-bag-check-fill"></i> ${(p.product_trx_count || 0).toLocaleString('id-ID')} Trx</span>`;
                    let sellerTrxHtml = `<span style="color: #8b5cf6; font-weight: 700; font-size: 0.65rem;" title="Total Transaksi Sukses Seller Ini"><i class="bi bi-box-seam-fill"></i> ${(p.seller_trx_count || 0).toLocaleString('id-ID')} Trx</span>`;

                    sellerHtml = `
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-bold text-truncate" style="font-size: 0.78rem; max-width: 120px; color: var(--text-primary);" title="${p.seller_name}">
                                <i class="bi bi-shop text-primary me-1"></i>${p.seller_name}
                            </span>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                ${sellerSrHtml}
                                ${prodSrHtml}
                                ${speedSrHtml}
                                ${prodTrxHtml}
                                ${sellerTrxHtml}
                            </div>
                        </div>
                    `;
                }

                // Profit calculation
                const sellerPrice = parseFloat(p.seller_price || 0);
                const sellPrice = parseFloat(p.sell_price || 0);
                const profit = sellPrice - sellerPrice;
                const profitPct = sellerPrice > 0 ? ((profit / sellerPrice) * 100).toFixed(1) : 0;
                
                let profitHtml = '<span class="text-muted small">-</span>';
                if (profit > 0) {
                    profitHtml = `<span class="profit-badge">+Rp ${parseInt(profit).toLocaleString('id-ID')} (${profitPct}%)</span>`;
                } else if (profit < 0) {
                    profitHtml = `<span class="profit-badge" style="background: rgba(239,68,68,0.12); color: #ef4444;">-Rp ${parseInt(Math.abs(profit)).toLocaleString('id-ID')}</span>`;
                }

                const catNormalized = (p.category || '').toUpperCase();
                const brandName = p.brand || '-';

                tbody.append(`
                    <tr data-category="${p.category || ''}" data-brand="${brandName}" data-type="${isPostpaid ? 'postpaid' : 'prepaid'}">
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-extrabold text-uppercase" style="font-size: 0.78rem; color: var(--primary); letter-spacing: 0.4px;">${catNormalized}</span>
                                <span class="fw-bold text-secondary" style="font-size: 0.72rem;">${brandName}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold" style="font-size: 0.82rem; color: var(--text-primary);">${p.product_name}</span>
                                <div class="mt-1"><span class="sku-badge">${p.buyer_sku_code}</span></div>
                            </div>
                        </td>
                        <td>${typeBadge}</td>
                        <td>${sellerHtml}</td>
                        <td class="text-end fw-semibold text-muted" style="font-size: 0.82rem;">Rp ${parseInt(sellerPrice).toLocaleString('id-ID')}</td>
                        <td class="text-end fw-extrabold text-success" style="font-size: 0.88rem;">Rp ${parseInt(sellPrice).toLocaleString('id-ID')}</td>
                        <td class="text-end">${profitHtml}</td>
                    </tr>
                `);
            });

            if (dataTable) dataTable.destroy();
            
            dataTable = $('#priceTable').DataTable({
                pageLength: 25,
                ordering: true,
                order: [[5, 'asc']],
                dom: 'rtip',
                language: {
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ produk",
                    infoEmpty: "Tidak ada data produk",
                    infoFiltered: "(disaring dari _MAX_ total produk)",
                    zeroRecords: "Tidak ada produk yang cocok dengan pencarian",
                    paginate: {
                        first: "«",
                        previous: "‹",
                        next: "›",
                        last: "»"
                    }
                }
            });
        } else {
            tbody.html('<tr><td colspan="7" class="text-center py-5 text-muted">Belum ada produk PPOB yang disinkronkan.</td></tr>');
        }
    } catch(e) {
        console.error(e);
        $('#priceTable tbody').html('<tr><td colspan="7" class="text-center py-5 text-danger">Gagal memuat data daftar harga. Harap periksa koneksi jaringan.</td></tr>');
    }
}

function filterCategory(cat, btnEl) {
    activeCategoryFilter = cat;
    
    // Update active pill styling
    $('#category-pills .cat-pill-btn').removeClass('active');
    $(btnEl).addClass('active');

    applyCustomFilters();
}

function applyCustomFilters() {
    if (!dataTable) return;

    const brandVal = $('#brandFilterSelect').val();
    const typeVal = $('#typeFilterSelect').val();

    // Custom filtering logic using DataTables search plugin
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, category) {
        const rowNode = dataTable.row(dataIndex).node();
        const rowCat = $(rowNode).data('category') || '';
        const rowBrand = $(rowNode).data('brand') || '';
        const rowType = $(rowNode).data('type') || '';

        // Category filter
        if (activeCategoryFilter) {
            if (activeCategoryFilter === 'postpaid') {
                if (rowType !== 'postpaid') return false;
            } else if (rowCat !== activeCategoryFilter) {
                return false;
            }
        }

        // Brand filter
        if (brandVal && rowBrand !== brandVal) {
            return false;
        }

        // Type filter
        if (typeVal && rowType !== typeVal) {
            return false;
        }

        return true;
    });

    dataTable.draw();

    // Pop the custom search function to avoid stacking
    $.fn.dataTable.ext.search.pop();
}

function changePageLength(len) {
    if (dataTable) {
        dataTable.page.len(parseInt(len)).draw();
    }
}

async function syncPrices() {
    const btn = document.getElementById('btn-sync-prices');
    const ogHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sinkronisasi...';
    btn.disabled = true;

    const modal = new bootstrap.Modal(document.getElementById('syncModal'));
    modal.show();

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/sync-prices', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({type: 'all'})
        });
        const data = await res.json();
        modal.hide();
        btn.innerHTML = ogHtml;
        btn.disabled = false;

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('✅ Sinkronisasi harga berhasil!', 'success');
            } else {
                alert('✅ Sinkronisasi harga berhasil!');
            }
            loadPrices();
        } else {
            if (typeof showAlert === 'function') {
                showAlert('❌ Gagal: ' + (data.message || 'Sinkronisasi gagal'), 'danger');
            } else {
                alert('❌ Gagal: ' + (data.message || 'Sinkronisasi gagal'));
            }
        }
    } catch(e) {
        modal.hide();
        btn.innerHTML = ogHtml;
        btn.disabled = false;
        alert('❌ Terjadi kesalahan jaringan saat sinkronisasi.');
    }
}
</script>
