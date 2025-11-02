<?php
require_once '../config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['embed_key']) && isset($input['hostname'])) {
    $embed_key = $input['embed_key'];
    $hostname = $input['hostname'];
    $customer_name = $input['customer_name'] ?? 'Guest';
    $customer_email = $input['customer_email'] ?? 'guest@example.com';

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validate embed_key and hostname
        $sql = "SELECT id, site_url FROM websites WHERE embed_key = :embed_key AND is_verified = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':embed_key', $embed_key, PDO::PARAM_STR);
        $stmt->execute();

        $website = $stmt->fetch(PDO::FETCH_ASSOC);

        // Strict hostname validation in PHP
        if ($website && parse_url($website['site_url'], PHP_URL_HOST) === $hostname) {
            // Create a new conversation
            $sql_insert_conv = "INSERT INTO conversations (website_id, customer_name, customer_email) VALUES (:website_id, :customer_name, :customer_email)";
            $stmt_insert_conv = $pdo->prepare($sql_insert_conv);
            $stmt_insert_conv->bindParam(':website_id', $website['id'], PDO::PARAM_INT);
            $stmt_insert_conv->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
            $stmt_insert_conv->bindParam(':customer_email', $customer_email, PDO::PARAM_STR);
            $stmt_insert_conv->execute();

            $conversation_id = $pdo->lastInsertId();

            echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid embed key or hostname.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters.']);
}
