<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Conversation #<?php echo $conversation['id']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .message { border-radius: 5px; padding: 10px; margin-bottom: 10px; }
        .message.customer { background-color: #e9ecef; }
        .message.agent { background-color: #007bff; color: white; text-align: right; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/admin/index.php?action=dashboard">Back to Dashboard</a>
</nav>
<div class="container mt-4">
    <h3>Conversation with <?php echo htmlspecialchars($conversation['customer_name']); ?></h3>
    <div id="messages-container" class="border p-3" style="height: 400px; overflow-y: scroll;">
        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['sender_type']; ?>">
                <strong><?php echo ucfirst($message['sender_type']); ?>:</strong>
                <p><?php echo htmlspecialchars($message['content']); ?></p>
                <small class="text-muted"><?php echo $message['timestamp']; ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="/admin/index.php?action=reply_conversation" method="post" class="mt-3">
        <?php require_once __DIR__ . '/../security.php'; csrf_input(); ?>
        <input type="hidden" name="conversation_id" value="<?php echo $conversation['id']; ?>">
        <div class="form-group">
            <textarea name="content" class="form-control" rows="3" placeholder="Type your reply..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Reply</button>
    </form>
</div>
</body>
</html>
