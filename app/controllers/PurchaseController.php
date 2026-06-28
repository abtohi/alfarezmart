<?php
/**
 * PurchaseController - Barang Masuk / Pembelian
 */
class PurchaseController extends Controller
{
    public function index()
    {
        $model = new PurchaseModel();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $purchases = $model->getList($page, 30);
        $groupedPurchases = $model->groupByDateAndSupplier($purchases['data']);

        $this->view('purchases.index', [
            'title' => 'Riwayat Barang Masuk',
            'activeNav' => 'purchase',
            'purchases' => $purchases,
            'groupedPurchases' => $groupedPurchases,
        ]);
    }

    public function create()
    {
        $supplierModel = new SupplierModel();
        $salesRepModel = new SalesRepModel();

        try {
            $salesReps = $salesRepModel->getAllWithSupplier();
        } catch (Exception $e) {
            error_log('PurchaseController::create salesReps error: ' . $e->getMessage());
            $salesReps = [];
        }
        try {
            $suppliers = $supplierModel->all('name', 'ASC');
        } catch (Exception $e) {
            error_log('PurchaseController::create suppliers error: ' . $e->getMessage());
            $suppliers = [];
        }

        $this->view('purchases.create', [
            'title' => 'Input Barang Masuk',
            'activeNav' => 'purchase',
            'salesReps' => $salesReps,
            'suppliers' => $suppliers,
        ]);
    }

    public function show(string $id)
    {
        $model = new PurchaseModel();
        $purchase = $model->getDetails($id);
        
        if (!$purchase) {
            header('Location: ' . BASE_URL . 'purchases');
            exit;
        }

        $this->view('purchases.show', [
            'title' => 'Detail Pembelian',
            'activeNav' => 'purchase',
            'purchase' => $purchase,
        ]);
    }

    public function edit(string $id)
    {
        $model = new PurchaseModel();
        $purchase = $model->getDetails($id);
        
        if (!$purchase) {
            header('Location: ' . BASE_URL . 'purchases');
            exit;
        }

        $supplierModel = new SupplierModel();
        $salesRepModel = new SalesRepModel();

        $this->view('purchases.edit', [
            'title' => 'Edit Pembelian',
            'activeNav' => 'purchase',
            'purchase' => $purchase,
            'salesReps' => $salesRepModel->getAllWithSupplier($purchase['sales_rep_id']),
            'suppliers' => $supplierModel->all('name', 'ASC'),
        ]);
    }
}
