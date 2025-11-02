<?php
require_once 'AuthController.php';
require_once 'models/Conversation.php';
require_once 'models/User.php';

require_once 'security.php';

class DashboardController {
    private $pdo;

    public function __construct() {
        AuthController::requireAuth();
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function index() {
        $conversation_model = new Conversation($this->pdo);
        $user_model = new User($this->pdo);

        $conversations = ($_SESSION['role'] === 'admin')
            ? $conversation_model->findAll()
            : $conversation_model->findByAgent($_SESSION['user_id']);

        $agents = $user_model->findAll();

        // Load the view
        require_once 'views/dashboard.php';
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
            validate_csrf_token();
            $user_model = new User($this->pdo);
            $user_model->setStatus($_SESSION['user_id'], $_POST['status']);
            $_SESSION['status'] = $_POST['status']; // Update session status
            header('Location: /admin/index.php?action=dashboard');
            exit;
        }
    }

    public function assign() {
        AuthController::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conversation_id'], $_POST['agent_id'])) {
            validate_csrf_token();
            $conversation_model = new Conversation($this->pdo);
            $conversation_model->assignAgent($_POST['conversation_id'], $_POST['agent_id']);
            header('Location: /admin/index.php?action=dashboard');
            exit;
        }
    }
}
