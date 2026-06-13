# PROMPT TEMPLATE — AlfarezMart

> Gunakan template ini untuk berkomunikasi dengan AI Agent guna menghemat penggunaan token.

---

## 1. Klasifikasi Tugas
- **Minor (Cukup baca: `ai-instructions.md`)**: Perubahan 1-2 file, bug fix kecil, tweak UI, tambah field kecil.
- **Mayor (Wajib baca: `ai-instructions.md` + `BLUEPRINT.md`)**: Modul baru, perubahan database, refactor besar, update SW/PWA.
*(Catatan: CHANGE_LOG.md dan CURRENT_STATE.md sudah dihapus untuk menghemat token).*

---

## 2. Template Prompt

```markdown
Klasifikasi: [Minor / Mayor]
Tujuan: [Penjelasan singkat apa yang ingin dicapai]

## Task
- [ ] Task 1
- [ ] Task 2

## Konteks Tambahan
- Modul terkait: [Nama modul]
- File terlibat: [Daftar file jika diketahui]
- Referensi pattern: [Nama file pembanding jika ada]
```

---

## 3. Contoh Penggunaan

### Contoh Tugas Minor
```markdown
Klasifikasi: Minor
Tujuan: Menampilkan profit hari ini di dashboard di bawah omzet

## Task
- [ ] Tampilkan "Profit: Rp X.XXX" dengan warna hijau di bawah "Omzet Hari Ini" pada dashboard.
- [ ] Hanya tampilkan jika user bukan role staff.

## Konteks Tambahan
- File terlibat: `app/controllers/DashboardController.php`, `app/views/dashboard/index.php`
```

### Contoh Tugas Mayor
```markdown
Klasifikasi: Mayor
Tujuan: Membuat modul Catatan Keuangan Harian baru

## Task
- [ ] Buat model, controller, dan view untuk rute `/finance`.
- [ ] Hubungkan form input transaksi dengan endpoint API baru.
- [ ] Integrasikan status ringkasan keuangan di dashboard utama.

## Konteks Tambahan
- Modul terkait: Finance
- Referensi pattern: Modul Debts (`app/views/debts/index.php`)
```
