<?php
// admin/dashboard.php - Simple Admin Dashboard
require_once '../config/database.php';
require_once '../includes/auth.php';

require_login();
require_role('admin');

$user = get_logged_in_user($pdo);
$fullname = $user['fullname'] ?? 'Admin';

// Simple stats
$total_users = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'] ?? 0;
$total_projects = $pdo->query("SELECT COUNT(*) as total FROM projects")->fetch()['total'] ?? 0;
$total_workers = $pdo->query("SELECT COUNT(*) as total FROM workers")->fetch()['total'] ?? 0;
$total_expenses = $pdo->query("SELECT SUM(amount) as total FROM expenses")->fetch()['total'] ?? 0;

include '../includes/header.php';
?>

<div style="display: flex; gap: 20px; margin-bottom: 30px;">
    <div class="card" style="flex:1; background: #007bff; color:#fff;">
        <h3>Users</h3>
        <div style="font-size:32px; font-weight:bold;"><?php echo $total_users; ?></div>
    </div>
    <div class="card" style="flex:1; background: #28a745; color:#fff;">
        <h3>Projects</h3>
        <div style="font-size:32px; font-weight:bold;"><?php echo $total_projects; ?></div>
    </div>
    <div class="card" style="flex:1; background: #ffc107; color:#000;">
        <h3>Workers</h3>
        <div style="font-size:32px; font-weight:bold;"><?php echo $total_workers; ?></div>
    </div>
    <div class="card" style="flex:1; background: #dc3545; color:#fff;">
        <h3>Expenses</h3>
        <div style="font-size:32px; font-weight:bold;">KES <?php echo number_format($total_expenses, 0); ?></div>
    </div>
</div>

<h3>Welcome, <?php echo htmlspecialchars($fullname); ?>!</h3>
<p>This is your admin dashboard. Use the navigation above to manage the system.</p>

<?php include '../includes/footer.php'; ?>