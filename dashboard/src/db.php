<?php
/**
 * Database Connection
 * PHP 7.2 compatible
 */

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $config = require __DIR__ . '/config.php';
        
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;sslmode=require",
            $config['db']['host'],
            $config['db']['port'],
            $config['db']['database']
        );
        
        try {
            $pdo = new PDO(
                $dsn,
                $config['db']['username'],
                $config['db']['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please check configuration.');
        }
    }
    
    return $pdo;
}

/**
 * Execute a query with parameters
 */
function query($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Get a single row
 */
function queryOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

/**
 * Get all rows
 */
function queryAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

/**
 * Execute a query (INSERT, UPDATE, DELETE)
 */
function execute($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->rowCount();
}

