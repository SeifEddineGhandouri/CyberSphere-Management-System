<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberSphere - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php
        if (isset($_GET['error'])) {
            echo '<p style="color:red; text-align:center;">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            <div class="form-group">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </form>
    </div>
</body>
</html> 