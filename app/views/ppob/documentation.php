<style>
/* Dark Mode Overrides */
.card { background: var(--surface-1) !important; color: var(--text-primary) !important; }
.list-group-item { background: var(--surface-1) !important; color: var(--text-primary) !important; border-color: var(--border-color) !important; }
.list-group-item:hover { background: var(--surface-2) !important; color: var(--primary) !important; }
.table { color: var(--text-primary) !important; border-color: var(--border-color) !important; }
.table-light { background-color: var(--surface-2) !important; color: var(--text-primary) !important; }
code.bg-light { background-color: var(--surface-2) !important; color: var(--text-primary) !important; }
.border-success { border-color: var(--success) !important; }
.border-warning { border-color: var(--warning) !important; }
.border-danger { border-color: var(--danger) !important; }
.text-success { color: var(--success) !important; }
.text-warning { color: var(--warning) !important; }
.text-danger { color: var(--danger) !important; }
</style>
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 mb-4">
            <div class="list-group sticky-top" style="top: 20px;">
                <a href="#webhook" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-link-45deg me-2"></i> Pengaturan Webhook</a>
                <a href="#response-codes" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-bug me-2"></i> Response Codes</a>
                <a href="#test-cases" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-magic me-2"></i> Test Cases (Sandbox)</a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-md-9">
            
            <!-- SECTION: WEBHOOK -->
            <div class="card border-0 shadow-sm mb-4" id="webhook" style="border-radius: 16px;">
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
                        <li>Masukkan URL Webhook Anda: <br><code class="fs-5 bg-light px-2 rounded">https://nama-domain-anda.com/api/ppob/webhook</code></li>
                        <li>Jika Anda menggunakan Secret Key di Digiflazz, pastikan untuk memasukkan Secret Key tersebut di menu <strong>Pengaturan PPOB</strong> di aplikasi ini. Jika tidak, kosongkan saja.</li>
                    </ol>
                </div>
            </div>

            <!-- SECTION: RESPONSE CODES -->
            <div class="card border-0 shadow-sm mb-4" id="response-codes" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Response Codes (Kode Error)</h5>
                    <p>Saat transaksi gagal, Digiflazz akan mengembalikan pesan error. Berikut adalah arti dari pesan-pesan umum tersebut:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
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
                                    <td><span class="badge bg-warning">Pending</span></td>
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

            <!-- SECTION: TEST CASES -->
            <div class="card border-0 shadow-sm mb-4" id="test-cases" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Test Cases (Panduan Sandbox)</h5>
                    <p>Saat aplikasi dalam mode <strong>Development</strong> (Sandbox), Anda tidak perlu melakukan Topup Saldo. Anda bisa menggunakan nomor sakti berikut untuk mensimulasikan berbagai skenario transaksi:</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded border-success" style="background: var(--success-bg);">
                                <h6 class="text-success fw-bold">087800001230</h6>
                                <p class="mb-0 small">Gunakan nomor ini sebagai Nomor Tujuan agar transaksi langsung berstatus <strong>Sukses</strong>.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded border-warning" style="background: var(--warning-bg);">
                                <h6 class="text-warning fw-bold">087800001231</h6>
                                <p class="mb-0 small">Gunakan nomor ini agar transaksi <strong>Pending</strong> selamanya.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded border-danger" style="background: var(--danger-bg);">
                                <h6 class="text-danger fw-bold">087800001232</h6>
                                <p class="mb-0 small">Gunakan nomor ini agar transaksi berstatus <strong>Gagal</strong> dan direfund.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded border-primary" style="background: #eff6ff;">
                                <h6 class="text-primary fw-bold">087800001233</h6>
                                <p class="mb-0 small">Transaksi Sukses, namun SN (Serial Number) kosong.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
