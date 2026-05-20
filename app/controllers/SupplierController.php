<?php
/**
 * SupplierController - Manajemen Supplier & Sales
 */
class SupplierController extends Controller
{
    public function index()
    {
        $supplierModel = new SupplierModel();
        $supplierTypeModel = new SupplierTypeModel();
        $suppliers = $supplierModel->getAllWithType();
        $supplierTypes = $supplierTypeModel->all('name', 'ASC');
        
        $this->view('suppliers.index', [
            'title' => 'Supplier',
            'activeNav' => 'home',
            'suppliers' => $suppliers,
            'supplierTypes' => $supplierTypes,
        ]);
    }
}
