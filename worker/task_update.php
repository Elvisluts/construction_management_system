<?php
// worker/task_update.php - Update Task Status

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('worker');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

$task_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'pending';

    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
    if ($stmt->execute([$status, $task_id, $user_id])) {
        $success = 'Task status updated successfully!';
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
        $stmt->execute([$task_id, $user_id]);
        $task = $stmt->fetch();
    } else {
        $error = 'Failed to update task.';
    }
}

include '../includes/header.php';
?>

<h2>Update Task</h2>

<p><strong>Task:</strong> <?php echo htmlspecialchars($task['title']); ?></p>
<p><strong>Current Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></p>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="status">New Status</label>
            <select id="status" name="status">
                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
            <button type="submit">Update Status</button>
        </form>
        <p class="form-link"><a href="dashboard.php">← Back to Dashboard</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>