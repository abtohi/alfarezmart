<?php
/**
 * UserModel - User management & authentication
 */
class UserModel extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email AND is_active = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Find user by phone
     */
    public function findByPhone($phone)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE phone = :phone AND is_active = 1 LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        return $stmt->fetch();
    }

    /**
     * Find user by email OR phone (for flexible login)
     */
    public function findByCredential($credential)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE (email = :cred1 OR phone = :cred2) AND is_active = 1 
            LIMIT 1
        ");
        $stmt->execute([':cred1' => $credential, ':cred2' => $credential]);
        return $stmt->fetch();
    }

    /**
     * Verify password against stored hash
     */
    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Update last login info
     */
    public function recordLogin($userId)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET last_login_at = NOW(), 
                last_login_ip = :ip, 
                login_count = login_count + 1 
            WHERE id = :id
        ");
        $stmt->execute([':ip' => $ip, ':id' => $userId]);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password_hash = :hash WHERE id = :id");
        return $stmt->execute([':hash' => $hash, ':id' => $userId]);
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers($page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $total = $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT id, name, email, phone, user_level, is_active, last_login_at, login_count, created_at
            FROM {$this->table}
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Create new user with hashed password
     */
    public function createUser($data)
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        return $this->create($data);
    }
}
