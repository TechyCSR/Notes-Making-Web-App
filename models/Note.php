<?php
require_once __DIR__ . '/../config/database.php';

class Note {
    private $db;
    private $table = 'notes';
    private $lastError;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function create($userId, $title, $content, $tags = '[]') {
        try {
            $query = "INSERT INTO " . $this->table . " (user_id, title, content, tags) VALUES (:user_id, :title, :content, :tags)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":tags", $tags);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Note creation error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $userId, $title, $content, $tags = '[]') {
        try {
            $query = "UPDATE " . $this->table . " SET title = :title, content = :content, tags = :tags WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":tags", $tags);
            
            if ($stmt->execute()) {
                return true;
            }
            
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Note update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id, $userId) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                return true;
            }
            
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Note deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function findById($id, $userId) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND user_id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Note retrieval error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllByUser($userId) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Note retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    // Create a public share token for a note
    public function createShareToken($noteId, $userId) {
        try {
            // First, check if the note exists and belongs to the user
            $note = $this->findById($noteId, $userId);
            if (!$note) {
                $this->lastError = "Note not found or does not belong to you.";
                return false;
            }
            
            // Check if a share token already exists
            $existingToken = $this->getShareToken($noteId);
            if ($existingToken) {
                return $existingToken; // Return existing token if one exists
            }
            
            // Generate a random, secure token
            $shareToken = bin2hex(random_bytes(32));
            
            // Store the token in the shared_notes table
            $query = "INSERT INTO shared_notes (note_id, user_id, share_token) VALUES (:note_id, :user_id, :share_token)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":note_id", $noteId);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":share_token", $shareToken);
            
            if ($stmt->execute()) {
                return $shareToken;
            }
            
            $this->lastError = "Failed to create share token: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Share token creation error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if a note is already shared
    public function isNoteShared($noteId) {
        try {
            $query = "SELECT share_token FROM shared_notes WHERE note_id = :note_id LIMIT 1";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":note_id", $noteId);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? true : false;
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Check shared note error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get share token for a note
    public function getShareToken($noteId) {
        try {
            $query = "SELECT share_token FROM shared_notes WHERE note_id = :note_id LIMIT 1";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":note_id", $noteId);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['share_token'] : false;
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Get share token error: " . $e->getMessage());
            return false;
        }
    }
    
    // Remove share token for a note
    public function removeShareToken($noteId, $userId) {
        try {
            $query = "DELETE FROM shared_notes WHERE note_id = :note_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":note_id", $noteId);
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                return true;
            }
            
            $this->lastError = "Failed to remove share token: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Share token removal error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get a note by share token (for public access)
    public function getNoteByShareToken($shareToken) {
        try {
            $query = "SELECT n.*, u.username as owner_name, s.created_at as shared_at 
                     FROM " . $this->table . " n
                     JOIN shared_notes s ON n.id = s.note_id
                     JOIN users u ON n.user_id = u.id
                     WHERE s.share_token = :share_token LIMIT 1";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":share_token", $shareToken);
            
            if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            $this->lastError = "Failed to execute query: " . implode(", ", $stmt->errorInfo());
            return false;
        } catch(PDOException $e) {
            $this->lastError = "Database error: " . $e->getMessage();
            error_log("Shared note retrieval error: " . $e->getMessage());
            return false;
        }
    }
} 