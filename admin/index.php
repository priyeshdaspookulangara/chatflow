<?php
session_start();

// Basic router
$action = $_GET['action'] ?? 'login';

// If user is logged in and tries to access login page, redirect to dashboard
if (isset($_SESSION['user_id']) && ($action === 'login' || $action === 'login_handler')) {
    header('Location: /admin/index.php?action=dashboard');
    exit;
}

switch ($action) {
    case 'login':
        require_once 'views/login.php';
        break;
    case 'login_handler':
        require_once 'controllers/AuthController.php';
        (new AuthController())->login();
        break;
    case 'dashboard':
        require_once 'controllers/DashboardController.php';
        (new DashboardController())->index();
        break;
    case 'logout':
        require_once 'controllers/AuthController.php';
        (new AuthController())->logout();
        break;
    case 'view_conversation':
        require_once 'controllers/ConversationController.php';
        (new ConversationController())->view();
        break;
    case 'reply_conversation':
        require_once 'controllers/ConversationController.php';
        (new ConversationController())->reply();
        break;
    case 'assign_conversation':
        require_once 'controllers/DashboardController.php';
        (new DashboardController())->assign();
        break;
    case 'update_status':
        require_once 'controllers/DashboardController.php';
        (new DashboardController())->updateStatus();
        break;
    case 'manage_agents':
        require_once 'controllers/AgentController.php';
        (new AgentController())->index();
        break;
    case 'add_agent':
        require_once 'controllers/AgentController.php';
        (new AgentController())->add();
        break;
    default:
        http_response_code(404);
        echo "Page not found.";
        break;
}
