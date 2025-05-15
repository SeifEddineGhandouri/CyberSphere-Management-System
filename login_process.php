<?php
session_start();
include 'includes/db.php';

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $login_error = "Username and Password are required.";
        header("Location: index.php?error=" . urlencode($login_error));
        exit;
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    if ($stmt === false) {
        // Handle error, e.g., log it or display a generic error message
        error_log("Failed to prepare statement: " . $conn->error);
        header("Location: index.php?error=" . urlencode("Login error. Please try again later."));
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        $stored_password_hash = $admin['password'];

        // Verify the MD5 hashed password
        if (md5($password) === $stored_password_hash) {
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();

    if (!empty($login_error)) {
        header("Location: index.php?error=" . urlencode($login_error));
        exit;
    }

} else {
    // If not a POST request, redirect to login page
    header("Location: index.php");
    exit;
}
?> 