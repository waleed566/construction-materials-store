<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class Model
{
    protected $table;
    protected $pdo;
    protected $fillable = [];

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getConnection();
    }

    /**
     * Get all records
     */
    public function all()
    {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Find by ID
     */
    public function find($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create new record
     */
    public function create($data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));

        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update record
     */
    public function update($id, $data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $updates = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data[':id'] = $id;

        $query = "UPDATE {$this->table} SET {$updates} WHERE id = :id";
        $stmt = $this->pdo->prepare($query);

        return $stmt->execute($data);
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Execute custom query
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count records
     */
    public function count($where = '')
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        if ($where) {
            $query .= " WHERE {$where}";
        }
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}