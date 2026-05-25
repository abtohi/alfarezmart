<?php
$host = '153.92.15.83';
$dbname = 'u573283697_alfarezmart';
$user = 'u573283697_alfarez';
$pass = 'ba5rRwhkKmM&b';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$prompt = "Kamu adalah AI asisten untuk AlfarezMart (Toko Retail/Grosir).\nTugasmu: Ekstrak data dari gambar invoice/faktur supplier menjadi array JSON valid sesuai schema.\n\nINSTRUKSI WAJIB:\n1. OUTPUT HARUS JSON VALID! Tidak boleh ada teks Markdown (seperti ```json) sebelum atau sesudah array JSON.\n2. JANGAN tambahkan penjelasan apapun, HANYA KELUARKAN ARRAY JSON.\n3. Ekstrak 15-20 item terpenting/terjelas saja jika item terlalu banyak untuk mencegah error.\n4. Nilai uang/harga hanya angka (tanpa titik, koma, atau Rp).\n\nEKSTRAKSI & IDENTIFIKASI CERDAS:\n1. Nama Barang: Ambil apa adanya dari invoice. Jika disingkat, tulis apa adanya di `name`.\n2. Kode Supplier: Jika ada kode barang di invoice (misal \"[CMY-125]\"), masukkan ke `supplier_product_code`.\n3. Quantity (Qty): Angka jumlah barang yang dibeli.\n4. Satuan (Unit): \n   - WAJIB deteksi: PCS, KARTON (KRT, CTN), RENCENG (RCG, RTG), PACK (PCK), BOX, SLOP.\n   - Ekstrak ke `unit` apa adanya sesuai yang tertera atau terdeteksi.\n5. Harga Beli Total & Satuan: \n   - `total_price`: Harga total untuk baris barang tersebut (sebelum diskon).\n   - `unit_price`: Harga per satuan kemasan (total_price dibagi qty). WAJIB ADA.\n6. Diskon: Nominal potongan harga/diskon baris (angka).\n7. Pengurangan Berat/Gramasi: Sertakan berat pada `name` jika ada.\n\nFORMAT JSON OUTPUT YANG WAJIB:\n[\n  {\n    \"supplier_product_code\": \"KODE123\",\n    \"name\": \"NAMA BARANG LENGKAP\",\n    \"qty\": 10,\n    \"unit\": \"Karton\",\n    \"unit_price\": 60000,\n    \"total_price\": 600000,\n    \"discount\": 0\n  }\n]";
$stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'ai_invoice_prompt'");
$stmt->execute([$prompt]);
echo "Prompt updated successfully.";
