# AI Agent Instructions — AlfarezMart

> **Baca file ini sebelum mengerjakan SETIAP task apapun di project ini.**
> File ini adalah aturan utama yang wajib diikuti oleh AI Agent.

---

## Context Files

Gunakan file berikut sebagai referensi tambahan sesuai skala task:

| File | Tujuan |
|------|--------|
| `docs/AI/BLUEPRINT.md` | Gambaran besar: arsitektur, modul, UI pattern, AJAX pattern, struktur database |
| `docs/AI/CURRENT_STATE.md` | Kondisi terakhir: pekerjaan terakhir, known issues, pending tasks, keputusan teknis |
| `docs/AI/CHANGE_LOG.md` | Histori perubahan lengkap; baca hanya jika perlu konteks histori spesifik |

**Kapan membaca file mana:**

- **Task kecil / minor** (fix bug, tweak UI, perubahan 1–2 file):
  - Wajib: `ai-instructions.md` + `CURRENT_STATE.md` + file/module terkait
  - Opsional: `BLUEPRINT.md` jika perlu memahami konteks modul

- **Task besar / mayor** (modul baru, refactor lintas modul, perubahan arsitektur, fitur kompleks):
  - Wajib: `ai-instructions.md` + `BLUEPRINT.md` + `CURRENT_STATE.md` + `CHANGE_LOG.md`
  - Tambahan: file modul yang paling mirip sebagai pattern referensi

> **Jangan membaca seluruh `CHANGE_LOG.md`** kecuali diminta untuk memahami histori spesifik.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP Native (tanpa framework) |
| Pattern | MVC + OOP |
| Frontend | Bootstrap 5.3 + Vanilla JS |
| AJAX | Fetch API / `api()` helper di `utils.js` |
| Icon | Bootstrap Icons (`bi bi-*`) utama, Font Awesome di area existing |
| Font | Google Fonts — Inter (300/400/500/600/700/800) |
| PWA | Service Worker (`sw.js`) + Web App Manifest (`manifest.json`) |
| Database | MySQL via PDO (XAMPP/MySQL) |
| Server | XAMPP (Apache + PHP) |

---

## Architecture Rules

- Setiap modul wajib mengikuti **MVC + OOP**.
- **Model** — query database, data access, data logic. Wajib extend base `Model`. Taruh di `app/models/`.
- **Controller** — penghubung Model dan View, mengatur request/response, validasi flow. Taruh di `app/controllers/`.
- **View** — hanya UI/tampilan. Taruh di `app/views/{module}/`.
- **ApiController** — semua JSON API endpoint terpusat di `app/controllers/ApiController.php`.
- Jangan letakkan query database atau business logic di View.
- Jangan mencampur query, business logic, dan UI dalam satu file.
- Ikuti struktur folder, helper, component, function, class, naming convention, dan pattern existing.
- Jangan membuat pendekatan baru jika pattern existing sudah tersedia.
- Jika ada konflik antara solusi baru dan standar existing, prioritaskan standar existing.
- Route baru didaftarkan di `app/config/Routes.php`.
- Konfigurasi sensitif dari `.env` (jangan hardcode).
- Konstanta app di `app/config/App.php`.
- Timezone: `Asia/Jakarta` (GMT+7).

---

## UI/UX Rules — Mobile-First

### Prinsip Dasar
- **Mobile-first**: semua desain dimulai dari tampilan mobile (lebar ≤ 480px), lalu scale up.
- UI harus **modern, elegan, dark theme, konsisten, dan user friendly** layaknya aplikasi Android native.
- Tidak ada elemen yang keluar dari design system existing.

### Design System (wajib ikuti)

