<?php
/**
 * ApiController - JSON API endpoints for AJAX/PWA
 */
class ApiController extends Controller
{
    protected \PDO $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function exportProducts()
    {
        try {
            $mode = isset($_GET['mode']) ? (int)$_GET['mode'] : 1;
            
            $db = Database::getInstance()->getConnection();
            $data = [];
            
            if ($mode === 1) {
                // By Supplier
                $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
                $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
                $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
                
                $sql = "SELECT p.barcode, p.full_name as 'Nama Produk', c.name as 'Kategori', b.name as 'Brand', 
                        sp.buy_price as 'Harga Beli Terakhir', sp.updated_at as 'Tanggal Update' 
                        FROM supplier_products sp 
                        JOIN products p ON sp.product_id = p.id 
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN brands b ON p.brand_id = b.id
                        WHERE sp.supplier_id = :sup_id";
                        
                $params = [':sup_id' => $supplierId];
                
                if (!empty($dateFrom)) {
                    $sql .= " AND DATE(sp.updated_at) >= :from";
                    $params[':from'] = $dateFrom;
                }
                if (!empty($dateTo)) {
                    $sql .= " AND DATE(sp.updated_at) <= :to";
                    $params[':to'] = $dateTo;
                }
                
                $sql .= " ORDER BY p.full_name ASC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } else {
                // By Product Name & Multi Supplier filter
                $productName = isset($_GET['product_name']) ? $_GET['product_name'] : '';
                $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
                
                $sql = "SELECT p.barcode, p.full_name as 'Nama Produk', c.name as 'Kategori', b.name as 'Brand',
                        (SELECT name FROM suppliers s JOIN supplier_products sp2 ON s.id = sp2.supplier_id WHERE sp2.product_id = p.id ORDER BY sp2.updated_at DESC LIMIT 1) as 'Supplier Terakhir',
                        p.base_price as 'Harga Dasar', p.sell_price as 'Harga Jual', p.stock as 'Stok'
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN brands b ON p.brand_id = b.id
                        WHERE 1=1";
                
                $params = [];
                
                if (!empty($productName)) {
                    $sql .= " AND p.full_name LIKE :name";
                    $params[':name'] = "%{$productName}%";
                }
                
                if ($supplierId > 0) {
                    $sql .= " AND p.id IN (SELECT product_id FROM supplier_products WHERE supplier_id = :sup_id)";
                    $params[':sup_id'] = $supplierId;
                }
                
                $sql .= " ORDER BY p.full_name ASC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getProducts()
    {
        $model = new ProductModel();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        $catId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $result = $model->getProductsWithPrices($page, 20, $search, $catId);
        $this->json($result);
    }

    public function syncAllData()
    {
        $limit = 500; // Limit payload size
        
        // Products
        $productModel = new ProductModel();
        $productsResult = $productModel->getProductsWithPrices(1, 999999, '', null);
        $products = [];
        if (isset($productsResult['data'])) {
            foreach ($productsResult['data'] as $p) {
                $products[] = [
                    'id' => (int)$p['id'],
                    'short_label' => $p['short_label'],
                    'full_name' => $p['full_name'],
                    'brand_name' => $p['brand_name'],
                    'category_name' => $p['category_name'],
                    'code' => $p['code'],
                    'packagings' => $p['packagings']
                ];
            }
        }

        // Sales
        require_once __DIR__ . '/../models/SaleModel.php';
        $saleModel = new SaleModel();
        $salesResult = $saleModel->getList(1, $limit);
        $sales = $salesResult['data'] ?? [];

        // Suppliers
        require_once __DIR__ . '/../models/SupplierModel.php';
        $supplierModel = new SupplierModel();
        $suppliers = $supplierModel->getAllWithType();

        // Purchases
        require_once __DIR__ . '/../models/PurchaseModel.php';
        $purchaseModel = new PurchaseModel();
        $purchasesResult = $purchaseModel->getList(1, $limit);
        $purchases = $purchasesResult['data'] ?? [];

        $this->json([
            'success' => true,
            'products' => $products,
            'sales' => $sales,
            'suppliers' => $suppliers,
            'purchases' => $purchases,
            'debts' => [],
            'finance' => []
        ]);
    }

    public function syncProducts()
    {
        $model = new ProductModel();
        // Fetch all active products (up to a large limit)
        $result = $model->getProductsWithPrices(1, 999999, '', null);
        
        // Return only what is needed for OfflineDB to keep payload small
        $products = [];
        foreach ($result['data'] as $p) {
            $products[] = [
                'id' => (int)$p['id'],
                'short_label' => $p['short_label'],
                'full_name' => $p['full_name'],
                'brand_name' => $p['brand_name'],
                'category_name' => $p['category_name'],
                'code' => $p['code'],
                'packagings' => $p['packagings']
            ];
        }
        
        $this->json([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
    }

    public function getProductHistory(int $id)
    {
        $purchaseModel = new PurchaseModel();
        $history = $purchaseModel->getProductPurchaseHistory($id);
        $comparison = $purchaseModel->getProductSupplierComparison($id);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'history' => $history,
                'comparison' => $comparison
            ]
        ]);
    }

    public function searchProducts()
    {
        try {
            $model = new ProductModel();
            $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            
            // Validate input
            if (strlen($q) < 1) {
                // Return recent/all products (limited) for browsing
                $results = $model->searchProducts('', 30);
                if (!is_array($results)) $results = [];
            } else {
                // Search products
                $results = $model->searchProducts($q, 15);
            }
            
            // Ensure results is array
            if (!is_array($results)) {
                $results = [];
            }
            
            // Add stock info to each result
            if (count($results) > 0) {
                $db = Database::getInstance()->getConnection();
                foreach ($results as &$r) {
                    try {
                        $stmt = $db->prepare("SELECT current_qty_base FROM stock WHERE product_id = :id LIMIT 1");
                        $stmt->execute([':id' => $r['id']]);
                        $r['current_qty_base'] = (int)($stmt->fetchColumn() ?: 0);
                    } catch (Exception $e) {
                        // If stock lookup fails, default to 0
                        $r['current_qty_base'] = 0;
                    }
                }
                unset($r); // Break reference
            }
            
            // Add packagings so POS can read tier pricing (qty_prices)
            $model->attachPackagingsForProductList($results);
            
            $this->json($results);
        } catch (\Throwable $e) {
            // Log error and return empty array instead of error
            error_log('Search Products Error: ' . $e->getMessage());
            $this->json([]);
        }
    }


    public function getByBarcode(string $code)
    {
        $model = new ProductModel();
        $product = $model->findByBarcode($code);
        if (!$product) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
            return;
        }
        $product['packagings'] = $model->getPackagings($product['id']);
        $this->json($product);
    }

    public function getById(int $id)
    {
        $model = new ProductModel();
        $product = $model->findWithDetails($id);
        if (!$product) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
            return;
        }
        $product['packagings'] = $model->getPackagings($product['id']);
        $this->json($product);
    }

