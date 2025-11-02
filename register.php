<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Website</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Register Your Website</h2>
    <form action="register_handler.php" method="post">
        <div class="form-group">
            <label for="owner_email">Owner Email</label>
            <input type="email" name="owner_email" id="owner_email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="site_url">Website URL</label>
            <input type="url" name="site_url" id="site_url" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<!-- Add the chatflow widget script -->
<script>
    window.ChatFlowConfig = {
        // This is a dummy key for testing purposes on the registration page.
        // A real implementation would get a key after registration/login.
        embedKey: 'DUMMY_KEY_FOR_REGISTRATION_PAGE'
    };
</script>
<script src="/chatflow-widget.js" defer></script>

</body>
</html>
