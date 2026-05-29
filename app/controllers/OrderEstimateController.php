<?php
/**
 * OrderEstimateController - Fitur "Hitung Orderan"
 * Estimasi belanja ke supplier + format pesan WhatsApp untuk copy-paste.
 */
class OrderEstimateController extends Controller
{
    public function index()
    {
        // Tersedia untuk semua role (staff perlu untuk siapkan orderan).
        $supplierModel = new SupplierModel();
        $suppliers = $supplierModel->all('name', 'ASC');

        $this->view('orders.hitung', [
            'title' => 'Hitung Orderan',
            'activeNav' => 'home',
            'suppliers' => $suppliers,
        ]);
    }
}
