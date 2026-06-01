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

        $debtSources = $debtModel->getDebtSources();

        $this->view('debts.index', [
            'title' => 'Catatan Hutang',
            'activeNav' => 'home',
            'suppliers' => $suppliers,
            'customerTypes' => $customerTypes,
            'debtSources' => $debtSources
        ]);
    }
}
