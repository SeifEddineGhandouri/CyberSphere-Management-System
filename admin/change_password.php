<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}
$is_subdir = true;
include '../includes/header.php';
include '../includes/db.php'; // For database operations

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "<p style=\"color:red;\">All fields are required.</p>";
    } elseif ($new_password !== $confirm_password) {
        $message = "<p style=\"color:red;\">New password and confirm password do not match.</p>";
    } else {
        // Fetch current password from DB for the logged-in admin
        // $admin_username = $_SESSION['admin_username'];
        // $sql = "SELECT password FROM admins WHERE username = ?"; // Assuming 'admins' table and 'password' column stores MD5 hash
        // Prepare statement, bind, execute, get result...
        // $stored_password_hash = ...; // This would be the MD5 hash from DB

        // IMPORTANT: This is a placeholder. Replace with actual DB check.
        // For demonstration, we'll check against the hardcoded 'admin123' MD5 hash.
        $correct_current_password_hash = md5('admin123'); 

        if (md5($current_password) === $correct_current_password_hash) {
            // Current password is correct, proceed to update
            // $new_password_hash = md5($new_password); // Use MD5 as per project spec
            // $update_sql = "UPDATE admins SET password = ? WHERE username = ?";
            // Prepare statement, bind $new_password_hash and $admin_username, execute...
            
            // If update successful:
            $message = "<p style=\"color:green;\">Password change functionality to be implemented. (New password would be: " . htmlspecialchars($new_password) . ")</p>";
            // Potentially force logout or show success message
        } else {
            $message = "<p style=\"color:red;\">Incorrect current password.</p>";
        }
    }
}
?>

<h1>Change Password</h1>
<?php echo $message; ?>
<form action="change_password.php" method="POST">
    <div class="form-group">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required>
    </div>
    <div class="form-group">
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit" class="btn">Change Password</button>
</form>

<?php include '../includes/footer.php'; ?> 