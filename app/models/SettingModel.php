<?php
class SettingModel extends Model { 
    protected $table = 'app_settings'; 

    private function ensureTable() {
        try {
            $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            if ($isSqlite) {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS app_settings (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        setting_key TEXT UNIQUE NOT NULL,
                        setting_value TEXT,
                        setting_type TEXT DEFAULT 'string',
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            }
        } catch (\Throwable $e) {}
    }
    
    public function get($key, $default = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = :key LIMIT 1");
            $stmt->execute([':key' => $key]);
            $row = $stmt->fetch();
            return $row ? $row['setting_value'] : $default;
        } catch (\Throwable $e) {
            $this->ensureTable();
            try {
                $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = :key LIMIT 1");
                $stmt->execute([':key' => $key]);
                $row = $stmt->fetch();
                return $row ? $row['setting_value'] : $default;
            } catch (\Throwable $e2) {
                return $default;
            }
        }
    }

    public function set($key, $value)
    {
        $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        try {
            if ($isSqlite) {
                $stmt = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value, updated_at) 
                    VALUES (:key, :val, CURRENT_TIMESTAMP)
                    ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP
                ");
                return $stmt->execute([':key' => $key, ':val' => $value]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value, updated_at) 
                    VALUES (:key, :val, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE setting_value = :val2, updated_at = CURRENT_TIMESTAMP
                ");
                return $stmt->execute([':key' => $key, ':val' => $value, ':val2' => $value]);
            }
        } catch (\Throwable $e) {
            $this->ensureTable();
            if ($isSqlite) {
                $stmt = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value, updated_at) 
                    VALUES (:key, :val, CURRENT_TIMESTAMP)
                    ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP
                ");
                return $stmt->execute([':key' => $key, ':val' => $value]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value, updated_at) 
                    VALUES (:key, :val, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE setting_value = :val2, updated_at = CURRENT_TIMESTAMP
                ");
                return $stmt->execute([':key' => $key, ':val' => $value, ':val2' => $value]);
            }
        }
    }
}
