<?php
session_start();
// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php'; // Include header
include 'includes/db.php';     // Include database connection

// Fetch total number of computers
$total_computers = 0;
$sql_computers = "SELECT COUNT(*) as count FROM computers";
$result_computers = $conn->query($sql_computers);
if ($result_computers && $result_computers->num_rows > 0) {
    $total_computers = $result_computers->fetch_assoc()['count'];
}

// Fetch total number of users today
$total_users_today = 0;
$today_date = date("Y-m-d");
// Query for entries where the date part of in_time matches today's date
$sql_users_today = "SELECT COUNT(*) as count FROM user_entries WHERE DATE(in_time) = ?";
$stmt_users = $conn->prepare($sql_users_today);
if ($stmt_users) {
    $stmt_users->bind_param("s", $today_date);
    $stmt_users->execute();
    $result_users = $stmt_users->get_result();
    if ($result_users && $result_users->num_rows > 0) {
        $total_users_today = $result_users->fetch_assoc()['count'];
    }
    $stmt_users->close();
} else {
    // Handle error if statement preparation fails - e.g., log it
    error_log("Failed to prepare statement for users today: " . $conn->error);
}

// $conn->close(); // Don't close connection if footer needs it or other operations follow

?>

<div class="dashboard-container">
    <h1>Welcome to the Admin Dashboard, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Computers</h3>
            <p id="total-computers"><?php echo $total_computers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Users Today</h3>
            <p id="total-users-today"><?php echo $total_users_today; ?></p>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <ul>
            <li><a href="admin/computers.php">Manage Computers</a></li>
            <li><a href="admin/users.php">Manage Users</a></li>
            <li><a href="admin/search.php">Search Users</a></li>
            <li><a href="admin/reports.php">View Reports</a></li>
        </ul>
    </div>

</div>

<?php include 'includes/footer.php'; // Include footer ?> 