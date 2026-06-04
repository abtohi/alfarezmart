<!-- Finance Report View -->
<div class="page-section">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Laporan Keuangan</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Ringkasan aktivitas harian toko</p>
    </div>

    <!-- Date Picker -->
    <div id="offlineWarning" style="display:none; background:var(--warning-bg); color:var(--warning); padding:10px 16px; border:1px solid var(--warning); border-radius:var(--radius-lg); margin-bottom:20px; font-size:13px; align-items:center; gap:10px;">
        <i class="bi bi-wifi-off"></i> Sedang offline. Laporan ini adalah versi terakhir yang tersimpan di perangkat Anda.
    </div>
    <script>
        if (!navigator.onLine) {
            document.getElementById('offlineWarning').style.display = 'flex';
        }
    </script>
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-bottom:20px; border:1px solid var(--border-color);">
        <form action="<?= BASE_URL ?>reports" method="GET" style="display:flex; gap:12px; align-items:center;">
            <input type="date" name="date" class="form-control-dark" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>" style="flex:1;" onchange="this.form.submit()">
            <button type="submit" class="btn-primary-custom" style="padding:10px 16px;"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <!-- Dashboard Stats Grid for Reports -->
    <div style="display:grid; grid-template-columns:1fr; gap:12px; margin-bottom:20px;">
        
        <!-- Pemasukan -->
        <div style="background:var(--success-bg); border:1px solid var(--success); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:16px;">
            <div style="background:var(--success); color:white; width:48px; height:48px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:1.5rem;">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--success); font-weight:600;">Total Pemasukan (Penjualan)</div>
                <div style="font-size:24px; font-weight:800; color:var(--text-primary);"><?= formatRupiah($salesStats['revenue'] ?? 0) ?></div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Dari <?= $salesStats['transactions'] ?? 0 ?> transaksi kasir</div>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div style="background:var(--danger-bg); border:1px solid var(--danger); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:16px;">
            <div style="background:var(--danger); color:white; width:48px; height:48px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:1.5rem;">
                <i class="bi bi-graph-down-arrow"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--danger); font-weight:600;">Total Pengeluaran (Pembelian/Restock)</div>
                <div style="font-size:24px; font-weight:800; color:var(--text-primary);"><?= formatRupiah($purchaseStats['expense'] ?? 0) ?></div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Ke <?= $purchaseStats['transactions'] ?? 0 ?> supplier/nota</div>
            </div>
        </div>

        <!-- Profit -->
        <div style="background:var(--info-bg); border:1px solid var(--info); border-radius:var(--radius-lg); padding:16px; display:flex; align-items:center; gap:16px;">
            <div style="background:var(--info); color:white; width:48px; height:48px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:1.5rem;">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--info); font-weight:600;">Estimasi Laba Kotor</div>
                <div style="font-size:24px; font-weight:800; color:var(--text-primary);"><?= formatRupiah($salesStats['gross_profit'] ?? 0) ?></div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Margin dari <?= $salesStats['items_sold'] ?? 0 ?> item terjual</div>
            </div>
        </div>

    </div>
    
    <div style="font-size:11px; color:var(--text-muted); text-align:center;">
        * Laba kotor dihitung dari Harga Jual dikurangi Harga Modal (Buy Price) pada saat barang terjual. Belum termasuk biaya operasional lainnya.
    </div>

    <!-- Additional Reports -->
    <div style="margin-top:30px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--text-muted);">Menu Analisa Tambahan</h3>
        <a href="<?= BASE_URL ?>reports/product-history" style="display:flex; align-items:center; gap:16px; background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; text-decoration:none; color:var(--text-primary); margin-bottom:12px;">
            <div style="background:rgba(59,130,246,0.1); color:var(--primary); width:40px; height:40px; border-radius:10px; display:flex; justify-content:center; align-items:center; font-size:1.2rem;">
                <i class="bi bi-box-seam"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:600; font-size:14px;">Riwayat & Analisa Produk</div>
                <div style="font-size:11px; color:var(--text-muted);">Bandingkan harga supplier & lihat riwayat pembelian</div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </a>
    </div>

</div>

<?php 
// Helper to format currency directly in view
function formatRupiah(float|int $angka = 0): string {
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
}
?>
