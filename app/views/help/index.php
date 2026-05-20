<div class="page-section" style="padding-bottom:100px;">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;"><i class="bi bi-question-circle" style="color:var(--primary);"></i> Bantuan & Panduan</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Panduan lengkap penggunaan AlfarezMart PWA</p>
    </div>

    <!-- Quick Nav -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px;">
        <a href="#alur" class="btn-outline-custom" style="padding:6px 12px; font-size:11px; text-decoration:none;"><i class="bi bi-diagram-3"></i> Alur Sistem</a>
        <a href="#fitur" class="btn-outline-custom" style="padding:6px 12px; font-size:11px; text-decoration:none;"><i class="bi bi-grid"></i> Fitur</a>
        <a href="#istilah" class="btn-outline-custom" style="padding:6px 12px; font-size:11px; text-decoration:none;"><i class="bi bi-book"></i> Istilah</a>
        <a href="#troubleshoot" class="btn-outline-custom" style="padding:6px 12px; font-size:11px; text-decoration:none;"><i class="bi bi-wrench"></i> Troubleshoot</a>
        <a href="#pembaruan" class="btn-outline-custom" style="padding:6px 12px; font-size:11px; text-decoration:none;"><i class="bi bi-lightning-fill" style="color:var(--warning);"></i> Pembaruan</a>
    </div>

    <!-- Section: Alur Sistem -->
    <div id="alur" class="help-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--primary);"><i class="bi bi-diagram-3"></i> Alur Kerja Sistem</h3>
        
        <div style="background:var(--surface-2); padding:14px; border-radius:var(--radius-md); margin-bottom:12px; font-size:13px; line-height:1.8;">
            <strong>1. Setup Awal (Master Data)</strong><br>
            Menu Lainnya → Pengaturan → Master Data → Tambah Kategori, Brand, Satuan.<br>
            <em>Atau Anda bisa menambahkannya langsung secara on-the-fly saat menginput produk baru.</em><br><br>
            <strong>2. Kelola Supplier & Sales</strong><br>
            Menu Lainnya → Supplier → Tambah Supplier dan Sales Rep. Data Sales ini akan digunakan saat input barang masuk.<br><br>
            <strong>3. Input Produk</strong><br>
            Menu Produk → Tambah Produk → Isi nama, brand, kategori → Atur kemasan multi-level (Satuan Terkecil hingga Terbesar) → Atur harga beli, jual ecer & grosir → Generate/scan barcode.<br><br>
            <strong>4. Barang Masuk (Pembelian & Input Massal)</strong><br>
            Menu Masuk → Pilih Sales (Supplier otomatis terisi) → Scan/cari produk atau gunakan <strong>Input Bulk (Massal)</strong> → Input qty & harga beli (Bisa input Total Harga) → Masukkan Diskon Nota & PPN lalu <em>Distribusikan ke Harga Modal</em> → Simpan → Stok otomatis bertambah. <br>
            <em>*Terdapat shortcut Buka Master Data di form Kemasan Beli agar mudah menambah Satuan tanpa perlu berpindah halaman jauh.</em><br><br>
            <strong>5. Penjualan (POS/Kasir)</strong><br>
            Menu Scan → Scan barcode / cari produk → Produk masuk keranjang → Atur qty / Harga Custom → Bayar → Cetak struk Bluetooth/Web.<br><br>
            <strong>6. Laporan & Analisis</strong><br>
            Menu Lainnya → Laporan → Perbandingan Harga Supplier (Pencarian Real-time) → Riwayat Pembelian Produk.
        </div>

        <div style="font-size:12px; color:var(--text-muted); padding:8px; background:var(--info-bg); border-radius:var(--radius-sm);">
            <i class="bi bi-info-circle" style="color:var(--info);"></i> <strong>Flow Stok:</strong> Stok bertambah otomatis saat Barang Masuk disimpan. Stok berkurang otomatis saat transaksi POS berhasil.
        </div>
    </div>

    <!-- Section: Fitur -->
    <div id="fitur" class="help-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--success);"><i class="bi bi-grid"></i> Panduan Fitur</h3>

        <!-- Produk -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-box-seam" style="color:var(--primary); margin-right:6px;"></i> Manajemen Produk</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Tambah Produk:</strong> Isi nama produk, pilih kategori & brand, atur kemasan multi-level (Pcs/Pack/Karton).<br>
                • <strong>Kemasan Multi-Level:</strong> Level 1 = satuan terkecil (Pcs), Level 2 = menengah (Pack), Level 3 = besar (Karton). Setiap level punya harga jual sendiri.<br>
                • <strong>Harga Bertingkat (Qty Pricing):</strong> Diskon otomatis berdasarkan jumlah beli. Contoh: beli 1-5 = Rp5.000, beli 6+ = Rp4.500.<br>
                • <strong>Barcode:</strong> Setiap level kemasan bisa punya barcode sendiri. Bisa di-generate otomatis atau input manual dari barcode produk.<br>
                • <strong>Label Struk:</strong> Nama singkat produk yang muncul di struk. Bisa diedit manual.<br>
                • <strong>Harga Custom:</strong> Centang "Harga custom" di POS untuk override harga secara manual per transaksi.<br>
                • <strong>Margin:</strong> Selisih antara harga beli dan harga jual. Ditampilkan dalam persen (%) dan Rupiah.
            </div>
        </details>

        <!-- POS -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-cart-check" style="color:var(--success); margin-right:6px;"></i> Kasir / POS</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Mode Ecer/Grosir:</strong> Tekan tombol Ecer/Grosir di atas untuk beralih. Harga otomatis menyesuaikan.<br>
                • <strong>Scan Barcode:</strong> Klik ikon scan di kolom pencarian, atau gunakan scanner Bluetooth fisik.<br>
                • <strong>Cari Produk:</strong> Ketik minimal 2 huruf, hasil pencarian otomatis muncul tanpa tekan Enter.<br>
                • <strong>Draft:</strong> Simpan keranjang sementara untuk dilanjutkan nanti. Berguna saat melayani beberapa pelanggan.<br>
                • <strong>Harga Custom di POS:</strong> Centang checkbox "Harga custom" lalu ketik total harga. Harga per satuan dihitung otomatis.<br>
                • <strong>Checkout:</strong> Tekan Bayar → Transaksi tersimpan → Pilih cetak struk (Bluetooth/Web).<br>
                • <strong>Auto-save:</strong> Keranjang otomatis tersimpan di perangkat. Jika halaman ditutup tidak sengaja, keranjang akan dimuat kembali.
            </div>
        </details>

        <!-- Pembelian -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-cart-plus" style="color:var(--warning); margin-right:6px;"></i> Barang Masuk (Pembelian)</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Pilih Supplier:</strong> Pilih supplier dari daftar atau tambah baru.<br>
                • <strong>Input Produk:</strong> Scan barcode atau cari nama. Pilih level kemasan, qty, dan harga beli.<br>
                • <strong>Harga Modal:</strong> Harga beli per satuan dari supplier. Harga ini digunakan untuk menghitung margin.<br>
                • <strong>Total Harga:</strong> Bisa input total langsung, harga per satuan dihitung otomatis (Total ÷ Qty).<br>
                • <strong>Margin Otomatis:</strong> Sistem menghitung margin berdasarkan harga beli vs harga jual existing.<br>
                • <strong>Stok Update:</strong> Setelah disimpan, stok produk otomatis bertambah sesuai qty yang diinput.
            </div>
        </details>

        <!-- Cetak Struk -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-printer" style="color:var(--info); margin-right:6px;"></i> Cetak Struk</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Printer Bluetooth Thermal:</strong> Tersedia untuk Android (Chrome/Edge). Hubungkan sekali, printer akan diingat untuk sesi berikutnya.<br>
                • <strong>Cetak Web/AirPrint:</strong> Untuk iOS atau jika Bluetooth tidak tersedia. Membuka jendela cetak browser.<br>
                • <strong>Pengaturan Struk:</strong> Lainnya → Pengaturan → Pengaturan Struk. Atur nama toko, alamat, telepon, header, footer, logo, dan lebar printer.<br>
                • <strong>Lebar Printer:</strong> Pilih 58mm (32 karakter) atau 80mm (48 karakter) sesuai printer Anda.<br>
                • <strong>Logo:</strong> Logo muncul di cetak browser/AirPrint dan di printer thermal Bluetooth (dicetak sebagai gambar raster).<br>
                • <strong>Preview:</strong> Di halaman Pengaturan Struk terdapat live preview simulasi struk thermal.
            </div>
        </details>

        <!-- Scanner -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-upc-scan" style="color:var(--danger); margin-right:6px;"></i> Scan Barcode & Cek Harga</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Kamera Scanner:</strong> Klik ikon kamera atau "Buka Kamera Scanner". Arahkan ke barcode, otomatis terbaca.<br>
                • <strong>Input Manual:</strong> Ketik kode barcode lalu tekan Enter atau tombol Cari.<br>
                • <strong>Hasil:</strong> Menampilkan nama produk, semua level harga (ecer & grosir), dan margin per satuan.<br>
                • <strong>Scanner Fisik:</strong> Scanner Bluetooth fisik yang terhubung ke HP akan otomatis mengisi field pencarian.
            </div>
        </details>

        <!-- Supplier -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-truck" style="color:var(--warning); margin-right:6px;"></i> Supplier & Sales Rep</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Kelola Supplier:</strong> Tambah, edit, hapus data supplier beserta kontak, alamat, dan tipe.<br>
                • <strong>Produk Supplier:</strong> Hubungkan produk ke supplier. Berguna untuk melacak dari mana barang dibeli.<br>
                • <strong>Sales Rep:</strong> Data sales/representatif dari supplier. Bisa dihubungkan ke transaksi pembelian.
            </div>
        </details>

        <!-- Laporan -->
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-graph-up" style="color:var(--primary); margin-right:6px;"></i> Laporan & Perbandingan Harga</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Perbandingan Harga:</strong> Cari produk → Lihat harga rata-rata, terendah, dan tertinggi dari setiap supplier.<br>
                • <strong>Rekomendasi:</strong> Sistem otomatis merekomendasikan supplier termurah dan menghitung potensi penghematan.<br>
                • <strong>Riwayat Pembelian:</strong> Tabel lengkap semua transaksi pembelian produk tertentu, termasuk tanggal, supplier, harga, dan qty.<br>
                • <strong>Export Excel:</strong> Unduh data riwayat pembelian ke file Excel untuk analisis lanjutan.<br>
                • <strong>Scan Barcode:</strong> Bisa scan barcode langsung di halaman Perbandingan Harga untuk mencari produk.
            </div>
        </details>
    </div>

    <!-- Section: Istilah -->
    <div id="istilah" class="help-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--warning);"><i class="bi bi-book"></i> Daftar Istilah</h3>
        <div style="font-size:12px; line-height:2;">
            <div style="display:grid; grid-template-columns:120px 1fr; gap:4px 12px;">
                <strong>POS</strong><span>Point of Sale — sistem kasir untuk mencatat penjualan.</span>
                <strong>Ecer & Grosir</strong><span>Ecer (Retail) untuk satuan kecil, Grosir (Wholesale) untuk pembelian banyak.</span>
                <strong>Modal (Buy Price)</strong><span>Harga beli barang dari supplier.</span>
                <strong>Margin</strong><span>Selisih persentase keuntungan antara harga modal dan harga jual.</span>
                <strong>Level Kemasan</strong><span>Struktur ukuran barang: Level 1 (Terkecil/Pcs), Level 2 (Menengah/Pack), Level 3 (Terbesar/Karton).</span>
                <strong>Satuan Dasar</strong><span>Satuan terkecil (Level 1) yang menjadi basis perhitungan stok (contoh: Pcs, Gram).</span>
                <strong>Base Qty</strong><span>Isi dari kemasan tersebut dihitung dalam satuan dasar (misal 1 Karton = 24 Pcs, maka Base Qty Karton adalah 24).</span>
                <strong>Qty Pricing</strong><span>Harga bertingkat berdasarkan jumlah pembelian (misal: beli 1 = 5.000, beli 5 = 4.500).</span>
                <strong>Auto-Suggest</strong><span>Fitur pencarian instan yang memunculkan rekomendasi saat Anda mengetik tanpa perlu klik Enter.</span>
                <strong>PWA</strong><span>Aplikasi web yang dapat di-install di layar utama HP layaknya aplikasi native.</span>
                <strong>Thermal Printer</strong><span>Printer struk kasir berbasis pemanas (tanpa tinta).</span>
            </div>
        </div>
    </div>

    <!-- Section: Troubleshooting -->
    <div id="troubleshoot" class="help-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--danger);"><i class="bi bi-wrench"></i> Troubleshooting</h3>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Aplikasi tidak memuat / halaman kosong</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Tutup aplikasi sepenuhnya (Force Close dari recent apps)<br>
                2. Buka kembali aplikasi<br>
                3. Jika masih bermasalah: Buka Chrome → Settings → Site Settings → Clear Data untuk situs AlfarezMart<br>
                4. Pastikan koneksi internet stabil
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Scan barcode error / kamera tidak bisa dibuka</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Pastikan sudah memberikan izin kamera ke browser/aplikasi<br>
                2. Pastikan tidak ada aplikasi lain yang menggunakan kamera<br>
                3. Gunakan browser Chrome atau Edge versi terbaru<br>
                4. Pastikan mengakses aplikasi via HTTPS (bukan HTTP biasa)<br>
                5. Coba refresh halaman dan buka scanner lagi
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Printer Bluetooth tidak bisa terhubung</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Pastikan printer sudah dinyalakan dan Bluetooth HP aktif<br>
                2. Pastikan printer dalam jangkauan (± 10 meter)<br>
                3. Gunakan Chrome/Edge di Android (iOS tidak mendukung Web Bluetooth)<br>
                4. Jika dialog pairing tidak muncul: refresh halaman lalu coba lagi<br>
                5. Jika masih gagal: matikan dan nyalakan kembali printer, lalu coba hubungkan ulang<br>
                6. <strong>Catatan:</strong> Setelah terhubung pertama kali, printer akan diingat. Pada cetak berikutnya, sistem akan mencoba auto-reconnect tanpa dialog.
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Struk tercetak berantakan / tidak rata</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Pastikan lebar printer sudah benar: Pengaturan → Pengaturan Struk → Lebar Printer<br>
                2. Untuk printer 58mm kertas kecil: pilih 58mm (32 karakter)<br>
                3. Untuk printer 80mm kertas besar: pilih 80mm (48 karakter)<br>
                4. Jika karakter tidak terbaca (kotak-kotak): printer tidak mendukung charset yang digunakan
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Database Connection Failed</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Pastikan server database (MySQL) sedang berjalan<br>
                2. Periksa koneksi internet jika database di server remote<br>
                3. Periksa file .env — pastikan DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD sudah benar<br>
                4. Jika menggunakan hosting: pastikan IP Anda sudah di-whitelist di Remote MySQL
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Perubahan tidak muncul di aplikasi (masih versi lama)</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Tutup aplikasi sepenuhnya (Force Close)<br>
                2. Buka kembali — Service Worker akan mengunduh versi terbaru<br>
                3. Jika masih lama: Chrome → Settings → Privacy → Clear browsing data → Cached images and files<br>
                4. Atau buka Chrome DevTools (F12) → Application → Storage → Clear site data
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Harga tidak sesuai / margin salah</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Periksa harga beli di halaman edit produk → tab Kemasan<br>
                2. Pastikan harga jual ecer dan grosir sudah diisi<br>
                3. Margin dihitung: (Jual - Beli) / Jual × 100%<br>
                4. Jika menggunakan Qty Pricing: periksa tabel harga bertingkat di halaman edit produk<br>
                5. Di POS, pastikan mode (Ecer/Grosir) sudah sesuai
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Login gagal / sesi expired</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Pastikan email/nomor HP dan password benar<br>
                2. Sesi login berlaku selama 2 jam. Setelah itu harus login ulang<br>
                3. Jika lupa password: hubungi admin untuk reset password<br>
                4. Pastikan cookies browser tidak diblokir
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Link "Master Data" tidak membuka</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Link "Master Data" di halaman Edit Produk dirancang untuk membuka tab baru<br>
                2. Pastikan browser Anda tidak memblokir pop-up/tab baru<br>
                3. Periksa setting browser: Privacy & Security → Pop-ups and redirects<br>
                4. Jika masih tidak muncul, buka Master Data secara manual: Menu Lainnya → Pengaturan → Master Data
            </div>
        </details>

        <details style="margin-bottom:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;">
            <summary style="padding:10px 12px; font-weight:600; font-size:12px; cursor:pointer; background:var(--surface-2);">Gagal Menghapus Master Data Satuan/Kategori/Brand</summary>
            <div style="padding:10px 12px; font-size:12px; line-height:1.8;">
                1. Sistem sekarang sudah menggunakan <em>ON DELETE SET NULL</em> sehingga satuan yang dihapus tidak akan merusak data yang sudah ada.<br>
                2. Namun, jika Anda menjumpai error constraint, ini menandakan data masih direferensikan pada log historis yang dikunci ketat (RESTRICT) oleh database.<br>
                3. Solusi: Ubah nama atau "non-aktifkan" master data tersebut jika memungkinkan, daripada dihapus secara permanen dari database.
            </div>
        </details>
    </div>

    <!-- Section: Info Teknis -->
    <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--warning);"><i class="bi bi-lightning-fill" style="color:var(--warning);"></i> Pembaruan & Fitur Terbaru</h3>
        
        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-graph-up-arrow" style="color:var(--primary); margin-right:6px;"></i> Rekomendasi Supplier Lebih Akurat (Berdasarkan Harga Terakhir)</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Perbandingan Per Satuan Dasar:</strong> Sistem kini secara cerdas mengonversi semua harga pembelian menjadi "Harga Per Satuan Terkecil" untuk membandingkan secara *fair* antara supplier yang menjual grosir (Karton) vs ecer (Pcs).<br>
                • <strong>Harga Terakhir:</strong> Penentuan label "TERMURAH" sekarang dinilai dari harga transaksi terakhir (*Latest Price*), bukan sekadar harga rata-rata keseluruhan (karena rentan inflasi/kenaikan harga di masa lalu).<br>
                • <strong>Tren Naik/Turun:</strong> Terdapat indikator tren harga yang membandingkan Harga Terakhir dengan Harga Rata-rata, sehingga Anda langsung tahu apakah harga barang sedang merangkak naik atau turun.
            </div>
        </details>

        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-search" style="color:var(--success); margin-right:6px;"></i> Pencarian Produk Real-time (Auto-Suggest)</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Otomatis Tanpa Enter:</strong> Di seluruh modul laporan dan pencarian, sistem sekarang menampilkan hasil secara <strong>otomatis saat Anda mengetik</strong>. Tidak perlu lagi menekan Enter atau tombol cari.<br>
                • <strong>Anti Lag (Debounce):</strong> Mekanisme canggih yang menahan beban *request* saat Anda mengetik cepat, menjadikan aplikasi tetap super ringan tanpa mengorbankan kuota atau memori HP.<br>
                • <strong>Scan Barcode Support:</strong> Tetap mendeteksi event *Enter* jika Anda menggunakan perangkat pemindai Barcode Fisik.
            </div>
        </details>

        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-box" style="color:var(--success); margin-right:6px;"></i> Perbaikan Master Data Satuan</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Error Duplikat Lebih Jelas:</strong> Jika satuan yang akan ditambahkan sudah ada, sistem sekarang menampilkan pesan error yang lebih deskriptif.<br>
                • <strong>Validasi Case-Insensitive:</strong> Sistem otomatis mendeteksi duplikat terlepas dari besar/kecil huruf (contoh: "Karton" dan "karton" dianggap sama).<br>
                • <strong>Jangan Perlu Khawatir Duplikat:</strong> Sistem akan mencegah Anda menambahkan satuan yang sama dua kali dengan pesan yang jelas.<br>
                • <strong>Tidak Bisa Menghapus?:</strong> Jika Anda tidak bisa menghapus satuan tertentu, kemungkinan satuan tersebut masih digunakan oleh produk. Coba gunakan fitur rename atau non-aktifkan daripada dihapus.
            </div>
        </details>

        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-rulers" style="color:var(--info); margin-right:6px;"></i> Penambahan Satuan Langsung dari Halaman Produk</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Shortcut Master Data:</strong> Saat mengedit produk atau menambahkan tingkat kemasan, di bagian Satuan ada tombol <strong>"Master Data"</strong> yang dapat membuka halaman Master Data dalam tab baru tanpa meninggalkan halaman edit.<br>
                • <strong>Tombol Tambah Satuan:</strong> Jika satuan yang Anda butuhkan belum ada, klik <strong>"Tambah Satuan Baru"</strong> di dropdown Satuan untuk menambahkan satuan baru langsung dari halaman Edit Produk.<br>
                • <strong>Otomatis Tersimpan:</strong> Satuan yang baru ditambahkan akan otomatis tersimpan di Master Data dan langsung bisa dipilih di form Satuan tanpa perlu refresh.<br>
                • <strong>Berlaku di Semua Halaman:</strong> Fitur ini juga tersedia di halaman Input Barang Masuk dan Input Massal agar memudahkan Anda bekerja tanpa perlu berganti halaman.
            </div>
        </details>

        <details style="margin-bottom:10px; border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
            <summary style="padding:12px 14px; font-weight:600; font-size:13px; cursor:pointer; background:var(--surface-2);"><i class="bi bi-upc-scan" style="color:var(--danger); margin-right:6px;"></i> Perbaikan Scanner Barcode</summary>
            <div style="padding:12px 14px; font-size:12px; line-height:1.8;">
                • <strong>Scanner Lebih Stabil:</strong> Kamera scanner sekarang lebih responsif dan tidak lagi menampilkan error "undefined".<br>
                • <strong>Deteksi Barcode Lebih Baik:</strong> Sistem dapat mendeteksi berbagai format barcode: CODE128, EAN-13, UPC-A, QR Code dengan lebih akurat.<br>
                • <strong>Error Message Lebih Jelas:</strong> Jika ada masalah dengan kamera, pesan error sekarang lebih informatif dan memudahkan troubleshooting.<br>
                • <strong>Kompatibilitas:</strong> Scanner bekerja optimal di browser Chrome, Edge, dan Samsung Internet di Android. iOS terbatas karena Safari tidak mendukung Web Bluetooth.
            </div>
        </details>
    </div>

    <!-- Section: Info Teknis -->
    <div id="teknis" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--text-muted);"><i class="bi bi-gear"></i> Informasi Teknis</h3>
        <div style="font-size:12px; line-height:2; color:var(--text-secondary);">
            <strong>Platform:</strong> Progressive Web App (PWA)<br>
            <strong>Browser Didukung:</strong> Chrome 85+, Edge 85+, Samsung Internet (Android). Safari (iOS) — terbatas, tanpa Bluetooth.<br>
            <strong>Printer Didukung:</strong> Printer thermal Bluetooth 58mm/80mm dengan protokol ESC/POS<br>
            <strong>Barcode Format:</strong> CODE128, EAN-13, UPC-A, QR Code<br>
            <strong>Keamanan:</strong> CSRF Token, Prepared Statements (SQL Injection safe), Session-based auth, XSS protection<br>
            <strong>Offline:</strong> Halaman dan aset statis tersedia offline via Service Worker cache
        </div>
    </div>

    <div style="text-align:center; padding:16px; font-size:11px; color:var(--text-muted);">
        <i class="bi bi-heart-fill" style="color:var(--danger);"></i> AlfarezMart PWA v3.5 — Sistem Manajemen Stok Toko<br>
        <span style="font-size:10px;">Pembaruan: Dashboard Kategorikal, Tren Harga Supplier, Auto-Suggest Realtime</span>
    </div>
</div>