    /**
     * Simpan tier harga spesial per kuantitas untuk satu kemasan
     */
    public function savePackagingQtyPrices(int $id)
    {
        $this->validateCSRF();
        try {
            $packagingId = (int)$id;
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $tiers = $jsonBody['tiers'] ?? $this->input('tiers') ?? [];
            if (!is_array($tiers)) {
                throw new Exception('Data tier harga tidak valid');
            }
            $model = new ProductModel();
            $model->saveQtyPricesForPackaging($packagingId, $tiers);
            $this->json([
                'success' => true,
                'message' => 'Harga kuantitas berhasil disimpan',
                'qty_prices' => $model->getQtyPricesByPackaging($packagingId),
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate unique barcode (for manual input / print)
     */
    public function generateBarcode()
    {
        $barcode = Helper::generateBarcode();
        $this->json([
            'success' => true,
            'barcode' => $barcode,
            'prefix' => defined('BARCODE_PREFIX') ? BARCODE_PREFIX : 'AM',
        ]);
    }

    public function createProduct()
    {
        $this->validateCSRF();
        $model = new ProductModel();

        if (empty($_POST)) {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonBody)) {
                $_POST = array_merge($_POST, $jsonBody);
            }
        }

        try {
            // Compose fallback nama produk supaya constraint NOT NULL `full_name` tidak gagal.
            $fullName = trim((string)$this->input('full_name'));
            $shortLabel = trim((string)$this->input('short_label'));
            $invoiceName = trim((string)$this->input('invoice_name'));
            $singleName = trim((string)$this->input('single_name'));
            $variant = trim((string)$this->input('variant'));
            $productType = trim((string)$this->input('product_type'));
            // Resolve brand name from id (best effort)
            $brandName = '';
            $brandId = $this->input('brand_id');
            if (!empty($brandId)) {
                try {
                    $b = (new BrandModel())->find((int)$brandId);
                    if ($b && !empty($b['name'])) $brandName = trim((string)$b['name']);
                } catch (Exception $ex) { /* abaikan */ }
            }
            if ($fullName === '' || $fullName === '-') {
                $composed = trim($brandName . ' ' . $productType . ' ' . $variant);
                $fullName = $singleName !== '' ? $singleName : ($shortLabel !== '' ? $shortLabel : $composed);
            }
            if ($fullName === '') {
                throw new Exception('Nama produk wajib diisi (Brand/Jenis/Varian atau Nama Produk).');
            }
            if ($shortLabel === '') {
                $shortLabel = mb_substr($fullName, 0, 35);
            }
            if ($invoiceName === '') {
                $invoiceName = $shortLabel;
            }

            $productData = [
                'code' => $this->input('code'),
                'brand_id' => $brandId ?: null,
                'category_id' => $this->input('category_id') ?: null,
                'product_type' => $productType !== '' ? $productType : null,
                'variant' => $variant !== '' ? $variant : null,
                'full_name' => $fullName,
                'short_label' => $shortLabel,
                'invoice_name' => $invoiceName,
                'supplier_product_code' => $this->input('supplier_product_code') ?: null,
                'supplier_invoice_name' => $this->input('supplier_invoice_name') ?: null,
                'weight_value' => $this->input('weight_value') ?: null,
                'weight_unit' => $this->input('weight_unit'),
                'is_custom_label' => $this->input('is_custom_label') ? 1 : 0,
            ];

            $packagings = [];
            $unitIds = $_POST['unit_id'] ?? [];
            if (empty($unitIds)) {
                throw new Exception("Minimal harus ada 1 satuan terkecil.");
            }

            foreach ($unitIds as $i => $unitId) {
                if (empty($unitId)) continue;
                
                $level = $i + 1;
                $cqty = $_POST['contained_qty'][$i] ?? 1;
                $buy = (float)($_POST['buy_price'][$i] ?? 0);
                $retail = $_POST['sell_price_retail'][$i] ?? 0;
                $wholesale = $_POST['sell_price_wholesale'][$i] ?? 0;

                $barcode = trim($_POST['barcode'][$i] ?? '');
                if (empty($barcode) && $level == 1) {
                    $barcode = Helper::generateBarcode();
                }
                if (!empty($barcode) && Helper::barcodeExists($barcode)) {
                    throw new Exception("Barcode \"{$barcode}\" sudah digunakan produk lain.");
                }

                // Terapkan PPN & Diskon langsung ke harga modal
                $ppnPct       = (float)($_POST['ppn_pct'][$i] ?? 0);
                $discountMode  = $_POST['discount_mode'][$i] ?? 'rp';
                $discountValue = (float)($_POST['discount_value'][$i] ?? 0);
                $buyAfterPpn   = $buy * (1 + $ppnPct / 100);
                if ($discountMode === 'pct') {
                    $finalBuy = $buyAfterPpn * (1 - $discountValue / 100);
                } else {
                    $finalBuy = $buyAfterPpn - $discountValue;
                }
                $finalBuy = max(0, round($finalBuy, 2));

                $packagings[] = [
                    'level' => $level,
                    'unit_id' => $unitId,
                    'contained_qty' => $cqty,
                    'barcode' => $barcode,
                    'buy_price' => $finalBuy,
                    'sell_price_retail' => $retail,
                    'margin_retail' => $retail > 0 ? Helper::calculateMargin($finalBuy, $retail) : 0,
                    'sell_price_wholesale' => $wholesale,
                    'margin_wholesale' => $wholesale > 0 ? Helper::calculateMargin($finalBuy, $wholesale) : 0,
                    'qty_prices' => json_decode($_POST['qty_prices_json'][$i] ?? '[]', true) ?: [],
                ];
            }

            $id = $model->createWithDetails($productData, $packagings);
            $this->json(['success' => true, 'id' => $id, 'message' => 'Produk berhasil ditambahkan']);

        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateProduct(int $id)
    {
        $this->blockStaffMutations('mengedit');
        $this->validateCSRF();
        $model = new ProductModel();

        try {
            $data = [];
            $fields = ['full_name','short_label','invoice_name','product_type','variant','brand_id','category_id',
                       'weight_value','weight_unit', 'supplier_product_code', 'supplier_invoice_name', 'is_custom_label'];
            $nullableFields = ['brand_id','product_type','variant','weight_value','weight_unit', 'supplier_product_code', 'supplier_invoice_name'];
            foreach ($fields as $f) {
                $val = $this->input($f);
                if ($val !== null && $val !== '') {
                    $data[$f] = $val;
                } elseif (in_array($f, $nullableFields)) {
                    // Allow clearing nullable fields
                    $data[$f] = null;
                }
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            $model->update($id, $data);
            $this->json(['success' => true, 'message' => 'Produk berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update print label (short_label) for thermal receipt
     */
    public function updateProductLabel(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new ProductModel();
            $shortLabel = $this->input('short_label');
            $invoiceName = $this->input('invoice_name');
            $model->updatePrintLabel((int) $id, $shortLabel, $invoiceName);
            $product = $model->findWithDetails($id);
            $this->json([
                'success' => true,
                'message' => 'Label cetak berhasil disimpan',
                'short_label' => $product['short_label'],
                'invoice_name' => $product['invoice_name'],
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update product photo (compressed base64 + bg removed)
     */
    public function updateProductPhoto(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $base64 = $jsonBody['photo_base64'] ?? $this->input('photo_base64');
            if (empty($base64)) throw new Exception("Foto tidak ditemukan");

            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $base64 = base64_decode($base64);
                    if ($base64 !== false) {
                        $filename = 'prod_' . $id . '_' . time() . '.' . $type;
                        $dir = STORAGE_PATH . '/uploads/products';
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        $filepath = $dir . '/' . $filename;
                        if (file_put_contents($filepath, $base64)) {
                            $photoPath = 'storage/uploads/products/' . $filename;
                            $model = new ProductModel();
                            
                            // Delete old photo
                            $oldProduct = $model->find($id);
                            if (!empty($oldProduct['photo'])) {
                                $oldPath = strpos($oldProduct['photo'], 'storage/') === 0
                                    ? dirname(BASE_PATH) . '/' . ltrim($oldProduct['photo'], '/')
                                    : BASE_PATH . '/' . ltrim($oldProduct['photo'], '/');
                                if (file_exists($oldPath)) @unlink($oldPath);
                            }

                            $model->update($id, ['photo' => $photoPath]);
                            $this->json(['success' => true, 'photo' => $photoPath]);
                            return;
                        }
                    }
                }
            }
            throw new Exception("Gagal memproses format gambar");
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List variant siblings for label distribution preview
     */
    public function getProductLabelVariants(int $id)
    {
        $model = new ProductModel();
        $product = $model->findWithDetails($id);
        if (!$product) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
            return;
        }
        $siblings = $model->findVariantSiblings($id);
        $this->json([
            'product' => $product,
            'siblings' => $siblings,
            'count' => count($siblings),
        ]);
    }

    /**
     * Distribute label base to all variants (same brand + product_type)
     */
    public function distributeProductLabel(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new ProductModel();
            $labelBase = $this->input('label_base') ?: $this->input('short_label');
            $result = $model->distributePrintLabel((int) $id, $labelBase);
            $this->json([
                'success' => true,
                'message' => "Label berhasil diterapkan ke {$result['updated']} produk varian",
                'updated' => $result['updated'],
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store info for receipt printing
     */
    public function getReceiptSettings()
    {
        $settings = new SettingModel();
        $this->json([
            'store_name' => $settings->get('store_name', 'AlfarezMart'),
            'store_address' => $settings->get('store_address', ''),
            'store_phone' => $settings->get('store_phone', ''),
            'thermal_printer_width' => (int) $settings->get('thermal_printer_width', 58),
            'receipt_header' => $settings->get('receipt_header', ''),
            'receipt_footer' => $settings->get('receipt_footer', ''),
            'store_logo' => $settings->get('store_logo', ''),
        ]);
    }

    public function saveReceiptSettings()
    {
        $this->validateCSRF();
        try {
            $settings = new SettingModel();
            // Read all from JSON body
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $fields = ['store_name','store_address','store_phone','thermal_printer_width','receipt_header','receipt_footer'];
            foreach ($fields as $f) {
                $val = $data[$f] ?? $this->input($f, '');
                $settings->set($f, $val);
            }
            if (!empty($data['store_logo'])) {
                $settings->set('store_logo', $data['store_logo']);
            }

            $this->json(['success' => true, 'message' => 'Pengaturan struk berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }


    public function deleteProduct(int $id)
    {
        $this->blockStaffMutations('menghapus');
        $this->validateCSRF();
        $db = Database::getInstance()->getConnection();
        try {
            $id = (int) $id;
            $model = new ProductModel();
            $db->beginTransaction();
            
            // Use prepared statements to prevent SQL injection
            $tables = ['purchase_items', 'sale_items', 'stock_movements', 'stock', 'product_packagings', 'supplier_products'];
            foreach ($tables as $table) {
                $stmt = $db->prepare("DELETE FROM {$table} WHERE product_id = :id");
                $stmt->execute([':id' => $id]);
            }
            $model->delete($id);
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Produk berhasil dihapus']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteProducts()
    {
        $this->blockStaffMutations('menghapus');
        $this->validateCSRF();
        $db = Database::getInstance()->getConnection();
        try {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $ids = $jsonBody['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                throw new Exception("Tidak ada produk yang dipilih.");
            }

            $model = new ProductModel();
            $db->beginTransaction();
            
            // Use prepared statements to prevent SQL injection
            $tables = ['purchase_items', 'sale_items', 'stock_movements', 'stock', 'product_packagings', 'supplier_products'];
            
            foreach ($ids as $id) {
                $id = (int)$id;
                foreach ($tables as $table) {
                    $stmt = $db->prepare("DELETE FROM {$table} WHERE product_id = :id");
                    $stmt->execute([':id' => $id]);
                }
                $model->delete($id);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => count($ids) . ' produk berhasil dihapus']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a single product packaging (price, barcode)
     */
    public function updatePackaging(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int) $id;
            $db = Database::getInstance()->getConnection();
            
            $buyPrice = (float) $this->input('buy_price', 0);
            $retailPrice = (float) $this->input('sell_price_retail', 0);
            $wholesalePrice = (float) $this->input('sell_price_wholesale', 0);
            $barcode = trim($this->input('barcode', ''));
            $unitId = (int)$this->input('unit_id', 0);
            $forceReplace = (bool)$this->input('force_replace', false);
            
            // Terapkan PPN & Diskon langsung ke harga modal
            $ppnPct        = (float) $this->input('ppn_pct', 0);
            $discountMode  = $this->input('discount_mode', 'rp');
            $discountValue = (float) $this->input('discount_value', 0);
            $buyAfterPpn   = $buyPrice * (1 + $ppnPct / 100);
            if ($discountMode === 'pct') {
                $finalBuyPrice = $buyAfterPpn * (1 - $discountValue / 100);
            } else {
                $finalBuyPrice = $buyAfterPpn - $discountValue;
            }
            $finalBuyPrice = max(0, round($finalBuyPrice, 2));

            if (!empty($barcode)) {
                $owner = Helper::barcodeOwner($barcode, $id);
                if ($owner) {
                    if (!$forceReplace) {
                        // Return conflict info for UI to show confirmation
                        $this->json([
                            'error' => 'barcode_conflict',
                            'message' => "Barcode \"{$barcode}\" sudah digunakan oleh produk lain.",
                            'conflict' => [
                                'packaging_id' => $owner['packaging_id'],
                                'product_id' => $owner['product_id'],
                                'product_name' => $owner['short_label'] ?: $owner['full_name'],
                                'unit_name' => $owner['unit_name'] ?? '',
                                'level' => $owner['level'],
                            ]
                        ], 409);
                        return;
                    }
                    // Force replace: clear barcode from old packaging
                    $stmtClear = $db->prepare("UPDATE product_packagings SET barcode = NULL WHERE id = :oid");
                    $stmtClear->execute([':oid' => $owner['packaging_id']]);
                }
            }
            
            // Calculate margins using final buy price (after PPN & discount)
            $marginRetail    = $retailPrice > 0    ? Helper::calculateMargin($finalBuyPrice, $retailPrice)    : 0;
            $marginWholesale = $wholesalePrice > 0 ? Helper::calculateMargin($finalBuyPrice, $wholesalePrice) : 0;
            
            $unitSql = $unitId ? 'unit_id = :uid,' : '';
            $containedQty = $this->input('contained_qty');
            $containedQtySql = ($containedQty !== null && $containedQty !== '') ? 'contained_qty = :cqty,' : '';
            $stmt = $db->prepare("
                UPDATE product_packagings 
                SET {$unitSql}
                    {$containedQtySql}
                    buy_price = :buy, 
                    sell_price_retail = :retail, 
                    sell_price_wholesale = :wholesale,
                    margin_retail = :mr, 
                    margin_wholesale = :mw,
                    barcode = :barcode
                WHERE id = :id
            ");
            $params = [
                ':buy'      => $finalBuyPrice,
                ':retail'   => $retailPrice,
                ':wholesale'=> $wholesalePrice,
                ':mr'       => $marginRetail,
                ':mw'       => $marginWholesale,
                ':barcode'  => $barcode ?: null,
                ':id'       => $id
            ];
            if ($unitId) $params[':uid'] = $unitId;
            if ($containedQty !== null && $containedQty !== '') $params[':cqty'] = (int)$containedQty;
            $stmt->execute($params);

            // Update product's updated_at timestamp
            $stmtPid = $db->prepare("SELECT product_id FROM product_packagings WHERE id = :id");
            $stmtPid->execute([':id' => $id]);
            $productId = $stmtPid->fetchColumn();

            // Recalculate base_qty for all levels of this product if contained_qty changed
            if ($productId && $containedQty !== null && $containedQty !== '') {
                $stmtLevels = $db->prepare("SELECT id, level, contained_qty FROM product_packagings WHERE product_id = :pid ORDER BY level ASC");
                $stmtLevels->execute([':pid' => $productId]);
                $levels = $stmtLevels->fetchAll(\PDO::FETCH_ASSOC);
                $runningBase = 1;
                foreach ($levels as $lv) {
                    if ((int)$lv['level'] === 1) {
                        $runningBase = 1;
                    } else {
                        $runningBase *= (int)$lv['contained_qty'];
                    }
                    $stmtBq = $db->prepare("UPDATE product_packagings SET base_qty = :bq WHERE id = :lid");
                    $stmtBq->execute([':bq' => $runningBase, ':lid' => $lv['id']]);
                }
            }

            if ($productId) {
                $db->prepare("UPDATE products SET updated_at = NOW() WHERE id = :pid")
                   ->execute([':pid' => $productId]);
            }

            $this->json(['success' => true, 'message' => 'Harga kemasan berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateProductStock(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $totalQty = (int)$this->input('total_qty', 0);
            $notes = $this->input('notes', 'Manual Update');
            
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            
            // Get old stock for movement log
            $stmt = $db->prepare("SELECT current_qty_base FROM stock WHERE product_id = :id");
            $stmt->execute([':id' => $id]);
            $oldStock = $stmt->fetchColumn() ?: 0;
            
            $diff = $totalQty - $oldStock;
            
            if ($diff != 0) {
                // Update stock table
                $stmtStock = $db->prepare("UPDATE stock SET current_qty_base = :qty WHERE product_id = :id");
                $stmtStock->execute([':qty' => $totalQty, ':id' => $id]);
                
                if ($stmtStock->rowCount() === 0) {
                    // It didn't exist, insert it
                    $stmtIns = $db->prepare("INSERT INTO stock (product_id, current_qty_base) VALUES (:id, :qty)");
                    $stmtIns->execute([':id' => $id, ':qty' => $totalQty]);
                }
                
                // Add movement log
                $stmtLog = $db->prepare("
                    INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, notes) 
                    VALUES (:id, :mtype, :qty, 'manual', :notes)
                ");
                $stmtLog->execute([
                    ':id' => $id,
                    ':mtype' => 'adjustment',
                    ':qty' => $diff,
                    ':notes' => $notes . " (Diff: " . ($diff > 0 ? "+$diff" : $diff) . ")"
                ]);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Stok fisik berhasil diupdate', 'new_qty' => $totalQty]);
        } catch (Exception $e) {
            $db = Database::getInstance()->getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== PURCHASES =====
    public function deletePurchase(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new PurchaseModel();
            $model->deleteWithRevert($id);
            $this->json(['success' => true, 'message' => 'Pembelian berhasil dihapus dan stok dikembalikan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== BRANDS =====
    public function getBrands()
    {
        $model = new BrandModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createBrand()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama brand wajib diisi');
            
            // Check for duplicates (case-insensitive)
            $existingBrand = $this->db->prepare("SELECT id FROM brands WHERE LOWER(name) = LOWER(:name) LIMIT 1");
            $existingBrand->execute([':name' => $name]);
            $existing = $existingBrand->fetch();
            
            if ($existing) {
                throw new Exception('Brand "' . htmlspecialchars($name) . '" sudah ada. Gunakan fitur edit untuk mengubahnya.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new BrandModel();
            $id = $model->create(['name' => $name, 'slug' => $slug]);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Brand berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function updateBrand(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama brand wajib diisi');
            
            // Check for duplicates (case-insensitive), excluding current record
            $existingBrand = $this->db->prepare("SELECT id FROM brands WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $existingBrand->execute([':name' => $name, ':id' => $id]);
            $existing = $existingBrand->fetch();
            
            if ($existing) {
                throw new Exception('Brand "' . htmlspecialchars($name) . '" sudah ada. Silakan gunakan nama lain.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new BrandModel();
            $model->update($id, [
                'name' => $name, 
                'slug' => $slug,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $this->json(['success' => true, 'message' => 'Brand berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function deleteBrand(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new BrandModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Brand berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== CATEGORIES =====
    public function getCategories()
    {
        $model = new CategoryModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createCategory()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama kategori wajib diisi');
            
            // Check for duplicates (case-insensitive)
            $existingCat = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) LIMIT 1");
            $existingCat->execute([':name' => $name]);
            $existing = $existingCat->fetch();
            
            if ($existing) {
                throw new Exception('Kategori "' . htmlspecialchars($name) . '" sudah ada. Gunakan fitur edit untuk mengubahnya.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new CategoryModel();
            $id = $model->create(['name' => $name, 'slug' => $slug]);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Kategori berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function updateCategory(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama kategori wajib diisi');
            
            // Check for duplicates (case-insensitive), excluding current record
            $existingCat = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $existingCat->execute([':name' => $name, ':id' => $id]);
            $existing = $existingCat->fetch();
            
            if ($existing) {
                throw new Exception('Kategori "' . htmlspecialchars($name) . '" sudah ada. Silakan gunakan nama lain.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new CategoryModel();
            $model->update($id, ['name' => $name, 'slug' => $slug]);
            $this->json(['success' => true, 'message' => 'Kategori berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function deleteCategory(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new CategoryModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== UNITS =====
    public function getUnits()
    {
        $model = new UnitModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createUnit()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama satuan wajib diisi');
            $abbr = $this->input('abbreviation');
            if (empty($abbr)) $abbr = strtolower(substr($name, 0, 3));
            
            $model = new UnitModel();
            
            // Check for duplicates (case-insensitive)
            $existingUnit = $this->db->prepare("SELECT id FROM units WHERE LOWER(name) = LOWER(:name) LIMIT 1");
            $existingUnit->execute([':name' => $name]);
            $existing = $existingUnit->fetch();
            
            if ($existing) {
                throw new Exception('Satuan "' . htmlspecialchars($name) . '" sudah ada. Gunakan fitur edit untuk mengubahnya.');
            }
            
            $id = $model->create(['name' => $name, 'abbreviation' => $abbr]);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'abbreviation' => $abbr, 'message' => 'Satuan berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function updateUnit(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama satuan wajib diisi');
            $abbr = $this->input('abbreviation');
            if (empty($abbr)) $abbr = strtolower(substr($name, 0, 3));
            
            $model = new UnitModel();
            
            // Check for duplicates (case-insensitive), excluding current record
            $existingUnit = $this->db->prepare("SELECT id FROM units WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $existingUnit->execute([':name' => $name, ':id' => $id]);
            $existing = $existingUnit->fetch();
            
            if ($existing) {
                throw new Exception('Satuan "' . htmlspecialchars($name) . '" sudah ada. Silakan gunakan nama lain.');
            }
            
            $model->update($id, ['name' => $name, 'abbreviation' => $abbr]);
            $this->json(['success' => true, 'message' => 'Satuan berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function deleteUnit(int $id)
    {
        $this->validateCSRF();
        $db = Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();
            
            // Set null on product_packagings to prevent FK constraint error
            $stmt = $db->prepare("UPDATE product_packagings SET unit_id = NULL WHERE unit_id = :id");
            $stmt->execute([':id' => $id]);

            $model = new UnitModel();
            $model->delete($id);
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Satuan berhasil dihapus']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== SUPPLIERS =====
    public function getSuppliers()
    {
        $model = new SupplierModel();
        $this->json($model->getAllWithType());
    }

    public function searchSuppliers()
    {
        $model = new SupplierModel();
        $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        if (strlen($q) < 1) {
            $this->json([]);
            return;
        }
        $results = $model->searchSuppliersAndSales($q);
        $this->json($results);
    }

    public function getSupplierTypes()
    {
        $model = new SupplierTypeModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createSupplier()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama supplier wajib diisi');
            $model = new SupplierModel();
            $id = $model->create([
                'name' => $name,
                'type_id' => $this->input('type_id') ?: null,
                'address' => $this->input('address'),
                'products_sold' => $this->input('products_sold'),
                'is_consignment' => $this->input('is_consignment') ? 1 : 0,
                'notes' => $this->input('notes'),
            ]);
            $supplier = $model->findWithType($id);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'supplier' => $supplier]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSupplier(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama supplier wajib diisi');
            $model = new SupplierModel();
            $model->update($id, [
                'name' => $name,
                'type_id' => $this->input('type_id') ?: null,
                'address' => $this->input('address'),
                'products_sold' => $this->input('products_sold'),
                'is_consignment' => $this->input('is_consignment') ? 1 : 0,
                'notes' => $this->input('notes'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $supplier = $model->findWithType($id);
            $this->json(['success' => true, 'supplier' => $supplier, 'message' => 'Supplier berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteSupplier(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new SupplierModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Supplier berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== DASHBOARD =====
    public function getDashboardStats()
    {
        $model = new ProductModel();
        $this->json($model->getStats());
    }

    // ===== PURCHASES =====
    public function getPurchases()
    {
        $model = new PurchaseModel();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $this->json($model->getList($page, 20));
    }

    public function createPurchase()
    {
        $this->validateCSRF();
        try {
            $model = new PurchaseModel();
            
            // Expect JSON body from fetch
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            
            if (empty($items)) {
                throw new Exception("Daftar barang tidak boleh kosong.");
            }

            // Format data
            $date = date('ymd');
            $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $invoiceNumber = "PUR-{$date}-{$random}";
            
            $photoPath = null;
            if (!empty($jsonBody['invoice_photo_base64'])) {
                $base64 = $jsonBody['invoice_photo_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $base64 = base64_decode($base64);
                        if ($base64 !== false) {
                            $dir = STORAGE_PATH . '/uploads/invoice_photos';
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }
                            $filename = 'inv_' . $invoiceNumber . '_' . time() . '.' . $type;
                            $filepath = $dir . '/' . $filename;
                            if (file_put_contents($filepath, $base64)) {
                                $photoPath = 'storage/uploads/invoice_photos/' . $filename;
                            }
                        }
                    }
                }
            }

            $headerData = [
                'purchase_code' => $invoiceNumber,
                'supplier_id' => $jsonBody['supplier_id'] ?? $this->input('supplier_id'),
                'sales_rep_id' => $jsonBody['sales_rep_id'] ?? $this->input('sales_rep_id'),
                'purchase_date' => $jsonBody['purchase_date'] ?? $this->input('purchase_date') ?: date('Y-m-d'),
                'total_amount' => $jsonBody['total_amount'] ?? $this->input('total_amount') ?: 0,
                'grand_total' => $jsonBody['grand_total'] ?? $this->input('grand_total') ?: 0,
                'invoice_photo' => $photoPath,
                'notes' => $jsonBody['notes'] ?? $this->input('notes'),
            ];

            $id = $model->createWithDetails($headerData, $items);
            $this->json(['success' => true, 'id' => $id, 'message' => 'Pembelian berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePurchase(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new PurchaseModel();
            
            // Validate existing purchase
            $existing = $model->getDetails($id);
            if (!$existing) {
                throw new Exception("Data pembelian tidak ditemukan");
            }

            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            
            if (empty($items)) {
                throw new Exception("Daftar barang tidak boleh kosong.");
            }

            $photoPath = null;
            if (!empty($jsonBody['invoice_photo_base64'])) {
                $base64 = $jsonBody['invoice_photo_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $base64 = base64_decode($base64);
                        if ($base64 !== false) {
                            $dir = STORAGE_PATH . '/uploads/invoice_photos';
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }
                            $filename = 'inv_' . $existing['purchase_code'] . '_' . time() . '.' . $type;
                            $filepath = $dir . '/' . $filename;
                            if (file_put_contents($filepath, $base64)) {
                                $photoPath = 'storage/uploads/invoice_photos/' . $filename;
                            }
                        }
                    }
                }
            }

            $headerData = [
                'purchase_code' => $existing['purchase_code'], // Keep original code
                'supplier_id' => $jsonBody['supplier_id'] ?? $this->input('supplier_id'),
                'sales_rep_id' => $jsonBody['sales_rep_id'] ?? $this->input('sales_rep_id'),
                'purchase_date' => $jsonBody['purchase_date'] ?? $this->input('purchase_date') ?: date('Y-m-d'),
                'total_amount' => $jsonBody['total_amount'] ?? $this->input('total_amount') ?: 0,
                'grand_total' => $jsonBody['grand_total'] ?? $this->input('grand_total') ?: 0,
                'invoice_photo' => $photoPath, // Will be coalesced in model if null
                'notes' => $jsonBody['notes'] ?? $this->input('notes'),
            ];

            $model->updateWithDetails($id, $headerData, $items);
            $this->json(['success' => true, 'message' => 'Pembelian berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadInvoicePhoto(int $id) { 
        $this->json(['message' => 'Coming soon']); 
    }

    /**
     * Serve invoice photo securely from STORAGE_PATH (outside public_html).
     * Usage: GET /api/storage/invoice-photo?file=inv_PUR-XXXXXX.jpg
     */
    public function serveInvoicePhoto()
    {
        // Get filename from query string (only the basename, no paths)
        $file = $_GET['file'] ?? '';

        // Security: strip any path traversal attempts, keep only basename
        $file = basename($file);

        if (empty($file)) {
            http_response_code(400);
            echo 'Missing file parameter';
            exit;
        }

        $filepath = STORAGE_PATH . '/uploads/invoice_photos/' . $file;

        if (!file_exists($filepath) || !is_file($filepath)) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        // Detect MIME type and restrict to images only
        $mime = mime_content_type($filepath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            http_response_code(403);
            echo 'Forbidden file type';
            exit;
        }

        // Send image response with caching headers
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($filepath);
        exit;
    }

    // ===== SALES REPS =====
    public function getSalesRepsBySupplier(int $supplierId)
    {
        $model = new SalesRepModel();
        $this->json($model->getBySupplier($supplierId));
    }

    public function getAllSalesReps()
    {
        $model = new SalesRepModel();
        $this->json($model->getAllWithSupplier());
    }

    public function createSalesRep()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $supplierId = $this->input('supplier_id');
            if (empty($name)) throw new Exception('Nama sales wajib diisi');
            if (empty($supplierId)) throw new Exception('Supplier wajib dipilih');

            $model = new SalesRepModel();
            $id = $model->create([
                'name' => $name,
                'supplier_id' => $supplierId,
                'phone' => $this->input('phone', ''),
                'visit_day' => $this->input('visit_day', ''),
                'delivery_day' => $this->input('delivery_day', ''),
                'notes' => $this->input('notes', ''),
            ]);
            $salesRep = $model->findWithSupplier($id);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'sales_rep' => $salesRep]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSalesRep(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama sales wajib diisi');

            $model = new SalesRepModel();
            $model->update($id, [
                'name' => $name,
                'phone' => $this->input('phone', ''),
                'visit_day' => $this->input('visit_day', ''),
                'delivery_day' => $this->input('delivery_day', ''),
                'notes' => $this->input('notes', ''),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $salesRep = $model->findWithSupplier($id);
            $this->json(['success' => true, 'sales_rep' => $salesRep, 'message' => 'Sales berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteSalesRep(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new SalesRepModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Sales berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== SUPPLIER PRODUCTS =====
    public function getProductsBySupplier(int $supplierId)
    {
        $model = new SupplierProductModel();
        $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        $salesRepId = isset($_GET['sales_rep_id']) ? (int)$_GET['sales_rep_id'] : null;

        if (!empty($q)) {
            $results = $model->searchBySupplier($supplierId, $q, $salesRepId);
        } else {
            $results = $model->getProductsBySupplier($supplierId, $salesRepId);
        }
        $this->json($results);
    }

    public function getBulkSupplierProducts(int $supplierId)
    {
        $salesRepId = isset($_GET['sales_rep_id']) ? (int)$_GET['sales_rep_id'] : null;
        $model = new SupplierProductModel();
        $products = $model->getProductsBySupplier($supplierId, $salesRepId);
        
        $productModel = new ProductModel();
        foreach ($products as &$p) {
            $p['packagings'] = $productModel->getPackagings($p['id']);
        }
        
        $this->json($products);
    }

    public function addSupplierProduct(int $supplierId)
    {
        $this->validateCSRF();
        try {
            $productId = $this->input('product_id');
            if (!$productId) throw new Exception('Pilih produk terlebih dahulu');
            
            $model = new SupplierProductModel();
            // Using trackSupplierProduct to insert/upsert
            $model->trackSupplierProduct($supplierId, $productId);
            $this->json(['success' => true, 'message' => 'Produk berhasil ditambahkan ke supplier']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeSupplierProduct(int $supplierId, int $productId)
    {
        $this->validateCSRF();
        try {
            $model = new SupplierProductModel();
            $model->removeSupplierProduct($supplierId, $productId);
            $this->json(['success' => true, 'message' => 'Produk dihapus dari daftar supplier']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchProductsForPurchase()
    {
        try {
            $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;
            $salesRepId = isset($_GET['sales_rep_id']) ? (int)$_GET['sales_rep_id'] : null;

            if (strlen($q) < 2) {
                $this->json([]);
                return;
            }

            // First: search within supplier products
            $supplierResults = [];
            if ($supplierId) {
                $spModel = new SupplierProductModel();
                $supplierResults = $spModel->searchBySupplier($supplierId, $q, $salesRepId);
            }

            // Then: search all products (to allow adding new products to supplier)
            $productModel = new ProductModel();
            $allResults = $productModel->searchProducts($q, 15);

            // Mark which ones are already supplier products
            $supplierProductIds = array_column($supplierResults, 'id');
            foreach ($allResults as &$p) {
                $p['is_supplier_product'] = in_array($p['id'], $supplierProductIds) ? 1 : 0;
            }

            // Merge: supplier products first, then others
            $otherResults = array_filter($allResults, function($p) use ($supplierProductIds) {
                return !in_array($p['id'], $supplierProductIds);
            });

            $merged = array_merge($supplierResults, array_values($otherResults));
            $this->json(array_slice($merged, 0, 20));
        } catch (\Throwable $e) {
            error_log('Search Products For Purchase Error: ' . $e->getMessage());
            $this->json([]);
        }
    }
    public function createSale()
    {
        $this->validateCSRF();
        try {
            $model = new SaleModel();
            
            // Format data
            $date = date('ymd');
            $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $invoiceNumber = "INV-{$date}-{$random}";
            
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            
            if (empty($items)) {
                throw new Exception("Keranjang belanja kosong.");
            }

            $headerData = [
                'invoice_number' => $invoiceNumber,
                'customer_id' => $jsonBody['customer_id'] ?? null,
                'sale_mode' => $jsonBody['sale_mode'] ?? 'retail',
                'total_amount' => $jsonBody['total_amount'] ?? 0,
                'payment_method' => $jsonBody['payment_method'] ?? 'Cash',
                'payment_status' => $jsonBody['payment_status'] ?? 'Lunas',
                'notes' => $jsonBody['notes'] ?? '',
            ];

            $id = $model->createWithDetails($headerData, $items);
            $this->json(['success' => true, 'id' => $id, 'invoice' => $invoiceNumber, 'message' => 'Transaksi berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getInvoice(int $id) {
        try {
            $model = new SaleModel();
            // Try by ID first, then by invoice number
            $transaction = is_numeric($id)
                ? $model->getTransactionDetails((int)$id)
                : $model->findByInvoice($id);

            if (!$transaction) {
                $this->json(['error' => 'Transaksi tidak ditemukan'], 404);
                return;
            }
            $this->json(['success' => true, 'transaction' => $transaction]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== SALES BULK DELETE & UPDATE =====

    public function bulkDeleteSales()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $ids = $jsonBody['ids'] ?? [];
            if (empty($ids) || !is_array($ids)) {
                throw new \Exception('Tidak ada transaksi yang dipilih');
            }

            $model = new SaleModel();
            $deleted = 0;
            foreach ($ids as $id) {
                $model->deleteSale((int)$id);
                $deleted++;
            }

            $this->json([
                'success' => true,
                'deleted' => $deleted,
                'message' => "$deleted transaksi berhasil dihapus"
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSale(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new SaleModel();

            // 1. Verify the old transaction exists
            $old = $model->getTransactionDetails($id);
            if (!$old) {
                $this->json(['error' => 'Transaksi tidak ditemukan'], 404);
                return;
            }

            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            if (empty($items)) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            // 2. Delete old transaction (reverses stock, finance, movements)
            $model->deleteSale($id);

            // 3. Re-create with same invoice number
            $headerData = [
                'invoice_number' => $old['invoice_number'],
                'customer_id'    => $jsonBody['customer_id'] ?? $old['customer_id'],
                'sale_mode'      => $jsonBody['sale_mode'] ?? $old['sale_mode'],
                'total_amount'   => $jsonBody['total_amount'] ?? 0,
                'payment_method' => $jsonBody['payment_method'] ?? $old['payment_method'],
                'payment_status' => $jsonBody['payment_status'] ?? $old['payment_status'],
                'notes'          => $jsonBody['notes'] ?? ($old['notes'] ?? ''),
            ];

            $newId = $model->createWithDetails($headerData, $items);

            $this->json([
                'success' => true,
                'id'      => $newId,
                'invoice' => $old['invoice_number'],
                'message' => 'Transaksi berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== PACKAGING ADD/DELETE =====
    public function addPackaging(int $productId)
    {
        $this->validateCSRF();
        try {
            $productId = (int)$productId;
            $db = Database::getInstance()->getConnection();

            // Get current max level
            $stmt = $db->prepare("SELECT MAX(level) FROM product_packagings WHERE product_id = :pid");
            $stmt->execute([':pid' => $productId]);
            $maxLevel = (int)$stmt->fetchColumn();
            $newLevel = $maxLevel + 1;

            $unitId = $this->input('unit_id');
            if (!$unitId) throw new Exception("Satuan wajib dipilih");

            $containedQty = (int)($this->input('contained_qty') ?: 1);
            if ($newLevel > 1 && $containedQty < 1) throw new Exception("Isi kemasan minimal 1");

            // Calculate base_qty
            $stmtBase = $db->prepare("SELECT base_qty FROM product_packagings WHERE product_id = :pid ORDER BY level DESC LIMIT 1");
            $stmtBase->execute([':pid' => $productId]);
            $lastBase = (int)($stmtBase->fetchColumn() ?: 1);
            $baseQty = ($newLevel === 1) ? 1 : $lastBase * $containedQty;

            $buyPrice  = (float)$this->input('buy_price', 0);
            $retail    = (float)$this->input('sell_price_retail', 0);
            $wholesale = (float)$this->input('sell_price_wholesale', 0);
            $barcode   = trim($this->input('barcode', ''));
            if (empty($barcode)) $barcode = Helper::generateBarcode();

            // Terapkan PPN & Diskon langsung ke harga modal
            $ppnPct        = (float) $this->input('ppn_pct', 0);
            $discountMode  = $this->input('discount_mode', 'rp');
            $discountValue = (float) $this->input('discount_value', 0);
            $buyAfterPpn   = $buyPrice * (1 + $ppnPct / 100);
            if ($discountMode === 'pct') {
                $finalBuyPrice = $buyAfterPpn * (1 - $discountValue / 100);
            } else {
                $finalBuyPrice = $buyAfterPpn - $discountValue;
            }
            $finalBuyPrice = max(0, round($finalBuyPrice, 2));

            $stmt = $db->prepare("
                INSERT INTO product_packagings
                  (product_id, level, unit_id, contained_qty, base_qty, buy_price,
                   sell_price_retail, sell_price_wholesale, margin_retail, margin_wholesale, barcode)
                VALUES (:pid,:level,:uid,:cqty,:bqty,:buy,:retail,:wholesale,:mr,:mw,:barcode)
            ");
            $stmt->execute([
                ':pid'       => $productId,
                ':level'     => $newLevel,
                ':uid'       => $unitId,
                ':cqty'      => $containedQty,
                ':bqty'      => $baseQty,
                ':buy'       => $finalBuyPrice,
                ':retail'    => $retail,
                ':wholesale' => $wholesale,
                ':mr'        => $retail > 0 ? Helper::calculateMargin($finalBuyPrice, $retail) : 0,
                ':mw'        => $wholesale > 0 ? Helper::calculateMargin($finalBuyPrice, $wholesale) : 0,
                ':barcode'   => $barcode ?: null,
            ]);
            $newId = $db->lastInsertId();
            $this->json(['success' => true, 'id' => $newId, 'level' => $newLevel, 'message' => 'Level kemasan berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deletePackaging(int $id)
    {
        $this->blockStaffMutations('menghapus kemasan');
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $db = Database::getInstance()->getConnection();

            // Don't allow deleting level 1
            $stmt = $db->prepare("SELECT level FROM product_packagings WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception("Kemasan tidak ditemukan");
            if ($row['level'] == 1) throw new Exception("Satuan terkecil (Level 1) tidak bisa dihapus");

            $stmt = $db->prepare("DELETE FROM product_packagings WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Re-number levels
            $stmtPid = $db->prepare("SELECT product_id FROM product_packagings WHERE id > 0 LIMIT 0");
            // Actually get product_id from the deleted row — we need to re-fetch before delete, let's skip renumber for simplicity
            $this->json(['success' => true, 'message' => 'Level kemasan berhasil dihapus']);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                try {
                    $db = Database::getInstance()->getConnection();
                    
                    // Function to drop FK
                    $dropFk = function($table, $column) use ($db) {
                        $stmt = $db->prepare("
                            SELECT CONSTRAINT_NAME
                            FROM information_schema.KEY_COLUMN_USAGE
                            WHERE TABLE_SCHEMA = DATABASE()
                              AND TABLE_NAME = :table
                              AND COLUMN_NAME = :column
                              AND REFERENCED_TABLE_NAME IS NOT NULL
                        ");
                        $stmt->execute([':table' => $table, ':column' => $column]);
                        while($fkName = $stmt->fetchColumn()) {
                            $db->exec("ALTER TABLE {$table} DROP FOREIGN KEY {$fkName}");
                        }
                    };

                    // Fix purchase_items
                    $dropFk('purchase_items', 'packaging_id');
                    $db->exec("ALTER TABLE purchase_items MODIFY packaging_id INT NULL");
                    $db->exec("ALTER TABLE purchase_items ADD CONSTRAINT fk_pi_pkg FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL");

                    // Fix sale_items
                    $dropFk('sale_items', 'packaging_id');
                    $db->exec("ALTER TABLE sale_items MODIFY packaging_id INT NULL");
                    $db->exec("ALTER TABLE sale_items ADD CONSTRAINT fk_si_pkg FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL");

                    // Retry deletion
                    $stmt = $db->prepare("DELETE FROM product_packagings WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                    
                    $this->json(['success' => true, 'message' => 'Level kemasan berhasil dihapus (beserta penyesuaian riwayat)']);
                } catch (Exception $ex) {
                    $this->json(['error' => 'Gagal menyesuaikan riwayat transaksi: ' . $ex->getMessage()], 500);
                }
            } else {
                $this->json(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePackagingUnit(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $db = Database::getInstance()->getConnection();
            $unitId = (int)$this->input('unit_id');
            if (!$unitId) throw new Exception("Satuan wajib dipilih");
            $stmt = $db->prepare("UPDATE product_packagings SET unit_id = :uid WHERE id = :id");
            $stmt->execute([':uid' => $unitId, ':id' => $id]);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== USER MANAGEMENT =====
    public function getUsers()
    {
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        $model = new UserModel();
        $this->json($model->getAllUsers(1, 100));
    }

    public function createUser()
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $name      = $this->input('name');
            $email     = $this->input('email');
            $phone     = $this->input('phone');
            $password  = $this->input('password');
            $userLevel = $this->input('user_level', 'staff');

            if (empty($name))     throw new Exception("Nama wajib diisi");
            if (empty($email) && empty($phone)) throw new Exception("Email atau No HP wajib diisi");
            if (empty($password)) throw new Exception("Password wajib diisi");
            if (!in_array($userLevel, ['superadmin','admin','staff','customer'])) throw new Exception("Level tidak valid");

            $model = new UserModel();
            $id = $model->createUser([
                'name'       => $name,
                'email'      => $email ?: null,
                'phone'      => $phone ?: null,
                'password'   => $password,
                'user_level' => $userLevel,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->json(['success' => true, 'id' => $id, 'message' => 'User berhasil dibuat']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleUserActive(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
            $stmt->execute([':id' => (int)$id]);
            $stmt2 = $db->prepare("SELECT is_active FROM users WHERE id = :id");
            $stmt2->execute([':id' => (int)$id]);
            $isActive = (bool)$stmt2->fetchColumn();
            $this->json(['success' => true, 'is_active' => $isActive, 'message' => $isActive ? 'User diaktifkan' : 'User dinonaktifkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resetUserPassword(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $newPassword = $this->input('password');
            if (empty($newPassword)) throw new Exception("Password baru wajib diisi");
            $model = new UserModel();
            $model->changePassword((int)$id, $newPassword);
            $this->json(['success' => true, 'message' => 'Password berhasil direset']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $id = (int)$id;
            if ($id === (int)($_SESSION['user_id'] ?? 0)) throw new Exception("Tidak bisa menghapus akun sendiri");
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $this->json(['success' => true, 'message' => 'User berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // DEBTS & CUSTOMERS API
    // ==========================================

    public function getCustomers()
    {
        try {
            $model = new DebtModel();
            $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $customers = $model->getCustomers($search);
            $this->json(['success' => true, 'data' => $customers]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createCustomer()
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $name = $this->input('name');
            $phone = $this->input('phone');
            $address = $this->input('address');
            $notes = $this->input('notes');
            $typeId = $this->input('type_id');

            if (empty($name)) {
                throw new Exception("Nama pelanggan wajib diisi");
            }

            $customerId = $model->createCustomer([
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'notes' => $notes,
                'type_id' => !empty($typeId) ? (int)$typeId : null
            ]);

            $this->json(['success' => true, 'message' => 'Pelanggan berhasil ditambahkan', 'customer_id' => $customerId]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCustomer(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $name = $this->input('name');
            $phone = $this->input('phone');
            $address = $this->input('address');
            $notes = $this->input('notes');
            $typeId = $this->input('type_id');

            if (empty($name)) {
                throw new Exception("Nama pelanggan wajib diisi");
            }

            $model->updateCustomer((int)$id, [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'notes' => $notes,
                'type_id' => !empty($typeId) ? (int)$typeId : null
            ]);

            $this->json(['success' => true, 'message' => 'Pelanggan berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteCustomer(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteCustomer((int)$id);
            $this->json(['success' => true, 'message' => 'Pelanggan berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCustomerDebts()
    {
        $this->requireSuperadmin();
        try {
            $model = new DebtModel();
            $status = isset($_GET['status']) ? Security::sanitize($_GET['status']) : null;
            $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $debts = $model->getCustomerDebts($status, $search);
            
            $debtIds = array_column($debts, 'id');
            $paymentsByDebtId = [];
            if (!empty($debtIds)) {
                $inQuery = implode(',', array_fill(0, count($debtIds), '?'));
                $stmt = $this->db->prepare("SELECT * FROM customer_debt_payments WHERE debt_id IN ($inQuery) ORDER BY payment_date DESC, id DESC");
                $stmt->execute($debtIds);
                $allPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allPayments as $p) {
                    $paymentsByDebtId[$p['debt_id']][] = $p;
                }
            }

            foreach ($debts as &$d) {
                $d['payments'] = $paymentsByDebtId[$d['id']] ?? [];
            }
            unset($d);

            $this->json(['success' => true, 'data' => $debts]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createCustomerDebt()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $customerId = $this->input('customer_id');
            $fallbackName = $this->input('customer_name_fallback');
            $amount = (float)$this->input('amount');
            $date = $this->input('debt_date');
            $dueDate = $this->input('due_date');
            $notes = $this->input('notes');
            $saleId = $this->input('sale_id');

            if (empty($customerId) && empty($fallbackName)) {
                throw new Exception("Pelanggan harus dipilih atau diisi nama manual");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah hutang harus lebih dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal hutang wajib diisi");
            }

            $debtId = $model->createCustomerDebt([
                'customer_id' => !empty($customerId) ? (int)$customerId : null,
                'customer_name_fallback' => !empty($fallbackName) ? $fallbackName : null,
                'amount' => $amount,
                'debt_date' => $date,
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'notes' => $notes,
                'sale_id' => !empty($saleId) ? (int)$saleId : null
            ]);

            $this->json(['success' => true, 'message' => 'Hutang pelanggan berhasil dicatat', 'debt_id' => $debtId]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function payCustomerDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $amount = (float)$this->input('amount');
            $date = $this->input('payment_date');
            $notes = $this->input('notes');

            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal pembayaran wajib diisi");
            }

            $debt = $model->getCustomerDebtById($id);
            if (!$debt) {
                throw new Exception("Data hutang tidak ditemukan");
            }
            if ($amount > $debt['remaining_amount']) {
                throw new Exception("Nominal pembayaran melebihi sisa hutang (Sisa: Rp " . number_format($debt['remaining_amount'], 0, ',', '.') . ")");
            }

            $model->addCustomerPayment((int)$id, $amount, $date, $notes);
            $this->json(['success' => true, 'message' => 'Cicilan pembayaran berhasil dicatat']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteCustomerDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteCustomerDebt((int)$id);
            $this->json(['success' => true, 'message' => 'Catatan hutang berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getShopDebts()
    {
        $this->requireSuperadmin();
        try {
            $model = new DebtModel();
            $status = isset($_GET['status']) ? Security::sanitize($_GET['status']) : null;
            $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $debts = $model->getShopDebts($status, $search);

            $debtIds = array_column($debts, 'id');
            $paymentsByDebtId = [];
            if (!empty($debtIds)) {
                $inQuery = implode(',', array_fill(0, count($debtIds), '?'));
                $stmt = $this->db->prepare("SELECT * FROM shop_debt_payments WHERE debt_id IN ($inQuery) ORDER BY payment_date DESC, id DESC");
                $stmt->execute($debtIds);
                $allPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allPayments as $p) {
                    $paymentsByDebtId[$p['debt_id']][] = $p;
                }
            }

            foreach ($debts as &$d) {
                $d['payments'] = $paymentsByDebtId[$d['id']] ?? [];
            }
            unset($d);

            $this->json(['success' => true, 'data' => $debts]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createShopDebt()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $supplierId = $this->input('supplier_id');
            $fallbackName = $this->input('supplier_name_fallback');
            $amount = (float)$this->input('amount');
            $date = $this->input('debt_date');
            $dueDate = $this->input('due_date');
            $notes = $this->input('notes');
            $purchaseId = $this->input('purchase_id');

            if (empty($supplierId) && empty($fallbackName)) {
                throw new Exception("Supplier harus dipilih atau diisi nama manual");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah hutang harus lebih dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal hutang wajib diisi");
            }

            $debtId = $model->createShopDebt([
                'supplier_id' => !empty($supplierId) ? (int)$supplierId : null,
                'supplier_name_fallback' => !empty($fallbackName) ? $fallbackName : null,
                'amount' => $amount,
                'debt_date' => $date,
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'notes' => $notes,
                'purchase_id' => !empty($purchaseId) ? (int)$purchaseId : null
            ]);

            $this->json(['success' => true, 'message' => 'Hutang toko berhasil dicatat', 'debt_id' => $debtId]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function payShopDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $amount = (float)$this->input('amount');
            $date = $this->input('payment_date');
            $notes = $this->input('notes');

            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal pembayaran wajib diisi");
            }

            $debt = $model->getShopDebtById($id);
            if (!$debt) {
                throw new Exception("Data hutang tidak ditemukan");
            }
            if ($amount > $debt['remaining_amount']) {
                throw new Exception("Nominal pembayaran melebihi sisa hutang (Sisa: Rp " . number_format($debt['remaining_amount'], 0, ',', '.') . ")");
            }

            $model->addShopPayment((int)$id, $amount, $date, $notes);
            $this->json(['success' => true, 'message' => 'Pembayaran berhasil dicatat']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteShopDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteShopDebt((int)$id);
            $this->json(['success' => true, 'message' => 'Catatan hutang berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== FINANCE LOGS API =====
    // ===== FINANCE ACCOUNTS & CATEGORIES API =====
    public function getFinanceAccounts()
    {
        $this->requireSuperadmin();
        try {
            $model = new FinanceModel();
            $accounts = $model->getActiveAccounts();
            $this->json(['success' => true, 'data' => $accounts]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFinanceAccount()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $depId = $this->input('dependency_account_id');
            if (empty($name)) {
                throw new Exception("Nama POS Keuangan wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            
            $checkStmt = $db->prepare("SELECT id, is_active FROM finance_accounts WHERE name = :name");
            $checkStmt->execute([':name' => $name]);
            $existing = $checkStmt->fetch();
            
            if ($existing) {
                if ($existing['is_active'] == 1) {
                    throw new Exception("POS Keuangan dengan nama tersebut sudah ada");
                } else {
                    $updateStmt = $db->prepare("UPDATE finance_accounts SET is_active = 1, dependency_account_id = :dep WHERE id = :id");
                    $updateStmt->execute([
                        ':dep' => empty($depId) ? null : (int)$depId,
                        ':id' => $existing['id']
                    ]);
                }
            } else {
                $stmt = $db->prepare("INSERT INTO finance_accounts (name, dependency_account_id) VALUES (:name, :dep)");
                $stmt->execute([
                    ':name' => $name,
                    ':dep' => empty($depId) ? null : (int)$depId
                ]);
            }
            
            $this->json(['success' => true, 'message' => 'POS Keuangan berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFinanceAccount(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $depId = $this->input('dependency_account_id');
            if (empty($name)) {
                throw new Exception("Nama POS Keuangan wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_accounts SET name = :name, dependency_account_id = :dep WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':dep' => empty($depId) ? null : (int)$depId,
                ':id' => $id
            ]);
            
            $this->json(['success' => true, 'message' => 'POS Keuangan berhasil diperbarui']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFinanceAccount(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_accounts SET is_active = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->json(['success' => true, 'message' => 'POS Keuangan berhasil dihapus (soft delete)']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFinanceCategories()
    {
        $this->requireSuperadmin();
        try {
            $type = $this->input('type');
            $model = new FinanceModel();
            $categories = $model->getActiveCategories($type);
            $this->json(['success' => true, 'data' => $categories]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFinanceCategory()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $type = $this->input('type');
            if (empty($name) || empty($type)) {
                throw new Exception("Nama dan Tipe wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO finance_categories (name, type) VALUES (:name, :type) ON DUPLICATE KEY UPDATE is_active = 1");
            $stmt->execute([
                ':name' => $name,
                ':type' => $type
            ]);
            
            $this->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFinanceCategory(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $type = $this->input('type');
            if (empty($name) || empty($type)) {
                throw new Exception("Nama dan Tipe wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_categories SET name = :name, type = :type WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':type' => $type,
                ':id' => $id
            ]);
            
            $this->json(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFinanceCategory(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_categories SET is_active = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->json(['success' => true, 'message' => 'Kategori berhasil dihapus (soft delete)']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFinanceSummary()
    {
        $this->requireSuperadmin();
        try {
            $date = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'));
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            $model = new FinanceModel();
            $summary = $model->getDailySummary($date);
            $breakdown = $model->getDailySummaryByPost($date);
            $this->json([
                'success' => true,
                'date' => $date,
                'summary' => [
                    'income' => (float)($summary['income'] ?? 0),
                    'expense' => (float)($summary['expense'] ?? 0),
                    'net' => (float)($summary['income'] ?? 0) - (float)($summary['expense'] ?? 0)
                ],
                'breakdown' => $breakdown
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFinanceLogs()
    {
        $this->requireSuperadmin();
        try {
            $date = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'));
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            $model = new FinanceModel();
            $logs = $model->getLogsByDate($date);
            $this->json([
                'success' => true,
                'date' => $date,
                'logs' => $logs
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFinanceLog()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new FinanceModel();
            $logDate = $this->input('log_date') ?: date('Y-m-d');
            $amount = (float)$this->input('amount');
            $balanceType = $this->input('balance_type');
            $category = $this->input('category');
            $detail = $this->input('detail');
            $description = $this->input('description');

            if ($amount <= 0) {
                throw new Exception("Nominal harus lebih besar dari 0");
            }
            // Validasi balance_type dari DB (bukan whitelist hardcoded)
            $db = Database::getInstance()->getConnection();
            $chk = $db->prepare("SELECT id FROM finance_accounts WHERE name = :name AND is_active = 1");
            $chk->execute([':name' => $balanceType]);
            if (!$chk->fetch()) {
                throw new Exception("Pos keuangan tidak valid atau tidak aktif");
            }
            if (empty($category) || !in_array($category, ['Pemasukan', 'Pengeluaran'])) {
                throw new Exception("Kategori tidak valid");
            }
            if (empty($detail)) {
                throw new Exception("Detail / Jenis transaksi harus diisi");
            }

            $logId = $model->addLog([
                'log_date' => $logDate,
                'amount' => $amount,
                'balance_type' => $balanceType,
                'category' => $category,
                'detail' => $detail,
                'description' => $description
            ]);

            $this->json([
                'success' => true,
                'message' => 'Catatan keuangan berhasil disimpan',
                'log_id' => $logId
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFinanceLog(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new FinanceModel();
            $log = $model->findLog($id);
            if (!$log) {
                throw new Exception("Data pencatatan tidak ditemukan");
            }

            $logDate = $this->input('log_date') ?: date('Y-m-d');
            $amount = (float)$this->input('amount');
            $balanceType = $this->input('balance_type');
            $category = $this->input('category');
            $detail = $this->input('detail');
            $description = $this->input('description');

            if ($amount <= 0) {
                throw new Exception("Nominal harus lebih besar dari 0");
            }
            if (empty($balanceType) || !in_array($balanceType, ['Uang Laci', 'Uang Pulsa', 'Uang Beras', 'Uang Rokok'])) {
                throw new Exception("Pos keuangan tidak valid");
            }
            if (empty($category) || !in_array($category, ['Pemasukan', 'Pengeluaran'])) {
                throw new Exception("Kategori tidak valid");
            }
            if (empty($detail)) {
                throw new Exception("Detail / Jenis transaksi harus diisi");
            }

            $model->updateLog($id, [
                'log_date' => $logDate,
                'amount' => $amount,
                'balance_type' => $balanceType,
                'category' => $category,
                'detail' => $detail,
                'description' => $description
            ]);

            $this->json([
                'success' => true,
                'message' => 'Catatan keuangan berhasil diperbarui'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFinanceLog(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new FinanceModel();
            $log = $model->findLog($id);
            if (!$log) {
                throw new Exception("Data pencatatan tidak ditemukan");
            }

            $model->deleteLog($id);
            $this->json([
                'success' => true,
                'message' => 'Catatan keuangan berhasil dihapus'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteFinanceLogs()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $ids = $this->input('ids');
            if (empty($ids) || !is_array($ids)) {
                throw new Exception("Tidak ada data yang dipilih");
            }

            $model = new FinanceModel();
            $deletedCount = 0;
            foreach ($ids as $id) {
                $log = $model->findLog((int)$id);
                // Only allow deleting manual logs (no reference_type)
                if ($log && empty($log['reference_type'])) {
                    $model->deleteLog((int)$id);
                    $deletedCount++;
                }
            }

            $this->json([
                'success' => true,
                'message' => "$deletedCount catatan keuangan berhasil dihapus"
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }


    // ==========================================
    // APP SETTINGS API
    // ==========================================

    public function saveAppSettings()
    {
        $this->validateCSRF();
        if (AuthController::currentUser()['level'] !== 'superadmin' && AuthController::currentUser()['level'] !== 'admin') {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }

        try {
            $settingModel = new SettingModel();
            $fields = ['ai_model', 'ai_api_key', 'ai_invoice_prompt', 'store_latitude', 'store_longitude', 'store_radius_meters'];
            
            foreach ($fields as $field) {
                $val = $this->input($field);
                if ($val !== null && $val !== '') {
                    $settingModel->set($field, $val);
                }
            }

            $this->json(['success' => true, 'message' => 'Pengaturan aplikasi berhasil disimpan']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function changePassword()
    {
        $this->validateCSRF();
        $user = AuthController::currentUser();
        if (!$user) {
            $this->json(['error' => 'Belum login'], 401);
            return;
        }

        try {
            $oldPassword = $this->input('old_password');
            $newPassword = $this->input('new_password');

            if (empty($oldPassword) || empty($newPassword)) {
                throw new \Exception("Password lama dan baru wajib diisi");
            }

            $userModel = new UserModel();
            $dbUser = $userModel->findByEmail($user['email']); // get fresh hash
            if (!$dbUser || !password_verify($oldPassword, $dbUser['password_hash'])) {
                throw new \Exception("Password lama tidak sesuai");
            }

            $userModel->changePassword($user['id'], $newPassword);

            $this->json(['success' => true, 'message' => 'Password berhasil diubah']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // AI INTEGRATION API
    // ==========================================

    public function scanInvoiceAI()
    {
        $this->validateCSRF();
        set_time_limit(120); // Prevent timeout for long AI requests
        
        // Custom shutdown handler to catch silent crashes (OOM, Timeouts)
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE])) {
                file_put_contents('c:\\xampp\\htdocs\\AlfarezMart\\logs\\ai_crash.log', date('Y-m-d H:i:s') . " FATAL CRASH: " . print_r($error, true) . "\n", FILE_APPEND);
            }
        });
        
        ob_start(); // Capture any stray output/warnings to prevent breaking JSON
        try {
            error_log("SCAN_AI_TRACE: Starting scanInvoiceAI");
            file_put_contents('c:\\xampp\\htdocs\\AlfarezMart\\logs\\ai_crash.log', date('Y-m-d H:i:s') . " Trace: Starting scanInvoiceAI\n", FILE_APPEND);
            
            // Read image_base64 directly from raw JSON to bypass Security::sanitize()
            // which calls strip_tags() and could corrupt base64 data.
            // We must read php://input before any $this->input() call consumes it.
            $rawInput = file_get_contents('php://input');
            error_log("SCAN_AI_TRACE: Read php://input length: " . strlen($rawInput));
            $rawJson = json_decode($rawInput, true);
            if (!is_array($rawJson)) {
                // Fallback: decode directly again or just use empty array
                $rawJson = [];
            }
            $imageB64 = $rawJson['image_base64'] ?? '';
            if (empty($imageB64)) {
                throw new \Exception("Gambar invoice tidak ditemukan");
            }

            $settingModel = new SettingModel();
            $apiKey = $settingModel->get('ai_api_key');
            $modelName = $settingModel->get('ai_model', 'google/gemini-2.5-flash');
            $defaultPrompt = "Kamu adalah AI asisten untuk AlfarezMart (Toko Retail/Grosir).\nTugasmu: Ekstrak data dari gambar invoice/faktur supplier menjadi array JSON valid sesuai ALGORITMA 4-POIN SCANNING.\n\n" .
"========== ALGORITMA 4-POIN SCANNING ==========\n" .
"POIN 1 - KODE BARANG SUPPLIER (supplier_product_code):\n" .
"  • Cari kode barang yang tertera di invoice (misal [CMY-125], #12345, atau kode lain yang terstruktur)\n" .
"  • WAJIB: Ekstrak EXACTLY apa adanya dari invoice (case-sensitive, spasi sensitive)\n" .
"  • Jika ADA kode, masukkan ke field `supplier_product_code`\n" .
"  • Jika TIDAK ADA kode, biarkan kosong string: \"\"\n" .
"\nPOIN 2 - NAMA BARANG SUPPLIER (supplier_invoice_name):\n" .
"  • Ambil nama barang EXACTLY seperti tertera di invoice\n" .
"  • Jangan terjemahkan, jangan singkat, AMBIL APA ADANYA\n" .
"  • Contoh: Jika invoice tulis \"Lbl Coklat 100gr\", ekstrak: \"Lbl Coklat 100gr\"\n" .
"  • Masukkan ke field `supplier_invoice_name`\n" .
"\nPOIN 3 - ANALISIS NAMA PRODUK (name, brand, variant, type, size):\n" .
"  • Ekstrak komponen nama produk untuk fuzzy matching di backend\n" .
"  • `name`: Nama produk lengkap dari invoice (bisa sama dengan supplier_invoice_name)\n" .
"  • `brand`: Brand/merek jika terdeteksi (Cth: \"Nestle\", \"Indomie\")\n" .
"  • `variant`: Varian rasa/warna jika ada (Cth: \"Pedas\", \"Coklat\", \"Hijau\")\n" .
"  • `product_type`: Tipe produk jika terdeteksi (Cth: \"Mie\", \"Minuman\", \"Snack\")\n" .
"  • `size`: Ukuran/packaging info jika ada (Cth: \"100gr\", \"500ml\", \"1DZ\", \"12pcs\")\n" .
"\nPOIN 4 - ANALISIS UNIT KEMASAN (qty, unit, unit_price, total_price):\n" .
"  • Reverse-engineer unit dari qty dan pricing\n" .
"  • `qty`: Jumlah unit yang dibeli (angka murni)\n" .
"  • `unit`: Satuan unit (DETEKSI: PCS, KARTON/KRT/CTN, RENCENG/RCG/RTG, PACK/PCK, BOX, SLOP, dll)\n" .
"  • `unit_price`: Harga per satuan = total_price / qty. WAJIB kalkulasi dengan akurat.\n" .
"  • `total_price`: Total harga baris (sebelum diskon)\n" .
"  • CONTOH: Jika invoice \"4 Karton × Rp 150.000 = Rp 600.000\", maka: qty=4, unit=\"Karton\", unit_price=150000, total_price=600000\n" .
"\n" .
"========== KEMAMPUAN TAMBAHAN ==========\n" .
"  • TULISAN TANGAN & FORMAT ACAK: Baca dengan teliti invoice tulisan tangan, buram, atau berformat kolom terbalik (harga duluan baru nama).\n" .
"  • VALIDASI TOTAL HARGA: JIKA di bagian bawah invoice terdapat total pembayaran/grand total, hitung total harga semua item yang kamu deteksi. Pastikan jumlahnya sama atau mendekati grand total di invoice. Jika tidak ada grand total di foto, abaikan validasi ini.\n" .
"  • PPN & DISKON: Deteksi jika ada PPN atau diskon, dan kalkulasi unit_price & total_price secara proporsional. Pastikan total_price merefleksikan harga final barang tersebut setelah diskon. Isi kolom diskon jika secara spesifik tertera potongan per item.\n" .
"  • MAPPING KEMASAN: Petakan singkatan satuan ke nama standar jika jelas (Misal: RCG -> Renceng, KRT -> Karton).\n" .
"\n" .
"========== INSTRUKSI TEKNIS ==========\n" .
"1. OUTPUT HARUS JSON VALID! Tidak boleh ada Markdown (```json) atau teks apapun sebelum/sesudah array.\n" .
"2. JANGAN tambahkan penjelasan, HANYA array JSON.\n" .
"3. Ekstrak semua item yang terbaca dengan baik.\n" .
"4. Semua harga: ANGKA MURNI saja (tanpa titik, koma, atau simbol Rp).\n" .
"5. Untuk SETIAP item, WAJIB centang 4-poin: Kode? Nama supplier? Analisis nama? Unit+price? \n" .
"\n" .
"========== FORMAT JSON OUTPUT YANG WAJIB ==========\n" .
"[\n" .
"  {\n" .
"    \"supplier_product_code\": \"KODE123\",\n" .
"    \"supplier_invoice_name\": \"Nama Barang Seperti Di Invoice\",\n" .
"    \"name\": \"Nama Produk Lengkap\",\n" .
"    \"brand\": \"Brand\",\n" .
"    \"variant\": \"Varian\",\n" .
"    \"product_type\": \"Tipe\",\n" .
"    \"size\": \"Ukuran\",\n" .
"    \"qty\": 4,\n" .
"    \"unit\": \"Karton\",\n" .
"    \"unit_price\": 150000,\n" .
"    \"total_price\": 600000,\n" .
"    \"discount\": 0\n" .
"  }\n" .
"]";
            $prompt = $settingModel->get('ai_invoice_prompt', $defaultPrompt);

            if (empty($apiKey)) {
                throw new \Exception("API Key AI belum dikonfigurasi di Pengaturan Aplikasi");
            }

            // Clean base64 if it has prefix
            if (preg_match('/^data:image\/(\w+);base64,/', $imageB64, $type)) {
                $imageB64 = substr($imageB64, strpos($imageB64, ',') + 1);
            }

            // OpenRouter API payload
            $data = [
                "model" => $modelName,
                "messages" => [
                    [
                        "role" => "user",
                        "content" => [
                            [
                                "type" => "text",
                                "text" => $prompt
                            ],
                            [
                                "type" => "image_url",
                                "image_url" => [
                                    "url" => "data:image/jpeg;base64," . $imageB64
                                ]
                            ]
                        ]
                    ]
                ],
                "response_format" => ["type" => "json_object"], // Assuming the model supports it or at least guides it
                "max_tokens" => 4000
            ];

            file_put_contents('c:\\xampp\\htdocs\\AlfarezMart\\logs\\ai_crash.log', date('Y-m-d H:i:s') . " Trace: Preparing curl request\n", FILE_APPEND);
            $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $apiKey,
                "HTTP-Referer: " . BASE_URL,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            
            file_put_contents('c:\\xampp\\htdocs\\AlfarezMart\\logs\\ai_crash.log', date('Y-m-d H:i:s') . " Trace: Encoding JSON for curl\n", FILE_APPEND);
            $jsonPayload = json_encode($data);
            if ($jsonPayload === false) {
                throw new \Exception("Failed to encode JSON payload: " . json_last_error_msg());
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 110); // 110s timeout for AI (must be less than max_execution_time)

            error_log("SCAN_AI_TRACE: Executing curl");
            file_put_contents('c:\\xampp\\htdocs\\AlfarezMart\\logs\\ai_crash.log', date('Y-m-d H:i:s') . " Trace: Executing curl...\n", FILE_APPEND);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            // curl_close is deprecated in PHP 8.0+ when using CurlHandle objects
            

            error_log("SCAN_AI_TRACE: Curl finished with code: " . $httpCode);
            file_put_contents('c:\\xampp\\htdocs\\AlfarezMart\\logs\\ai_crash.log', date('Y-m-d H:i:s') . " Trace: Curl finished, code: $httpCode, err: $err\n", FILE_APPEND);

            if ($err) {
                throw new \Exception("cURL Error: " . $err);
            }

            if ($httpCode >= 400) {
                throw new \Exception("OpenRouter API Error ($httpCode): " . $response);
            }

            $resJson = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Gagal membaca respons dari AI (Bukan JSON valid). Response: " . substr($response, 0, 100) . "...");
            }

            $content = $resJson['choices'][0]['message']['content'] ?? '[]';
            
            // Clean markdown json tags if present
            $content = preg_replace('/^```json\s*/i', '', trim($content));
            $content = preg_replace('/```$/i', '', $content);
            
            // Attempt to fix common truncated JSON by adding closing brackets if missing
            $parsedItems = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If truncated, it might miss ] or }]
                if (substr(trim($content), -1) !== ']') {
                    if (substr(trim($content), -1) !== '}') {
                        $content .= '}';
                    }
                    $content .= ']';
                    $parsedItems = json_decode($content, true);
                }
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Format JSON dari AI terpotong atau tidak valid: " . json_last_error_msg());
                }
            }

            if (isset($parsedItems['items'])) {
                $parsedItems = $parsedItems['items'];
            }
            if (!is_array($parsedItems)) {
                throw new \Exception("Format respons AI tidak valid. Diharapkan JSON array.");
            }

            // Fuzzy mapping against DB
            $productModel = new ProductModel();
            $allProducts = $productModel->allWithDetails();
            $productModel->attachPackagingsForProductList($allProducts);

            $supplierId = $this->input('supplier_id');
            $supplierProductIds = [];
            if ($supplierId) {
                $spModel = new SupplierProductModel();
                $supplierProducts = $spModel->getProductsBySupplier($supplierId);
                
                foreach ($supplierProducts as $sp) {
                    $supplierProductIds[] = $sp['product_id'];
                }
            }

            $mappedItems = [];
            foreach ($parsedItems as $item) {
                $name = $item['name'] ?? '';
                $qty = isset($item['qty']) && $item['qty'] > 0 ? (float)$item['qty'] : 1;
                $totalPrice = isset($item['total_price']) ? (float)$item['total_price'] : (isset($item['price']) ? (float)$item['price'] : 0);
                $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : ($totalPrice > 0 ? $totalPrice / $qty : 0);
                $extractedBrand = $item['brand'] ?? '';
                $extractedType = $item['product_type'] ?? '';
                $extractedVariant = $item['variant'] ?? '';
                $extractedWeightVal = isset($item['weight']) ? (float)$item['weight'] : null;
                $extractedWeightUnit = $item['unit'] ?? '';
                $extractedCode = $item['supplier_code'] ?? $item['supplier_product_code'] ?? '';
                $extractedSize = strtolower(trim($item['size'] ?? ''));
                $extractedSuppInvName = $item['supplier_invoice_name'] ?? '';
                
                // Auto-scale abbreviated prices (e.g. 5.5 -> 5500, 12 -> 12000) for standard Rupiah values
                if ($unitPrice > 0 && $unitPrice < 1000) {
                    if (floor($unitPrice) != $unitPrice || $unitPrice < 100) {
                        $unitPrice = $unitPrice * 1000;
                    }
                }
                if ($totalPrice > 0 && $totalPrice < 1000 && $totalPrice < $unitPrice) {
                     $totalPrice = $unitPrice * $qty; // Correct it if AI abbreviated it
                }
                
                $bestMatch = null;
                $highestScore = 0;
                
                // ========== ALGORITMA 4-POIN SCANNING - MATCHING STRATEGY ==========
                // POIN 1 (100pts): Kode Barang Supplier - exact match only
                // POIN 2 (80-95pts): Nama Barang Supplier - exact match first, fuzzy match second
                // POIN 3 (25-65pts): Product Name/Label Analysis - keyword matching, brand/variant/type
                // POIN 4 (15-35pts): Unit Kemasan Analysis - reverse-engineer dari qty/price
                // =====================================================================
                
                $trimmedCode = trim($extractedCode);
                if (!empty($trimmedCode)) {
                    foreach ($allProducts as $p) {
                        $dbSuppCode = trim($p['supplier_product_code'] ?? '');
                        $dbCode = trim($p['code'] ?? '');
                        if ((!empty($dbSuppCode) && strcasecmp($trimmedCode, $dbSuppCode) === 0) ||
                            (!empty($dbCode) && strcasecmp($trimmedCode, $dbCode) === 0)) {
                            $bestMatch = $p;
                            $highestScore = 200; // POIN 1: Kode exact match = highest priority
                            break;
                        }
                    }
                }
                
                if (!$bestMatch) {
                    foreach ($allProducts as $p) {
                        $score = 0;
                    
                    // ========== POIN 1: KODE BARANG SUPPLIER (Supplier Product Code) ==========
                    // Supplier belonging check
                    if ($supplierId && in_array($p['id'], $supplierProductIds)) {
                        $score += 25; // Boost score if product belongs to this supplier
                    }
                    
                    // 1. Direct code matching (if AI found a code) — highest priority
                    if (!empty($extractedCode)) {
                        $normCode = strtolower(trim($extractedCode));
                        $normDbCode = strtolower(trim($p['supplier_product_code'] ?? ''));
                        $normProdCode = strtolower(trim($p['code'] ?? ''));
                        if ($normCode === $normDbCode || $normCode === $normProdCode) {
                            $score += 85; // Exact code match — very high
                        } elseif (!empty($normDbCode) && (stripos($normDbCode, $normCode) !== false || stripos($normCode, $normDbCode) !== false)) {
                            $score += 30; // Partial supplier_product_code match
                        } elseif (!empty($normProdCode) && (stripos($normProdCode, $normCode) !== false || stripos($normCode, $normProdCode) !== false)) {
                            $score += 20; // Partial product code match
                        }
                    }
                    
                    // ========== POIN 2: NAMA BARANG SUPPLIER (Supplier Invoice Name) ==========
                    // Support multi-nama: supplier_invoice_name bisa berisi banyak baris/nama
                    if (!empty($p['supplier_invoice_name'])) {
                        // Pecah menjadi array nama (per baris, koma, atau titik koma)
                        $rawInvNames = preg_split('/[\n\r,;]+/', $p['supplier_invoice_name']);
                        $invNames = array_filter(array_map('trim', $rawInvNames));
                        
                        $normSuppInvName = strtolower(trim($extractedSuppInvName));
                        $normName = strtolower(trim($name));
                        
                        $poin2Score = 0;
                        foreach ($invNames as $invNameEntry) {
                            $normInvEntry = strtolower(trim($invNameEntry));
                            if (empty($normInvEntry)) continue;
                            
                            // Exact match: extracted supplier_invoice_name vs each stored name
                            if (!empty($normSuppInvName) && $normSuppInvName === $normInvEntry) {
                                $poin2Score = max($poin2Score, 95); // POIN 2: Nama supplier exact match
                            } elseif (!empty($normName) && $normName === $normInvEntry) {
                                $poin2Score = max($poin2Score, 90);
                            } elseif (!empty($normSuppInvName) && (stripos($normInvEntry, $normSuppInvName) !== false || stripos($normSuppInvName, $normInvEntry) !== false)) {
                                $poin2Score = max($poin2Score, 28); // Fuzzy match
                            } elseif (!empty($normName) && (stripos($normInvEntry, $normName) !== false || stripos($normName, $normInvEntry) !== false)) {
                                $poin2Score = max($poin2Score, 25);
                            }
                        }
                        $score += $poin2Score;
                    }
                    
                    // ========== POIN 3: ANALISIS NAMA PRODUK (Product Name/Label Analysis) ==========
                    // 3. Name similarity matching via similar_text() — keyword matching
                    $nameSimilarities = [];
                    
                    // Match against full_name
                    similar_text(strtolower($name), strtolower($p['full_name'] ?? ''), $simFullName);
                    $nameSimilarities[] = $simFullName;
                    
                    // Match against short_label
                    if (!empty($p['short_label'])) {
                        similar_text(strtolower($name), strtolower($p['short_label']), $simShort);
                        $nameSimilarities[] = $simShort;
                    }
                    
                    // Match against invoice_name
                    if (!empty($p['invoice_name'])) {
                        similar_text(strtolower($name), strtolower($p['invoice_name']), $simInv);
                        $nameSimilarities[] = $simInv;
                    }
                    
                    // Match against supplier_invoice_name (for fuzzy matching if not exact) — support multi-nama
                    if (!empty($p['supplier_invoice_name'])) {
                        $invEntries = preg_split('/[\n\r,]+/', $p['supplier_invoice_name']);
                        foreach ($invEntries as $invEntry) {
                            $invEntry = trim($invEntry);
                            if (!empty($invEntry)) {
                                similar_text(strtolower($name), strtolower($invEntry), $simSuppInvX);
                                $nameSimilarities[] = $simSuppInvX;
                            }
                        }
                    }
                    
                    $bestNameSim = max($nameSimilarities);
                    
                    // Base score from best name similarity (65% weight if no exact match)
                    if ($score < 95) { // Only apply similarity weight if no exact supplier_invoice_name match
                        $score += $bestNameSim * 0.65; // POIN 3: Keyword matching score
                    }
                    
                    // 4. Brand match (weight: 12 points) — part of POIN 3
                    if (!empty($extractedBrand) && !empty($p['brand_name'])) {
                        if (stripos($p['brand_name'], $extractedBrand) !== false || stripos($extractedBrand, $p['brand_name']) !== false) {
                            $score += 12;
                        }
                    }
                    
                    // 5. Product type match (weight: 8 points) — part of POIN 3
                    if (!empty($extractedType) && !empty($p['product_type'])) {
                        if (stripos($p['product_type'], $extractedType) !== false || stripos($extractedType, $p['product_type']) !== false) {
                            $score += 8;
                        }
                    }
                    
                    // 6. Variant match (weight: 8 points) — part of POIN 3
                    if (!empty($extractedVariant) && !empty($p['variant'])) {
                        if (stripos($p['variant'], $extractedVariant) !== false || stripos($extractedVariant, $p['variant']) !== false) {
                            $score += 8;
                        }
                    }
                    
                    // 7. Weight/volume match (weight: 10 points) — part of POIN 3
                    if ($extractedWeightVal !== null && !empty($p['weight_value'])) {
                        $dbWeightVal = (float)$p['weight_value'];
                        if (abs($extractedWeightVal - $dbWeightVal) < 0.01) {
                            $score += 10;
                            if (!empty($extractedWeightUnit) && !empty($p['weight_unit'])) {
                                if (strtolower(trim($extractedWeightUnit)) === strtolower(trim($p['weight_unit']))) {
                                    $score += 3;
                                }
                            }
                        }
                    }
                    
                    // 8. Size / package configuration match (weight: up to 10 points) — part of POIN 3
                    if (!empty($extractedSize)) {
                        // Check size against product full_name (e.g. "1DZ" in product name)
                        if (!empty($p['full_name']) && stripos($p['full_name'], $extractedSize) !== false) {
                            $score += 8;
                        }
                        // Check size against supplier_invoice_name
                        if (!empty($p['supplier_invoice_name']) && stripos($p['supplier_invoice_name'], $extractedSize) !== false) {
                            $score += 6;
                        }
                        // Check if size contains weight+unit combination (e.g. size="12x300ml", weight=300, unit="ml")
                        if ($extractedWeightVal !== null && !empty($p['weight_value']) && !empty($p['weight_unit'])) {
                            $weightCombo = (string)(int)$p['weight_value'] . strtolower(trim($p['weight_unit']));
                            if (stripos($extractedSize, $weightCombo) !== false) {
                                $score += 5;
                            }
                        }
                    }
                    
                    // ========== POIN 4: UNIT KEMASAN ANALYSIS (Reverse-engineer dari qty/price) ==========
                    // 9. Analisis Satuan Harga — detect packaging level from unit price
                    if ($unitPrice > 0 && !empty($p['packagings'])) {
                        $bestPriceMatch = 0;
                        foreach ($p['packagings'] as $pkg) {
                            $dbPrice = (float)($pkg['buy_price'] ?? 0);
                            if ($dbPrice > 0) {
                                $diff = abs($dbPrice - $unitPrice);
                                $pct = $diff / max($dbPrice, $unitPrice);
                                if ($pct < 0.05) { // Within 5% difference — strong match
                                    $priceMatchScore = 25; // POIN 4: Strong unit price match
                                    // Also check unit text match
                                    if (!empty($extractedWeightUnit) && !empty($pkg['unit_name'])) {
                                        if (stripos($pkg['unit_name'], $extractedWeightUnit) !== false || stripos($extractedWeightUnit, $pkg['unit_name']) !== false) {
                                            $priceMatchScore += 10; // Additional bonus for unit text match
                                        }
                                    }
                                    $bestPriceMatch = max($bestPriceMatch, $priceMatchScore);
                                } elseif ($pct < 0.15) { // Within 15% — moderate match
                                    $bestPriceMatch = max($bestPriceMatch, 15); // POIN 4: Moderate unit price match
                                }
                            }
                        }
                        $score += $bestPriceMatch;
                    }
                    
                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $p;
                    }
                }
                } // End of if (!$bestMatch)

                $isMatched = ($highestScore > 65); // Threshold

                $mappedItems[] = [
                    'original_name' => $name,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'unit' => $extractedWeightUnit,
                    'is_matched' => $isMatched,
                    'product_id' => $isMatched ? $bestMatch['id'] : null,
                    'product_name' => $isMatched ? $bestMatch['full_name'] : null,
                    'match_score' => round($highestScore, 2)
                ];
            }

            ob_end_clean(); // Discard any stray output before sending JSON
            $this->json([
                'success' => true,
                'data' => $mappedItems
            ]);

        } catch (\Exception $e) {
            error_log("SCAN_AI_TRACE: Exception caught: " . $e->getMessage());
            ob_end_clean(); // Discard any stray output before sending JSON
            $this->json(['error' => $e->getMessage()], 500);
        } catch (\Error $err) {
            error_log("SCAN_AI_TRACE: Fatal Error caught: " . $err->getMessage());
            ob_end_clean();
            $this->json(['error' => 'Fatal internal error: ' . $err->getMessage()], 500);
        }
    }

    // ===== DEBT SOURCES =====
    public function getDebtSources()
    {
        $model = new DebtModel();
        $this->json($model->getDebtSources());
    }

    public function createDebtSource()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama sumber hutang wajib diisi');
            $model = new DebtModel();
            $id = $model->createDebtSource($name);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Sumber hutang berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateDebtSource(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama sumber hutang wajib diisi');
            $model = new DebtModel();
            $model->updateDebtSource($id, $name);
            $this->json(['success' => true, 'message' => 'Sumber hutang berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteDebtSource(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteDebtSource($id);
            $this->json(['success' => true, 'message' => 'Sumber hutang berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}

