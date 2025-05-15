<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php"); // Redirect to login page if not logged in
    exit;
}
$is_subdir = true; // Variable to help header.php construct correct asset paths
include '../includes/header.php';
include '../includes/db.php'; // For database operations

$page_message = '';
if (isset($_SESSION['success_message'])) {
    $page_message = '<p style="color:green;">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']); // Clear the message after displaying it
}
if (isset($_SESSION['error_message'])) {
    $page_message = '<p style="color:red;">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']); // Clear the message after displaying it
}

?>

<div class="page-header">
    <h1>Manage Computers</h1>
    <a href="add_computer.php" class="btn btn-success">Add New Computer</a>
</div>

<?php if (!empty($page_message)) echo $page_message; ?>

<?php
$sql = "SELECT id, computer_name, ip_address, status, description, created_at, updated_at FROM computers ORDER BY computer_name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table class=\"content-table\">";
    echo "<thead><tr><th>ID</th><th>Name/No.</th><th>IP Address</th><th>Status</th><th>Description</th><th>Added On</th><th>Last Updated</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["computer_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["ip_address"] ?? 'N/A') . "</td>"; // Use null coalescing for optional fields
        echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
        echo "<td>" . nl2br(htmlspecialchars($row["description"] ?? 'N/A')) . "</td>";
        echo "<td>" . date("Y-m-d H:i:s", strtotime($row["created_at"])) . "</td>";
        echo "<td>" . date("Y-m-d H:i:s", strtotime($row["updated_at"])) . "</td>";
        echo "<td class=\"action-links\">";
        echo "<a href=\"edit_computer.php?id=" . $row["id"] . "\" class=\"btn btn-sm btn-primary\">Edit</a> ";
        echo "<a href=\"delete_computer.php?id=" . $row["id"] . "\" onclick=\"return confirm('Are you sure you want to delete this computer? This action cannot be undone.');\" class=\"btn btn-sm btn-danger delete\">Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No computers found. Click 'Add New Computer' to add one.</p>";
}
$conn->close();
?>

<?php include '../includes/footer.php'; ?> 