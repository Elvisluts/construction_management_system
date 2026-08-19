<?php
// worker/dashboard.php - Worker Dashboard with Clickable Cards

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('worker');

$user = get_logged_in_user($pdo);
$fullname = $user['fullname'] ?? 'Worker';
$user_id = $user['id'] ?? 0;

$tasks = get_tasks_by_worker($pdo, $user_id);

$total_tasks = count($tasks);
$completed_tasks = 0;
$in_progress_tasks = 0;
$pending_tasks = 0;

foreach ($tasks as $task) {
    if ($task['status'] === 'completed') $completed_tasks++;
    elseif ($task['status'] === 'in_progress') $in_progress_tasks++;
    else $pending_tasks++;
}

include '../includes/header.php';
?>

<h2>Worker Dashboard</h2>
<p>Welcome, <strong><?php echo htmlspecialchars($fullname); ?></strong>!</p>

<!-- Clickable Cards -->
<div class="card-grid">
    <a href="dashboard.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card primary">
            <h3>Total Tasks</h3>
            <div class="number"><?php echo $total_tasks; ?></div>
        </div>
    </a>
    <a href="dashboard.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card success">
            <h3>Completed</h3>
            <div class="number"><?php echo $completed_tasks; ?></div>
        </div>
    </a>
    <a href="dashboard.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card warning">
            <h3>In Progress</h3>
            <div class="number"><?php echo $in_progress_tasks; ?></div>
        </div>
    </a>
    <a href="dashboard.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card danger">
            <h3>Pending</h3>
            <div class="number"><?php echo $pending_tasks; ?></div>
        </div>
    </a>
</div>

<!-- My Tasks -->
<div class="table-container">
    <h3>My Assigned Tasks</h3>
    <?php if (count($tasks) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task Title</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo $task['id']; ?></td>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?></td>
                        <td><span class="badge badge-<?php echo get_task_status_badge($task['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></td>
                        <td><a href="task_update.php?id=<?php echo $task['id']; ?>" class="btn btn-blue btn-sm">Update</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tasks assigned to you yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>