<div class="page-section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Riwayat Produk</h2>
            <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Analisa harga & riwayat pembelian</p>
        </div>
        <a href="<?= BASE_URL ?>reports" class="btn-primary-custom" style="padding:8px 16px; font-size:12px; background:var(--surface-1); color:var(--text-primary); border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Product Search -->
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-bottom:24px; border:1px solid var(--border-color); position:relative;">
        <label style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:8px; display:block;">CARI PRODUK</label>
        
        <div id="searchWrapper" style="position:relative;">
            <div class="search-input-wrapper" style="background:var(--bg-primary); border-radius:var(--radius-md); padding:0 12px; border:1px solid var(--border-color); display:flex; align-items:center; gap:8px;">
                <i class="bi bi-upc-scan" style="color:var(--primary); cursor:pointer; font-size:1.1rem;" id="btnScanBarcode" title="Scan Barcode Kamera"></i>
                <input type="text" id="productSearch" placeholder="Ketik nama produk atau scan barcode..." autocomplete="off" style="flex:1; border:none; background:transparent; padding:12px 0; color:var(--text-primary); font-size:14px; outline:none; font-family:var(--font-family);">
                <i class="bi bi-search" style="color:var(--text-muted);"></i>
            </div>
            
            <div id="historySearchResults" style="position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:var(--shadow-lg); z-index:1000; display:none; max-height:300px; overflow-y:auto;">
                <!-- Populated via JS -->
            </div>
        </div>

        <div id="selectedProductCard" style="display:none; background:var(--bg-primary); padding:16px; border-radius:var(--radius-md); border:1px solid var(--border-color); flex-direction:column; gap:12px; margin-top:12px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div style="display:flex; gap:12px; align-items:center;">
                    <img id="selectedProductPhoto" src="" style="width:50px; height:50px; object-fit:cover; border-radius:var(--radius-sm); border:1px solid var(--border-color); display:none;" onerror="this.style.display='none'">
                    <div>
                        <div id="selectedProductName" style="font-weight:700; font-size:15px; color:var(--text-primary); margin-bottom:2px;">-</div>
                        <div id="selectedProductCode" style="font-size:12px; color:var(--text-muted);">Kode: -</div>
                    </div>
                </div>
                <button id="btnClearProduct" style="background:transparent; border:1px solid var(--danger); color:var(--danger); border-radius:var(--radius-sm); padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer;">
                    <i class="bi bi-x-lg"></i> Ganti
                </button>
            </div>
            <div id="selectedProductPackagings" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:8px; border-top:1px dashed var(--border-color); padding-top:12px; margin-top:4px;">
                <!-- Filled via JS -->
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state">
        <i class="bi bi-box-seam"></i>
        <h3>Pilih Produk</h3>
        <p>Cari produk di atas untuk melihat perbandingan harga antar supplier dan riwayat pembeliannya.</p>
    </div>

    <!-- Analysis Container -->
    <div id="analysisContainer" style="display:none;">
        
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--text-muted);"><i class="bi bi-award text-warning"></i> REKOMENDASI SUPPLIER</h3>
        <div id="supplierComparisonGrid" style="display:grid; grid-template-columns:1fr; gap:12px; margin-bottom:24px;">
            <!-- JS -->
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h3 style="font-size:14px; font-weight:700; color:var(--text-muted); margin:0;"><i class="bi bi-clock-history text-primary"></i> RIWAYAT PEMBELIAN</h3>
            <a href="#" id="btnExport" style="font-size:11px; background:var(--success); color:white; padding:6px 12px; border-radius:var(--radius-sm); text-decoration:none; font-weight:600;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); border:1px solid var(--border-color); overflow:hidden;">
            <div style="overflow-x:auto;">
                <table style="width:100%; text-align:left; border-collapse:collapse; font-size:12px;">
                    <thead style="background:var(--bg-secondary); color:var(--text-muted); font-size:11px; text-transform:uppercase;">
                        <tr>
                            <th style="padding:12px; border-bottom:1px solid var(--border-color);">Tanggal</th>
                            <th style="padding:12px; border-bottom:1px solid var(--border-color);">Supplier & Ref</th>
                            <th style="padding:12px; border-bottom:1px solid var(--border-color);">Harga Satuan</th>
                            <th style="padding:12px; border-bottom:1px solid var(--border-color); text-align:right;">Qty / Total</th>
                        </tr>
                    </thead>
                    <tbody id="historyTbody">
                        <!-- JS -->
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('productSearch');
    var searchWrapper = document.getElementById('searchWrapper');
    var searchResults = document.getElementById('historySearchResults');
    var selectedProductCard = document.getElementById('selectedProductCard');
    var btnClearProduct = document.getElementById('btnClearProduct');
    var analysisContainer = document.getElementById('analysisContainer');
    var emptyState = document.getElementById('emptyState');
    var btnExport = document.getElementById('btnExport');
    var btnScanBarcode = document.getElementById('btnScanBarcode');

    function escapeHtmlLocal(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    // ── Barcode Scanner ─────────────────────────────────────────────
    if (btnScanBarcode) {
        btnScanBarcode.addEventListener('click', function() {
            if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
                var fakeInput = document.createElement('input');
                BarcodeUtil.scanBarcode(fakeInput, function(code) {
                    if (code) {
                        searchInput.value = code;
                        lookupBarcode(code);
                    }
                });
            } else {
                showToast('Modul scanner belum dimuat. Refresh halaman.', 'error');
            }
        });
    }

    // ── Barcode lookup by code ───────────────────────────────────────
    async function lookupBarcode(code) {
        searchResults.style.display = 'none';
        try {
            var resp = await fetch(BASE_URL + 'api/products/barcode/' + encodeURIComponent(code));
            if (resp.ok) {
                var data = await resp.json();
                if (data && data.id) {
                    selectProduct(data);
                    return;
                }
            }
        } catch(e) {}
        // Fallback to text search
        doSearch(code);
    }

    // ── Auto-suggest on typing ─────────────────────
    var searchTimeout;
    var currentSearchAbortController = null;

    searchInput.addEventListener('input', function(e) {
        var query = e.target.value.trim();
        if (query.length < 1) {
            clearTimeout(searchTimeout);
            searchResults.style.display = 'none';
            if (currentSearchAbortController) currentSearchAbortController.abort();
            return;
        }
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            doSearch(query);
        }, 300);
    });

    // ── Show results on focus ──────────────────────
    searchInput.addEventListener('focus', function(e) {
        var query = searchInput.value.trim();
        if (query.length >= 1 && searchResults.children.length > 0) {
            searchResults.style.display = 'block';
        }
    });

    // ── Enter key: try barcode first ────────────────────────────────
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var q = searchInput.value.trim();
            if (q) lookupBarcode(q);
        }
    });

    // ── Perform search and render results ───────────────────────────
    async function doSearch(query) {
        searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:var(--text-muted); font-size:13px;"><i class="spinner-border spinner-border-sm"></i> Mencari...</div>';
        searchResults.style.display = 'block';

        if (currentSearchAbortController) {
            currentSearchAbortController.abort();
        }
        currentSearchAbortController = new AbortController();

        try {
            const res = await fetch(BASE_URL + 'api/products/search?q=' + encodeURIComponent(query), {
                signal: currentSearchAbortController.signal
            });
            const data = await res.json();

            searchResults.innerHTML = '';
            
            if (!data || !Array.isArray(data) || data.length === 0) {
                searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">Tidak ada produk ditemukan</div>';
                searchResults.style.display = 'block';
                return;
            }

                data.forEach(function(p) {
                    var item = document.createElement('div');
                    item.style.cssText = 'padding:12px 16px; border-bottom:1px solid var(--border-color); cursor:pointer; transition:background 150ms ease; display:flex; gap:12px; align-items:center;';
                    
                    var name = escapeHtmlLocal(p.short_label || p.full_name || '');
                    var brand = escapeHtmlLocal(p.brand_name || '');
                    
                    var photoHtml = '';
                    if (p.photo) {
                        photoHtml = '<img src="' + BASE_URL + p.photo + '" style="width:40px; height:40px; object-fit:cover; border-radius:var(--radius-sm); border:1px solid var(--border-color);" onerror="this.style.display=\'none\'">';
                    } else {
                        photoHtml = '<div style="width:40px; height:40px; background:var(--surface-2); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; color:var(--text-muted);"><i class="bi bi-image"></i></div>';
                    }

                    var packagingsHtml = '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:6px;">';
                    if (p.packagings && p.packagings.length > 0) {
                        p.packagings.forEach(function(pkg) {
                            packagingsHtml += '<span style="font-size:10px; background:var(--surface-2); padding:3px 6px; border-radius:4px; color:var(--text-primary); border:1px solid var(--border-color);"><span style="color:var(--text-muted);">' + escapeHtmlLocal(pkg.unit_name) + ':</span> <strong style="color:var(--primary);">' + formatRupiah(pkg.buy_price) + '</strong></span>';
                        });
                    }
                    packagingsHtml += '</div>';
                    
                    item.innerHTML = photoHtml + 
                        '<div style="flex:1;">' + 
                            '<div style="font-weight:600; font-size:13px; color:var(--text-primary);">' + name + '</div>' +
                            (brand ? '<div style="font-size:11px; color:var(--text-muted);">' + brand + '</div>' : '') + 
                            packagingsHtml +
                        '</div>';
                        
                    item.addEventListener('click', function() { selectProduct(p); });
                    item.addEventListener('mouseenter', function() { this.style.background = 'var(--surface-2)'; });
                    item.addEventListener('mouseleave', function() { this.style.background = ''; });
                    searchResults.appendChild(item);
                });
                searchResults.style.display = 'block';
        } catch(err) {
            if (err.name === 'AbortError') return; // Ignore aborted fetch requests
            console.error('Search error:', err);
            searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:var(--danger); font-size:13px;">Gagal mencari: ' + (err.message || 'Unknown') + '</div>';
            searchResults.style.display = 'block';
        }
    }

    // Handle clicking outside search results
    document.addEventListener('click', function(e) {
        var isClickInsideSearch = searchWrapper.contains(e.target) || e.target === btnScanBarcode;
        if (!isClickInsideSearch) {
            searchResults.style.display = 'none';
        }
    });

    // Clear product selection
    btnClearProduct.addEventListener('click', function() {
        selectedProductCard.style.display = 'none';
        searchWrapper.style.display = 'block';
        searchInput.value = '';
        searchInput.focus();
        
        analysisContainer.style.display = 'none';
        emptyState.style.display = 'flex';
        btnExport.href = '#';
    });

    function selectProduct(p) {
        searchResults.style.display = 'none';
        searchWrapper.style.display = 'none';
        
        document.getElementById('selectedProductName').textContent = p.full_name || p.short_label || '';
        document.getElementById('selectedProductCode').textContent = 'Kode: ' + (p.code || '-');
        
        var photoEl = document.getElementById('selectedProductPhoto');
        if (p.photo) {
            photoEl.src = BASE_URL + p.photo;
            photoEl.style.display = 'block';
        } else {
            photoEl.style.display = 'none';
        }

        var packagingsEl = document.getElementById('selectedProductPackagings');
        packagingsEl.innerHTML = '';
        if (p.packagings && p.packagings.length > 0) {
            p.packagings.forEach(function(pkg) {
                var div = document.createElement('div');
                div.style.cssText = 'background:var(--surface-2); padding:8px; border-radius:var(--radius-sm); border:1px solid var(--border-color);';
                div.innerHTML = '<div style="font-size:10px; color:var(--text-muted); margin-bottom:2px;">' + escapeHtmlLocal(pkg.unit_name) + ' (Lvl ' + pkg.level + ')</div>' +
                                '<div style="font-size:13px; font-weight:700; color:var(--primary);">' + formatRupiah(pkg.buy_price) + ' <span style="font-size:9px; font-weight:400; color:var(--text-muted);">/ modal</span></div>';
                packagingsEl.appendChild(div);
            });
            packagingsEl.style.display = 'grid';
        } else {
            packagingsEl.style.display = 'none';
        }
        
        selectedProductCard.style.display = 'flex';
        btnExport.href = BASE_URL + 'reports/product-history/export/' + p.id;
        
        loadProductData(p.id);
    }

    function loadProductData(id) {
        emptyState.style.display = 'none';
        analysisContainer.style.display = 'block';
        
        document.getElementById('supplierComparisonGrid').innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;"><i class="spinner-border spinner-border-sm"></i> Memuat data...</div>';
        document.getElementById('historyTbody').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);"><i class="spinner-border spinner-border-sm"></i> Memuat riwayat...</td></tr>';

        fetch(BASE_URL + 'api/reports/product-history/' + id, { credentials: 'same-origin' })
            .then(function(resp) {
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                return resp.json();
            })
            .then(function(res) {
                if (res.status === 'success') {
                    renderComparison(res.data.comparison);
                    renderHistory(res.data.history);
                } else {
                    document.getElementById('supplierComparisonGrid').innerHTML = '<div style="color:var(--danger); padding:16px; text-align:center;">Data tidak tersedia.</div>';
                    document.getElementById('historyTbody').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);">Tidak ada riwayat.</td></tr>';
                }
            })
            .catch(function(err) {
                console.error('Load error:', err);
                document.getElementById('supplierComparisonGrid').innerHTML = '<div style="color:var(--danger); padding:16px; text-align:center;">Gagal memuat data: ' + (err.message || 'Unknown error') + '</div>';
            });
    }

    function renderComparison(data) {
        var grid = document.getElementById('supplierComparisonGrid');
        grid.innerHTML = '';
        
        if (!data || data.length === 0) {
            grid.innerHTML = '<div style="background:var(--surface-2); padding:16px; border-radius:var(--radius-md); font-size:13px; color:var(--text-muted); text-align:center;">Belum ada riwayat pembelian untuk produk ini.</div>';
            return;
        }

        // Find the cheapest supplier based on LATEST base price
        var bestSupplier = data[0];
        data.forEach(function(item) {
            if (parseFloat(item.latest_base_price) < parseFloat(bestSupplier.latest_base_price)) {
                bestSupplier = item;
            }
        });

        var cardGrid = document.createElement('div');
        cardGrid.style.cssText = 'display:grid; grid-template-columns:1fr; gap:12px;';

        data.forEach(function(item) {
            var isBest = item.supplier_id === bestSupplier.supplier_id;
            var card = document.createElement('div');
            
            card.style.cssText = 'background:var(--surface-1); border:1px solid ' + (isBest ? 'var(--warning)' : 'var(--border-color)') + '; border-radius:var(--radius-lg); padding:16px; position:relative;';
            
            var bestBadge = isBest ? '<div style="position:absolute; top:-10px; right:16px; background:var(--warning); color:#000; font-size:10px; font-weight:800; padding:2px 10px; border-radius:10px;"><i class="bi bi-star-fill"></i> TERMURAH SAAT INI</div>' : '';
            
            var supplierIcon = isBest ? '<i class="bi bi-star-fill" style="color:var(--warning);margin-right:4px;"></i>' : '<i class="bi bi-truck" style="color:var(--text-muted);margin-right:4px;"></i>';
            var nameColor = isBest ? 'var(--warning)' : 'var(--text-primary)';
            
            var latestBase = parseFloat(item.latest_base_price);
            var avgBase = parseFloat(item.avg_base_price);
            var trendHtml = '';
            
            if (latestBase > avgBase) {
                var pct = ((latestBase - avgBase) / avgBase * 100).toFixed(1);
                trendHtml = '<span style="color:var(--danger); font-size:11px; font-weight:600;"><i class="bi bi-graph-up-arrow"></i> Naik ' + pct + '% dari rata-rata</span>';
            } else if (latestBase < avgBase) {
                var pct = ((avgBase - latestBase) / avgBase * 100).toFixed(1);
                trendHtml = '<span style="color:var(--success); font-size:11px; font-weight:600;"><i class="bi bi-graph-down-arrow"></i> Turun ' + pct + '% dari rata-rata</span>';
            } else {
                trendHtml = '<span style="color:var(--text-muted); font-size:11px;"><i class="bi bi-dash"></i> Harga stabil</span>';
            }
            
            card.innerHTML = bestBadge +
                '<div style="font-weight:700; font-size:14px; margin-bottom:12px; color:' + nameColor + ';">' +
                    supplierIcon + escapeHtmlLocal(item.supplier_name || 'Tanpa Supplier') +
                '</div>' +
                '<div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:12px;">' +
                    '<span style="color:var(--text-muted);">Pembelian Terakhir <small>(per ' + escapeHtmlLocal(item.latest_unit_name) + ')</small></span>' +
                    '<span style="color:var(--text-primary); font-weight:600; font-size:13px;">' + formatRupiah(item.latest_actual_price) + '</span>' +
                '</div>' +
                '<div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:12px;">' +
                    '<span style="color:var(--text-muted); font-weight:600;">Harga Modal per ' + escapeHtmlLocal(item.base_unit_name) + '</span>' +
                    '<span style="color:var(--primary); font-weight:800; font-size:15px;">' + formatRupiah(item.latest_base_price) + '</span>' +
                '</div>' +
                '<div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:11px;">' +
                    '<span style="color:var(--text-muted);">Tren Modal</span>' +
                    trendHtml +
                '</div>' +
                '<div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:11px;">' +
                    '<span style="color:var(--text-muted);">Rata-rata: ' + formatRupiah(item.avg_base_price) + '</span>' +
                    '<span style="color:var(--text-muted);">Rentang: ' + formatRupiah(item.min_base_price) + ' - ' + formatRupiah(item.max_base_price) + '</span>' +
                '</div>' +
                '<div style="border-top:1px dashed var(--border-color); padding-top:8px; margin-top:4px; font-size:11px; color:var(--text-muted);">' +
                    '<i class="bi bi-calendar2-check"></i> Pembelian terakhir: ' + formatDate(item.last_purchase_date) +
                '</div>';

            cardGrid.appendChild(card);
        });

        grid.appendChild(cardGrid);

        // Add recommendation summary based on latest_base_price
        if (data.length > 1) {
            var maxLatest = parseFloat(data[0].latest_base_price);
            data.forEach(function(item) {
                if (parseFloat(item.latest_base_price) > maxLatest) maxLatest = parseFloat(item.latest_base_price);
            });
            
            var savings = maxLatest - parseFloat(bestSupplier.latest_base_price);
            if (savings > 0) {
                var summaryCard = document.createElement('div');
                summaryCard.style.cssText = 'background:var(--success-bg); border:1px solid var(--success); border-radius:var(--radius-lg); padding:14px; text-align:center; margin-top:12px;';
                summaryCard.innerHTML = '<div style="font-size:12px; color:var(--success); font-weight:600;">' +
                    '<i class="bi bi-lightbulb-fill"></i> Rekomendasi: Beli dari <strong>' + escapeHtmlLocal(bestSupplier.supplier_name || '-') + '</strong>' +
                    '<br>Hemat modal hingga <strong>' + formatRupiah(savings) + '</strong> per ' + escapeHtmlLocal(bestSupplier.base_unit_name) + ' dibanding supplier lain' +
                '</div>';
                grid.appendChild(summaryCard);
            }
        }
    }

    function renderHistory(data) {
        var tbody = document.getElementById('historyTbody');
        tbody.innerHTML = '';
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:30px 16px; color:var(--text-muted);">Tidak ada riwayat pembelian.</td></tr>';
            return;
        }

        data.forEach(function(row) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td style="padding:12px; border-bottom:1px solid var(--border-color);">' +
                    '<div style="font-weight:600; color:var(--text-primary);">' + formatDate(row.purchase_date) + '</div>' +
                '</td>' +
                '<td style="padding:12px; border-bottom:1px solid var(--border-color);">' +
                    '<div style="font-weight:600; color:var(--text-primary);">' + escapeHtmlLocal(row.supplier_name || '-') + '</div>' +
                    '<div style="font-size:10px; color:var(--text-muted); background:var(--surface-2); display:inline-block; padding:2px 6px; border-radius:4px; margin-top:4px;">' + escapeHtmlLocal(row.purchase_code) + '</div>' +
                '</td>' +
                '<td style="padding:12px; border-bottom:1px solid var(--border-color);">' +
                    '<div style="font-weight:700; color:var(--text-primary);">' + formatRupiah(row.buy_price) + '</div>' +
                    '<div style="font-size:10px; color:var(--text-muted);">' + escapeHtmlLocal(row.unit_name || '-') + ' (Lvl ' + row.level + ')</div>' +
                '</td>' +
                '<td style="padding:12px; border-bottom:1px solid var(--border-color); text-align:right;">' +
                    '<div style="font-weight:600;">' + row.quantity + 'x</div>' +
                    '<div style="font-weight:800; color:var(--text-primary); margin-top:2px;">' + formatRupiah(row.total_price) + '</div>' +
                '</td>';
            tbody.appendChild(tr);
        });
    }
});
</script>
