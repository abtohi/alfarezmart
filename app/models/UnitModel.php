<?php
class UnitModel extends Model 
{ 
    protected $table = 'units'; 

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE LOWER(name) = LOWER(:name) LIMIT 1");
        $stmt->execute([':name' => trim($name)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}
