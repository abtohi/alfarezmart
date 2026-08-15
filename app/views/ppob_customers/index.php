<?php 
/** @var string $csrfToken */
$csrfToken = $csrfToken ?? '';
?>

<style>
/* Glassmorphism styling */
.page-header {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 14px 20px;
    margin: 15px auto;
    max-width: 800px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.page-title {
    color: var(--text-primary);
    font-weight: 800;
    margin-bottom: 2px;
    font-size: 1.15rem;
}
.page-subtitle {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin: 0;
}
.customer-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 10px 16px;
    margin-bottom: 10px;
    transition: all 0.2s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.customer-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: var(--primary);
}
.customer-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 12px;
    flex-shrink: 0;
}
.icon-pln { background: rgba(249, 115, 22, 0.1); color: #f97316; }
.icon-hp { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.icon-game { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.icon-tv { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.icon-pdam { background: rgba(6, 182, 212, 0.1); color: #06b6d4; }
.icon-bpjs { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.icon-internet { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.icon-other { background: rgba(107, 114, 128, 0.1); color: #6b7280; }

.customer-info {
    flex-grow: 1;
}
.customer-name {
    font-weight: 700;
    font-size: 14.5px;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.customer-no {
    font-size: 12.5px;
    color: var(--text-muted);
    font-family: monospace;
    letter-spacing: 0.5px;
}
.customer-detail {
    font-size: 10.5px;
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.1);
    padding: 1px 8px;
    border-radius: 20px;
    display: inline-block;
    margin-top: 2px;
}
.action-buttons .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-left: 4px;
    font-size: 13px;
}
.nav-pills .nav-link {
    color: var(--text-muted);
    border-radius: 8px;
    padding: 6px 14px;
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

/* Search input focus styling */
.search-input:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15) !important;
    outline: none;
}

/* Modal styles */
.glass-input {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    color: var(--text-primary) !important;
}
.glass-input::placeholder {
    color: var(--text-secondary) !important;
    opacity: 0.7 !important;
}
.type-radio {
    display: none;
}
.type-label {
    padding: 10px 8px;
    border: 1.5px solid var(--border-color);
    border-radius: 12px;
    cursor: pointer;
    background: var(--surface-2);
    color: var(--text-secondary);
    font-size: 11px;
    font-weight: 700;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.type-label i {
    font-size: 16px;
}
.type-label:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}
.type-radio:checked + .type-label {
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary);
    border-color: var(--primary);
    box-shadow: inset 0 0 0 1px var(--primary);
}
.glass-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}
.modal-content label.text-muted {
    color: var(--text-secondary) !important;
}
</style>

<div class="container-fluid px-3">
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>ppob" class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; color: var(--text-primary); background: var(--surface-2); border: 1px solid var(--border-color); text-decoration: none; transition: all 0.2s;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h1 class="page-title">Pelanggan PPOB</h1>
                <p class="page-subtitle">Kelola data kontak transaksi PPOB</p>
            </div>
        </div>
        <button class="btn btn-primary-custom rounded-pill px-4 fw-bold shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-2"></i> Tambah
        </button>
    </div>
</div>

<div class="container-fluid py-4 pb-5 mb-5" style="max-width: 800px; margin: 0 auto;">
    
    <!-- Filter Tabs -->
    <ul class="nav nav-pills mb-3 overflow-auto flex-nowrap" id="customerTabs" role="tablist" style="scrollbar-width: none;">
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
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-tv" type="button" role="tab" onclick="filterCustomers('tv')">TV</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-pdam" type="button" role="tab" onclick="filterCustomers('pdam')">PDAM</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-bpjs" type="button" role="tab" onclick="filterCustomers('bpjs')">BPJS</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link whitespace-nowrap" data-bs-toggle="pill" data-bs-target="#tab-internet" type="button" role="tab" onclick="filterCustomers('internet')">Internet</button>
        </li>
    </ul>

    <!-- Search Box -->
    <div class="search-wrapper" style="position: relative; margin-bottom: 20px;">
        <i class="bi bi-search search-icon" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
        <input type="text" id="customerSearchInput" class="search-input" placeholder="Cari nama, nomor HP, nomor meter PLN, nama asli PLN..." onkeyup="searchCustomers()" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 16px 10px 42px; font-size: 14px; color: var(--text-primary); width: 100%; transition: all 0.2s ease;">
    </div>

    <!-- Customer List -->
    <div id="customer-list">
        <?php if(empty($customers)): ?>
            <div class="text-center py-5 my-5">
                <div class="mb-3" style="font-size: 64px; color: var(--border-color); opacity: 0.5;"><i class="bi bi-person-x"></i></div>
                <h5 class="fw-bold" style="color: var(--text-primary);">Belum ada pelanggan</h5>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Klik tombol tambah untuk menyimpan pelanggan baru.</p>
            </div>
        <?php else: ?>
            <?php foreach($customers as $c): 
                $iconClass = 'icon-hp'; $iconBi = 'bi-phone';
                if($c['type'] == 'pln') { $iconClass = 'icon-pln'; $iconBi = 'bi-lightning-charge'; }
                else if($c['type'] == 'game') { $iconClass = 'icon-game'; $iconBi = 'bi-controller'; }
                else if($c['type'] == 'tv') { $iconClass = 'icon-tv'; $iconBi = 'bi-tv'; }
                else if($c['type'] == 'pdam') { $iconClass = 'icon-pdam'; $iconBi = 'bi-droplet'; }
                else if($c['type'] == 'bpjs') { $iconClass = 'icon-bpjs'; $iconBi = 'bi-shield-check'; }
                else if($c['type'] == 'internet') { $iconClass = 'icon-internet'; $iconBi = 'bi-wifi'; }
                else if($c['type'] == 'other') { $iconClass = 'icon-other'; $iconBi = 'bi-person'; }
            ?>
            <div class="customer-card customer-item" 
                 data-type="<?= $c['type'] ?>"
                 data-no="<?= htmlspecialchars(strtolower($c['customer_no'] ?: '')) ?>"
                 data-name="<?= htmlspecialchars(strtolower($c['customer_name'] ?: '')) ?>"
                 data-pln-name="<?= htmlspecialchars(strtolower($c['pln_name'] ?? '')) ?>">
                <div class="customer-icon <?= $iconClass ?>">
                    <i class="bi <?= $iconBi ?>"></i>
                </div>
                <div class="customer-info">
                    <div class="customer-name"><?= htmlspecialchars($c['customer_name'] ?: 'Tanpa Nama') ?></div>
                    <div class="customer-no"><?= htmlspecialchars($c['customer_no']) ?></div>
                    
                    <?php if($c['type'] == 'pln' && !empty($c['pln_name'])): ?>
                        <div class="customer-detail">
                            <i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($c['pln_name']) ?> 
                            <?php if(!empty($c['pln_power'])) echo " &bull; " . htmlspecialchars($c['pln_power']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    if($c['type'] == 'hp' && !empty($c['ewallet_accounts'])): 
                        $ewallets = json_decode($c['ewallet_accounts'], true);
                        if(is_array($ewallets) && count($ewallets) > 0):
                    ?>
                        <div class="mt-1">
                            <?php foreach($ewallets as $brand => $accName): if(!empty($accName)): ?>
                                <span class="badge bg-primary me-1" style="font-size:10px;"><?= htmlspecialchars($brand) ?></span>
                            <?php endif; endforeach; ?>
                        </div>
                        <div class="mt-1" style="font-size:11px; color:var(--text-muted);">
                            <?php 
                            $names = [];
                            foreach($ewallets as $brand => $accName) {
                                if(!empty($accName)) $names[] = htmlspecialchars("$brand: $accName");
                            }
                            echo implode(', ', $names);
                            ?>
                        </div>
                    <?php endif; endif; ?>
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

<script>
function openAddModal() {
    openUnifiedContactModal();
}

function editCustomer(c) {
    // If it's hp and has ewallet_accounts, we ensure it's passed properly
    openUnifiedContactModal(c, c.type, c.customer_no);
}

let activeFilterType = 'all';

function filterCustomers(type) {
    activeFilterType = type;
    applyFilterAndSearch();
}

function searchCustomers() {
    applyFilterAndSearch();
}

function applyFilterAndSearch() {
    const query = document.getElementById('customerSearchInput').value.toLowerCase().trim();
    const keywords = query ? query.split(/\s+/) : [];
    const items = document.querySelectorAll('.customer-item');

    items.forEach(item => {
        // 1. Check tab filter type
        const matchesType = (activeFilterType === 'all' || item.dataset.type === activeFilterType);
        if (!matchesType) {
            item.style.display = 'none';
            return;
        }

        // 2. Check search query keywords
        if (keywords.length === 0) {
            item.style.display = 'flex';
            return;
        }

        const no = item.dataset.no || '';
        const name = item.dataset.name || '';
        const plnName = item.dataset.plnName || '';

        // All keywords must be found (AND relationship) in at least one of the fields
        const matchesSearch = keywords.every(keyword => {
            return no.includes(keyword) || name.includes(keyword) || plnName.includes(keyword);
        });

        if (matchesSearch) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

async function deleteCustomer(id) {
    const ok = await AppModal.confirm('Hapus Pelanggan', 'Data pelanggan PPOB ini akan dihapus permanen. Lanjutkan?', 'Ya, Hapus', 'var(--danger)');
    if (!ok) return;

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
            showToast('✅ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('❌ ' + data.message, 'danger');
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan', 'danger'));
}
</script>

