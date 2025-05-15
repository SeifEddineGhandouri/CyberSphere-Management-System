<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}
$is_subdir = true;
include '../includes/header.php';
include '../includes/db.php'; // For database operations if needed

// Fetch admin details (placeholder - replace with actual DB query if you store more admin details)
$admin_username = $_SESSION['admin_username'];

// Placeholder for admin details that might be stored in a database
// $admin_email = "admin@example.com"; 
// $admin_name = "Administrator";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile update logic here
    // E.g., update admin username, email, etc. in the database
    // Remember to sanitize inputs and provide feedback
    $new_username = $_POST['username']; // Example
    // $new_email = $_POST['email'];

    // if ($new_username != $admin_username) { /* Update logic */ }

    echo "<p style=\"color:green;\">Profile update functionality to be implemented. New username (example): " . htmlspecialchars($new_username) . "</p>";
    // Potentially update session variable if username changes
    // $_SESSION['admin_username'] = $new_username;
    // $admin_username = $new_username; // Update for current page display
}

?>

<h1>Admin Profile</h1>
<p>Here you can update your profile information.</p>

<form action="profile.php" method="POST">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin_username); ?>" required>
    </div>
    <!-- Add other fields like email, name etc. as needed -->
    <!-- 
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_email); ?>">
    </div>
    -->
    <button type="submit" class="btn">Update Profile</button>
</form>

<?php include '../includes/footer.php'; ?> 