<?php
/**
 * ReportController - Modul Laporan Keuangan Harian
 */
class ReportController extends Controller
{
    public function index()
    {
        $saleModel = new SaleModel();
        $purchaseModel = new PurchaseModel();
        
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        
        // Basic Stats for the day
        $salesStats = $saleModel->getDailyStats($date);
        $purchaseStats = $purchaseModel->getDailyStats($date);
        
        $this->view('reports.index', [
            'title' => 'Laporan Harian',
            'activeNav' => 'home',
            'date' => $date,
            'salesStats' => $salesStats,
            'purchaseStats' => $purchaseStats
        ]);
    }

    public function productHistory()
    {
        $this->view('reports.product_history', [
            'title' => 'Riwayat Pembelian Produk',
            'activeNav' => 'home'
        ]);
    }

    public function exportProductHistory($id)
    {
        $productModel = new ProductModel();
        $purchaseModel = new PurchaseModel();
        
        $product = $productModel->findWithDetails($id);
        if (!$product) {
            die("Produk tidak ditemukan");
        }
        
        $history = $purchaseModel->getProductPurchaseHistory($id);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="riwayat_pembelian_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $product['full_name']) . '_' . date('Ymd') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal', 'Kode Pembelian', 'Supplier', 'Kemasan', 'Qty', 'Harga Beli Satuan', 'Total']);
        
        foreach ($history as $row) {
            fputcsv($output, [
                $row['purchase_date'],
                $row['purchase_code'],
                $row['supplier_name'],
                $row['unit_name'] . ' (Lvl ' . $row['level'] . ')',
                $row['quantity'],
                $row['buy_price'],
                $row['total_price']
            ]);
        }
        fclose($output);
        exit;
    }
}
