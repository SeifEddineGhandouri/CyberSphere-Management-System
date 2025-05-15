<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}
$is_subdir = true;
include '../includes/header.php';
include '../includes/db.php';

$report_data = [];
$report_message = '';
$from_date_value = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date_value = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$total_sessions_in_period = 0;

?>

<div class="page-header">
    <h1>User Session Reports</h1>
</div>

<form action="reports.php" method="GET" class="form-styled" style="max-width: 800px; margin-bottom: 30px;">
    <div style="display: flex; gap: 20px; align-items: flex-end;">
        <div class="form-group" style="flex-grow: 1;">
            <label for="from_date">From Date:</label>
            <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date_value); ?>" required>
        </div>
        <div class="form-group" style="flex-grow: 1;">
            <label for="to_date">To Date:</label>
            <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date_value); ?>" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </div>
</form>

<?php
if (!empty($from_date_value) && !empty($to_date_value)) {
    $from_date = $from_date_value;
    $to_date = $to_date_value;

    // Validate dates
    if (strtotime($from_date) > strtotime($to_date)) {
        $report_message = "<p style=\"color:red;\">'From Date' cannot be after 'To Date'.</p>";
    } else {
        // Adjust to_date to include the whole day
        $to_date_adjusted = date('Y-m-d', strtotime($to_date . ' +1 day'));

        $stmt = $conn->prepare(
            "SELECT ue.*, c.computer_name 
             FROM user_entries ue
             JOIN computers c ON ue.computer_id = c.id
             WHERE ue.in_time >= ? AND ue.in_time < ?
             ORDER BY ue.in_time DESC"
        );
        // Bind parameters: from_date (start of day) and to_date_adjusted (start of next day)
        $stmt->bind_param("ss", $from_date, $to_date_adjusted);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
            $total_sessions_in_period = count($report_data);
            if ($total_sessions_in_period === 0) {
                $report_message = "<p style=\"color:orange;\">No user sessions found for the selected period: " . htmlspecialchars($from_date) . " to " . htmlspecialchars($to_date) . ".</p>";
            }
        } else {
            $report_message = "<p style=\"color:red;\">Error fetching report data: " . $conn->error . "</p>";
        }
        $stmt->close();
    }
    echo "<hr><h3>Report for Period: " . htmlspecialchars($from_date) . " to " . htmlspecialchars($to_date) . "</h3>";
    echo "<p><strong>Total User Sessions in this period: " . $total_sessions_in_period . "</strong></p>";
}

if (!empty($report_message)) {
    echo $report_message;
}

if (!empty($report_data)): ?>
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
            <?php foreach ($report_data as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["entry_id"]); ?></td>
                <td><?php echo htmlspecialchars($row["user_name"]); ?></td>
                <td><?php echo htmlspecialchars($row["computer_name"]); ?></td>
                <td><?php echo htmlspecialchars(date("Y-m-d H:i:s", strtotime($row["in_time"]))); ?></td>
                <td><?php echo $row["out_time"] ? htmlspecialchars(date("Y-m-d H:i:s", strtotime($row["out_time"]))) : '<em>Active</em>'; ?></td>
                <td><?php echo $row["duration_minutes"] ? htmlspecialchars($row["duration_minutes"]) : '-'; ?></td>
                <td><?php echo $row["price_per_hour"] ? htmlspecialchars(number_format($row["price_per_hour"], 2)) : '-'; ?></td>
                <td><?php echo $row["total_amount"] ? htmlspecialchars(number_format($row["total_amount"], 2)) : '-'; ?></td>
                <td><?php echo htmlspecialchars($row["status"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row["remarks"] ?? '')); ?></td>
                <td class="action-links">
                    <a href="edit_user_entry.php?id=<?php echo $row["entry_id"]; ?>&ref=reports&from_date=<?php echo urlencode($from_date_value); ?>&to_date=<?php echo urlencode($to_date_value); ?>" class="btn btn-sm btn-primary">Manage</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (empty($report_message) && !empty($from_date_value) && !empty($to_date_value)): ?>
    <?php // This condition is now handled by the $report_message for no data ?>
<?php endif; ?>

<?php 
// $conn->close(); // Connection handled by footer or if script ends
include '../includes/footer.php'; 
?> 