<?php
/**
 * Database class - PDO wrapper
 */
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $charset;
    private $port;
    private $pdo;
    private $stmt;
    
    /**
     * Constructor - Connect to the database
     */

    public function __construct() {
        global $db_config;
        
        $this->host = $db_config['host'];
        $this->username = $db_config['username'];
        $this->password = $db_config['password'];
        $this->database = $db_config['database'];
        $this->charset = $db_config['charset'];
        $this->port = $db_config['port'];
        
        // Set DSN
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset};port={$this->port}";
        
        // Set options
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // Create PDO instance
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare a statement
     * 
     * @param string $sql The SQL query
     * @return void
     */
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }
    
    /**
     * Bind values to prepared statement
     * 
     * @param string $param The parameter to bind
     * @param mixed $value The value to bind
     * @param mixed $type The data type (optional)
     * @return void
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
    }
    
    /**
     * Execute the prepared statement
     * 
     * @return bool True on success, false on failure
     */
    public function execute() {
        return $this->stmt->execute();
    }
    
    /**
     * Get result set as array of objects
     * 
     * @return array The result set
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    /**
     * Get single record as object
     * 
     * @return object The record
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    /**
     * Get row count
     * 
     * @return int The row count
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Get last insert ID
     * 
     * @return int The last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Roll back a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