**Tema:** Dark modern dengan aksen merah (#e63946).

| Token | Nilai |
|-------|-------|
| `--primary` | `#e63946` (merah utama) |
| `--bg-primary` | `#0f0f1a` (latar paling gelap) |
| `--bg-secondary` | `#1a1a2e` (latar header/modal) |
| `--bg-card` | `#16213e` (latar card) |
| `--success` | `#2ec4b6` (teal) |
| `--warning` | `#ffb703` (kuning) |
| `--info` | `#4cc9f0` (biru muda) |
| `--font-family` | `'Inter', -apple-system, sans-serif` |
| `--header-height` | `56px` |
| `--bottom-nav-height` | `64px` |

Semua token lengkap di `public/css/variables.css`.

### Layout Aplikasi
```
[Status Bar Overlay]
[App Header — fixed, 56px, hide-on-scroll]
[Main Content — app-content, padding top/bottom untuk header+bottom nav]
[Bottom Navigation Bar — fixed, 64px, 5 item]
[Toast Container — fixed, z-index 400]
```

### Komponen UI Wajib
- **Toast** → `showToast(message, type, duration, onClick)` dari `utils.js`
- **Modal/Dialog** → gunakan Bootstrap Modal dengan class existing, atau modal custom yang sudah ada di `components.js`
- **Alert/Konfirmasi** → gunakan `showCustomAlert()` atau modal konfirmasi existing — **jangan gunakan** `alert()`, `confirm()`, `prompt()` browser default
- **Loading** → gunakan `elegant-loader` (3 dots) atau spinner yang sudah ada
- **Empty state** → class `empty-state` dengan icon + teks
- **Card** → class `app-card` dengan `--bg-card` background
- **Badge/Status** → gunakan class badge existing (`badge-success`, `badge-warning`, dll)
- **Form** → ikuti style form existing (dark input, border `--border-color`)
- **Tabel** → gunakan class existing untuk tabel interaktif

### Animasi & Transisi
- Transisi standar: `var(--transition-base)` = `250ms ease`
- Transisi lambat: `var(--transition-slow)` = `400ms cubic-bezier(0.4, 0, 0.2, 1)`
- App loader: elegant-loader (3 animated dots) + fade out 0.5s
- Header: hide/show on scroll (translateY -100% / 0)
- Toast: slide in from bottom, fade out
- Modal: Bootstrap default transition (sudah disesuaikan dark theme)
- Card hover: `--bg-card-hover` background

### Icon
- Utama: **Bootstrap Icons** (`bi bi-*`)
- Boleh tambahan Font Awesome (`fas fa-*`) hanya di area yang sudah ada FA

---

## PWA Rules

- AlfarezMart adalah **PWA (Progressive Web App)** yang dioptimalkan untuk Android.
- **Service Worker** di `sw.js` (root) — cache strategy:
  - API (`/api/*`): **Network First**, fallback cache
  - Static assets (CSS, JS, images, fonts): **Cache First**, fallback network
  - HTML pages: **Network First**, fallback cache, fallback index
- **Cache version** di `sw.js`: perbarui `CACHE_NAME` jika ada perubahan static asset besar.
- **Manifest** di `manifest.json` (root): `display: standalone`, orientasi portrait, lang id.
- Service Worker didaftarkan di layout `app/views/layouts/app.php`.
- `localStorage` key `alfarezmart_logged_in` digunakan untuk auto-login hint.
- **Jangan ganggu** mekanisme install prompt (`beforeinstallprompt`) di `app.js`.
- Setiap perubahan asset static yang signifikan: update versi `?v=X.X` di `app/views/layouts/app.php`.
- Setiap static asset baru yang penting: pertimbangkan tambahkan ke `STATIC_ASSETS` di `sw.js`.

---

## JavaScript & AJAX Rules

- Semua AJAX menggunakan fungsi `api()` dari `public/js/utils.js`.
- Format response AJAX wajib konsisten: `{ success: bool, data: any, message: string }`.
- Jangan buat `alert()` / `confirm()` browser default — gunakan `showToast()` atau modal konfirmasi.
- Jangan buat global variable tidak perlu.
- Jangan duplicate event listener atau memory leak.
- Gunakan `debounce()` dari `utils.js` untuk input search.
- Jangan ganggu perubahan JS yang merusak modul lain.
- File JS utama:

| File | Fungsi |
|------|--------|
| `public/js/utils.js` | `formatRupiah`, `showToast`, `debounce`, `api()`, `calcMargin` |
| `public/js/app.js` | PWA install prompt, global search, header scroll |
| `public/js/components.js` | Komponen UI reusable (modal, card, dll) |
| `public/js/barcode.js` | Scanner barcode (ZXing-JS + html5-qrcode) |
| `public/js/printer.js` | Thermal printer / ESC-POS via Web Serial |
| `public/js/packaging-prices.js` | Logika harga packaging produk |
| `public/js/qty-pricing.js` | Logika tier pricing berbasis kuantitas |

---

## Security Rules

- Semua query database wajib **prepared statement / parameterized query** (via PDO).
- Validasi dan sanitasi **semua input** dari form, AJAX, URL parameter.
- **Escape output** ke View dengan `htmlspecialchars()`.
- Lindungi dari: SQL Injection, XSS, CSRF, broken access control, path traversal, file upload abuse.
- **Jangan hardcode** credentials/token/key — gunakan `.env`.
- CSRF: gunakan `CsrfHelper::tokenField()` di setiap form POST. Validasi di Controller.
- File upload: validasi extension, MIME type, ukuran, nama, dan lokasi simpan.
- Role/permission: validasi akses di backend (Controller), bukan hanya frontend.
- Jangan tambah dependency/library eksternal yang tidak jelas keamanannya.

---

## Anti-Regression Rules

- Jangan merusak fitur, flow, dan functionality existing.
- Jangan ubah behavior existing kecuali diminta.
- Sebelum ubah file, pahami dependency dan impact-nya.
- Gunakan perubahan **seminimal mungkin** untuk menyelesaikan task.
- Jika fix bug, pastikan fix tidak menimbulkan bug baru di modul lain.
- Jangan hapus, rename, atau pindah file/function/class tanpa memastikan semua referensinya aman.
- Jangan ubah format AJAX response existing.
- Jangan ubah nama/struktur route existing tanpa instruksi eksplisit.

---

## Execution Rules

- Jangan keluar dari scope task.
- Jangan refactor besar-besaran jika tidak diminta.
- Jangan ubah struktur besar project tanpa instruksi eksplisit.
- Jangan hapus fitur existing tanpa instruksi eksplisit.
- Jangan buat file test/debug/backup/temporary permanen.
- Jangan buat **dependency baru** kecuali diperlukan — jelaskan alasannya.
- Jangan ubah konfigurasi utama, environment, route, permission, atau schema DB tanpa kebutuhan jelas.
- Jangan buat dummy data permanen.
- Jangan tulis kode redundant, duplicate, atau sulit dimaintain.
- Komentar hanya jika membantu memahami logic penting.

---

## Workflow Rules

### Sebelum Implementasi
1. Klasifikasikan task: **minor** atau **mayor** (lihat bagian Context Files).
2. Baca file context yang sesuai skala task.
3. Pahami struktur file dan pattern existing yang berkaitan.
4. Identifikasi Model, View, Controller, JS, AJAX, asset, config, helper relevan.
5. Tentukan impact perubahan sebelum coding.

### Saat Implementasi
- Kerjakan task secara berurutan.
- Ikuti MVC + OOP.
- Ikuti UI template existing (mobile-first, dark theme, aksen merah).
- Terapkan security.
- Hindari regression.
- Jangan keluar scope.

### Setelah Implementasi
- Update `docs/AI/CHANGE_LOG.md`.
- Update `docs/AI/CURRENT_STATE.md` jika ada progress, kendala, keputusan teknis, atau next step.
- Update dokumentasi relevan jika ada.
- Cek `.gitignore`.
- Cleanup file temporary hanya jika aman.

---

## Validation Rules

Setelah implementasi:
- Cek ulang semua file yang diubah.
- Cek syntax error, runtime error, logic error, broken flow.
- Jika ada error/warning, perbaiki sebelum laporan akhir.
- Pastikan fitur baru berjalan sesuai task.
- Pastikan fitur existing terkait tidak regression.
- Jika ada AJAX: pastikan request/response benar dan error handling konsisten.
- Jika ada form: pastikan validasi frontend dan backend berjalan.
- Jika ada JS baru: pastikan tidak ada console error penting.
- Jika ada perubahan PWA/SW: pastikan cache version diperbarui jika diperlukan.

---

## Database Rules

- Jangan ubah struktur database tanpa instruksi eksplisit.
- Jika perubahan DB diperlukan, jelaskan alasan dan impact-nya.
- Query baru tempatkan di Model (`app/models/`).
- Query harus aman, efisien, prepared statement via PDO.
- Jangan query langsung di Controller atau View.
- Jangan hapus data production tanpa instruksi eksplisit.
- Jika ada perubahan schema: sertakan dokumentasi dan migration SQL di `storage/migrations/` (jika ada).
- Database utama: MySQL via XAMPP. Konfigurasi di `app/config/Database.php` dan `.env`.

---

## Documentation & Changelog Rules

Setelah task selesai:
- Update `docs/AI/CHANGE_LOG.md` sesuai perubahan yang dilakukan.
- Update `docs/AI/CURRENT_STATE.md` jika status project berubah.
- Dokumentasikan secara singkat, jelas, dan sesuai fakta.
- Jangan tulis perubahan yang tidak benar-benar dilakukan.
- Jika ada perubahan behavior, flow, endpoint, konfigurasi, atau cara penggunaan fitur — tulis di dokumentasi.

---

## File Deletion Warning

- Jangan hapus file hanya karena namanya `test`, `debug`, `backup`, `temp`, `old`, `copy`, `cleanup`.
- Sebelum hapus, pastikan file tidak digunakan oleh route, controller, model, view, config, AJAX endpoint, JS, CSS, atau deployment.
- Jika ragu, **jangan hapus** — laporkan sebagai `perlu review manual`.
- Jangan hapus file production, asset utama, konfigurasi, atau file core tanpa instruksi eksplisit.

---

## Gitignore & Cleanup Rules

- Cek dan update `.gitignore` jika diperlukan.
- Tambahkan file/folder sensitif, credential, cache, temporary, log, generated files, env file, atau file lokal yang tidak boleh masuk repository.
- Hapus file temporary hanya jika benar-benar aman.

---

## Output Format

Akhiri setiap task dengan ringkasan singkat:

```
## Ringkasan Task

**Klasifikasi:** [Minor / Mayor]

1. ✅ Task yang diselesaikan
2. 📁 File yang diubah/dibuat: [daftar file]
3. ✔️ Validasi yang dilakukan
4. 📝 Update CHANGE_LOG.md: [ya/tidak + keterangan singkat]
5. 📝 Update CURRENT_STATE.md: [ya/tidak + keterangan singkat]
6. 🧹 Cleanup & .gitignore: [jika ada]
7. ⚠️ File perlu review manual: [jika ada]
8. 🚨 Catatan penting / risiko: [jika ada]
```
