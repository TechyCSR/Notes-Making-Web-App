<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'users';
    private $lastError;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function create($name, $email, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO " . $this->table . " (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":username", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashedPassword);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            return false;
        }
    }

    private function generateBaseUsername($display_name) {
        // Convert to lowercase and remove special characters
        $username = strtolower($display_name);
        $username = preg_replace('/[^a-z0-9]/', '', $username);
        
        // If username is empty after cleaning, use 'user'
        if (empty($username)) {
            $username = 'user';
        }
        
        return $username;
    }

    private function generateUniqueUsername($base) {
        $username = $base;
        $counter = 1;
        
        while ($this->usernameExists($username)) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }

    private function usernameExists($username) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            return true; // Assume exists on error to be safe
        }
    }

    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":id", $userId);
            
            if($stmt->execute()) {
                return true;
            }
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password']);
    }

    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function storeResetCode($email, $code) {
        try {
            // First check if the email exists
            $user = $this->findByEmail($email);
            if (!$user) {
                $this->lastError = "Email not found: " . $email;
                error_log("Reset code failed - Email not found: " . $email);
                return false;
            }
            
            // Ensure code is formatted as string with leading zeros
            $code = sprintf("%04d", $code);
            
            // Store the code and expiration time (30 minutes from now)
            $expire = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $userId = $user['id'];
            
            $query = "UPDATE " . $this->table . " SET reset_code = :code, reset_expires = :expire WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":code", $code);
            $stmt->bindParam(":expire", $expire);
            $stmt->bindParam(":id", $userId);
            
            $result = $stmt->execute();
            
            // Log the operation result
            if ($result) {
                error_log("Reset code stored successfully for user ID: " . $userId . " with code: " . $code);
            } else {
                error_log("Failed to store reset code for user ID: " . $userId . " - " . implode(", ", $stmt->errorInfo()));
                $this->lastError = "Failed to update reset code: " . implode(", ", $stmt->errorInfo());
            }
            
            return $result;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("DB Error in storeResetCode: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyResetCode($email, $code) {
        try {
            // First get the user record
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("Reset code verify failed - User not found with email: " . $email);
                return false; // User not found
            }
            
            // Ensure code is formatted as a 4-digit string with leading zeros
            $enteredCode = sprintf("%04d", $code);
            $dbCode = $user['reset_code'];
            
            // Log for debugging
            error_log("Reset code verify - DB: '{$dbCode}' (" . gettype($dbCode) . "), Entered: '{$enteredCode}' (" . gettype($enteredCode) . ")");
            
            // Check if codes match exactly and code hasn't expired
            if (strcmp($dbCode, $enteredCode) === 0 && strtotime($user['reset_expires']) > time()) {
                error_log("Reset code verified successfully for user: " . $user['id']);
                return $user;
            }
            
            if (strtotime($user['reset_expires']) <= time()) {
                error_log("Reset code expired for user: " . $user['id'] . " - Expiry: " . $user['reset_expires']);
                $this->lastError = "Reset code has expired";
            } else {
                error_log("Reset code mismatch - DB: '{$dbCode}', Entered: '{$enteredCode}'");
                $this->lastError = "Invalid reset code";
            }
            
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Reset code DB error: " . $e->getMessage());
            return false;
        }
    }
    
    public function resetPassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $query = "UPDATE " . $this->table . " 
                     SET password = :password, reset_code = NULL, reset_expires = NULL 
                     WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":id", $userId);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            return false;
        }
    }
} 