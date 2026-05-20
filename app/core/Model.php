<?php
/**
 * Base Model - Parent class for all models
 * 
 * ATURAN: Semua query SQL HANYA boleh ada di Model.
 * WAJIB menggunakan PDO Prepared Statements.
 */

if (!class_exists('Model')) {
    class Model
    {
        protected $db;
        protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all records
     */
    public function all($orderBy = 'id', $direction = 'ASC')
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get paginated records
     */
    public function paginate($page = 1, $perPage = 20, $orderBy = 'id', $direction = 'DESC')
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        // Count total
        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        // Get data
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction} LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Insert new record
     */
    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        
        foreach ($data as $key => $value) {
            if ($value === null) {
                $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update record by ID
     */
    public function update($id, array $data)
    {
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "$key = :$key";
        }
        $setString = implode(', ', $setParts);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setString} WHERE id = :id");
        $data['id'] = $id;

        foreach ($data as $key => $value) {
            if ($value === null) {
                $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }

        return $stmt->execute();
    }

    /**
     * Delete record by ID
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Search records by column
     */
    public function where($column, $value, $operator = '=')
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value"
        );
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Search with LIKE
     */
    public function search($column, $keyword)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} LIKE :keyword"
        );
        $stmt->execute([':keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Count records
     */
    public function count()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->db->rollBack();
    }
    }
}
