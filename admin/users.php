<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}
$is_subdir = true;
include '../includes/header.php';
include '../includes/db.php';

$page_message = '';
if (isset($_SESSION['success_message'])) {
    $page_message = '<p style="color:green;">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $page_message = '<p style="color:red;">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']);
}
?>

<div class="page-header">
    <h1>Manage User Sessions</h1>
    <a href="add_user_entry.php" class="btn btn-success">Add New User Session</a>
</div>

<?php if (!empty($page_message)) echo $page_message; ?>

<?php
// Fetch user entries, joining with computers table to get computer name
$sql = "SELECT ue.*, c.computer_name 
        FROM user_entries ue
        JOIN computers c ON ue.computer_id = c.id
        ORDER BY ue.in_time DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table class=\"content-table\">";
    echo "<thead><tr>
            <th>Entry ID</th>
            <th>User Name</th>
            <th>Computer</th>
            <th>In Time</th>
            <th>Out Time</th>
            <th>Duration (Mins)</th>
            <th>Price/Hour</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Actions</th>
          </tr></thead>";
    echo "<tbody>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["entry_id"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["user_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["computer_name"]) . "</td>";
        echo "<td>" . htmlspecialchars(date("Y-m-d H:i:s", strtotime($row["in_time"]))) . "</td>";
        echo "<td>" . ($row["out_time"] ? htmlspecialchars(date("Y-m-d H:i:s", strtotime($row["out_time"]))) : '<em>Active</em>') . "</td>";
        echo "<td>" . ($row["duration_minutes"] ? htmlspecialchars($row["duration_minutes"]) : '-') . "</td>";
        echo "<td>" . ($row["price_per_hour"] ? htmlspecialchars(number_format($row["price_per_hour"], 2)) : '-') . "</td>";
        echo "<td>" . ($row["total_amount"] ? htmlspecialchars(number_format($row["total_amount"], 2)) : '-') . "</td>";
        echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
        echo "<td>" . nl2br(htmlspecialchars($row["remarks"] ?? '')) . "</td>";
        echo "<td class=\"action-links\">";
        // Link to edit/update (which will also handle logout)
        echo "<a href=\"edit_user_entry.php?id=" . $row["entry_id"] . "\" class=\"btn btn-sm btn-primary\">Manage</a>";
        // Add other actions like view details if needed, or delete (with caution)
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No user sessions found. Click 'Add New User Session' to start one.</p>";
}
// $conn->close(); // Connection will be closed by subsequent includes like footer or if other operations are needed on the same page.
?>

<?php include '../includes/footer.php'; ?> 