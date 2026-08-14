<!-- Multivariant Pricing View -->

<style>
/* ── Layout ─────────────────────────────────────── */
.mv-page        { max-width: 960px; margin: 0 auto; padding-bottom: 96px; }
.mv-page-header { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
.mv-page-title  { font-size: 22px; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.5px; }
.mv-page-sub    { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

/* ── Step card ───────────────────────────────────── */
.mv-step        { background: var(--surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); margin-bottom: 20px; overflow: hidden; transition: box-shadow 0.2s; position: relative; }
.mv-step:hover  { box-shadow: var(--shadow-md); }
.mv-step-head   { padding: 18px 20px 14px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; }
.mv-step-badge  { width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: #fff; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mv-step-info   { flex: 1; }
.mv-step-title  { font-size: 15px; font-weight: 700; color: var(--text-primary); margin: 0 0 2px; }
.mv-step-desc   { font-size: 12px; color: var(--text-muted); margin: 0; }
.mv-step-body   { padding: 16px 20px 20px; }

/* ── POS-style search box ────────────────────────── */
.mv-search-box  {
    background: var(--bg-input, var(--surface-1));
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mv-search-box:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.mv-search-box i.icon-lead  { color: var(--primary); font-size: 1.1rem; flex-shrink: 0; }
.mv-search-box input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 13px 4px;
    color: var(--text-primary);
    font-size: var(--font-size-base, 14px);
    font-family: var(--font-family);
    outline: none;
}
.mv-search-box input::placeholder { color: var(--text-muted); }
.mv-search-box input:disabled { opacity: 0.5; cursor: not-allowed; }
.mv-search-box i.icon-clear { color: var(--text-muted); font-size: 0.9rem; cursor: pointer; flex-shrink: 0; display: none; }
.mv-search-box input:not(:placeholder-shown) ~ i.icon-clear { display: block; }

/* ── Search results panel ────────────────────────── */
.mv-results {
    margin-top: 8px;
    max-height: 340px;
    overflow-y: auto;
    display: none;
}
.mv-results.active { display: block; }
.mv-result-item {
    padding: 10px 12px;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 6px;
    cursor: pointer;
    transition: all 0.18s;
    box-shadow: var(--shadow-sm);
}
.mv-result-item:last-child { margin-bottom: 0; }
.mv-result-item:hover { background: var(--surface-2); border-color: var(--primary); transform: translateX(2px); }
.mv-result-thumb {
    width: 44px; height: 44px;
    border-radius: var(--radius-sm);
    background: var(--primary-bg);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
    overflow: hidden;
}
.mv-result-thumb img { width: 100%; height: 100%; object-fit: contain; }
.mv-result-name   { font-weight: 700; font-size: 13px; color: var(--text-primary); line-height: 1.3; word-break: break-word; }
.mv-result-meta   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.mv-result-prices { text-align: right; flex-shrink: 0; }
.mv-result-price  { font-size: 12px; font-weight: 700; color: var(--primary); white-space: nowrap; }
.mv-result-pkgs   { margin-top: 5px; display: flex; flex-wrap: wrap; gap: 3px; }
.mv-pkg-tag       { font-size: 10px; padding: 2px 6px; border-radius: 4px; background: var(--surface); border: 1px solid var(--border-color); color: var(--text-secondary); white-space: nowrap; }
.mv-empty-msg     { padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px; }

/* ── Reference selected card ─────────────────────── */
.mv-ref-card {
    display: none;
    margin-top: 12px;
    background: linear-gradient(135deg, rgba(99,102,241,0.07), rgba(99,102,241,0.03));
    border: 1px solid rgba(99,102,241,0.25);
    border-radius: var(--radius-md);
    padding: 16px;
}
.mv-ref-card.visible { display: block; }
.mv-ref-top { display: flex; align-items: center; gap: 14px; }
.mv-ref-photo {
    width: 56px; height: 56px;
    border-radius: var(--radius-md);
    background: var(--primary-bg);
    color: var(--primary);
    font-size: 1.5rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
    border: 2px solid rgba(99,102,241,0.2);
}
.mv-ref-photo img { width: 100%; height: 100%; object-fit: contain; }
.mv-ref-name  { font-weight: 800; font-size: 15px; color: var(--primary); }
.mv-ref-sub   { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* ── Price table ─────────────────────────────────── */
.mv-price-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 14px; }
.mv-price-table th { background: var(--surface-1); color: var(--text-secondary); font-weight: 600; padding: 8px 10px; text-align: left; border-bottom: 2px solid var(--border-color); }
.mv-price-table td { padding: 9px 10px; border-bottom: 1px solid var(--border-color); color: var(--text-primary); vertical-align: middle; }
.mv-price-table tr:last-child td { border-bottom: none; }
.mv-tier-tag  { display: inline-block; padding: 2px 6px; background: var(--info-bg); color: var(--info); border-radius: 4px; font-size: 10px; margin: 1px 2px; }

/* ── Target list ─────────────────────────────────── */
.mv-target-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; margin-top: 14px; }
.mv-target-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    transition: all 0.2s;
    animation: popIn 0.2s ease-out;
}
@keyframes popIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.mv-target-card:hover { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.08); }
.mv-target-icon {
    width: 38px; height: 38px;
    border-radius: var(--radius-sm);
    background: rgba(99,102,241,0.1);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.mv-target-name { font-weight: 600; font-size: 13px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mv-target-meta { font-size: 10px; color: var(--text-muted); margin-top: 1px; }
.mv-target-del  {
    position: absolute; top: 9px; right: 9px;
    width: 26px; height: 26px;
    border-radius: 50%;
    background: rgba(239,68,68,0.1);
    color: var(--danger);
    border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.18s;
    font-size: 12px;
}
.mv-target-del:hover { background: var(--danger); color: #fff; }

.mv-target-empty {
    grid-column: 1 / -1;
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
    background: var(--surface-1);
    border-radius: var(--radius-md);
    border: 2px dashed var(--border-color);
    font-size: 13px;
}
.mv-target-empty i { font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.4; }

/* ── Floating bar ────────────────────────────────── */
.mv-fab {
    position: sticky;
    bottom: 20px;
    background: var(--surface);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-lg);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    z-index: 40;
    margin-top: 24px;
}
@media (max-width: 640px) {
    .mv-fab { flex-direction: column; align-items: stretch; gap: 12px; }
    .mv-fab-stats { justify-content: space-between; width: 100%; }
}
.mv-fab.visible { display: flex; }
.mv-fab-stats { display: flex; gap: 24px; }
.mv-fab-stat  { display: flex; flex-direction: column; }
.mv-fab-label { font-size: 11px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.4px; }
.mv-fab-val   { font-size: 15px; font-weight: 700; color: var(--text-primary); }

/* ── Loading overlay ─────────────────────────────── */
.mv-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.75); display: flex; align-items: center; justify-content: center; z-index: 10; border-radius: var(--radius-lg); opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.mv-overlay.show { opacity: 1; pointer-events: auto; }
.dark-mode .mv-overlay { background: rgba(15,23,42,0.75); }

/* ── Target card pricing ───────────────────────────── */
.mv-target-card { flex-direction: column; align-items: stretch; gap: 0; padding: 0; }
.mv-target-card-top { display: flex; align-items: center; gap: 12px; padding: 12px 14px 10px; }
.mv-target-card-prices { padding: 0 14px 12px; border-top: 1px dashed var(--border-color); margin-top: 2px; padding-top: 10px; }
.mv-target-pkg-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; gap: 6px; }
.mv-target-pkg-row:last-child { margin-bottom: 0; }
.mv-pkg-label { font-size: 11px; font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 4px; min-width: 60px; }
.mv-pkg-prices { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.mv-price-chip { font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 700; white-space: nowrap; }
.mv-price-chip.modal { background: rgba(245,158,11,0.1); color: var(--warning); }
.mv-price-chip.ecer  { background: rgba(16,185,129,0.12); color: var(--success); }
.mv-price-chip.grosir { background: rgba(59,130,246,0.1); color: var(--info); }
.mv-tier-chips { display: flex; flex-wrap: wrap; gap: 3px; margin-top: 4px; }
.mv-count-badge {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 2px 8px;
    background: var(--primary-bg);
    color: var(--primary);
    border-radius: 99px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 8px;
}

/* ── Info hint ───────────────────────────────────── */
.mv-info-hint {
    font-size: 11px; color: var(--text-muted);
    background: var(--surface-1);
    border-left: 3px solid var(--primary);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    padding: 6px 10px;
    margin-bottom: 12px;
}
</style>

<div class="mv-page">
    <!-- Header -->
    <div class="mv-page-header">
        <a href="<?= BASE_URL ?>products" class="btn btn-icon btn-light" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="mv-page-title">Harga Multivarian</h1>
            <div class="mv-page-sub">Salin konfigurasi harga dari satu produk ke varian lainnya secara massal</div>
        </div>
    </div>

    <!-- Step 1: Reference Product -->
    <div class="mv-step" id="stepRef">
        <div class="mv-overlay" id="refLoader"></div>
        <div class="mv-step-head">
            <div class="mv-step-badge">1</div>
            <div class="mv-step-info">
                <p class="mv-step-title">Pilih Produk Referensi</p>
                <p class="mv-step-desc">Cari produk yang harganya sudah benar untuk dijadikan acuan</p>
            </div>
        </div>
        <div class="mv-step-body">
            <!-- Search Box (POS style) -->
            <div class="mv-search-box" id="refSearchBox">
                <i class="bi bi-search icon-lead"></i>
                <input type="text" id="refSearchInput" placeholder="Ketik nama produk, barcode, atau kode..." autocomplete="off">
                <i class="bi bi-x-circle icon-clear" id="refClearIcon" onclick="clearRefSearch()"></i>
            </div>
            <!-- Results -->
            <div class="mv-results" id="refResults"></div>

            <!-- Selected reference card -->
            <div class="mv-ref-card" id="refSelectedCard">
                <div class="mv-ref-top">
                    <div class="mv-ref-photo" id="refPhoto">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="mv-ref-name" id="refName">-</div>
                        <div class="mv-ref-sub" id="refMeta">-</div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearReference()" style="flex-shrink:0;">
                        <i class="bi bi-arrow-repeat"></i> Ganti
                    </button>
                </div>

                <!-- Price table -->
                <div style="margin-top: 14px; overflow-x: auto;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                        Detail Harga (akan disalin ke varian target)
                    </div>
                    <table class="mv-price-table" id="refPriceTable">
                        <thead>
                            <tr>
                                <th>Lvl</th>
                                <th>Kemasan</th>
                                <th>Isi</th>
                                <th>Modal</th>
                                <th>Ecer (Selisih &amp; Markup)</th>
                                <th>Grosir (Selisih &amp; Markup)</th>
                                <th>Harga Tier</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Target Products -->
    <div class="mv-step" id="stepTarget">
        <div class="mv-overlay" id="targetLoader"></div>
        <div class="mv-step-head">
            <div class="mv-step-badge" style="background: var(--text-muted);">2</div>
            <div class="mv-step-info">
                <p class="mv-step-title">
                    Varian Target
                    <span class="mv-count-badge" id="targetCountBadge">0</span>
                </p>
                <p class="mv-step-desc">Varian terdeteksi otomatis. Tambah atau hapus sesuai kebutuhan</p>
            </div>
        </div>
        <div class="mv-step-body">
            <div class="mv-info-hint" id="targetHint" style="display:none;">
                <i class="bi bi-info-circle"></i>
                Produk referensi telah dipilih. Varian berikut terdeteksi otomatis berdasarkan nama &amp; ukuran yang sama. Anda bisa tambah manual di bawah.
            </div>

            <!-- Add more manually -->
            <div class="mv-search-box" id="targetSearchBox" style="margin-bottom: 12px;">
                <i class="bi bi-plus-circle icon-lead"></i>
                <input type="text" id="targetSearchInput" placeholder="Tambah varian lain secara manual..." autocomplete="off" disabled>
                <i class="bi bi-x-circle icon-clear" id="targetClearIcon" onclick="clearTargetSearch()"></i>
            </div>
            <div class="mv-results" id="targetResults"></div>

            <!-- Target grid -->
            <div class="mv-target-grid" id="targetGrid">
                <div class="mv-target-empty">
                    <i class="bi bi-arrow-up-circle"></i>
                    Pilih Produk Referensi dahulu untuk mendeteksi varian otomatis
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Bar -->
<div class="mv-fab" id="fabBar">
    <div class="mv-fab-stats">
        <div class="mv-fab-stat">
            <span class="mv-fab-label">Referensi</span>
            <span class="mv-fab-val text-primary" id="fabRefName" style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">-</span>
        </div>
        <div class="mv-fab-stat">
            <span class="mv-fab-label">Target</span>
            <span class="mv-fab-val" id="fabTargetCount">0 varian</span>
        </div>
    </div>
    <button id="btnApply" onclick="applyPricing()"
            style="padding: 11px 22px; font-weight: 700; display: flex; align-items: center; gap: 8px; border-radius: var(--radius-md);"
            class="btn btn-danger">
        <i class="bi bi-check2-all" style="font-size: 17px;"></i>
        Aplikasikan Harga
    </button>
</div>

<script>
// ─── State ──────────────────────────────────────────
let referenceProduct = null;
let targetProducts   = [];  // [{id, name, meta, photo}]
let searchTimer      = null;
const BASE = '<?= BASE_URL ?>';
const fmt  = n => 'Rp ' + parseFloat(n || 0).toLocaleString('id-ID');

// ─── Init ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const ri = document.getElementById('refSearchInput');
    const ti = document.getElementById('targetSearchInput');

    ri.addEventListener('input', e => triggerSearch(e.target.value, 'ref'));
    ti.addEventListener('input', e => triggerSearch(e.target.value, 'target'));

    document.addEventListener('click', e => {
        if (!e.target.closest('#stepRef'))    closeResults('ref');
        if (!e.target.closest('#stepTarget')) closeResults('target');
    });

    // Show clear icon reactively
    ri.addEventListener('input', () => {
        document.getElementById('refClearIcon').style.display = ri.value ? 'block' : 'none';
    });
    ti.addEventListener('input', () => {
        document.getElementById('targetClearIcon').style.display = ti.value ? 'block' : 'none';
    });
});

