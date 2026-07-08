<style>
/* ===== Dark Mode Overrides - Documentation Page ===== */
.card {
    background: var(--surface-1) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: transparent !important;
}
.list-group-item {
    background: var(--surface-1) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.list-group-item:hover {
    background: var(--surface-2) !important;
    color: var(--primary) !important;
}
/* Table overrides */
.table {
    color: var(--text-primary) !important;
    --bs-table-bg: var(--surface-1);
    --bs-table-striped-bg: var(--surface-2);
    --bs-table-border-color: var(--border-color);
    --bs-table-striped-color: var(--text-primary);
}
.table > :not(caption) > * > * {
    background-color: var(--bs-table-bg) !important;
    color: var(--text-primary) !important;
    border-bottom-color: var(--border-color) !important;
}
.table-striped > tbody > tr:nth-of-type(odd) > * {
    background-color: var(--surface-2) !important;
}
.table-light, .table thead.table-light th {
    background-color: var(--surface-2) !important;
    color: var(--text-secondary) !important;
    border-color: var(--border-color) !important;
}
.table-bordered > :not(caption) > * > * {
    border-color: var(--border-color) !important;
}
/* Alert overrides */
.alert { border-radius: 12px !important; }
.alert-warning {
    background-color: var(--warning-bg) !important;
    border-color: rgba(255,183,3,0.3) !important;
    color: var(--warning) !important;
}
.alert-warning strong { color: var(--warning) !important; }
/* Code blocks */
code {
    background-color: var(--surface-2) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 2px 6px;
}
/* Border / text colors */
.border-success { border-color: var(--success) !important; }
.border-warning { border-color: var(--warning) !important; }
.border-danger  { border-color: var(--danger)  !important; }
.border-primary { border-color: var(--primary) !important; }
.text-success   { color: var(--success) !important; }
.text-warning   { color: var(--warning) !important; }
.text-danger    { color: var(--danger)  !important; }
.border-bottom  { border-color: var(--border-color) !important; }
/* Test case info boxes */
.doc-info-box {
    background: var(--surface-2);
    border-radius: 12px;
    padding: 14px;
    border: 1px solid var(--border-color);
}
.doc-info-box.success { border-color:rgba(46,196,182,.4)!important; background:var(--success-bg)!important; }
.doc-info-box.warning { border-color:rgba(255,183,3,.4)!important;  background:var(--warning-bg)!important; }
.doc-info-box.danger  { border-color:rgba(239,71,111,.4)!important; background:var(--danger-bg)!important;  }
.doc-info-box.info    { border-color:rgba(17,138,178,.4)!important; background:rgba(17,138,178,.08)!important; }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="list-group sticky-top" style="top:20px;">
                <a href="#webhook"        class="list-group-item list-group-item-action fw-bold"><i class="bi bi-link-45deg me-2"></i> Pengaturan Webhook</a>
                <a href="#response-codes" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-bug me-2"></i> Response Codes</a>
                <a href="#test-cases"     class="list-group-item list-group-item-action fw-bold"><i class="bi bi-magic me-2"></i> Test Cases (Sandbox)</a>
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9">

            <!-- WEBHOOK -->
            <div class="card border-0 shadow-sm mb-4" id="webhook" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Pengaturan Webhook (Otomatisasi Status)</h5>
                    <p>Webhook berfungsi agar status transaksi di aplikasi Anda bisa berubah secara otomatis dari <strong>Pending</strong> menjadi <strong>Sukses</strong> atau <strong>Gagal</strong> tanpa harus mengeceknya secara manual.</p>
                    <div class="alert alert-warning">
                        <strong>Perhatian:</strong> Webhook tidak akan bekerja jika Anda masih menggunakan XAMPP (localhost). Sistem Anda harus sudah di-hosting (online).
                    </div>
                    <h6>Langkah-langkah:</h6>
                    <ol>
                        <li>Login ke Dashboard Digiflazz.</li>
                        <li>Masuk ke menu <strong>Atur Koneksi &gt; API &gt; Webhook URL</strong>.</li>
                        <li>Masukkan URL Webhook Anda:<br><code class="fs-5">https://nama-domain-anda.com/api/ppob/webhook</code></li>
                        <li>Jika Anda menggunakan Secret Key di Digiflazz, pastikan untuk memasukkan Secret Key tersebut di menu <strong>Pengaturan PPOB</strong> di aplikasi ini. Jika tidak, kosongkan saja.</li>
                    </ol>
                </div>
            </div>

            <!-- RESPONSE CODES -->
            <div class="card border-0 shadow-sm mb-4" id="response-codes" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Response Codes (Kode Error)</h5>
                    <p>Saat transaksi gagal, Digiflazz akan mengembalikan pesan error. Berikut adalah arti dari pesan-pesan umum tersebut:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr><th>Status</th><th>Pesan</th><th>Penjelasan</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">Sukses</span></td>
                                    <td>Transaksi Sukses</td>
                                    <td>Transaksi berhasil diproses. Saldo Digiflazz terpotong. SN akan tersedia.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>Sedang Diproses</td>
                                    <td>Transaksi sedang diantre oleh provider (Telkomsel/PLN). Tunggu 1-5 menit.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Gagal</span></td>
                                    <td>Gagal - Nomor Tujuan Salah</td>
                                    <td>Format nomor tidak sesuai atau nomor hangus.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Gagal</span></td>
                                    <td>Gagal - Produk Sedang Gangguan</td>
                                    <td>Sistem dari provider (mis: PLN/Telkomsel) sedang maintenance. Coba lagi nanti.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Gagal</span></td>
                                    <td>Gagal - Saldo Tidak Cukup</td>
                                    <td>Saldo Digiflazz Anda tidak mencukupi untuk melakukan transaksi ini. Segera Topup!</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TEST CASES -->
            <div class="card border-0 shadow-sm mb-4" id="test-cases" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Test Cases (Panduan Sandbox)</h5>
                    <p>Saat aplikasi dalam mode <strong>Development</strong> (Sandbox), Anda tidak perlu melakukan Topup Saldo. Anda bisa menggunakan nomor sakti berikut untuk mensimulasikan berbagai skenario transaksi:</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="doc-info-box success">
                                <h6 class="text-success fw-bold mb-1">087800001230</h6>
                                <p class="mb-0 small">Gunakan nomor ini sebagai Nomor Tujuan agar transaksi langsung berstatus <strong>Sukses</strong>.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="doc-info-box warning">
                                <h6 class="text-warning fw-bold mb-1">087800001231</h6>
                                <p class="mb-0 small">Gunakan nomor ini agar transaksi <strong>Pending</strong> selamanya.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="doc-info-box danger">
                                <h6 class="text-danger fw-bold mb-1">087800001232</h6>
                                <p class="mb-0 small">Gunakan nomor ini agar transaksi berstatus <strong>Gagal</strong> dan direfund.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="doc-info-box info">
                                <h6 class="fw-bold mb-1" style="color:var(--primary);">087800001233</h6>
                                <p class="mb-0 small">Transaksi Sukses, namun SN (Serial Number) kosong.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
