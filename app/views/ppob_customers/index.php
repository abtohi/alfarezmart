<?php include __DIR__ . '/../layouts/app.php'; ?>
<?php 
ob_start(); 
/** @var string $csrfToken */
$csrfToken = $csrfToken ?? '';
?>

<style>
/* Glassmorphism styling */
.glass-header {
    background: var(--surface-1);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 60px; /* Adjust based on your header height */
    z-index: 100;
    padding: 15px 20px;
}
.customer-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.customer-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-color: var(--primary);
}
.customer-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-right: 15px;
    flex-shrink: 0;
}
.icon-pln { background: rgba(249, 115, 22, 0.1); color: #f97316; }
.icon-hp { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.icon-game { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.icon-tv { background: rgba(34, 197, 94, 0.1); color: #22c55e; }

.customer-info {
    flex-grow: 1;
}
.customer-name {
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
    margin-bottom: 3px;
}
.customer-no {
    font-size: 13px;
    color: var(--text-muted);
    font-family: monospace;
    letter-spacing: 0.5px;
}
.customer-detail {
    font-size: 11px;
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.1);
    padding: 2px 8px;
    border-radius: 20px;
    display: inline-block;
    margin-top: 4px;
}
.action-buttons .btn {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    margin-left: 5px;
}
.nav-pills .nav-link {
    color: var(--text-muted);
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 13px;
}
.nav-pills .nav-link.active {
    background-color: var(--primary);
    color: white;
}
.btn-whatsapp {
    background-color: #25D366;
    color: white;
    border: none;
}
.btn-whatsapp:hover {
    background-color: #128C7E;
    color: white;
}

/* Modal styles */
.glass-input {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    color: var(--text-primary);
}
.glass-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}
</style>

<div class="glass-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold">Pelanggan PPOB</h4>
            <div class="text-muted small">Kelola data kontak transaksi PPOB</div>
        </div>
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-2"></i> Tambah
        </button>
    </div>
</div>

<div class="container-fluid py-4 pb-5 mb-5" style="max-width: 800px; margin: 0 auto;">
    
    <!-- Filter Tabs -->
    <ul class="nav nav-pills mb-4 overflow-auto flex-nowrap" id="customerTabs" role="tablist" style="scrollbar-width: none;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab" onclick="filterCustomers('all')">Semua</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-pln" type="button" role="tab" onclick="filterCustomers('pln')">PLN</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-hp" type="button" role="tab" onclick="filterCustomers('hp')">Nomor HP</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-game" type="button" role="tab" onclick="filterCustomers('game')">Game</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-tv" type="button" role="tab" onclick="filterCustomers('tv')">TV Voucher</button>
        </li>
    </ul>

    <!-- Customer List -->
    <div id="customer-list">
        <?php if(empty($customers)): ?>
            <div class="text-center py-5">
                <div class="mb-3" style="font-size: 60px; color: var(--border-color);"><i class="bi bi-person-x"></i></div>
                <h5 class="text-muted fw-bold">Belum ada pelanggan</h5>
                <p class="text-muted small">Klik tombol tambah untuk menyimpan pelanggan baru.</p>
            </div>
        <?php else: ?>
            <?php foreach($customers as $c): 
                $iconClass = 'icon-hp'; $iconBi = 'bi-phone';
                if($c['type'] == 'pln') { $iconClass = 'icon-pln'; $iconBi = 'bi-lightning-charge'; }
                else if($c['type'] == 'game') { $iconClass = 'icon-game'; $iconBi = 'bi-controller'; }
                else if($c['type'] == 'tv') { $iconClass = 'icon-tv'; $iconBi = 'bi-tv'; }
            ?>
            <div class="customer-card customer-item" data-type="<?= $c['type'] ?>">
                <div class="customer-icon <?= $iconClass ?>">
                    <i class="bi <?= $iconBi ?>"></i>
                </div>
                <div class="customer-info">
                    <div class="customer-name"><?= htmlspecialchars($c['customer_name'] ?: 'Tanpa Nama') ?></div>
                    <div class="customer-no"><?= htmlspecialchars($c['customer_no']) ?></div>
                    
                    <?php if($c['type'] == 'pln' && !empty($c['pln_name'])): ?>
                        <div class="customer-detail">
                            <i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($c['pln_name']) ?> 
                            <?php if(!empty($c['pln_power'])) echo " • " . htmlspecialchars($c['pln_power']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="action-buttons">
                    <?php if($c['type'] == 'hp'): ?>
                        <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $c['customer_no'])) ?>" target="_blank" class="btn btn-whatsapp" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-light text-primary" onclick="editCustomer(<?= htmlspecialchars(json_encode($c)) ?>)" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-light text-danger" onclick="deleteCustomer(<?= $c['id'] ?>)" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Form Pelanggan -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; background: var(--surface-1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--btn-close-filter);"></button>
            </div>
            <div class="modal-body p-4">
                <form id="customerForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="id" id="customerId">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Klasifikasi</label>
                        <select class="form-select glass-input" name="type" id="customerType" onchange="toggleFields()">
                            <option value="hp">Nomor HP</option>
                            <option value="pln">PLN</option>
                            <option value="game">Game (ID User)</option>
                            <option value="tv">TV Voucher</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase" id="lblCustomerNo">Nomor HP</label>
                        <div class="d-flex position-relative align-items-center">
                            <input type="text" class="form-control glass-input w-100" name="customer_no" id="customerNo" required>
                            <button type="button" class="btn btn-primary position-absolute end-0 h-100 px-3" id="btnCekPln" onclick="cekNamaPln()" style="display:none; border-radius: 0 10px 10px 0; font-size:13px;">Cek Nama</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase" id="lblCustomerName">Nama Pelanggan (Alias)</label>
                        <input type="text" class="form-control glass-input" name="customer_name" id="customerName" placeholder="Contoh: Budi Rumah / HP Istri" required>
                    </div>

                    <!-- Extra fields for PLN -->
                    <div id="plnFields" style="display:none;" class="p-3 mb-3 rounded-3 border">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">Nama Asli PLN</label>
                            <input type="text" class="form-control glass-input" name="pln_name" id="plnName" readonly style="background: var(--surface-2); opacity: 0.8;">
                        </div>
                        <div>
                            <label class="form-label small fw-bold text-muted">Besaran Daya</label>
                            <input type="text" class="form-control glass-input" name="pln_power" id="plnPower" readonly style="background: var(--surface-2); opacity: 0.8;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mt-2" id="btnSave">Simpan Pelanggan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let customerModal;
document.addEventListener('DOMContentLoaded', function() {
    customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    
    document.getElementById('customerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('customerId').value;
        const url = id ? '<?= BASE_URL ?>api/ppob/customers/update' : '<?= BASE_URL ?>api/ppob/customers';
        const formData = new FormData(this);
        
        const btnSave = document.getElementById('btnSave');
        const originalText = btnSave.innerText;
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

        try {
            const res = await fetch(url, { method: 'POST', body: formData });
            const data = await res.json();
            
            if(data.success) {
                showAlert('✅ ' + data.message, 'success');
                customerModal.hide();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert('❌ ' + data.message, 'danger');
            }
        } catch(err) {
            showAlert('❌ Terjadi kesalahan', 'danger');
        }
        btnSave.disabled = false;
        btnSave.innerText = originalText;
    });
});

