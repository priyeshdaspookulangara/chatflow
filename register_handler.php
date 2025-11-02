<?php
require_once 'config.php';

// Function to generate a UUID
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_email = trim($_POST['owner_email']);
    $site_url = trim($_POST['site_url']);

    if (filter_var($owner_email, FILTER_VALIDATE_EMAIL) && filter_var($site_url, FILTER_VALIDATE_URL)) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $embed_key = generate_uuid();
            $email_verification_token = generate_uuid();

            $sql = "INSERT INTO websites (owner_email, site_url, embed_key, email_verification_token) VALUES (:owner_email, :site_url, :embed_key, :token)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':owner_email', $owner_email, PDO::PARAM_STR);
            $stmt->bindParam(':site_url', $site_url, PDO::PARAM_STR);
            $stmt->bindParam(':embed_key', $embed_key, PDO::PARAM_STR);
            $stmt->bindParam(':token', $email_verification_token, PDO::PARAM_STR);

            $stmt->execute();

            // NOTE: This is a simulation of email verification.
            // In a production environment, you would use a library like PHPMailer to send a real email.
            $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . $email_verification_token;
            echo "Registration successful! Please check your email for the verification link.<br>";
            echo "Verification Link: <a href='{$verification_link}'>{$verification_link}</a>";

        } catch (PDOException $e) {
            die("ERROR: Could not execute. " . $e->getMessage());
        }
    } else {
        echo "Invalid email or URL.";
    }
}
