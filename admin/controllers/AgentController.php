<?php
require_once 'AuthController.php';
require_once 'models/User.php';

class AgentController {
    private $pdo;

    public function __construct() {
        AuthController::requireAdmin();
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function index() {
        $user_model = new User($this->pdo);
        $agents = $user_model->findAll();
        require_once 'views/agents/index.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validate_csrf_token();
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $role = trim($_POST['role']);

            if (!empty($username) && !empty($password) && !empty($role)) {
                $user_model = new User($this->pdo);
                $user_model->create($username, $password, $role);
            }
            header('Location: /admin/index.php?action=manage_agents');
            exit;
        } else {
            require_once 'views/agents/add.php';
        }
    }
}
