<?php
// manager/task_edit.php - Edit Task

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

$task_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT t.* FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ? AND p.created_by = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: tasks.php');
    exit();
}

$projects = get_projects_by_manager($pdo, $user_id);
$workers = $pdo->query("SELECT w.*, u.fullname, u.id as user_id FROM workers w LEFT JOIN users u ON w.user_id = u.id")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $assigned_to = $_POST['assigned_to'] ?? 0;
    $due_date = $_POST['due_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';

    if (empty($title) || empty($project_id) || empty($due_date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET project_id = ?, title = ?, description = ?, assigned_to = ?, due_date = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$project_id, $title, $description, $assigned_to, $due_date, $status, $task_id])) {
            $success = 'Task updated successfully!';
            // Refresh task data
            $stmt = $pdo->prepare("SELECT t.* FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ? AND p.created_by = ?");
            $stmt->execute([$task_id, $user_id]);
            $task = $stmt->fetch();
        } else {
            $error = 'Failed to update task.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Edit Task</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="project_id">Project *</label>
            <select id="project_id" name="project_id" required>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo $project['id']; ?>" <?php echo $project['id'] == $task['project_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($project['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="title">Task Title *</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($task['description']); ?></textarea>

            <label for="assigned_to">Assign To</label>
            <select id="assigned_to" name="assigned_to">
                <option value="">Unassigned</option>
                <?php foreach ($workers as $worker): ?>
                    <option value="<?php echo $worker['user_id']; ?>" <?php echo $worker['user_id'] == $task['assigned_to'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($worker['fullname']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="due_date">Due Date *</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo $task['due_date']; ?>" required>

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>

            <button type="submit">Update Task</button>
        </form>
        <p class="form-link"><a href="tasks.php">← Back to Tasks</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>