<?php
/**
 * ProductController - CRUD Produk
 */
class ProductController extends Controller
{
    private $productModel;
    private $brandModel;
    private $categoryModel;
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

        $products = $this->productModel->getProductsWithPrices($page, 20, $search, $categoryId);
        $categories = $this->categoryModel->all('name', 'ASC');

        $this->view('products.index', [
            'title' => 'Produk',
            'activeNav' => 'products',
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $categoryId,
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

    public function show($id)
    {
        $product = $this->productModel->findWithDetails($id);
        if (!$product) {
            $this->redirect('/products');
            return;
        }

        $packagings = $this->productModel->getPackagings($id);

        $this->view('products.show', [
            'title' => $product['short_label'] ?: $product['full_name'],
            'activeNav' => 'products',
            'product' => $product,
            'packagings' => $packagings,
        ]);
    }

    public function edit($id)
    {
        $product = $this->productModel->findWithDetails($id);
        if (!$product) {
            $this->redirect('/products');
            return;
        }

        $packagings = $this->productModel->getPackagings($id);
        $brands = $this->brandModel->all('name', 'ASC');
        $categories = $this->categoryModel->all('name', 'ASC');
        $units = $this->unitModel->all('name', 'ASC');

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