// ─── Search ──────────────────────────────────────────
function triggerSearch(kw, mode) {
    clearTimeout(searchTimer);
    const resultsEl = document.getElementById(mode === 'ref' ? 'refResults' : 'targetResults');
    if (!kw || kw.length < 2) { closeResults(mode); return; }

    resultsEl.innerHTML = '<div class="mv-empty-msg"><i class="bi bi-hourglass-split"></i> Mencari...</div>';
    resultsEl.classList.add('active');

    searchTimer = setTimeout(() => fetchSearch(kw, mode), 280);
}

async function fetchSearch(kw, mode) {
    const resultsEl = document.getElementById(mode === 'ref' ? 'refResults' : 'targetResults');
    try {
        const r    = await fetch(`${BASE}api/products/search?q=${encodeURIComponent(kw)}`);
        const data = await r.json();

        if (!Array.isArray(data) || data.length === 0) {
            resultsEl.innerHTML = '<div class="mv-empty-msg"><i class="bi bi-inbox"></i> Produk tidak ditemukan</div>';
            return;
        }

        resultsEl.innerHTML = '';
        data.forEach(p => {
            const thumbHtml = p.photo
                ? `<div class="mv-result-thumb"><img src="${BASE}${p.photo}" loading="lazy"></div>`
                : `<div class="mv-result-thumb"><i class="bi bi-box-seam"></i></div>`;

            let pkgsHtml = '';
            if (p.packagings && p.packagings.length > 0) {
                pkgsHtml = '<div class="mv-result-pkgs">' + p.packagings.map(pkg => {
                    const price = parseFloat(pkg.sell_price_retail) || 0;
                    return price > 0
                        ? `<span class="mv-pkg-tag"><strong style="color:var(--success);">Rp${price.toLocaleString('id-ID')}</strong>/${pkg.unit_name}</span>`
                        : '';
                }).join('') + '</div>';
            }

            const el = document.createElement('div');
            el.className = 'mv-result-item';
            el.innerHTML = `
                ${thumbHtml}
                <div style="flex:1; min-width:0;">
                    <div class="mv-result-name">${escHtml(p.short_label || p.full_name)}</div>
                    <div class="mv-result-meta">${escHtml(p.code || '')} · ${escHtml(p.category_name || '-')} · ${escHtml(p.brand_name || '-')}</div>
                    ${pkgsHtml}
                </div>`;
            el.addEventListener('click', () => {
                closeResults(mode);
                mode === 'ref' ? onSelectReference(p) : onSelectTargetManual(p);
            });
            resultsEl.appendChild(el);
        });
    } catch (err) {
        resultsEl.innerHTML = '<div class="mv-empty-msg" style="color:var(--danger);"><i class="bi bi-exclamation-triangle"></i> Gagal memuat</div>';
    }
}

