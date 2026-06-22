<?php
/**
 * CatalogController - Handles the Catalog Builder feature
 */
class CatalogController extends Controller
{
    public function index()
    {
        // Must be logged in
        AuthController::requireAuth();

        // Fetch categories to populate the bulk select dropdown
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->all() ?: [];

        $data = [
            'title' => 'Buat Katalog Produk',
            'categories' => $categories
        ];

        $this->view('catalog.index', $data);
    }
}
