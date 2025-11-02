<?php
require_once 'AuthController.php';
require_once 'models/Conversation.php';

require_once 'security.php';

class ConversationController {
    public function __construct() {
        AuthController::requireAuth();
    }

    public function view() {
        if (isset($_GET['id'])) {
            $conversation_model = new Conversation();
            $conversation = $conversation_model->findById($_GET['id']);
            $messages = $conversation_model->getMessages($_GET['id']);

            // Authorization: either admin or assigned agent
            if ($_SESSION['role'] !== 'admin' && $conversation['user_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo "Forbidden";
                exit;
            }

            require_once 'views/conversation.php';
        } else {
            http_response_code(404);
            echo "Conversation not found.";
        }
    }

    public function reply() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conversation_id'], $_POST['content'])) {
            validate_csrf_token();
            $conversation_model = new Conversation();
            $conversation_model->addMessage($_POST['conversation_id'], 'agent', trim($_POST['content']));
            header('Location: /admin/index.php?action=view_conversation&id=' . $_POST['conversation_id']);
            exit;
        }
    }
}
