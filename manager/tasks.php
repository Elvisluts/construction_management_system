<?php
// manager/tasks.php - Task Management

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE t FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ? AND p.created_by = ?");
    $stmt->execute([$_GET['delete'], $user_id]);
    header('Location: tasks.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT t.*, p.name as project_name, u.fullname as assigned_name 
    FROM tasks t 
    LEFT JOIN projects p ON t.project_id = p.id 
    LEFT JOIN users u ON t.assigned_to = u.id 
    WHERE p.created_by = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Tasks</h2>
    <a href="task_create.php" class="btn btn-success">+ New Task</a>
</div>

<div class="table-container">
    <h3>All Tasks</h3>
    <?php if (count($tasks) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task Title</th>
                    <th>Project</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo $task['id']; ?></td>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['assigned_name'] ?? 'Unassigned'); ?></td>
                        <td><span class="badge badge-<?php echo get_task_status_badge($task['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></td>
                        <td>
                            <a href="task_edit.php?id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="tasks.php?delete=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this task?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tasks found. <a href="task_create.php">Create your first task</a>.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>