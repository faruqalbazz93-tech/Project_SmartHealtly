<?php

namespace App\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected ?PDO $db = null;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get raw PDO database connection
     */
    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * Begin Database Transaction
     */
    public function beginTransaction(): bool
    {
        if (!$this->db->inTransaction()) {
            return $this->db->beginTransaction();
        }
        return true;
    }

    /**
     * Commit Database Transaction
     */
    public function commit(): bool
    {
        if ($this->db->inTransaction()) {
            return $this->db->commit();
        }
        return true;
    }

    /**
     * Rollback Database Transaction
     */
    public function rollBack(): bool
    {
        if ($this->db->inTransaction()) {
            return $this->db->rollBack();
        }
        return true;
    }

    /**
     * Check if inside Transaction
     */
    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }

    /**
     * Get all records
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    /**
     * Find a record by primary key
     */
    public function find(int|string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find single record by column
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = :val LIMIT 1");
        $stmt->execute(['val' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get records matching conditions
     */
    public function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = :val ORDER BY {$orderBy}");
        $stmt->execute(['val' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Create new record
     */
    public function create(array $data): int|string|false
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update record by primary key
     */
    public function update(int|string $id, array $data): bool
    {
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "`{$key}` = :{$key}";
        }
        $setSql = implode(', ', $setClauses);
        $sql = "UPDATE `{$this->table}` SET {$setSql} WHERE `{$this->primaryKey}` = :pk_id";
        
        $data['pk_id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Delete record by primary key
     */
    public function delete(int|string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count total records
     */
    public function count(string $whereClause = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($whereClause) {
            $sql .= " WHERE {$whereClause}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Execute custom query
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute custom single row query
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute custom non-select query (insert, update, delete)
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
