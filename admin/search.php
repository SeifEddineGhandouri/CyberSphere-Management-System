<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}
$is_subdir = true;
include '../includes/header.php';
include '../includes/db.php';

$search_result = null;
$search_message = '';
$searched_entry_id = '';

if (isset($_GET['entry_id'])) {
    $searched_entry_id = trim($_GET['entry_id']);

    if (empty($searched_entry_id)) {
        $search_message = "<p style=\"color:orange;\">Please enter an Entry ID to search.</p>";
    } elseif (!ctype_digit($searched_entry_id)) { // Basic validation: check if it's a number
        $search_message = "<p style=\"color:red;\">Invalid Entry ID format. Please enter a number.</p>";
    } else {
        $entry_id_to_search = intval($searched_entry_id);

        $stmt = $conn->prepare(
            "SELECT ue.*, c.computer_name 
             FROM user_entries ue
             JOIN computers c ON ue.computer_id = c.id
             WHERE ue.entry_id = ?"
        );
        $stmt->bind_param("i", $entry_id_to_search);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $search_result = $result->fetch_assoc();
        } else {
            $search_message = "<p style=\"color:orange;\">No user entry found with ID: " . htmlspecialchars($searched_entry_id) . ".</p>";
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
    <h1>Search User Sessions by Entry ID</h1>
</div>

<?php if (!empty($search_message)) echo $search_message; ?>

<form action="search.php" method="GET" class="form-styled" style="max-width: 600px; margin-bottom: 30px;">
    <div class="form-group">
        <label for="search_entry_id">Enter Entry ID:</label>
        <input type="text" id="search_entry_id" name="entry_id" value="<?php echo htmlspecialchars($searched_entry_id); ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<?php if ($search_result): ?>
    <h2>Search Result</h2>
    <table class="content-table">
        <thead>
            <tr>
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
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($search_result["entry_id"]); ?></td>
                <td><?php echo htmlspecialchars($search_result["user_name"]); ?></td>
                <td><?php echo htmlspecialchars($search_result["computer_name"]); ?></td>
                <td><?php echo htmlspecialchars(date("Y-m-d H:i:s", strtotime($search_result["in_time"]))); ?></td>
                <td><?php echo $search_result["out_time"] ? htmlspecialchars(date("Y-m-d H:i:s", strtotime($search_result["out_time"]))) : '<em>Active</em>'; ?></td>
                <td><?php echo $search_result["duration_minutes"] ? htmlspecialchars($search_result["duration_minutes"]) : '-'; ?></td>
                <td><?php echo $search_result["price_per_hour"] ? htmlspecialchars(number_format($search_result["price_per_hour"], 2)) : '-'; ?></td>
                <td><?php echo $search_result["total_amount"] ? htmlspecialchars(number_format($search_result["total_amount"], 2)) : '-'; ?></td>
                <td><?php echo htmlspecialchars($search_result["status"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($search_result["remarks"] ?? '')); ?></td>
                <td class="action-links">
                    <a href="edit_user_entry.php?id=<?php echo $search_result["entry_id"]; ?>" class="btn btn-sm btn-primary">Manage</a>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<?php 
// $conn->close(); // Only close if no further DB operations are expected on the page.
include '../includes/footer.php'; 
?> 