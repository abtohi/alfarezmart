# Panduan Pengembangan & Anti-Regresi AlfarezMart (ALFAREZMART_DEV_RULES.md)

Dokumen ini adalah **satu-satunya sumber kebenaran (single source of truth)** untuk arsitektur, aturan performa, kebijakan offline-first & sinkronisasi, serta sistem invoice scanner AI pada sistem AlfarezMart.

---

## 1. Arsitektur Inti & Prinsip Utama

### A. Offline-First & Kecepatan Respons (0–50 ms)
1. **In-Memory & IndexedDB Priority**:
   - Selalu periksa data lokal (`window._posProductsCatalog` atau `OfflineDB`) sebelum melakukan panggilan jaringan.
   - Papan kasir POS dan pencarian barang harus selalu responsif dalam 0–2 ms bahkan saat jaringan terputus atau sinyal lemah.
2. **Stale-While-Revalidate Caching**:
   - Pada halaman analytics/riwayat (`/sales`, `/purchases`, `/debts`), data tersimpan di `localStorage` / `sessionStorage` **langsung dirender seketika (0 ms)**.
   - Panggilan server berjalan di latar belakang secara halus tanpa mengosongkan antarmuka pengguna.
   - Jika jaringan mati atau timeout, data cache lokal tetap tampil utuh dengan indikator status ramah.

---

## 2. Kebijakan Anti-Hilang Data & Sinkronisasi (`public/js/app.js` & `db.js`)

> [!IMPORTANT]
> **ATURAN MUTLAK SYNC ENGINE**:
> 1. **DILARANG MENGHAPUS DATA SECARA DIAM-DIAM**: Jika server mengembalikan status error (4xx atau 5xx) atau jaringan putus, payload perubahan **TIDAK BOLEH** langsung dibuang dari antrian tanpa dicadangkan.
> 2. **Backup Fail-Safe**: Data yang gagal sinkron disimpan ke `sync_failed_backup` di `localStorage` dan dimunculkan sebagai banner merah persisten agar user mengetahui status datanya.
> 3. **Penyegaran CSRF Token**: Sebelum memproses antrian mutasi offline, sistem harus menyegarkan CSRF token dari server.

---

## 3. Aturan Kasir POS & Mode Transaksi

1. **Presisi Multi-Level Kemasan**:
   - Kemasan Level 1 (Pcs), Level 2 (Renceng/Pack), Level 3 (Karton), Level 4 (Sak).
   - Jika kemasan Level > 1 dipilih kasir, unit price kemasan tersebut **harus 100% dipatuhi** tanpa di-chunking ke satuan base level 1.
2. **Edit Transaksi ke Kasir POS**:
   - Seluruh status transaksi asli (Mode Ecer/Grosir/Mix, Override Mode per item, Level Kemasan, Barang Custom, dan Harga Custom) harus dimuat 100% presisi sama persis sebelum dibayar.
   - Transisi dari halaman detail ke POS menggunakan payload instan `localStorage` untuk kecepatan 0 milidetik.

---

## 4. Sistem AI Invoice Scanner & Supplier Skills

### A. Alur Pemrosesan Berjenjang (Hierarchical Pipeline)
```text
Invoice Image/PDF
      │
      ▼
[1] OCR & Text Extraction (Vision Model / Local Parser)
      │
      ▼
[2] Supplier Detection (Header / Phone / NPWP / Keyword)
      │
      ▼
[3] Supplier-Specific Skill Parsing (CV Indoberas, Indomarco, dll.)
      │
      ▼
[4] Deterministic Product Matching (Barcode > Code > Exact Label > Alias Map)
      │
      ▼
[5] Fallback AI Resolver (Hanya jika confidence < 85%)
```

### B. Anti-Regresi Invoice Scanner
- **Deterministic First**: Gunakan regex dan pemetaan alias lokal terlebih dahulu sebelum memanggil AI API.
- **Model Agnostik**: Gunakan fallback model berjenjang (misal Gemini Flash → OpenRouter / Router Fallback) dengan timeout terukur (maks. 30–60 detik).
- **Format Output JSON Baku**:
```json
{
  "supplier_name": "CV Indoberas",
  "invoice_number": "INV-2026-001",
  "invoice_date": "2026-08-16",
  "total_amount": 1500000,
  "items": [
    {
      "raw_name": "BERAS PREMIUM 5KG",
      "quantity": 10,
      "unit": "Sak",
      "unit_price": 75000,
      "total_price": 750000
    }
  ]
}
```

---

## 5. Milestone & Jejak Perbaikan (Changelog)

| Versi / Tanggal | Area | Ringkasan Perbaikan |
|---|---|---|
| **v15.97** (16 Agu 2026) | Sync & Sales | Memperbaiki bug kritis penghapusan data saat sync gagal, menambahkan banner merah fail-safe, auto-load fallback cache riwayat penjualan, dan presisi 100% load edit transaksi ke POS. |
| **v15.96** (16 Agu 2026) | POS & Multi-Tier | Memperbaiki harga kemasan level 2/3/4 di POS tanpa chunking, deklarasi `fixAndSyncProducts` di `<head>`, modul cetak struk riwayat penjualan. |
| **v15.95** (16 Agu 2026) | Invoice Scanner | Modularisasi skill CV Indoberas & guidelines registry anti-regresi invoice scanner. |
