<?php
/**
 * Database Core Engine - Robust PDO Singleton Wrapper
 * Supports transaction management, parameter binding, connection retries, and clean error messages.
 */

class Database {
    private static $instance = null;
    private $pdo;
    private static $config = null;

    private function __construct() {
        if (self::$config === null) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
            $configFile = $basePath . '/config/database.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            } else {
                throw new Exception("Database configuration file not found at: {$configFile}");
            }
        }

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            self::$config['driver'],
            self::$config['host'],
            self::$config['port'],
            self::$config['database'],
            self::$config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'],
                self::$config['options']
            );
        } catch (PDOException $e) {
            // In development, show readable error; in production, log and show friendly message
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die("<div style='font-family:sans-serif;padding:20px;background:#ffebee;border-left:4px solid #f44336;margin:20px;border-radius:4px;'>
                    <h3 style='color:#c62828;margin:0 0 10px 0;'>Database Connection Error</h3>
                    <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>
                    <p><strong>Host:</strong> " . htmlspecialchars(self::$config['host'], ENT_QUOTES, 'UTF-8') . ":" . htmlspecialchars(self::$config['port'], ENT_QUOTES, 'UTF-8') . " | <strong>Database:</strong> " . htmlspecialchars(self::$config['database'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p style='color:#555;'><em>Tip: Please verify that MySQL is started in Laragon and the database `gym_master` is created and populated using <code>database/schema.sql</code>.</em></p>
                </div>");
            } else {
                error_log("Database Connection Error: " . $e->getMessage());
                die("A system error occurred. Please contact the administrator.");
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getPDO() {
        return self::getInstance()->pdo;
    }

    /**
     * Delegate instance calls to internal PDO instance (prepare, lastInsertId, etc.)
     */
    public function __call($method, $args) {
        if ($this->pdo && method_exists($this->pdo, $method)) {
            return call_user_func_array([$this->pdo, $method], $args);
        }
        throw new BadMethodCallException("Method {$method} does not exist on Database or PDO instance.");
    }

    /**
     * Prepare and execute a parameterized query
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    public static function query($query, $params = []) {
        $stmt = self::getPDO()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row
     */
    public static function fetchOne($query, $params = []) {
        $stmt = self::query($query, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows
     */
    public static function fetchAll($query, $params = []) {
        $stmt = self::query($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single scalar value
     */
    public static function fetchValue($query, $params = []) {
        $stmt = self::query($query, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Execute an INSERT query and return last insert ID
     */
    public static function insert($query, $params = []) {
        self::query($query, $params);
        return (int) self::getPDO()->lastInsertId();
    }

    /**
     * Execute an UPDATE or DELETE query and return affected rows
     */
    public static function execute($query, $params = []) {
        $stmt = self::query($query, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin Database Transaction
     */
    public static function beginTransaction() {
        return self::getPDO()->beginTransaction();
    }

    /**
     * Commit Transaction
     */
    public static function commit() {
        return self::getPDO()->commit();
    }

    /**
     * Rollback Transaction
     */
    public static function rollBack() {
        return self::getPDO()->rollBack();
    }

    /**
     * Check if database table exists
     */
    public static function tableExists($tableName) {
        try {
            $stmt = self::query("SHOW TABLES LIKE :table", [':table' => $tableName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
