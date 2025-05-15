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
$entry_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($entry_id <= 0) {
    $_SESSION['error_message'] = "Invalid user entry ID.";
    header("Location: users.php");
    exit;
}

// Fetch the user entry details
$stmt_fetch = $conn->prepare("SELECT ue.*, c.computer_name FROM user_entries ue JOIN computers c ON ue.computer_id = c.id WHERE ue.entry_id = ?");
$stmt_fetch->bind_param("i", $entry_id);
$stmt_fetch->execute();
$result_entry = $stmt_fetch->get_result();

if ($result_entry->num_rows === 0) {
    $_SESSION['error_message'] = "User entry not found.";
    header("Location: users.php");
    exit;
}
$entry = $result_entry->fetch_assoc();
$stmt_fetch->close();

// Initialize form variables with fetched data
$user_name = $entry['user_name'];
$computer_id = $entry['computer_id'];
$computer_name = $entry['computer_name'];
$in_time_display = date("Y-m-d H:i:s", strtotime($entry['in_time']));
$out_time = $entry['out_time'] ? date("Y-m-d\TH:i", strtotime($entry['out_time'])) : '';
$price_per_hour = $entry['price_per_hour'];
$remarks = $entry['remarks'];
$current_status = $entry['status'];
$total_amount_display = $entry['total_amount'] ? number_format($entry['total_amount'], 2) : 'N/A';
$duration_display = $entry['duration_minutes'] ? $entry['duration_minutes'] . ' minutes' : 'N/A';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['user_name']);
    // $out_time can be empty if not logging out yet, or a datetime string
    $posted_out_time = !empty($_POST['out_time']) ? trim($_POST['out_time']) : null;
    $price_per_hour = trim($_POST['price_per_hour']); // Allow updating price per hour
    $remarks = trim($_POST['remarks']);
    $new_status = $_POST['status']; // e.g., Active, Completed, Paid

    if (empty($user_name) || !is_numeric($price_per_hour) || $price_per_hour < 0) {
        $message = "<p style=\"color:red;\">User Name and a valid Price Per Hour are required.</p>";
    } else {
        $conn->begin_transaction();
        try {
            $duration_minutes = $entry['duration_minutes'];
            $total_amount = $entry['total_amount'];
            $computer_status_to_update = false;

            // If logging out (out_time is set and was previously null)
            if ($posted_out_time && !$entry['out_time']) {
                $in_timestamp = strtotime($entry['in_time']);
                $out_timestamp = strtotime($posted_out_time);

                if ($out_timestamp < $in_timestamp) {
                    throw new Exception("Out Time cannot be before In Time.");
                }

                $duration_seconds = $out_timestamp - $in_timestamp;
                $duration_minutes = round($duration_seconds / 60);
                $total_amount = ($duration_minutes / 60) * $price_per_hour;
                $computer_status_to_update = true;
                // $new_status = 'Completed'; // Default to completed when logging out
            } elseif ($posted_out_time && $entry['out_time']) {
                 // If out_time is being changed (was already set)
                $in_timestamp = strtotime($entry['in_time']);
                $out_timestamp = strtotime($posted_out_time);
                if ($out_timestamp < $in_timestamp) {
                    throw new Exception("Out Time cannot be before In Time.");
                }
                $duration_seconds = $out_timestamp - $in_timestamp;
                $duration_minutes = round($duration_seconds / 60);
                $total_amount = ($duration_minutes / 60) * $price_per_hour;
                // No change to computer status if only modifying existing out_time
            }

            $stmt_update = $conn->prepare("UPDATE user_entries SET user_name = ?, out_time = ?, duration_minutes = ?, price_per_hour = ?, total_amount = ?, remarks = ?, status = ? WHERE entry_id = ?");
            $stmt_update->bind_param("ssiddssi", $user_name, $posted_out_time, $duration_minutes, $price_per_hour, $total_amount, $remarks, $new_status, $entry_id);

            if ($stmt_update->execute()) {
                if ($computer_status_to_update) {
                    $stmt_free_computer = $conn->prepare("UPDATE computers SET status = 'Available' WHERE id = ?");
                    $stmt_free_computer->bind_param("i", $computer_id);
                    if (!$stmt_free_computer->execute()) {
                        throw new Exception("Failed to update computer status: " . $stmt_free_computer->error);
                    }
                    $stmt_free_computer->close();
                }
                $conn->commit();
                $_SESSION['success_message'] = "User session updated successfully!";
                header("Location: users.php");
                exit;
            } else {
                throw new Exception("Error updating user session: " . $stmt_update->error);
            }
            $stmt_update->close();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p style=\"color:red;\">Update failed: " . $e->getMessage() . "</p>";
        }
    }
}

?>

<h1>Manage User Session (ID: <?php echo $entry_id; ?>)</h1>

<?php if (!empty($message)) echo $message; ?>

<form action="edit_user_entry.php?id=<?php echo $entry_id; ?>" method="POST" class="form-styled">
    <div class="form-group">
        <label for="user_name">User Name:</label>
        <input type="text" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" required>
    </div>

    <div class="form-group">
        <label>Computer:</label>
        <input type="text" value="<?php echo htmlspecialchars($computer_name); ?> (ID: <?php echo $computer_id; ?>)" disabled>
    </div>

    <div class="form-group">
        <label>In Time:</label>
        <input type="text" value="<?php echo htmlspecialchars($in_time_display); ?>" disabled>
    </div>

    <div class="form-group">
        <label for="out_time">Out Time:</label>
        <input type="datetime-local" id="out_time" name="out_time" value="<?php echo $out_time; ?>">
        <small>Set this to log out the user. If already set, you can adjust it.</small>
    </div>
    
    <div class="form-group">
        <label for="price_per_hour">Price Per Hour (DT):</label>
        <input type="number" id="price_per_hour" name="price_per_hour" step="0.01" min="0" value="<?php echo htmlspecialchars($price_per_hour); ?>" required>
    </div>

    <div class="form-group">
        <label>Calculated Duration:</label>
        <input type="text" value="<?php echo $duration_display; ?>" disabled>
    </div>

    <div class="form-group">
        <label>Calculated Total Amount (DT):</label>
        <input type="text" value="<?php echo $total_amount_display; ?>" disabled>
    </div>

    <div class="form-group">
        <label for="status">Session Status:</label>
        <select id="status" name="status">
            <option value="Active" <?php echo ($current_status == 'Active') ? 'selected' : ''; ?>>Active</option>
            <option value="Completed" <?php echo ($current_status == 'Completed') ? 'selected' : ''; ?>>Completed</option>
            <option value="Paid" <?php echo ($current_status == 'Paid') ? 'selected' : ''; ?>>Paid</option>
            <!-- Add other statuses if needed -->
        </select>
    </div>

    <div class="form-group">
        <label for="remarks">Remarks (Optional):</label>
        <textarea id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($remarks); ?></textarea>
    </div>

    <div class="form-group-buttons">
        <button type="submit" class="btn btn-primary">Update Session</button>
        <a href="users.php" class="btn btn-secondary">Back to User Sessions</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?> 