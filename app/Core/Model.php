<?php
/**
 * Model base class
 */
class Model {
    protected $db;
    protected $table;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all records from the table
     *
     * @return array The records
     */
    public function getAll() {
        $this->db->query("SELECT * FROM {$this->table}");
        return $this->db->resultSet();
    }

    /**
     * Get a record by ID
     *
     * @param int $id The ID of the record
     * @return object The record
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Create a new record
     *
     * @param array $data The data to insert
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Build query
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);

        // Escape field names with backticks to handle reserved keywords
        $escapedFields = array_map(function($field) {
            return "`{$field}`";
        }, $fields);

        $fieldsStr = implode(', ', $escapedFields);
        $placeholdersStr = implode(', ', $placeholders);

        $this->db->query("INSERT INTO {$this->table} ({$fieldsStr}) VALUES ({$placeholdersStr})");

        // Bind values
        foreach ($data as $key => $value) {
            $this->db->bind(":{$key}", $value);
        }

        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Update a record
     *
     * @param int $id The ID of the record
     * @param array $data The data to update
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        // Build query
        $fields = array_keys($data);

        // Escape field names with backticks to handle reserved keywords
        $escapedFields = array_map(function($field) {
            return "`{$field}`";
        }, $fields);

        $setStr = implode(' = ?, ', $escapedFields) . ' = ?';

        $this->db->query("UPDATE {$this->table} SET {$setStr} WHERE id = ?");

        // Bind values
        $values = array_values($data);
        $values[] = $id;

        foreach ($values as $i => $value) {
            $this->db->bind($i + 1, $value);
        }

        // Execute
        return $this->db->execute();
    }

    /**
     * Delete a record
     *
     * @param int $id The ID of the record
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Get records by a field
     *
     * @param string $field The field to search by
     * @param mixed $value The value to search for
     * @return array The records
     */
    public function getBy($field, $value) {
        $this->db->query("SELECT * FROM {$this->table} WHERE `{$field}` = :{$field}");
        $this->db->bind(":{$field}", $value);
        return $this->db->resultSet();
    }

    /**
     * Get a single record by a field
     *
     * @param string $field The field to search by
     * @param mixed $value The value to search for
     * @return object The record
     */
    public function getSingleBy($field, $value) {
        $this->db->query("SELECT * FROM {$this->table} WHERE `{$field}` = :{$field}");
        $this->db->bind(":{$field}", $value);
        return $this->db->single();
    }

    /**
     * Begin a transaction
     *
     * @return bool True on success, false on failure
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return bool True on success, false on failure
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Roll back a transaction
     *
     * @return bool True on success, false on failure
     */
    public function rollBack() {
        return $this->db->rollBack();
    }

    /**
     * Get the database instance
     *
     * @return Database The database instance
     */
    public function getDb() {
        return $this->db;
    }
}