function closeResults(mode) {
    const el = document.getElementById(mode === 'ref' ? 'refResults' : 'targetResults');
    el.classList.remove('active');
}

function clearRefSearch() {
    document.getElementById('refSearchInput').value = '';
    document.getElementById('refClearIcon').style.display = 'none';
    closeResults('ref');
}

function clearTargetSearch() {
    document.getElementById('targetSearchInput').value = '';
    document.getElementById('targetClearIcon').style.display = 'none';
    closeResults('target');
}

// ─── Select Reference ─────────────────────────────────
async function onSelectReference(prod) {
    document.getElementById('refLoader').classList.add('show');
    try {
        let data = null;
        if (typeof OfflineDB !== 'undefined' && OfflineDB.getProductById) {
            try { data = await OfflineDB.getProductById(prod.id); } catch(e) {}
        }
        if (!data || !data.packagings || data.packagings.length === 0) {
            const r    = await fetch(`${BASE}api/products/${prod.id}`);
            data = await r.json();
        }
        if (!data || data.error) { showToast('Gagal memuat data produk', 'error'); return; }

        referenceProduct = data.product || data;

        // Update card
        const photoEl = document.getElementById('refPhoto');
        if (referenceProduct.photo) {
            photoEl.innerHTML = `<img src="${BASE}${referenceProduct.photo}" style="width:100%;height:100%;object-fit:contain;">`;
        } else {
            photoEl.innerHTML = '<i class="bi bi-box-seam"></i>';
        }
        document.getElementById('refName').textContent = referenceProduct.full_name || referenceProduct.short_label;
        document.getElementById('refMeta').textContent =
            `${referenceProduct.code || ''} · ${referenceProduct.category_name || '-'} · ${referenceProduct.brand_name || '-'}`;
        document.getElementById('fabRefName').textContent = referenceProduct.short_label || referenceProduct.full_name;

        document.getElementById('refSearchBox').style.display = 'none';
        document.getElementById('refResults').classList.remove('active');
        document.getElementById('refSelectedCard').classList.add('visible');

        // Step 2 badge color
        document.querySelector('#stepTarget .mv-step-badge').style.background = 'var(--primary)';

        renderPriceTable();
        await loadVariants();

        document.getElementById('targetSearchInput').disabled = false;
        document.getElementById('targetHint').style.display   = '';
        document.getElementById('fabBar').classList.add('visible');
    } catch (e) {
        console.error(e);
        showToast('Terjadi kesalahan jaringan', 'error');
    } finally {
        document.getElementById('refLoader').classList.remove('show');
    }
}

