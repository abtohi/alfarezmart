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
$router->get('/products/multivariant', 'ProductController@multivariant');
$router->get('/products/{id}', 'ProductController@show');
$router->get('/products/{id}/edit', 'ProductController@edit');

// Purchases (Barang Masuk)
$router->get('/purchases', 'PurchaseController@index');
$router->get('/purchases/create', 'PurchaseController@create');
$router->get('/purchases/{id}/edit', 'PurchaseController@edit');
$router->get('/purchases/{id}', 'PurchaseController@show');

// Sales (Penjualan)
$router->get('/sales', 'SaleController@index');
$router->get('/sales/pos', 'SaleController@pos');
$router->get('/sales/{id}', 'SaleController@show');

// Suppliers
$router->get('/suppliers', 'SupplierController@index');

// Customers
$router->get('/customers', 'CustomerController@index');
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

// Catalog
$router->get('/catalog', 'CatalogController@index');

// ============================================
// PPOB / Produk Digital (Digiflazz)
// ============================================
$router->get('/ppob', 'DigiflazzController@index');
$router->get('/ppob/history', 'DigiflazzController@history');
$router->get('/ppob/settings', 'DigiflazzController@settings');
$router->get('/ppob/prices', 'DigiflazzController@priceList');
$router->get('/ppob/docs', 'DigiflazzController@documentation');
$router->get('/ppob/summary', 'DigiflazzController@summaryView');

// PPOB API Endpoints
$router->post('/api/ppob/settings', 'DigiflazzController@apiSaveSettings');
$router->get('/api/ppob/balance', 'DigiflazzController@apiGetBalance');
$router->post('/api/ppob/sync-prices', 'DigiflazzController@apiSyncPrices');
$router->post('/api/ppob/webhook', 'DigiflazzController@webhook');

// ============================================
// API ROUTES (Returns JSON — auth required)
// ============================================

// Products API
$router->get('/api/sync/all', 'ApiController@syncAllData');
$router->get('/api/products/sync', 'ApiController@syncProducts');
$router->get('/api/products/export', 'ApiController@exportProducts');
$router->get('/api/products/names', 'ApiController@getProductNames');
$router->get('/api/products', 'ApiController@getProducts');
$router->get('/api/products/search', 'ApiController@searchProducts');
$router->get('/api/products/{id}/variants', 'ApiController@getProductVariants');
$router->post('/api/products/multivariant-apply', 'ApiController@applyMultivariantPricing');
$router->get('/api/products/barcode/{code}', 'ApiController@getByBarcode');
$router->get('/api/barcode/generate', 'ApiController@generateBarcode');
$router->get('/api/products/{id}', 'ApiController@getById');
$router->post('/api/products', 'ApiController@createProduct');
$router->post('/api/products/update/{id}', 'ApiController@updateProduct');
$router->post('/api/products/{id}/availability', 'ApiController@updateProductAvailability');
$router->post('/api/products/{id}/delete', 'ApiController@deleteProduct');
$router->post('/api/products/bulk-delete', 'ApiController@bulkDeleteProducts');
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

// PPOB API
$router->get('/api/ppob/balance', 'DigiflazzController@apiGetBalance');
$router->get('/api/ppob/products/all', 'DigiflazzController@apiGetAllProducts');
$router->get('/api/ppob/products/search', 'DigiflazzController@apiSearchProducts');
$router->get('/api/ppob/products/{category}', 'DigiflazzController@apiGetProducts');
$router->get('/api/ppob/brands/{category}', 'DigiflazzController@apiGetBrands');
$router->post('/api/ppob/inquiry-pln', 'DigiflazzController@apiInquiryPLN');
$router->post('/api/ppob/inquiry-pasca', 'DigiflazzController@apiInquiryPostpaid');
$router->post('/api/ppob/transaction', 'DigiflazzController@apiCreateTransaction');
$router->post('/api/ppob/pay-pasca', 'DigiflazzController@apiPayPostpaid');
$router->get('/api/ppob/transactions', 'DigiflazzController@apiGetTransactions');
$router->get('/api/ppob/transaction/{refId}', 'DigiflazzController@apiGetTransaction');
$router->post('/api/ppob/sync-prices', 'DigiflazzController@apiSyncPrices');
$router->post('/api/ppob/settings', 'DigiflazzController@apiSaveSettings');
$router->post('/api/ppob/check-transaction', 'DigiflazzController@apiCheckTransaction');
$router->post('/api/ppob/deposit', 'DigiflazzController@apiCreateDeposit');

$router->post('/api/ppob/markup-rules', 'DigiflazzController@apiSaveMarkupRules');
$router->post('/api/ppob/custom-price', 'DigiflazzController@apiSaveCustomPrice');
$router->post('/api/ppob/custom-price/reset', 'DigiflazzController@apiResetCustomPrice');

// Webhook (PUBLIC — no auth)
$router->post('/api/ppob/webhook', 'DigiflazzController@webhook');
$router->get('/api/ppob/webhook', 'DigiflazzController@webhookTest');
$router->get('/api/ppob/webhook-log', 'DigiflazzController@webhookLog');
$router->get('/api/ppob/request-log', 'DigiflazzController@requestLog');
$router->get('/api/ppob/summary', 'DigiflazzController@apiGetSummary');

