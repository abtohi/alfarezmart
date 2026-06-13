# AI Agent Instructions — AlfarezMart

> **Wajib dibaca sebelum memulai setiap tugas.**

---

## 1. Tech Stack & Architecture
- **Tech Stack**: PHP Native (MVC + OOP), MySQL (PDO), Bootstrap 5.3 + Vanilla JS, Service Worker (`sw.js`) + Manifest.
- **MVC Division**:
  - **Model** (`app/models/`): Query DB & business logic.
  - **Controller** (`app/controllers/`): Flow control & validation. All JSON APIs in `ApiController.php`.
  - **View** (`app/views/`): UI only. No direct DB query / logic.
- **Routing**: Registered in `app/config/Routes.php`.
- **Config**: `.env` (sensitive) & `app/config/App.php` (constants). Timezone: `Asia/Jakarta`.

---

## 2. UI/UX (Mobile-First)
- **Mobile-first**: Optimized for screens ≤ 480px. Dark theme with red accent (`#e63946`).
- **Design Tokens** (complete list in `public/css/variables.css`):
  - `--primary`: `#e63946` | `--bg-primary`: `#0f0f1a` | `--bg-secondary`: `#1a1a2e` | `--bg-card`: `#16213e`
  - `--success`: `#2ec4b6` | `--warning`: `#ffb703` | `--info`: `#4cc9f0`
- **Required Components**:
  - Toast → `showToast(msg, type)` from `utils.js`.
  - Modal → Bootstrap modal class or existing custom components.
  - Alert/Confirm → Use modal / `showCustomAlert()` (never use native browser `alert()`, `confirm()`, or `prompt()`).
  - Loading → `elegant-loader` (3 dots).

---

## 3. PWA, AJAX, & Offline Strategy
- **PWA Service Worker** (`sw.js`):
  - API & HTML: Network First (timeout fallback to cache 800ms for HTML).
  - Assets: Cache First. Update `CACHE_NAME` on significant asset changes.
- **Offline Mode**:
  - Sync data to IndexedDB (`public/js/offline-db.js`).
  - When offline (`!navigator.onLine`), features fetching/mutating data must fallback to `OfflineDB` (IndexedDB).
  - Update `ApiController::syncAllData` if database schema/fields change.
- **AJAX**: Use `api(url, method, data)` helper in `public/js/utils.js`. Standard response: `{ success: bool, data: any, message: string }`.

---

## 4. Security & Coding Standards
- **DB Security**: Prepared statements via PDO. No direct SQL queries in Controller/View.
- **Sanitization**: Escape HTML in views with `htmlspecialchars()`.
- **JS Injection**: When injecting PHP string to JS variable, use `json_encode()` (not `htmlspecialchars`) to prevent HTML entity rendering (`&amp;`).
- **CSRF**: Use `CsrfHelper::tokenField()` in POST forms, validate in Controller.
- **Anti-Regression**:
  - Make minimal, targeted changes.
  - Never rename/delete files/methods without checking all references.
  - Do not modify existing API response structures.

---

## 5. Execution & Cleanup Rules
- **No bloat**: Do not add unnecessary external dependencies.
- **File Cleanup**: After completing any task, scan and delete unused temporary files:
  - Pattern: `test_*`, `debug_*`, `check_*`, `*_backup.*`, `*_old.*`, `*.diff`, `*.patch`.
  - Scratch: Clear temporary scripts from `scratch/`.
  - Verify before deletion that files are not referenced anywhere.

---

## 6. Output Summary Format
End every task with this summary:
```markdown
## Ringkasan Task
**Klasifikasi:** [Minor / Mayor]
1. ✅ Task yang diselesaikan: ...
2. 📁 File yang diubah/dibuat: ...
3. ✔️ Validasi yang dilakukan: ...
4. 🧹 Pembersihan File Sampah: [daftar file yang dihapus, atau 'tidak ada']
5. ⚠️ File perlu review manual: ...
6. 🚨 Catatan penting / risiko: ...
```
