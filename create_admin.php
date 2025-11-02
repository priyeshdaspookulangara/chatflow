<?php
// Standalone script to create the first admin user.
// Place this in the root directory of the project and run it ONCE from your browser or CLI.
// IMPORTANT: Delete this file after you have successfully created the admin user.

require_once 'config.php';

// --- Admin User Configuration ---
$admin_username = 'admin';
$admin_password = 'Password123!'; // Please change this after your first login
$admin_role = 'admin';

try {
    // 1. Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Check if the user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->bindParam(':username', $admin_username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        echo "Admin user '{$admin_username}' already exists. No action taken.\n";
        exit;
    }

    // 3. Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // 4. Prepare and execute the INSERT statement
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (:username, :password, :role, 'offline')");
    $stmt->bindParam(':username', $admin_username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $stmt->bindParam(':role', $admin_role, PDO::PARAM_STR);

    // 5. Execute and report result
    if ($stmt->execute()) {
        echo "Successfully created admin user!\n";
        echo "--------------------------\n";
        echo "Username: " . $admin_username . "\n";
        echo "Password: " . $admin_password . "\n";
        echo "--------------------------\n";
        echo "You can now log in at /admin/index.php\n";
        echo "IMPORTANT: Please delete this script (create_admin.php) now.\n";
    } else {
        echo "Failed to create admin user.\n";
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