// Purchases API
$router->get('/api/purchases', 'ApiController@getPurchases');
$router->post('/api/purchases', 'ApiController@createPurchase');
$router->post('/api/purchases/{id}/photo', 'ApiController@uploadInvoicePhoto');
$router->post('/api/purchases/{id}/update', 'ApiController@updatePurchase');
$router->post('/api/purchases/{id}/delete', 'ApiController@deletePurchase');
$router->get('/api/purchases/search-products', 'ApiController@searchProductsForPurchase');
// Serve invoice photo files securely from storage (outside public_html)
$router->get('/api/storage/invoice-photo', 'ApiController@serveInvoicePhoto');

// Reports API
$router->get('/api/reports/product-history/{id}', 'ApiController@getProductHistory');
$router->get('/api/reports/supplier-price-comparison/{id}', 'ApiController@getSupplierPriceComparison');
$router->get('/api/reports/suppliers-for-comparison', 'ApiController@getSuppliersForComparison');

// Sales Transactions API
$router->get('/api/sales/analytics', 'ApiController@getSalesAnalytics');
$router->post('/api/sales', 'ApiController@createSale');
$router->get('/api/sales/invoice/{id}', 'ApiController@getInvoice');
$router->post('/api/sales/bulk-delete', 'ApiController@bulkDeleteSales');
$router->post('/api/sales/update/{id}', 'ApiController@updateSale');

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
$router->post('/api/users/{id}/update', 'ApiController@updateUser');
$router->post('/api/users/{id}/toggle', 'ApiController@toggleUserStatus');
$router->post('/api/users/{id}/delete', 'ApiController@deleteUser');
$router->post('/api/users/{id}/reset-password', 'ApiController@resetUserPassword');
$router->post('/api/users/change-password', 'ApiController@changePassword');

// User Activity Tracking API
$router->post('/api/activity/log', 'ApiController@logActivity');
$router->get('/api/users/activity/all', 'ApiController@getAllUsersActivity');
$router->get('/api/users/{id}/activity', 'ApiController@getUserActivity');

// User Management Web Routes
$router->get('/users', 'UserController@index');

// Debts Web Routes
$router->get('/debts', 'DebtController@index');

// Hitung Orderan (Order Estimate Builder)
$router->get('/hitung-orderan', 'OrderEstimateController@index');
$router->get('/api/orders/estimates', 'ApiController@getOrderEstimates');
$router->post('/api/orders/estimates', 'ApiController@saveOrderEstimate');
$router->get('/api/orders/estimates/{id}', 'ApiController@getOrderEstimateDetails');
$router->post('/api/orders/estimates/{id}/delete', 'ApiController@deleteOrderEstimate');

// Dashboard Summary (superadmin)
$router->get('/dashboard/summary', 'DashboardController@summary');

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

$router->get('/api/debts/sources', 'ApiController@getDebtSources');
$router->post('/api/debts/sources', 'ApiController@createDebtSource');
$router->post('/api/debts/sources/{id}', 'ApiController@updateDebtSource');
$router->post('/api/debts/sources/{id}/delete', 'ApiController@deleteDebtSource');

// Finance Web Routes
$router->get('/finance', 'FinanceController@index');

// Finance API Routes
$router->get('/api/finance/summary', 'ApiController@getFinanceSummary');
$router->get('/api/finance/logs', 'ApiController@getFinanceLogs');
    $router->post('/api/finance/logs', 'ApiController@createFinanceLog');
    $router->post('/api/finance/logs/bulk-delete', 'ApiController@bulkDeleteFinanceLogs');
    $router->post('/api/finance/logs/{id}/update', 'ApiController@updateFinanceLog');
    $router->post('/api/finance/logs/{id}/delete', 'ApiController@deleteFinanceLog');


// Finance Accounts & Categories API
$router->get('/api/finance/accounts', 'ApiController@getFinanceAccounts');
$router->post('/api/finance/accounts', 'ApiController@createFinanceAccount');
$router->post('/api/finance/accounts/{id}/update', 'ApiController@updateFinanceAccount');
$router->post('/api/finance/accounts/{id}/delete', 'ApiController@deleteFinanceAccount');

$router->get('/api/finance/categories', 'ApiController@getFinanceCategories');
$router->post('/api/finance/categories', 'ApiController@createFinanceCategory');
$router->post('/api/finance/categories/{id}/update', 'ApiController@updateFinanceCategory');
$router->post('/api/finance/categories/{id}/delete', 'ApiController@deleteFinanceCategory');

// Database Setup (only in development)
$router->get('/setup', 'SettingController@setupDatabase');

// Offline Sync
$router->get('/api/sync/all', 'ApiController@syncAllData');

// AI Chat Routes
$router->get('/chat', 'AiChatController@index');
$router->post('/api/chat', 'AiChatController@sendMessage');
$router->get('/api/chat/history', 'AiChatController@getHistory');
$router->post('/api/chat/clear', 'AiChatController@clearHistory');
$router->post('/api/chat/feedback', 'AiChatController@saveFeedback'); // Knowledge base
$router->post('/api/settings/chat', 'ApiController@saveChatSettings');

