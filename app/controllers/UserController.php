<?php
/**
 * UserController - User Management (Superadmin Only)
 */
class UserController extends Controller
{
    public function index()
    {
        // Only superadmin can access
        $level = $_SESSION['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->redirect(BASE_URL . 'settings');
            return;
        }

        $model = new UserModel();
        $users = $model->getAllUsers(1, 100);

        $this->view('users.index', [
            'title'     => 'Manajemen User',
            'activeNav' => 'home',
            'users'     => $users,
            'csrfToken' => (new Security())->getCSRFToken(),
        ]);
    }
}
