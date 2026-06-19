<?php
/**
 * AuthController - Login, Logout, Session management
 */
class AuthController extends Controller
{
    /**
     * Show login page
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL);
            return;
        }

        // Render login without main layout
        $csrfToken = $this->security->getCSRFToken();
        require APP_PATH . '/views/auth/login.php';
    }

    /**
     * Handle login POST request
     */
    public function login()
    {
        $this->validateCSRF();

        $credential = $this->input('credential');
        $password = $this->input('password');

        if (empty($credential) || empty($password)) {
            $this->json(['error' => 'Email/No HP dan Password wajib diisi'], 400);
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findByCredential($credential);

        if (!$user || !$userModel->verifyPassword($password, $user['password_hash'])) {
            $this->json(['error' => 'Email/No HP atau Password salah'], 401);
            return;
        }

        if (!$user['is_active']) {
            $this->json(['error' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.'], 403);
            return;
        }

        // Enforce staff schedule on login
        if ($user['user_level'] === 'staff') {
            if (!$this->isStaffScheduleValid($user)) {
                $this->json(['error' => 'Anda berada di luar jadwal kerja yang diizinkan.'], 403);
                return;
            }
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_level'] = $user['user_level'];
        $_SESSION['work_days'] = $user['work_days'];
        $_SESSION['work_start'] = $user['work_start'];
        $_SESSION['work_end'] = $user['work_end'];
        $_SESSION['login_time'] = time();

        // Record login
        $userModel->recordLogin($user['id']);

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $this->json([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang, ' . $user['name'],
            'redirect' => BASE_URL,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'level' => $user['user_level'],
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        $redirectUrl = BASE_URL . 'login';
        if (isset($_GET['reason'])) {
            $redirectUrl .= '?error=' . urlencode($_GET['reason']);
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Check if user is authenticated (middleware)
     * Call this at the beginning of protected routes
     */
    public static function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            // Check if it's an API request
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/api/') !== false) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized. Please login.']);
                exit;
            }
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Check if user has required level
     */
    public static function requireLevel($levels = [])
    {
        self::requireAuth();
        if (!empty($levels) && !in_array($_SESSION['user_level'] ?? '', $levels)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Akses ditolak. Level akun Anda tidak memiliki izin.']);
            exit;
        }
    }

    /**
     * Get current logged-in user info
     */
    public static function currentUser()
    {
        if (!isset($_SESSION['user_id'])) return null;
        
        // Dynamic schedule check on every action/page change
        if (($_SESSION['user_level'] ?? '') === 'staff') {
            $user = [
                'work_days'  => $_SESSION['work_days'] ?? null,
                'work_start' => $_SESSION['work_start'] ?? null,
                'work_end'   => $_SESSION['work_end'] ?? null,
            ];
            
            // To be able to call non-static method, we instantiate AuthController or make isStaffScheduleValid static
            if (!self::checkStaffScheduleStatic($user)) {
                // Auto logout
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params['path'], $params['domain'],
                        $params['secure'], $params['httponly']
                    );
                }
                session_destroy();
                
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                if (strpos($uri, '/api/') !== false) {
                    http_response_code(401);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Sesi berakhir karena di luar jadwal kerja.']);
                    exit;
                }
                header('Location: ' . BASE_URL . 'login?error=' . urlencode('Sesi berakhir karena di luar jam kerja'));
                exit;
            }
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'level' => $_SESSION['user_level'],
        ];
    }

    private function isStaffScheduleValid($user)
    {
        return self::checkStaffScheduleStatic($user);
    }

    private static function checkStaffScheduleStatic($user)
    {
        if (empty($user['work_days']) && empty($user['work_start']) && empty($user['work_end'])) {
            return true; // No restrictions set
        }

        date_default_timezone_set('Asia/Jakarta');
        
        $currentDayEng = date('l');
        $mapDays = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $currentDayId = $mapDays[$currentDayEng];
        
        $currentTimeStr = date('H:i:s');
        
        // Check days
        if (!empty($user['work_days'])) {
            $days = json_decode($user['work_days'], true);
            if (is_array($days) && !in_array($currentDayId, $days)) {
                return false;
            }
        }
        
        // Check time
        if (!empty($user['work_start']) && $currentTimeStr < $user['work_start']) {
            return false;
        }
        if (!empty($user['work_end']) && $currentTimeStr > $user['work_end']) {
            return false;
        }
        
        return true;
    }
}
