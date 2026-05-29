<?php
/**
 * DebtController - Controller for Catatan Hutang (Pelanggan & Toko)
 */
class DebtController extends Controller
{
    public function index()
    {
        $this->requireSuperadmin();
        $supplierModel = new SupplierModel();
        $suppliers = $supplierModel->all('name', 'ASC');

        $debtModel = new DebtModel();
        $customerTypes = $debtModel->getCustomerTypes();

        $this->view('debts.index', [
            'title' => 'Catatan Hutang',
            'activeNav' => 'home', // Keeps home menu active in bottom-nav since it's an extension of dashboard
            'suppliers' => $suppliers,
            'customerTypes' => $customerTypes
        ]);
    }
}
