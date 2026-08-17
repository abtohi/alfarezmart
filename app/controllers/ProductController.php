<?php
/**
 * ProductController - CRUD Produk
 */
class ProductController extends Controller
{
    /** @var ProductModel */
    private $productModel;
    /** @var BrandModel */
    private $brandModel;
    /** @var CategoryModel */
    private $categoryModel;
    /** @var UnitModel */
    private $unitModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new ProductModel();
        $this->brandModel = new BrandModel();
        $this->categoryModel = new CategoryModel();
        $this->unitModel = new UnitModel();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? max(0, (float)$_GET['min_price']) : null;
        $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? max(0, (float)$_GET['max_price']) : null;

        $products = $this->productModel->getProductsWithPrices($page, 20, $search, $categoryId, $minPrice, $maxPrice);
        $categories = $this->categoryModel->all('name', 'ASC');

        $this->view('products.index', [
            'title' => 'Produk',
            'activeNav' => 'products',
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    public function multivariant()
    {
        $this->view('products.multivariant', [
            'title' => 'Harga Produk Multivarian',
            'activeNav' => 'products'
        ]);
    }

    public function barcodeEditor()
    {
        $this->view('products.barcode_editor', [
            'title' => 'Edit Barcode Kemasan',
            'activeNav' => 'barcode_editor',
            'csrfToken' => (new Security())->getCSRFToken()
        ]);
    }

    public function create()
    {
        $brands = $this->brandModel->all('name', 'ASC');
        $categories = $this->categoryModel->all('name', 'ASC');
        $units = $this->unitModel->all('name', 'ASC');

        $this->view('products.create', [
            'title' => 'Tambah Produk',
            'activeNav' => 'products',
            'brands' => $brands,
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function show(string $id)
    {
        $id = trim($id);
        $product = null;

        // 1. Try finding by primary product ID
        try {
            $product = $this->productModel->findWithDetails($id);
        } catch (\Throwable $e) {
            $product = null;
        }

        // 2. If not found, try resolving if $id is a packaging ID
        if (!$product) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT product_id FROM product_packagings WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $pkgRow = $stmt->fetch();
                if ($pkgRow && !empty($pkgRow['product_id'])) {
                    header('Location: ' . BASE_URL . 'products/' . (int)$pkgRow['product_id']);
                    exit;
                }
            } catch (\Throwable $e) {}
        }

        // 3. If not found, try resolving by barcode
        if (!$product) {
            try {
                $byBarcode = $this->productModel->findByBarcode($id);
                if ($byBarcode && !empty($byBarcode['id'])) {
                    header('Location: ' . BASE_URL . 'products/' . (int)$byBarcode['id']);
                    exit;
                }
            } catch (\Throwable $e) {}
        }

        // 4. If not found, try resolving by product code or supplier product code
        if (!$product) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id FROM products WHERE code = :code OR supplier_product_code = :scode LIMIT 1");
                $stmt->execute([':code' => $id, ':scode' => $id]);
                $codeRow = $stmt->fetch();
                if ($codeRow && !empty($codeRow['id'])) {
                    header('Location: ' . BASE_URL . 'products/' . (int)$codeRow['id']);
                    exit;
                }
            } catch (\Throwable $e) {}
        }

        $productFound = ($product !== null);

        if (!$product) {
            $product = [
                'id' => (int)$id,
                'full_name' => 'Produk #' . $id,
                'short_label' => '',
                'brand_name' => '',
                'category_name' => '',
                'weight_value' => '',
                'weight_unit' => '',
                'current_qty_base' => 0,
                'photo' => '',
                'code' => '',
                'supplier_product_code' => '',
                'supplier_invoice_name' => '',
                'is_available' => 1
            ];
        }

        try {
            $packagings = $this->productModel->getPackagings($product['id']) ?: [];
        } catch (\Throwable $e) {
            $packagings = [];
        }
        
        $supplierProductModel = new SupplierProductModel();
        $salesRepModel = new SalesRepModel();
        
        try {
            $suppliers = $supplierProductModel->getProductSuppliers($product['id']) ?: [];
            $salesReps = [];
            if (!empty($suppliers)) {
                $supplierIds = array_column($suppliers, 'id');
                $salesReps = $salesRepModel->getActiveBySupplierIds($supplierIds) ?: [];
            }
        } catch (\Throwable $e) {
            $suppliers = [];
            $salesReps = [];
        }

        $this->view('products.show', [
            'title' => $product['short_label'] ?: $product['full_name'],
            'activeNav' => 'products',
            'product' => $product,
            'productFound' => $productFound,
            'packagings' => $packagings,
            'suppliers' => $suppliers,
            'salesReps' => $salesReps,
            'csrfToken' => (new Security())->getCSRFToken()
        ]);
    }


    public function edit(string $id)
    {
        $this->blockStaffMutations('mengedit');
        $id = trim($id);
        $product = null;

        // 1. Try finding by primary product ID
        try {
            $product = $this->productModel->findWithDetails($id);
        } catch (\Throwable $e) {
            $product = null;
        }

        // 2. If not found, try resolving if $id is packaging ID
        if (!$product) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT product_id FROM product_packagings WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $pkgRow = $stmt->fetch();
                if ($pkgRow && !empty($pkgRow['product_id'])) {
                    header('Location: ' . BASE_URL . 'products/' . (int)$pkgRow['product_id'] . '/edit');
                    exit;
                }
            } catch (\Throwable $e) {}
        }

        // 3. If not found, try resolving by barcode
        if (!$product) {
            try {
                $byBarcode = $this->productModel->findByBarcode($id);
                if ($byBarcode && !empty($byBarcode['id'])) {
                    header('Location: ' . BASE_URL . 'products/' . (int)$byBarcode['id'] . '/edit');
                    exit;
                }
            } catch (\Throwable $e) {}
        }

        if (!$product) {
            $product = [
                'id' => (int)$id,
                'code' => '',
                'brand_id' => null,
                'category_id' => null,
                'product_type' => '',
                'variant' => '',
                'full_name' => 'Produk #' . $id,
                'short_label' => '',
                'invoice_name' => '',
                'supplier_product_code' => '',
                'supplier_invoice_name' => '',
                'weight_value' => '',
                'weight_unit' => '',
                'is_available' => 1,
                'is_custom_label' => 0,
                'photo' => ''
            ];
        }

        try {
            $packagings = $this->productModel->getPackagings($product['id']) ?: [];
        } catch (\Throwable $e) {
            $packagings = [];
        }
        try {
            $brands = $this->brandModel->all('name', 'ASC') ?: [];
        } catch (\Throwable $e) {
            $brands = [];
        }
        try {
            $categories = $this->categoryModel->all('name', 'ASC') ?: [];
        } catch (\Throwable $e) {
            $categories = [];
        }
        try {
            $units = $this->unitModel->all('name', 'ASC') ?: [];
        } catch (\Throwable $e) {
            $units = [];
        }

        $this->view('products.edit', [
            'title' => 'Edit Produk',
            'activeNav' => 'products',
            'product' => $product,
            'packagings' => $packagings,
            'brands' => $brands,
            'categories' => $categories,
            'units' => $units,
        ]);
    }
}
