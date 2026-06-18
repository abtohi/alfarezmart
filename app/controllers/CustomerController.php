<?php
/**
 * CustomerController - Manajemen Pelanggan
 */
class CustomerController extends Controller
{
    public function index()
    {
        // Load DebtModel since it contains customer operations right now
        // If there's a dedicated CustomerModel, we would use that instead.
        require_once __DIR__ . '/../models/DebtModel.php';
        $debtModel = new DebtModel();
        
        // Fetch customer types for the form
        $customerTypes = $debtModel->getCustomerTypes();

        $this->view('customers.index', [
            'title' => 'Daftar Pelanggan',
            'activeNav' => 'home',
            'customerTypes' => $customerTypes,
            'csrfToken' => (new Security())->getCSRFToken()
        ]);
    }
}
