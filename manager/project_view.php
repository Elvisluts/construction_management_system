<?php
// manager/project_view.php - View Project Details

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

// Get project ID
$project_id = $_GET['id'] ?? 0;
if (!is_numeric($project_id) || $project_id <= 0) {
    header('Location: projects.php');
    exit();
}

// Fetch project
$stmt = $pdo->prepare("SELECT p.*, u.fullname as manager_name FROM projects p LEFT JOIN users u ON p.created_by = u.id WHERE p.id = ? AND p.created_by = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: projects.php');
    exit();
}

// Get project statistics
$total_tasks = get_project_tasks($pdo, $project_id);
$completed_tasks = get_completed_tasks($pdo, $project_id);
$total_expenses = get_project_expenses($pdo, $project_id);
$total_workers = get_project_workers($pdo, $project_id);
$budget_variance = calculate_variance($project['budget'], $total_expenses);
$percentage_used = calculate_percentage($project['budget'], $total_expenses);

include '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2><?php echo htmlspecialchars($project['name']); ?></h2>
    <div>
        <a href="project_edit.php?id=<?php echo $project['id']; ?>" class="btn btn-warning">Edit</a>
        <a href="projects.php" class="btn btn-dark">← Back</a>
    </div>
</div>

<!-- Project Info -->
<div class="card" style="margin-bottom: 20px;">
    <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']) ?: 'No description provided.'; ?></p>
    <p><strong>Status:</strong> <span class="badge badge-<?php echo get_status_badge($project['status']); ?>"><?php echo ucfirst($project['status']); ?></span></p>
    <p><strong>Budget:</strong> KES <?php echo number_format($project['budget'], 2); ?></p>
    <p><strong>Start Date:</strong> <?php echo date('d/m/Y', strtotime($project['start_date'])); ?></p>
    <p><strong>End Date:</strong> <?php echo date('d/m/Y', strtotime($project['end_date'])); ?></p>
</div>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card primary">
        <h3>Total Tasks</h3>
        <div class="number"><?php echo $total_tasks; ?></div>
    </div>
    <div class="card success">
        <h3>Completed Tasks</h3>
        <div class="number"><?php echo $completed_tasks; ?></div>
    </div>
    <div class="card warning">
        <h3>Total Expenses</h3>
        <div class="number">KES <?php echo number_format($total_expenses, 0); ?></div>
    </div>
    <div class="card info">
        <h3>Workers Assigned</h3>
        <div class="number"><?php echo $total_workers; ?></div>
    </div>
</div>

<!-- Budget Status -->
<div class="card" style="margin-bottom: 20px;">
    <h3>Budget Status</h3>
    <p><strong>Budget:</strong> KES <?php echo number_format($project['budget'], 2); ?></p>
    <p><strong>Expenses:</strong> KES <?php echo number_format($total_expenses, 2); ?></p>
    <p><strong>Variance:</strong> KES <?php echo number_format($budget_variance, 2); ?></p>
    <p><strong>Percentage Used:</strong> <?php echo $percentage_used; ?>%</p>
    <?php if ($budget_variance < 0): ?>
        <div class="alert alert-danger">⚠️ Project is over budget!</div>
    <?php elseif ($budget_variance > 0): ?>
        <div class="alert alert-success">✅ Project is under budget.</div>
    <?php else: ?>
        <div class="alert alert-info">ℹ️ Project is on budget.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>