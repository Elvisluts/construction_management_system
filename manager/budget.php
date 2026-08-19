<?php
// manager/budget.php - Budget & Expense Management (Styled)

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

// Handle Expense Delete
if (isset($_GET['delete_expense']) && is_numeric($_GET['delete_expense'])) {
    $stmt = $pdo->prepare("DELETE e FROM expenses e JOIN projects p ON e.project_id = p.id WHERE e.id = ? AND p.created_by = ?");
    $stmt->execute([$_GET['delete_expense'], $user_id]);
    header('Location: budget.php');
    exit();
}

// Get all projects
$projects = get_projects_by_manager($pdo, $user_id);

// Get total expenses for each project
$project_expenses = [];
foreach ($projects as $project) {
    $project_expenses[$project['id']] = get_project_expenses($pdo, $project['id']);
}

// Get all expenses
$stmt = $pdo->prepare("
    SELECT e.*, p.name as project_name, u.fullname as recorded_by_name
    FROM expenses e
    JOIN projects p ON e.project_id = p.id
    LEFT JOIN users u ON e.recorded_by = u.id
    WHERE p.created_by = ?
    ORDER BY e.expense_date DESC, e.created_at DESC
");
$stmt->execute([$user_id]);
$all_expenses = $stmt->fetchAll();

// Calculate totals
$total_budget = 0;
$total_spent = 0;
foreach ($projects as $project) {
    $total_budget += $project['budget'];
    $total_spent += $project_expenses[$project['id']];
}
$total_variance = $total_budget - $total_spent;

include '../includes/header.php';
?>

<div class="budget-wrapper">
    <!-- Header -->
    <div class="budget-header">
        <div>
            <h2>💰 Budget & Expenses</h2>
            <p>Track your project budgets and expenses in real-time.</p>
        </div>
        <a href="expense_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Record Expense
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="budget-stats">
        <div class="stat-card stat-budget">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <span class="stat-label">Total Budget</span>
                <span class="stat-number">KES <?php echo number_format($total_budget, 0); ?></span>
            </div>
        </div>
        <div class="stat-card stat-spent">
            <div class="stat-icon">💸</div>
            <div class="stat-info">
                <span class="stat-label">Total Spent</span>
                <span class="stat-number">KES <?php echo number_format($total_spent, 0); ?></span>
            </div>
        </div>
        <div class="stat-card stat-variance">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <span class="stat-label">Total Variance</span>
                <span class="stat-number" style="color: <?php echo $total_variance >= 0 ? '#2e7d32' : '#e74c3c'; ?>;">
                    KES <?php echo number_format($total_variance, 0); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Project Budget Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>📋 Project Budget Overview</h3>
            <span class="badge badge-primary"><?php echo count($projects); ?> projects</span>
        </div>

        <?php if (count($projects) > 0): ?>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Budget</th>
                            <th>Spent</th>
                            <th>Remaining</th>
                            <th>Used %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): 
                            $budget = $project['budget'];
                            $spent = $project_expenses[$project['id']] ?? 0;
                            $remaining = $budget - $spent;
                            $percent = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0;
                            $status = $remaining >= 0 ? 'Under Budget' : 'Over Budget';
                            $status_class = $remaining >= 0 ? 'badge-success' : 'badge-danger';
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($project['name']); ?></strong></td>
                                <td>KES <?php echo number_format($budget, 0); ?></td>
                                <td>KES <?php echo number_format($spent, 0); ?></td>
                                <td style="color: <?php echo $remaining >= 0 ? '#2e7d32' : '#e74c3c'; ?>;">
                                    KES <?php echo number_format($remaining, 0); ?>
                                </td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo min($percent, 100); ?>%; background: <?php echo $percent > 90 ? '#e74c3c' : ($percent > 70 ? '#f57c00' : '#2e7d32'); ?>;"></div>
                                        <span><?php echo $percent; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📂</div>
                <p>No projects found.</p>
                <a href="project_create.php" class="btn btn-primary" style="margin-top:10px;">
                    <i class="fas fa-plus"></i> Create a Project
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Expenses -->
    <div class="table-card" style="margin-top: 25px;">
        <div class="table-header">
            <h3>📝 Recent Expenses</h3>
            <span class="badge badge-primary"><?php echo count($all_expenses); ?> records</span>
        </div>

        <?php if (count($all_expenses) > 0): ?>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Project</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_expenses as $expense): ?>
                            <tr>
                                <td><?php echo $expense['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($expense['description']); ?></strong></td>
                                <td><?php echo htmlspecialchars($expense['project_name']); ?></td>
                                <td>KES <?php echo number_format($expense['amount'], 0); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo get_category_badge($expense['category']); ?>">
                                        <?php echo ucfirst($expense['category']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?></td>
                                <td>
                                    <a href="budget.php?delete_expense=<?php echo $expense['id']; ?>" 
                                       class="btn-action delete" 
                                       onclick="return confirm('Delete this expense?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding:30px 20px;">
                <div class="empty-icon" style="font-size:32px;">📭</div>
                <p>No expenses recorded yet.</p>
                <a href="expense_add.php" class="btn btn-primary" style="margin-top:10px;">
                    <i class="fas fa-plus"></i> Record Your First Expense
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ========== BUDGET PAGE STYLES ========== */
.budget-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0;
}

/* Header */
.budget-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.budget-header h2 {
    font-size: 26px;
    color: #1a1a1a;
    margin: 0;
}

.budget-header p {
    color: #777;
    font-size: 15px;
    margin: 2px 0 0 0;
}

/* Stats */
.budget-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-icon {
    font-size: 32px;
    line-height: 1;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 13px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
}

.stat-budget .stat-number { color: #1565c0; }
.stat-spent .stat-number { color: #e74c3c; }
.stat-variance .stat-number { color: #2e7d32; }

/* Table */
.table-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.table-header h3 {
    font-size: 18px;
    color: #1a1a1a;
    margin: 0;
}

.table-responsive {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.modern-table thead th {
    background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
    color: #fff;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    white-space: nowrap;
}

.modern-table tbody td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.modern-table tbody tr:hover {
    background: rgba(245, 124, 0, 0.04);
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}

.badge-primary { background: #1565c0; }
.badge-success { background: #2e7d32; }
.badge-danger { background: #e74c3c; }
.badge-warning { background: #f9a825; color: #1a1a1a; }
.badge-labour { background: #6a1b9a; }
.badge-materials { background: #1565c0; }
.badge-equipment { background: #2e7d32; }
.badge-transport { background: #f9a825; color: #1a1a1a; }
.badge-other { background: #6c757d; }

/* Progress Bar */
.progress-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 100px;
}

.progress-bar .progress-fill {
    height: 6px;
    border-radius: 4px;
    flex: 1;
    min-width: 50px;
    background: #e0e0e0;
}

.progress-bar span {
    font-size: 13px;
    font-weight: 600;
    min-width: 40px;
}

/* Action Button */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.btn-action.delete:hover {
    background: #e74c3c;
    color: #fff;
    transform: translateY(-2px);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #f57c00, #e65100);
    color: #fff;
    box-shadow: 0 4px 15px rgba(245, 124, 0, 0.3);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(245, 124, 0, 0.4);
}

.btn i { font-size: 14px; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 30px 20px;
}

.empty-icon {
    font-size: 36px;
    margin-bottom: 10px;
}

.empty-state p {
    color: #888;
    font-size: 15px;
}

/* Responsive */
@media (max-width: 992px) {
    .budget-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .budget-header {
        flex-direction: column;
        align-items: stretch;
    }
    .budget-header h2 { font-size: 22px; }
    .budget-stats {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .stat-card { padding: 15px 18px; }
    .stat-number { font-size: 22px; }
    .table-card { padding: 15px; }
    .modern-table { font-size: 13px; min-width: 600px; }
}

@media (max-width: 480px) {
    .budget-stats {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .stat-card { padding: 12px 15px; }
    .stat-number { font-size: 20px; }
    .btn { padding: 8px 16px; font-size: 13px; }
    .progress-bar { min-width: 70px; }
}
</style>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>