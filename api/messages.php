<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Send a new message
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['conversation_id'], $input['sender_type'], $input['content'])) {
            $sql = "INSERT INTO messages (conversation_id, sender_type, content) VALUES (:conv_id, :sender, :content)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':conv_id', $input['conversation_id'], PDO::PARAM_INT);
            $stmt->bindParam(':sender', $input['sender_type'], PDO::PARAM_STR);
            $stmt->bindParam(':content', $input['content'], PDO::PARAM_STR);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing parameters.']);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Fetch messages for a conversation
        if (isset($_GET['conversation_id'])) {
            $sql = "SELECT sender_type, content, timestamp FROM messages WHERE conversation_id = :conv_id ORDER BY timestamp ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':conv_id', $_GET['conversation_id'], PDO::PARAM_INT);
            $stmt->execute();

            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($messages);
        } else {
            echo json_encode([]);
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
