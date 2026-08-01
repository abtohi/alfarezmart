<?php
/**
 * UserController - User Management & Access Control (Superadmin Only)
 */
class UserController extends Controller
{
    public function index()
    {
        $this->requireSuperadmin();

        $model = new UserModel();
        $users = $model->getAllUsers(1, 100);

        $settingModel = new SettingModel();
        $adminPermissionsJson = $settingModel->get('role_permissions_admin', null);
        $staffPermissionsJson = $settingModel->get('role_permissions_staff', null);

        $defaultAdmin = [
            'finance', 'reports', 'debts', 'purchases', 'suppliers',
            'customers', 'products', 'catalog', 'multivariant',
            'product_history', 'supplier_analysis', 'export_data', 'order_estimate'
        ];
        $defaultStaff = [
            'suppliers', 'customers', 'products', 'catalog',
            'product_history', 'supplier_analysis', 'order_estimate'
        ];

        $adminPermissions = $adminPermissionsJson !== null ? (json_decode($adminPermissionsJson, true) ?: []) : $defaultAdmin;
        $staffPermissions = $staffPermissionsJson !== null ? (json_decode($staffPermissionsJson, true) ?: []) : $defaultStaff;

        $this->view('users.index', [
            'title'            => 'Manajemen User & Kontrol Akses',
            'activeNav'        => 'home',
            'users'            => $users,
            'adminPermissions' => $adminPermissions,
            'staffPermissions' => $staffPermissions,
            'csrfToken'        => (new Security())->getCSRFToken(),
        ]);
    }

    public function saveAccessControl()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();

        $adminPermissions = $this->input('admin_permissions', []);
        $staffPermissions = $this->input('staff_permissions', []);

        if (!is_array($adminPermissions)) $adminPermissions = [];
        if (!is_array($staffPermissions)) $staffPermissions = [];

        $adminPermissions = array_values(array_filter(array_map('trim', $adminPermissions)));
        $staffPermissions = array_values(array_filter(array_map('trim', $staffPermissions)));

        $settingModel = new SettingModel();
        $settingModel->set('role_permissions_admin', json_encode($adminPermissions));
        $settingModel->set('role_permissions_staff', json_encode($staffPermissions));

        $this->json([
            'success' => true,
            'message' => 'Pengaturan Kontrol Akses Layanan berhasil disimpan.'
        ]);
    }
}
