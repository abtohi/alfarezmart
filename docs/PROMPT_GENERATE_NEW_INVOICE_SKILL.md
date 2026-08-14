# 📋 Template Prompt Tambah Skill Supplier Baru (AlfarezMart)

Dokumen ini berisi **Template Prompt Siap Pakai** yang bisa Anda copy-paste langsung ke chat AI kapan saja Anda mengupload contoh invoice dari supplier baru.

---

## 🚀 Template Prompt Utama: "Generate Skill Invoice Supplier Baru"

> **Cara Penggunaan:**
> 1. Upload 1 atau beberapa foto faktur/invoice dari supplier baru tersebut.
> 2. Copy teks prompt di bawah ini.
> 3. Isi bagian di dalam tanda kurung siku `[...]` sesuai data supplier jika Anda tahu (atau biarkan AI mendeteksi otomatis dari gambar).
> 4. Kirim ke AI.

```markdown
Tolong pelajari gambar contoh faktur supplier yang saya upload ini, dan buatkan file skill invoice baru untuk supplier ini sesuai dengan panduan di docs/AI_INVOICE_SKILL_GUIDELINES.md.

Informasi Supplier (jika ada):
- Nama Supplier: [Contoh: PT Indomarco Adi Prima / PT Unilever Indonesia / dll]
- Brand Produk Utama: [Contoh: Indofood, Indomie / Unilever, Lifebuoy / Mayora / dll]
- Key Skill: [Contoh: indomarco / unilever / mayora / dll (huruf kecil tanpa spasi)]

Tolong lakukan hal-hal berikut:
1. Analisis tata letak tabel invoice (posisi kolom Quantity, Kode Barang, Nama Barang, Harga Satuan, Diskon/Promo, dan Total Jumlah akhir).
2. Buat file skill baru di `app/services/invoice/skills/[NamaSupplier]InvoiceSkill.php` yang mengimplementasikan `InvoiceSkillInterface`.
   - Pastikan menyertakan daftar singkatan umum (ABBREVIATIONS) dan daftar merk (KNOWN_BRANDS) supplier ini.
   - Pastikan parser cerdas mengekstrak merk, varian, berat/volume, dan jenis kemasan (sachet, karton, botol, dll).
   - Pastikan logika penentuan level kemasan (Price Distance & Unit matching) presisi.
3. Daftarkan skill baru tersebut di `app/services/invoice/skills/SkillManager.php`.
4. Tambahkan kata kunci pengenal (kata kunci header, nama PT, atau nomor rekening) di `app/services/invoice/SupplierDetector.php` agar faktur ini otomatis terdeteksi.
5. Uji sintaks PHP dan pastikan bebas error.
6. Commit dan push perubahannya ke GitHub repository.
```

---

## 🔧 Template Prompt Variasi 2: "Koreksi / Penyesuaian Skill yang Sudah Ada"

Gunakan template ini jika ada supplier yang sudah memiliki skill (misal MDR), namun ada format nota baru atau ada produk yang belum terbaca dengan sempurna.

```markdown
Tolong evaluasi hasil scan invoice untuk supplier [Nama Supplier, misal: MDR / Wings Group] berdasarkan gambar yang saya upload ini.

Catatan Masalah:
- Total item di nota ada: [Contoh: 12 item]
- Masalah yang terjadi: [Contoh: Baris ke-8 dan ke-9 tidak terbaca / varian rasa tertentu salah terpetakan / kolom diskon belum memotong harga]

Tolong:
1. Analisis letak perbedaan atau baris yang terlewat pada gambar ini.
2. Perbarui file skill di `app/services/invoice/skills/[NamaSupplier]InvoiceSkill.php` atau `ProductMatcher.php`.
3. Pastikan 100% item pada nota terbaca lengkap dan terpetakan dengan tepat ke database.
4. Uji sintaks, lalu commit dan push ke GitHub repository.
```

---

## 📦 Template Prompt Variasi 3: "Generate Multi-Supplier Sekaligus"

Gunakan template ini jika Anda mengupload beberapa foto nota dari berbagai supplier berbeda sekaligus (misal 3 supplier berbeda).

```markdown
Saya telah mengupload beberapa invoice dari supplier yang berbeda-beda.
Tolong pelajari masing-masing invoice tersebut sesuai panduan di `docs/AI_INVOICE_SKILL_GUIDELINES.md`:

1. Identifikasi tiap supplier dari header / footer / nomor rekeningnya.
2. Buatkan file skill terpisah untuk masing-masing supplier di `app/services/invoice/skills/`.
3. Daftarkan seluruh skill di `SkillManager.php` dan perbarui `SupplierDetector.php`.
4. Pastikan semua skill kompatibel dengan model AI vision gratisan (Gemma 4 / Nemotron) dan mendukung sistem auto-learning.
5. Jalankan pengecekan sintaks PHP, lalu commit dan push hasilnya.
```

---

### 💡 Tips Agar Hasil Scan Maksimal:
- **Foto Tegak & Jelas**: Pastikan tabel faktur dari header kolom sampai garis `End Of Document` atau footer terlihat utuh.
- **Sistem Pembelajaran Otomatis**: Setiap kali Anda menyimpan pembelian di aplikasi, sistem akan otomatis mempelajari alias nota produk tersebut sehingga scan berikutnya semakin instan dan akurat.
