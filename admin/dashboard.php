<?php
// admin/dashboard.php - Admin Dashboard with Clickable Cards

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('admin');

$user = get_logged_in_user($pdo);
$fullname = $user['fullname'] ?? 'Admin';

include '../includes/header.php';
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <strong><?php echo htmlspecialchars($fullname); ?></strong>!</p>

<!-- Clickable Cards -->
<div class="card-grid">
    <a href="users.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card primary">
            <h3>Total Users</h3>
            <div class="number"><?php echo get_total_users($pdo); ?></div>
        </div>
    </a>
    <a href="../manager/projects.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card success">
            <h3>Total Projects</h3>
            <div class="number"><?php echo get_total_projects($pdo); ?></div>
        </div>
    </a>
    <a href="../manager/workers.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card warning">
            <h3>Total Workers</h3>
            <div class="number"><?php echo get_total_workers($pdo); ?></div>
        </div>
    </a>
    <a href="../manager/budget.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="card danger">
            <h3>Total Expenses</h3>
            <div class="number">KES <?php echo number_format(get_total_expenses($pdo), 0); ?></div>
        </div>
    </a>
</div>

<!-- Recent Projects -->
<div class="table-container">
    <h3>Recent Projects</h3>
    <?php $projects = get_recent_projects($pdo, 5); ?>
    <?php if (count($projects) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Project Name</th>
                    <th>Status</th>
                    <th>Budget</th>
                    <th>Manager</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo $project['id']; ?></td>
                        <td><?php echo htmlspecialchars($project['name']); ?></td>
                        <td><span class="badge badge-<?php echo get_status_badge($project['status']); ?>"><?php echo ucfirst($project['status']); ?></span></td>
                        <td>KES <?php echo number_format($project['budget'], 0); ?></td>
                        <td><?php echo htmlspecialchars($project['manager_name'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>