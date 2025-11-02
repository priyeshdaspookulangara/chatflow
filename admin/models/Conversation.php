<?php
require_once __DIR__ . '/../../config.php';

class Conversation {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function findAll() {
        $stmt = $this->pdo->query("
            SELECT c.*, w.site_url, u.username as agent_name
            FROM conversations c
            JOIN websites w ON c.website_id = w.id
            LEFT JOIN users u ON c.user_id = u.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByAgent($agent_id) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, w.site_url, u.username as agent_name
            FROM conversations c
            JOIN websites w ON c.website_id = w.id
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.user_id = :agent_id
            ORDER BY c.created_at DESC
        ");
        $stmt->bindParam(':agent_id', $agent_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMessages($conversation_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE conversation_id = :id ORDER BY timestamp ASC");
        $stmt->bindParam(':id', $conversation_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMessage($conversation_id, $sender_type, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO messages (conversation_id, sender_type, content) VALUES (:conv_id, :sender, :content)");
        $stmt->bindParam(':conv_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':sender', $sender_type, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function assignAgent($conversation_id, $agent_id) {
        $stmt = $this->pdo->prepare("UPDATE conversations SET user_id = :agent_id WHERE id = :conv_id");
        $stmt->bindParam(':agent_id', $agent_id, PDO::PARAM_INT);
        $stmt->bindParam(':conv_id', $conversation_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
