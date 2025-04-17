<?php
require_once 'config.php'; // Include the configuration file

class Database {
    private $conn;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            $this->conn = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (PDOException $e) {
            // In production, you might want to log this instead of displaying it
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
    
    public function __destruct() {
        // Close the connection when the object is destroyed
        $this->conn = null;
    }
}