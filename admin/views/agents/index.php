<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Agents</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/admin/index.php?action=dashboard">Back to Dashboard</a>
</nav>
<div class="container mt-4">
    <h3>Manage Agents</h3>
    <a href="/admin/index.php?action=add_agent" class="btn btn-success mb-3">Add New Agent</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agents as $agent): ?>
            <tr>
                <td><?php echo $agent['id']; ?></td>
                <td><?php echo htmlspecialchars($agent['username']); ?></td>
                <td><?php echo htmlspecialchars($agent['role']); ?></td>
                <td><span class="badge badge-<?php echo $agent['status'] === 'online' ? 'success' : 'secondary'; ?>"><?php echo $agent['status']; ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
