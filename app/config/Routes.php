<?php
/**
 * Routes Configuration
 * 
 * Define all application routes here.
 * Format: $router->method('/path', 'Controller@method');
 */

// ============================================
// AUTH ROUTES (Public — no auth required)
// ============================================
$router->get('/login', 'AuthController@showLogin');
$router->post('/api/auth/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// ============================================
// WEB ROUTES (Returns HTML views — auth required)
// ============================================

// Dashboard
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Products
$router->get('/products', 'ProductController@index');
$router->get('/products/create', 'ProductController@create');
$router->get('/products/{id}', 'ProductController@show');
$router->get('/products/{id}/edit', 'ProductController@edit');

// Purchases (Barang Masuk)
$router->get('/purchases', 'PurchaseController@index');
$router->get('/purchases/create', 'PurchaseController@create');
$router->get('/purchases/{id}', 'PurchaseController@show');

// Sales (Penjualan)
$router->get('/sales', 'SaleController@index');
$router->get('/sales/pos', 'SaleController@pos');
$router->get('/sales/{id}', 'SaleController@show');

// Suppliers
$router->get('/suppliers', 'SupplierController@index');

// Scanner (Cek Harga)
$router->get('/scanner', 'BarcodeController@scanner');

// Settings & Help
$router->get('/settings', 'DashboardController@index');
$router->get('/settings/master-data', 'SettingController@masterData');
$router->get('/settings/receipt', 'SettingController@receiptSettings');
$router->get('/settings/app', 'SettingController@appSettings');
$router->get('/help', 'DashboardController@help');

// Reports
$router->get('/reports', 'ReportController@index');
$router->get('/reports/product-history', 'ReportController@productHistory');
$router->get('/reports/product-history/export/{id}', 'ReportController@exportProductHistory');

// ============================================
// API ROUTES (Returns JSON — auth required)
// ============================================

// Products API
$router->get('/api/products', 'ApiController@getProducts');
$router->get('/api/products/search', 'ApiController@searchProducts');
$router->get('/api/products/barcode/{code}', 'ApiController@getByBarcode');
$router->get('/api/barcode/generate', 'ApiController@generateBarcode');
$router->get('/api/products/{id}', 'ApiController@getById');
$router->post('/api/products', 'ApiController@createProduct');
$router->post('/api/products/update/{id}', 'ApiController@updateProduct');
$router->post('/api/products/{id}/delete', 'ApiController@deleteProduct');
$router->post('/api/products/packaging/{id}', 'ApiController@updatePackaging');
$router->post('/api/products/packaging/{id}/qty-prices', 'ApiController@savePackagingQtyPrices');
$router->post('/api/products/packaging/{id}/delete', 'ApiController@deletePackaging');
$router->post('/api/products/{id}/packaging/add', 'ApiController@addPackaging');
$router->post('/api/products/{id}/label', 'ApiController@updateProductLabel');
$router->get('/api/products/{id}/label-variants', 'ApiController@getProductLabelVariants');
$router->post('/api/products/{id}/label/distribute', 'ApiController@distributeProductLabel');
$router->post('/api/products/{id}/photo', 'ApiController@updateProductPhoto');
$router->post('/api/products/{id}/stock', 'ApiController@updateProductStock');
$router->get('/api/settings/receipt', 'ApiController@getReceiptSettings');
$router->post('/api/settings/receipt', 'ApiController@saveReceiptSettings');
$router->post('/api/settings/app', 'ApiController@saveAppSettings');

// AI Agent API
$router->post('/api/ai/scan-invoice', 'ApiController@scanInvoiceAI');

// Purchases API
$router->get('/api/purchases', 'ApiController@getPurchases');
$router->post('/api/purchases', 'ApiController@createPurchase');
$router->post('/api/purchases/{id}/photo', 'ApiController@uploadInvoicePhoto');
$router->post('/api/purchases/{id}/delete', 'ApiController@deletePurchase');
$router->get('/api/purchases/search-products', 'ApiController@searchProductsForPurchase');

// Reports API
$router->get('/api/reports/product-history/{id}', 'ApiController@getProductHistory');

// Sales Transactions API
$router->post('/api/sales', 'ApiController@createSale');
$router->get('/api/sales/invoice/{id}', 'ApiController@getInvoice');

// Suppliers API
$router->get('/api/suppliers', 'ApiController@getSuppliers');
$router->get('/api/suppliers/search', 'ApiController@searchSuppliers');
$router->post('/api/suppliers', 'ApiController@createSupplier');
$router->post('/api/suppliers/{id}', 'ApiController@updateSupplier'); // PUT logic using POST
$router->post('/api/suppliers/{id}/delete', 'ApiController@deleteSupplier'); // DELETE logic using POST
$router->get('/api/supplier-types', 'ApiController@getSupplierTypes');
$router->get('/api/suppliers/{id}/products', 'ApiController@getProductsBySupplier');
$router->post('/api/suppliers/{id}/products', 'ApiController@addSupplierProduct');
$router->post('/api/suppliers/{id}/products/{pid}/delete', 'ApiController@removeSupplierProduct');
$router->get('/api/suppliers/{id}/bulk-products', 'ApiController@getBulkSupplierProducts');

// Sales Reps API
$router->get('/api/sales-reps', 'ApiController@getAllSalesReps');
$router->get('/api/suppliers/{id}/sales-reps', 'ApiController@getSalesRepsBySupplier');
$router->post('/api/sales-reps', 'ApiController@createSalesRep');
$router->post('/api/sales-reps/{id}', 'ApiController@updateSalesRep'); // PUT
$router->post('/api/sales-reps/{id}/delete', 'ApiController@deleteSalesRep'); // DELETE

// Brands API
$router->get('/api/brands', 'ApiController@getBrands');
$router->post('/api/brands', 'ApiController@createBrand');
$router->post('/api/brands/{id}', 'ApiController@updateBrand'); // PUT
$router->post('/api/brands/{id}/delete', 'ApiController@deleteBrand'); // DELETE

// Categories API
$router->get('/api/categories', 'ApiController@getCategories');
$router->post('/api/categories', 'ApiController@createCategory');
$router->post('/api/categories/{id}', 'ApiController@updateCategory'); // PUT
$router->post('/api/categories/{id}/delete', 'ApiController@deleteCategory'); // DELETE

// Units API
$router->get('/api/units', 'ApiController@getUnits');
$router->post('/api/units', 'ApiController@createUnit');
$router->post('/api/units/{id}', 'ApiController@updateUnit'); // PUT
$router->post('/api/units/{id}/delete', 'ApiController@deleteUnit'); // DELETE

// Dashboard API
$router->get('/api/dashboard/stats', 'ApiController@getDashboardStats');

// User Management API
$router->get('/api/users', 'ApiController@getUsers');
$router->post('/api/users', 'ApiController@createUser');
$router->post('/api/users/{id}/toggle', 'ApiController@toggleUserStatus');
$router->post('/api/users/{id}/delete', 'ApiController@deleteUser');
$router->post('/api/users/{id}/reset-password', 'ApiController@resetUserPassword');
$router->post('/api/users/change-password', 'ApiController@changePassword');

// User Management Web Routes
$router->get('/users', 'UserController@index');

// Debts Web Routes
$router->get('/debts', 'DebtController@index');

// Debts & Customers API Routes
$router->get('/api/customers', 'ApiController@getCustomers');
$router->post('/api/customers', 'ApiController@createCustomer');
$router->post('/api/customers/{id}', 'ApiController@updateCustomer');
$router->post('/api/customers/{id}/delete', 'ApiController@deleteCustomer');

$router->get('/api/debts/customer', 'ApiController@getCustomerDebts');
$router->post('/api/debts/customer', 'ApiController@createCustomerDebt');
$router->post('/api/debts/customer/{id}/pay', 'ApiController@payCustomerDebt');
$router->post('/api/debts/customer/{id}/delete', 'ApiController@deleteCustomerDebt');

$router->get('/api/debts/shop', 'ApiController@getShopDebts');
$router->post('/api/debts/shop', 'ApiController@createShopDebt');
$router->post('/api/debts/shop/{id}/pay', 'ApiController@payShopDebt');
$router->post('/api/debts/shop/{id}/delete', 'ApiController@deleteShopDebt');

// Finance Web Routes
$router->get('/finance', 'FinanceController@index');

// Finance API Routes
$router->get('/api/finance/summary', 'ApiController@getFinanceSummary');
$router->get('/api/finance/logs', 'ApiController@getFinanceLogs');
$router->post('/api/finance/logs', 'ApiController@createFinanceLog');
$router->post('/api/finance/logs/{id}/update', 'ApiController@updateFinanceLog');
$router->post('/api/finance/logs/{id}/delete', 'ApiController@deleteFinanceLog');

// Database Setup (only in development)
$router->get('/setup', 'SettingController@setupDatabase');
