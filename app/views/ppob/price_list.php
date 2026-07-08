<style>
/* Dark/Light Mode Overrides */
.card { background: var(--surface-1) !important; color: var(--text-primary) !important; }
.table { color: var(--text-primary) !important; border-color: var(--border-color) !important; }
.table-light { background-color: var(--surface-2) !important; color: var(--text-primary) !important; }
.table-hover tbody tr:hover { background-color: var(--surface-2) !important; color: var(--text-primary) !important; }
.modal-content { background: var(--bg-modal) !important; color: var(--text-primary) !important; }
.table > :not(caption) > * > * {
    background-color: var(--surface-1) !important;
    color: var(--text-primary) !important;
    border-bottom-color: var(--border-color) !important;
}
.table thead.table-light th {
    background-color: var(--surface-2) !important;
    color: var(--text-secondary) !important;
}
/* DataTables styling */
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
    background: var(--surface-2) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: var(--radius-md) !important;
    padding: 6px 10px !important;
}
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    color: var(--text-muted) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    color: var(--text-primary) !important;
    background: var(--surface-2) !important;
    border-color: var(--border-color) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--primary) !important;
    color: #fff !important;
    border-color: var(--primary) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: var(--surface-3) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
code {
    background: var(--surface-2) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 11px;
}
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-tags text-primary me-2"></i> Daftar Harga PPOB</h4>
        <button class="btn btn-primary rounded-pill px-4" onclick="syncPrices()"><i class="bi bi-arrow-repeat me-1"></i> Sync Manual</button>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-4">
            <p class="text-muted small mb-4">Daftar harga di bawah ini otomatis diupdate setiap hari melalui sistem Cron Job. Anda juga dapat melakukan sinkronisasi manual jika ada perubahan mendadak dari server pusat.</p>
            
            <!-- We will load datatables dynamically here or just a beautiful table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="priceTable">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Brand / Provider</th>
                            <th>Kode SKU</th>
                            <th>Nama Produk</th>
                            <th>Tipe</th>
                            <th>Harga Modal</th>
                            <th>Harga Jual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <span class="spinner-border text-primary mb-3"></span>
                                <p class="text-muted">Memuat daftar harga...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="mt-3 fw-bold">Menyinkronkan...</h5>
            <p class="small text-muted mb-0">Menarik data puluhan ribu produk dari server Digiflazz. Mohon tunggu beberapa saat.</p>
        </div>
    </div>
</div>

<!-- Add DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
let dataTable;

$(document).ready(function() {
    loadPrices();
});

async function loadPrices() {
    try {
        // We'll reuse the products endpoint but make it fetch all if no category
        // Actually, we need an endpoint to get ALL active products for datatable.
        // Or we can just use DataTables server-side processing later. For now, fetch via an api.
        const res = await fetch('<?= BASE_URL ?>api/ppob/products/all');
        const data = await res.json();
        
        const tbody = $('#priceTable tbody');
        tbody.empty();

        if(data.success && data.data.length > 0) {
            data.data.forEach(p => {
                const typeBadge = p.type === 'postpaid' 
                    ? '<span class="badge bg-warning text-dark">Pasca</span>' 
                    : '<span class="badge bg-info text-dark">Pra</span>';
                
                tbody.append(`
                    <tr>
                        <td><span class="fw-bold">${p.category}</span></td>
                        <td>${p.brand || '-'}</td>
                        <td><code>${p.buyer_sku_code}</code></td>
                        <td>${p.product_name}</td>
                        <td>${typeBadge}</td>
                        <td class="text-danger fw-bold">Rp${parseInt(p.seller_price).toLocaleString('id-ID')}</td>
                        <td class="text-success fw-bold">Rp${parseInt(p.sell_price).toLocaleString('id-ID')}</td>
                    </tr>
                `);
            });
            
            if(dataTable) dataTable.destroy();
            dataTable = $('#priceTable').DataTable({
                pageLength: 25,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
            });
        }
    } catch(e) {
        $('#priceTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
    }
}

async function syncPrices() {
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
        if(data.success) {
            alert('Sinkronisasi Berhasil!');
            loadPrices();
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch(e) {
        modal.hide();
        alert('Terjadi kesalahan jaringan.');
    }
}
</script>
