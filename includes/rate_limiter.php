<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class RateLimiter {
    private $db;
    private $table = 'rate_limits';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function checkLimit($ipAddress, $endpoint) {
        try {
            // Clean up old records first
            $this->cleanupOldRecords();

            // Get current attempts
            $query = "SELECT * FROM " . $this->table . " WHERE ip_address = :ip AND endpoint = :endpoint AND last_attempt > DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND) LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                // First attempt
                $query = "INSERT INTO " . $this->table . " (ip_address, endpoint, attempts) VALUES (:ip, :endpoint, 1)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":ip", $ipAddress);
                $stmt->bindParam(":endpoint", $endpoint);
                $stmt->execute();
                return true;
            }
            
            if ($record['attempts'] >= MAX_LOGIN_ATTEMPTS) {
                return false; // Rate limit exceeded
            }
            
            // Increment attempts
            $query = "UPDATE " . $this->table . " SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE ip_address = :ip AND endpoint = :endpoint";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            $stmt->execute();
            
            return true;
        } catch(PDOException $e) {
            return true; // On error, allow the request to prevent blocking legitimate users
        }
    }

    private function cleanupOldRecords() {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE last_attempt < DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND)";
            $this->db->exec($query);
        } catch(PDOException $e) {
            // Log error if needed
        }
    }

    public function getRemainingAttempts($ipAddress, $endpoint) {
        try {
            $query = "SELECT attempts FROM " . $this->table . " 
                     WHERE ip_address = :ip AND endpoint = :endpoint 
                     AND last_attempt > DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND) 
                     LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                return MAX_LOGIN_ATTEMPTS;
            }
            
            return max(0, MAX_LOGIN_ATTEMPTS - $record['attempts']);
        } catch(PDOException $e) {
            return MAX_LOGIN_ATTEMPTS; // On error, allow maximum attempts
        }
    }

    public function getTimeToReset($ipAddress, $endpoint) {
        try {
            $query = "SELECT TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(last_attempt, INTERVAL " . LOCKOUT_TIME . " SECOND)) as time_left 
                     FROM " . $this->table . " 
                     WHERE ip_address = :ip AND endpoint = :endpoint 
                     AND last_attempt > DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND) 
                     LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record || $record['time_left'] <= 0) {
                return 0;
            }
            
            return (int)$record['time_left'];
        } catch(PDOException $e) {
            return 0; // On error, allow immediate retry
        }
    }

    public function resetLimit($ipAddress, $endpoint) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE ip_address = :ip AND endpoint = :endpoint";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Increment the attempt counter for a specific IP and endpoint
     *
     * @param string $ipAddress The IP address
     * @param string $endpoint The endpoint being accessed
     * @return bool True on success, false on failure
     */
    public function increment($ipAddress, $endpoint) {
        try {
            // Check if record exists
            $query = "SELECT id FROM " . $this->table . " 
                     WHERE ip_address = :ip AND endpoint = :endpoint 
                     AND last_attempt > DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND) 
                     LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                // First attempt - create new record
                $query = "INSERT INTO " . $this->table . " (ip_address, endpoint, attempts) VALUES (:ip, :endpoint, 1)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":ip", $ipAddress);
                $stmt->bindParam(":endpoint", $endpoint);
                return $stmt->execute();
            }
            
            // Increment existing record
            $query = "UPDATE " . $this->table . " 
                     SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP 
                     WHERE ip_address = :ip AND endpoint = :endpoint";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ip", $ipAddress);
            $stmt->bindParam(":endpoint", $endpoint);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
} 