<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}
$is_subdir = true;
include '../includes/header.php';
include '../includes/db.php';

$message = '';
$user_name = '';
$computer_id = '';
$price_per_hour = ''; // You can set a default rate
$remarks = '';

// Fetch available computers for the dropdown
$available_computers = [];
$sql_computers = "SELECT id, computer_name FROM computers WHERE status = 'Available' ORDER BY computer_name ASC";
$result_computers = $conn->query($sql_computers);
if ($result_computers) {
    while ($row = $result_computers->fetch_assoc()) {
        $available_computers[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['user_name']);
    $computer_id = $_POST['computer_id'];
    $in_time = $_POST['in_time']; // Should be validated and formatted if necessary
    $price_per_hour = trim($_POST['price_per_hour']);
    $remarks = trim($_POST['remarks']);

    if (empty($user_name) || empty($computer_id) || empty($in_time) || !is_numeric($price_per_hour) || $price_per_hour < 0) {
        $message = "<p style=\"color:red;\">User Name, Computer, In-Time, and a valid Price Per Hour are required.</p>";
    } else {
        $conn->begin_transaction(); // Start a transaction

        try {
            // Insert into user_entries
            $stmt_insert = $conn->prepare("INSERT INTO user_entries (user_name, computer_id, in_time, price_per_hour, remarks, status, admin_id) VALUES (?, ?, ?, ?, ?, 'Active', ?)");
            $admin_id = $_SESSION['admin_id']; // Assuming admin_id is stored in session during login
            $stmt_insert->bind_param("sisdsi", $user_name, $computer_id, $in_time, $price_per_hour, $remarks, $admin_id);
            
            if ($stmt_insert->execute()) {
                // Update computer status to 'In Use'
                $stmt_update_computer = $conn->prepare("UPDATE computers SET status = 'In Use' WHERE id = ? AND status = 'Available'");
                $stmt_update_computer->bind_param("i", $computer_id);
                
                if ($stmt_update_computer->execute() && $stmt_update_computer->affected_rows > 0) {
                    $conn->commit(); // Commit transaction
                    $_SESSION['success_message'] = "User session started successfully!";
                    header("Location: users.php");
                    exit;
                } else {
                    $conn->rollback(); // Rollback transaction
                    if ($stmt_update_computer->affected_rows == 0) {
                         $message = "<p style=\"color:red;\">Failed to update computer status (it might have been taken by another admin or is not available).</p>";
                    } else {
                         $message = "<p style=\"color:red;\">Error updating computer status: " . $stmt_update_computer->error . "</p>";
                    }
                }
                $stmt_update_computer->close();
            } else {
                $conn->rollback(); // Rollback transaction
                $message = "<p style=\"color:red;\">Error starting user session: " . $stmt_insert->error . "</p>";
            }
            $stmt_insert->close();
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on exception
            $message = "<p style=\"color:red;\">An unexpected error occurred: " . $e->getMessage() . "</p>";
        }
    }
    // $conn->close(); // Don't close here if a redirect might not happen and form needs to be redisplayed
}
?>

<h1>Add New User Session</h1>

<?php if (!empty($message)) echo $message; ?>

<form action="add_user_entry.php" method="POST" class="form-styled">
    <div class="form-group">
        <label for="user_name">User Name:</label>
        <input type="text" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" required>
    </div>

    <div class="form-group">
        <label for="computer_id">Select Computer:</label>
        <select id="computer_id" name="computer_id" required>
            <option value="">-- Select an Available Computer --</option>
            <?php foreach ($available_computers as $computer): ?>
                <option value="<?php echo $computer['id']; ?>" <?php echo ($computer_id == $computer['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($computer['computer_name']); ?>
                </option>
            <?php endforeach; ?>
            <?php if (empty($available_computers)): ?>
                <option value="" disabled>No computers currently available</option>
            <?php endif; ?>
        </select>
         <?php if (empty($available_computers)): ?>
            <small style="color:red;">All computers are currently in use or none are registered.</small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="in_time">In Time:</label>
        <input type="datetime-local" id="in_time" name="in_time" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
        <small>Defaults to current date and time.</small>
    </div>

    <div class="form-group">
        <label for="price_per_hour">Price Per Hour (DT):</label> 
        <input type="number" id="price_per_hour" name="price_per_hour" step="0.01" min="0" value="<?php echo htmlspecialchars($price_per_hour); ?>" required>
        <small>Enter the hourly rate for this session.</small>
    </div>

    <div class="form-group">
        <label for="remarks">Remarks (Optional):</label>
        <textarea id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($remarks); ?></textarea>
    </div>

    <div class="form-group-buttons">
        <button type="submit" class="btn btn-success" <?php echo empty($available_computers) ? 'disabled' : ''; ?>>Start Session</button>
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?> 