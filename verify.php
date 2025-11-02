<?php
require_once 'config.php';

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if token exists
        $sql = "SELECT id, embed_key FROM websites WHERE email_verification_token = :token AND is_verified = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        $website = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($website) {
            // Token is valid, update the database
            $sql_update = "UPDATE websites SET is_verified = 1, email_verification_token = NULL WHERE id = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':id', $website['id'], PDO::PARAM_INT);
            $stmt_update->execute();

            echo "Email verified successfully! You can now embed the chat widget on your website.<br><br>";

            // Display the embed snippet
            $embed_snippet = htmlspecialchars("
<script>
    window.ChatFlowConfig = {
        embedKey: '{$website['embed_key']}'
    };
</script>
<script src='http://{$_SERVER['HTTP_HOST']}/chatflow-widget.js' defer></script>
            ");

            echo "<h3>Embed Snippet:</h3>";
            echo "<pre><code>{$embed_snippet}</code></pre>";

        } else {
            echo "Invalid or expired verification token.";
        }

    } catch (PDOException $e) {
        die("ERROR: Database error. " . $e->getMessage());
    }
} else {
    echo "No verification token provided.";
}
