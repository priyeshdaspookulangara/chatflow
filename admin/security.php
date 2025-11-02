<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate a CSRF token
function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Handle token mismatch - e.g., die, redirect, or show an error
        die('CSRF token validation failed.');
    }
    // Invalidate the token after use to prevent replay attacks
    unset($_SESSION['csrf_token']);
}

// Function to output the CSRF token input field
function csrf_input() {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}
