<?php
// manager/dashboard.php - Manager Dashboard

// Enable error reporting (temporary)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login and manager role
require_login();
require_role('manager');

// Get user info
$user = get_logged_in_user($pdo);
$fullname = $user['fullname'] ?? 'Manager';
$user_id = $user['id'] ?? 0;

include '../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h2>Manager Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($fullname); ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card primary">
        <h3>My Projects</h3>
        <div class="number"><?php echo count(get_projects_by_manager($pdo, $user_id)); ?></div>
    </div>
    <div class="card success">
        <h3>Ongoing Projects</h3>
        <div class="number"><?php echo get_ongoing_projects($pdo); ?></div>
    </div>
    <div class="card warning">
        <h3>Pending Tasks</h3>
        <div class="number"><?php echo get_pending_tasks($pdo); ?></div>
    </div>
    <div class="card info">
        <h3>Total Workers</h3>
        <div class="number"><?php echo get_total_workers($pdo); ?></div>
    </div>
</div>

<!-- Low Stock Alert -->
<?php $low_stock = get_low_stock_materials($pdo); ?>
<?php if (count($low_stock) > 0): ?>
    <div class="alert alert-warning">
        <strong>⚠️ Low Stock Alert</strong>
        <ul>
            <?php foreach ($low_stock as $material): ?>
                <li>
                    <?php echo htmlspecialchars($material['name']); ?> - 
                    Stock: <?php echo $material['quantity']; ?> <?php echo $material['unit']; ?> 
                    (Reorder Level: <?php echo $material['reorder_level']; ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

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
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo $project['id']; ?></td>
                        <td><?php echo htmlspecialchars($project['name']); ?></td>
                        <td><span class="badge badge-<?php echo get_status_badge($project['status']); ?>"><?php echo ucfirst($project['status']); ?></span></td>
                        <td>KES <?php echo number_format($project['budget'], 0); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($project['start_date'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($project['end_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>