function openAddModal() {
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Pelanggan';
    document.getElementById('plnFields').style.display = 'none';
    toggleFields();
    customerModal.show();
}

function editCustomer(c) {
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = c.id;
    document.getElementById('customerType').value = c.type;
    document.getElementById('customerNo').value = c.customer_no;
    document.getElementById('customerName').value = c.customer_name;
    document.getElementById('modalTitle').innerText = 'Edit Pelanggan';
    
    if(c.type === 'pln') {
        document.getElementById('plnName').value = c.pln_name || '';
        document.getElementById('plnPower').value = c.pln_power || '';
    }
    
    toggleFields();
    customerModal.show();
}

function toggleFields() {
    const type = document.getElementById('customerType').value;
    const lblNo = document.getElementById('lblCustomerNo');
    const lblName = document.getElementById('lblCustomerName');
    const btnCek = document.getElementById('btnCekPln');
    const plnFields = document.getElementById('plnFields');
    const customerNo = document.getElementById('customerNo');

    btnCek.style.display = 'none';
    plnFields.style.display = 'none';
    customerNo.style.paddingRight = '16px';

    if(type === 'hp') {
        lblNo.innerText = 'Nomor HP';
        lblName.innerText = 'Nama Pelanggan';
        customerNo.placeholder = '081234567890';
    } else if(type === 'pln') {
        lblNo.innerText = 'Nomor Meter / ID Pelanggan';
        lblName.innerText = 'Nama Alias (Opsional)';
        btnCek.style.display = 'block';
        plnFields.style.display = 'block';
        customerNo.placeholder = 'Contoh: 14356145996';
        customerNo.style.paddingRight = '100px';
    } else if(type === 'game') {
        lblNo.innerText = 'ID User Game';
        lblName.innerText = 'Nama / Nickname';
        customerNo.placeholder = '12345678 (1234)';
    } else if(type === 'tv') {
        lblNo.innerText = 'Nomor Pelanggan TV';
        lblName.innerText = 'Nama Pelanggan';
        customerNo.placeholder = 'Masukkan no pelanggan';
    }
}

async function cekNamaPln() {
    const no = document.getElementById('customerNo').value;
    if(!no) { showAlert('⚠️ Masukkan nomor meter terlebih dahulu', 'warning'); return; }

    const btn = document.getElementById('btnCekPln');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const formData = new FormData();
        formData.append('customer_no', no);
        formData.append('csrf_token', '<?= $csrfToken ?>');

        const res = await fetch('<?= BASE_URL ?>api/ppob/customers/check-pln', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if(data.success && data.data) {
            document.getElementById('plnName').value = data.data.name;
            document.getElementById('plnPower').value = data.data.power;
            
            // Auto fill alias if empty
            const alias = document.getElementById('customerName');
            if(alias.value === '') {
                alias.value = data.data.name;
            }
            showAlert('✅ Berhasil cek nama PLN', 'success');
        } else {
            showAlert('❌ ' + (data.message || 'Gagal cek nomor'), 'danger');
            document.getElementById('plnName').value = '';
            document.getElementById('plnPower').value = '';
        }
    } catch(err) {
        showAlert('❌ Terjadi kesalahan jaringan', 'danger');
    }

    btn.disabled = false;
    btn.innerText = originalText;
}

function filterCustomers(type) {
    const items = document.querySelectorAll('.customer-item');
    items.forEach(item => {
        if(type === 'all' || item.dataset.type === type) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function deleteCustomer(id) {
    if(!confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', '<?= $csrfToken ?>');

    fetch('<?= BASE_URL ?>api/ppob/customers/delete', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showAlert('✅ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert('❌ ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('❌ Terjadi kesalahan jaringan', 'danger'));
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
