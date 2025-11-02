<?php
require_once 'AuthController.php';
require_once 'models/User.php';

class AgentController {
    public function __construct() {
        AuthController::requireAdmin();
    }

    public function index() {
        $user_model = new User();
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
                $user_model = new User();
                $user_model->create($username, $password, $role);
            }
            header('Location: /admin/index.php?action=manage_agents');
            exit;
        } else {
            require_once 'views/agents/add.php';
        }
    }
}
