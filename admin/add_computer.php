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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $computer_name = trim($_POST['computer_name']);
    $ip_address = trim($_POST['ip_address']);
    $description = trim($_POST['description']);
    $status = $_POST['status']; // 'Available', 'In Use', 'Maintenance'

    if (empty($computer_name)) {
        $message = "<p style=\"color:red;\">Computer Name is required.</p>";
    } else {
        // Check if computer name already exists
        $stmt_check = $conn->prepare("SELECT id FROM computers WHERE computer_name = ?");
        $stmt_check->bind_param("s", $computer_name);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "<p style=\"color:red;\">Computer with this name already exists.</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO computers (computer_name, ip_address, description, status) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $computer_name, $ip_address, $description, $status);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Computer added successfully!";
                    header("Location: computers.php");
                    exit;
                } else {
                    $message = "<p style=\"color:red;\">Error adding computer: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                 $message = "<p style=\"color:red;\">Error preparing statement: " . $conn->error . "</p>";
            }
        }
        $stmt_check->close();
    }
    $conn->close();
}
?>

<h1>Add New Computer</h1>

<?php if (!empty($message)) echo $message; ?>

<form action="add_computer.php" method="POST" class="form-styled">
    <div class="form-group">
        <label for="computer_name">Computer Name/No.:</label>
        <input type="text" id="computer_name" name="computer_name" value="<?php echo isset($_POST['computer_name']) ? htmlspecialchars($_POST['computer_name']) : ''; ?>" required>
        <small>E.g., PC01, Comp05</small>
    </div>

    <div class="form-group">
        <label for="ip_address">IP Address (Optional):</label>
        <input type="text" id="ip_address" name="ip_address" value="<?php echo isset($_POST['ip_address']) ? htmlspecialchars($_POST['ip_address']) : ''; ?>">
        <small>E.g., 192.168.1.10</small>
    </div>

    <div class="form-group">
        <label for="description">Description (Optional):</label>
        <textarea id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
    </div>

    <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="Available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
            <option value="In Use" <?php echo (isset($_POST['status']) && $_POST['status'] == 'In Use') ? 'selected' : ''; ?>>In Use</option>
            <option value="Maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
        </select>
    </div>

    <div class="form-group-buttons">
        <button type="submit" class="btn btn-success">Add Computer</button>
        <a href="computers.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?> 