<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">ChatFlow</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <span class="navbar-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            </li>
        </ul>
        <a href="/admin/index.php?action=logout" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-9">
            <h3>Conversations</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Website</th>
                        <th>Status</th>
                        <th>Assigned Agent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conversations as $convo): ?>
                    <tr>
                        <td><?php echo $convo['id']; ?></td>
                        <td><?php echo htmlspecialchars($convo['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($convo['site_url']); ?></td>
                        <td><span class="badge badge-primary"><?php echo $convo['status']; ?></span></td>
                        <td><?php echo $convo['agent_name'] ?? 'Unassigned'; ?></td>
                        <td>
                            <a href="/admin/index.php?action=view_conversation&id=<?php echo $convo['id']; ?>" class="btn btn-sm btn-info">View</a>
                            <?php if ($_SESSION['role'] === 'admin' && !$convo['user_id']): ?>
                            <form action="/admin/index.php?action=assign_conversation" method="post" class="d-inline">
                                <?php csrf_input(); ?>
                                <input type="hidden" name="conversation_id" value="<?php echo $convo['id']; ?>">
                                <select name="agent_id" required>
                                    <option value="">Assign to...</option>
                                    <?php foreach($agents as $agent): if($agent['status'] === 'online'): ?>
                                    <option value="<?php echo $agent['id']; ?>"><?php echo htmlspecialchars($agent['username']); ?></option>
                                    <?php endif; endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sidebar -->
        <div class="col-md-3">
            <h3>Agent Status</h3>
            <form action="/admin/index.php?action=update_status" method="post">
                <?php require_once __DIR__ . '/../security.php'; csrf_input(); ?>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="online" <?php echo ($_SESSION['status'] ?? '') === 'online' ? 'selected' : ''; ?>>Online</option>
                    <option value="offline" <?php echo ($_SESSION['status'] ?? '') === 'offline' ? 'selected' : ''; ?>>Offline</option>
                </select>
            </form>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <h3 class="mt-4">Agent Management</h3>
            <a href="/admin/index.php?action=manage_agents" class="btn btn-success btn-block">Manage Agents</a>
            <ul class="list-group mt-2">
                <?php foreach($agents as $agent): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($agent['username']); ?>
                    <span class="badge badge-<?php echo $agent['status'] === 'online' ? 'success' : 'secondary'; ?>"><?php echo $agent['status']; ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
