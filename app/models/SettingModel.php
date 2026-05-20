<?php
class SettingModel extends Model { 
    protected $table = 'app_settings'; 
    
    public function get($key, $default = null)
    {
        $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    public function set($key, $value)
    {
        $stmt = $this->db->prepare("
            INSERT INTO app_settings (setting_key, setting_value, updated_at) 
            VALUES (:key, :val, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE setting_value = :val2, updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([':key' => $key, ':val' => $value, ':val2' => $value]);
    }
}
