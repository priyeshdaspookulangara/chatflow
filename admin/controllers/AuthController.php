<?php
require_once '../config.php';
require_once 'models/User.php';

require_once 'security.php';

class AuthController {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validate_csrf_token();
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $user_model = new User($this->pdo);
            $user = $user_model->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = $user['status']; // Set status in session
                header('Location: /admin/index.php?action=dashboard');
                exit;
            } else {
                // Redirect back to login with an error
                header('Location: /admin/index.php?error=1');
                exit;
            }
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: /admin/index.php');
        exit;
    }

    public static function requireAuth() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: /admin/index.php');
            exit;
        }
    }

    public static function requireAdmin() {
        self::requireAuth();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }
    }
}