function renderPriceTable() {
    const tbody = document.querySelector('#refPriceTable tbody');
    tbody.innerHTML = '';
    const pkgs = referenceProduct.packagings || [];
    if (!pkgs.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:16px;">Tidak ada data kemasan</td></tr>';
        return;
    }
    pkgs.forEach(pkg => {
        let tierHtml = '-';
        if (pkg.qty_prices && pkg.qty_prices.length > 0) {
            tierHtml = pkg.qty_prices.map(t =>
                `<span class="mv-tier-tag">≥${t.min_qty} : ${fmt(t.unit_price)}</span>`
            ).join('');
        }
        const modal = parseFloat(pkg.buy_price) || 0;
        const ecer  = parseFloat(pkg.sell_price_retail) || 0;
        const grosir = parseFloat(pkg.sell_price_wholesale) || 0;

        const selisihEcer = ecer - modal;
        const markupEcer  = modal > 0 ? (selisihEcer / modal * 100) : 0;
        const ecerHtml = `
            <div style="font-weight:700; color:var(--success);">${fmt(ecer)}</div>
            ${ecer > 0 ? `<div style="font-size:10px; color:var(--text-muted); margin-top:2px;">+${fmt(selisihEcer)} (${markupEcer.toFixed(1)}%)</div>` : ''}
        `;

        const selisihGrosir = grosir - modal;
        const markupGrosir  = modal > 0 ? (selisihGrosir / modal * 100) : 0;
        const grosirHtml = `
            <div style="font-weight:700; color:var(--info);">${fmt(grosir)}</div>
            ${grosir > 0 ? `<div style="font-size:10px; color:var(--text-muted); margin-top:2px;">+${fmt(selisihGrosir)} (${markupGrosir.toFixed(1)}%)</div>` : ''}
        `;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="badge bg-secondary">${pkg.level}</span></td>
            <td><strong>${pkg.unit_name || 'Pcs'}</strong><br><small class="text-muted">${pkg.barcode || ''}</small></td>
            <td>${parseFloat(pkg.base_qty)}</td>
            <td style="color:var(--warning); font-weight:700;">${fmt(modal)}</td>
            <td>${ecerHtml}</td>
            <td>${grosirHtml}</td>
            <td>${tierHtml}</td>`;
        tbody.appendChild(tr);
    });
}

// ─── Clear Reference ──────────────────────────────────
function clearReference() {
    referenceProduct = null;
    targetProducts   = [];

    document.getElementById('refSearchBox').style.display   = '';
    document.getElementById('refSelectedCard').classList.remove('visible');
    document.getElementById('refSearchInput').value = '';
    document.getElementById('refClearIcon').style.display = 'none';
    closeResults('ref');

    document.getElementById('targetSearchInput').disabled = true;
    document.getElementById('targetHint').style.display   = 'none';
    document.getElementById('fabBar').classList.remove('visible');
    document.querySelector('#stepTarget .mv-step-badge').style.background = 'var(--text-muted)';

    renderTargetGrid();
    setTimeout(() => document.getElementById('refSearchInput').focus(), 100);
}

// ─── Load Variants ────────────────────────────────────
async function loadVariants() {
    document.getElementById('targetLoader').classList.add('show');
    try {
        const r    = await fetch(`${BASE}api/products/${referenceProduct.id}/variants`);
        const data = await r.json();
        if (data.success && data.variants) {
            targetProducts = data.variants.map(v => ({
                id:        v.id,
                name:      v.full_name || v.short_label,
                meta:      `${v.code || ''} · ${v.category_name || '-'} · ${v.brand_name || '-'}`,
                photo:     v.photo || null,
                packagings: v.packagings || []
            }));
        }
    } catch (e) {
        console.error(e);
        showToast('Gagal memuat varian otomatis', 'error');
    } finally {
        document.getElementById('targetLoader').classList.remove('show');
        renderTargetGrid();
    }
}

// ─── Select Target Manual ─────────────────────────────
function onSelectTargetManual(prod) {
    clearTargetSearch();
    if (prod.id == referenceProduct?.id) {
        showToast('Produk referensi tidak bisa dijadikan target', 'warning'); return;
    }
    if (targetProducts.find(t => t.id == prod.id)) {
        showToast('Produk sudah ada di daftar target', 'warning'); return;
    }
    targetProducts.unshift({ id: prod.id, name: prod.full_name || prod.short_label,
        meta: `${prod.code || ''} · ${prod.category_name || '-'} · ${prod.brand_name || '-'}`,
        photo: prod.photo || null,
        packagings: prod.packagings || [] });
    renderTargetGrid();
    showToast('Produk ditambahkan ke target', 'success');
}

function removeTarget(id) {
    targetProducts = targetProducts.filter(t => t.id != id);
    renderTargetGrid();
}

// ─── Render Target Grid ───────────────────────────────
function renderTargetGrid() {
    const grid = document.getElementById('targetGrid');
    const cnt  = document.getElementById('targetCountBadge');
    const fab  = document.getElementById('fabTargetCount');

    cnt.textContent = targetProducts.length;
    fab.textContent = `${targetProducts.length} varian`;
    grid.innerHTML  = '';

    if (!referenceProduct) {
        grid.innerHTML = `<div class="mv-target-empty"><i class="bi bi-arrow-up-circle"></i>Pilih Produk Referensi dahulu</div>`;
        return;
    }
    if (targetProducts.length === 0) {
        grid.innerHTML = `<div class="mv-target-empty"><i class="bi bi-inbox"></i>Belum ada varian target.<br>Cari dan tambah manual di atas.</div>`;
        return;
    }

    targetProducts.forEach(t => {
        const thumbHtml = t.photo
            ? `<div class="mv-target-icon" style="overflow:hidden;"><img src="${BASE}${t.photo}" style="width:100%;height:100%;object-fit:contain;"></div>`
            : `<div class="mv-target-icon"><i class="bi bi-box"></i></div>`;

        // Build packaging price rows
        let pkgRowsHtml = '';
        if (t.packagings && t.packagings.length > 0) {
            pkgRowsHtml = t.packagings.map(pkg => {
                const modal  = parseFloat(pkg.buy_price || 0);
                const ecer   = parseFloat(pkg.sell_price_retail || 0);
                const grosir = parseFloat(pkg.sell_price_wholesale || 0);

                let tierHtml = '';
                if (pkg.qty_prices && pkg.qty_prices.length > 0) {
                    tierHtml = `<div class="mv-tier-chips">`
                        + pkg.qty_prices.map(tp =>
                            `<span class="mv-tier-tag" style="font-size:9px;">≥${parseFloat(tp.min_qty)} : Rp${parseFloat(tp.unit_price).toLocaleString('id-ID')}</span>`
                        ).join('')
                        + `</div>`;
                }

                return `<div class="mv-target-pkg-row">
                    <div class="mv-pkg-label"><i class="bi bi-box2"></i> ${escHtml(pkg.unit_name || 'Pcs')}</div>
                    <div>
                        <div class="mv-pkg-prices">
                            <span class="mv-price-chip modal">Modal: Rp${modal.toLocaleString('id-ID')}</span>
                            <span class="mv-price-chip ecer">Ecer: Rp${ecer.toLocaleString('id-ID')}</span>
                            ${grosir > 0 ? `<span class="mv-price-chip grosir">Grosir: Rp${grosir.toLocaleString('id-ID')}</span>` : ''}
                        </div>
                        ${tierHtml}
                    </div>
                </div>`;
            }).join('');
        }

        const card = document.createElement('div');
        card.className = 'mv-target-card';
        card.innerHTML = `
            <div class="mv-target-card-top">
                ${thumbHtml}
                <div style="flex:1; min-width:0; padding-right: 24px;">
                    <div class="mv-target-name" title="${escHtml(t.name)}">${escHtml(t.name)}</div>
                    <div class="mv-target-meta">${escHtml(t.meta)}</div>
                </div>
                <button class="mv-target-del" onclick="removeTarget(${t.id})" title="Hapus dari target" style="position:absolute;top:9px;right:9px;">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            ${pkgRowsHtml ? `<div class="mv-target-card-prices">${pkgRowsHtml}</div>` : ''}
        `;
        grid.appendChild(card);
    });
}

// ─── Apply Pricing ────────────────────────────────────
async function applyPricing() {
    if (!referenceProduct || targetProducts.length === 0) return;

    const ok = await AppModal.show({
        title:      'Konfirmasi Multivarian',
        bodyHTML:   `Anda akan menerapkan seluruh harga &amp; kemasan dari <strong>${escHtml(referenceProduct.short_label || referenceProduct.full_name)}</strong> ke <strong>${targetProducts.length}</strong> varian target.<br><br><span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Data kemasan dan harga tier produk target akan <strong>ditimpa sepenuhnya</strong>. Tindakan ini tidak bisa dibatalkan.</span>`,
        submitText: 'Ya, Aplikasikan Sekarang',
        icon:       'bi-exclamation-triangle-fill',
        iconColor:  'var(--warning-bg)',
        iconAccent: 'var(--warning)'
    });
    if (!ok) return;

    const btn = document.getElementById('btnApply');
    const ori = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    btn.disabled  = true;

    try {
        const fd = new FormData();
        fd.append('reference_id', referenceProduct.id);
        targetProducts.forEach(t => fd.append('target_ids[]', t.id));

        const r    = await fetch(`${BASE}api/products/multivariant-apply`, { method: 'POST', body: fd });
        const data = await r.json();

        if (data.success) {
            showToast('Harga berhasil diaplikasikan ke seluruh target!', 'success');
            setTimeout(() => window.location.reload(), 1600);
        } else {
            showToast(data.message || 'Gagal mengaplikasikan harga', 'error');
            btn.innerHTML = ori; btn.disabled = false;
        }
    } catch (e) {
        console.error(e);
        showToast('Terjadi kesalahan jaringan', 'error');
        btn.innerHTML = ori; btn.disabled = false;
    }
}

// ─── Helper ───────────────────────────────────────────
function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}
</script>
