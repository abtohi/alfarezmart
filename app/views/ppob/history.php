

<div class="page-section">
    <div class="d-flex align-items-center mb-4">
        <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-2" style="background:var(--surface-3);color:var(--text-color);">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="m-0 fw-bold">Riwayat PPOB</h4>
    </div>

    <div class="card card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px;">
                    <thead style="background:var(--surface-3);">
                        <tr>
                            <th class="ps-3 py-3">Tanggal</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3">No Tujuan</th>
                            <th class="py-3 text-end">Harga</th>
                            <th class="pe-3 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody">
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', loadHistory);

    async function loadHistory() {
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/transactions?limit=100');
            const data = await res.json();
            const tbody = document.getElementById('history-tbody');
            tbody.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(trx => {
                    let statusBadge = '';
                    if (trx.status === 'success') statusBadge = '<span class="badge bg-success">Sukses</span>';
                    else if (trx.status === 'pending' || trx.status === 'processing') statusBadge = '<span class="badge bg-warning text-dark">Proses</span>';
                    else statusBadge = '<span class="badge bg-danger">Gagal</span>';

                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-3 py-3 text-nowrap">${trx.created_at}</td>
                            <td class="py-3">
                                <div class="fw-bold">${trx.product_name}</div>
                                <div class="text-muted" style="font-size:11px;">SN: ${trx.sn || '-'}</div>
                            </td>
                            <td class="py-3">${trx.customer_no}</td>
                            <td class="py-3 text-end fw-bold">Rp${parseInt(trx.sell_price).toLocaleString('id-ID')}</td>
                            <td class="pe-3 py-3 text-center">${statusBadge}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat transaksi.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('history-tbody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>';
        }
    }
</script>


