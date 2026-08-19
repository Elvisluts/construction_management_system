<?php
// reports.php - Reports with Tabs & Pie Chart

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_login();

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;
$role = $user['role'] ?? '';
$fullname = $user['fullname'] ?? 'User';

if ($role === 'worker') {
    header('Location: worker/dashboard.php');
    exit();
}

$is_admin = ($role === 'admin');

// Get data
if ($is_admin) {
    $projects = $pdo->query("SELECT p.*, u.fullname as manager_name FROM projects p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC")->fetchAll();
} else {
    $projects = get_projects_by_manager($pdo, $user_id);
}

$workers = $pdo->query("SELECT w.*, u.fullname, u.email FROM workers w LEFT JOIN users u ON w.user_id = u.id ORDER BY u.fullname ASC")->fetchAll();
$materials = $pdo->query("SELECT * FROM materials ORDER BY name ASC")->fetchAll();

if ($is_admin) {
    $expenses = $pdo->query("SELECT e.*, p.name as project_name, u.fullname as recorded_by_name FROM expenses e JOIN projects p ON e.project_id = p.id LEFT JOIN users u ON e.recorded_by = u.id ORDER BY e.expense_date DESC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT e.*, p.name as project_name, u.fullname as recorded_by_name FROM expenses e JOIN projects p ON e.project_id = p.id LEFT JOIN users u ON e.recorded_by = u.id WHERE p.created_by = ? ORDER BY e.expense_date DESC");
    $stmt->execute([$user_id]);
    $expenses = $stmt->fetchAll();
}

// Calculate totals for chart
$total_budget = 0;
$total_expenses = 0;
$project_names = [];
$project_expenses = [];

foreach ($projects as $project) {
    $spent = get_project_expenses($pdo, $project['id']);
    $total_budget += $project['budget'];
    $total_expenses += $spent;
    $project_names[] = $project['name'];
    $project_expenses[] = $spent;
}

// Determine default tab
$tab = $_GET['tab'] ?? 'projects';

include 'includes/header.php';
?>

<div class="reports-wrapper">
    <h2>📊 Reports Dashboard</h2>
    <p>Welcome, <strong><?php echo htmlspecialchars($fullname); ?></strong>!</p>

    <!-- Tab Navigation -->
    <div class="reports-tabs">
        <a href="?tab=projects" class="tab-btn <?php echo $tab == 'projects' ? 'active' : ''; ?>">📋 Projects</a>
        <a href="?tab=budget" class="tab-btn <?php echo $tab == 'budget' ? 'active' : ''; ?>">💰 Budget</a>
        <a href="?tab=workers" class="tab-btn <?php echo $tab == 'workers' ? 'active' : ''; ?>">👷 Workers</a>
        <a href="?tab=materials" class="tab-btn <?php echo $tab == 'materials' ? 'active' : ''; ?>">📦 Materials</a>
    </div>

    <!-- ============================================ -->
    <!-- TAB 1: PROJECT REPORT -->
    <!-- ============================================ -->
    <?php if ($tab == 'projects'): ?>
    <div class="report-section">
        <div class="report-header">
            <h3>📋 Project Progress Report</h3>
            <button onclick="window.print();" class="btn btn-sm btn-primary">🖨️ Print</button>
        </div>
        <?php if (count($projects) > 0): ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project Name</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Budget</th>
                        <th>Expenses</th>
                        <th>Variance</th>
                        <th>Tasks</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): 
                        $expenses_total = get_project_expenses($pdo, $project['id']);
                        $total_tasks = get_project_tasks($pdo, $project['id']);
                        $completed_tasks = get_completed_tasks($pdo, $project['id']);
                        $variance = $project['budget'] - $expenses_total;
                    ?>
                        <tr>
                            <td><?php echo $project['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($project['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($project['manager_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge badge-<?php echo get_status_badge($project['status']); ?>"><?php echo ucfirst($project['status']); ?></span></td>
                            <td>KES <?php echo number_format($project['budget'], 0); ?></td>
                            <td>KES <?php echo number_format($expenses_total, 0); ?></td>
                            <td style="color: <?php echo $variance >= 0 ? '#2e7d32' : '#e74c3c'; ?>;">KES <?php echo number_format($variance, 0); ?></td>
                            <td><?php echo $total_tasks; ?></td>
                            <td><?php echo $completed_tasks; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No projects found.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- TAB 2: BUDGET REPORT WITH PIE CHART -->
    <!-- ============================================ -->
    <?php if ($tab == 'budget'): ?>
    <div class="report-section">
        <div class="report-header">
            <h3>💰 Budget Summary Report</h3>
            <button onclick="window.print();" class="btn btn-sm btn-primary">🖨️ Print</button>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
            <div class="stat-card stat-projects">
                <div class="stat-info">
                    <span class="stat-label">Total Budget</span>
                    <span class="stat-number">KES <?php echo number_format($total_budget, 0); ?></span>
                </div>
            </div>
            <div class="stat-card stat-expenses">
                <div class="stat-info">
                    <span class="stat-label">Total Expenses</span>
                    <span class="stat-number">KES <?php echo number_format($total_expenses, 0); ?></span>
                </div>
            </div>
            <div class="stat-card stat-users">
                <div class="stat-info">
                    <span class="stat-label">Under Budget</span>
                    <span class="stat-number"><?php 
                        $under = 0; foreach ($projects as $p) { if (get_project_expenses($pdo, $p['id']) < $p['budget']) $under++; } 
                        echo $under; 
                    ?></span>
                </div>
            </div>
            <div class="stat-card stat-tasks">
                <div class="stat-info">
                    <span class="stat-label">Over Budget</span>
                    <span class="stat-number"><?php 
                        $over = 0; foreach ($projects as $p) { if (get_project_expenses($pdo, $p['id']) > $p['budget']) $over++; } 
                        echo $over; 
                    ?></span>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div style="max-width: 400px; margin: 0 auto 30px;">
            <canvas id="budgetChart" width="400" height="400"></canvas>
        </div>

        <!-- Budget Table -->
        <?php if (count($projects) > 0): ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Budget</th>
                        <th>Expenses</th>
                        <th>Remaining</th>
                        <th>Used %</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): 
                        $spent = get_project_expenses($pdo, $project['id']);
                        $remaining = $project['budget'] - $spent;
                        $percent = $project['budget'] > 0 ? round(($spent / $project['budget']) * 100, 1) : 0;
                        $status = $remaining >= 0 ? '✅ Under Budget' : '❌ Over Budget';
                        $status_color = $remaining >= 0 ? '#2e7d32' : '#e74c3c';
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($project['name']); ?></strong></td>
                            <td>KES <?php echo number_format($project['budget'], 0); ?></td>
                            <td>KES <?php echo number_format($spent, 0); ?></td>
                            <td style="color: <?php echo $status_color; ?>;">KES <?php echo number_format($remaining, 0); ?></td>
                            <td><?php echo $percent; ?>%</td>
                            <td style="color: <?php echo $status_color; ?>;"><?php echo $status; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No projects found.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- TAB 3: WORKER REPORT -->
    <!-- ============================================ -->
    <?php if ($tab == 'workers'): ?>
    <div class="report-section">
        <div class="report-header">
            <h3>👷 Worker Performance Report</h3>
            <button onclick="window.print();" class="btn btn-sm btn-primary">🖨️ Print</button>
        </div>
        <?php if (count($workers) > 0): ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Worker Name</th>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>In Progress</th>
                        <th>Pending</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workers as $worker): 
                        $stmt2 = $pdo->prepare("SELECT * FROM tasks WHERE assigned_to = ?");
                        $stmt2->execute([$worker['user_id']]);
                        $worker_tasks = $stmt2->fetchAll();
                        $total = count($worker_tasks);
                        $completed = 0; $in_progress = 0; $pending = 0;
                        foreach ($worker_tasks as $task) {
                            if ($task['status'] === 'completed') $completed++;
                            elseif ($task['status'] === 'in_progress') $in_progress++;
                            else $pending++;
                        }
                        $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><?php echo $worker['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($worker['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($worker['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($worker['department']); ?></td>
                            <td><?php echo $total; ?></td>
                            <td style="color: #2e7d32;"><?php echo $completed; ?></td>
                            <td style="color: #f57c00;"><?php echo $in_progress; ?></td>
                            <td style="color: #e74c3c;"><?php echo $pending; ?></td>
                            <td><span class="badge <?php echo $rate >= 70 ? 'badge-completed' : ($rate >= 40 ? 'badge-warning' : 'badge-danger'); ?>"><?php echo $rate; ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No workers found.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- TAB 4: MATERIAL REPORT -->
    <!-- ============================================ -->
    <?php if ($tab == 'materials'): ?>
    <div class="report-section">
        <div class="report-header">
            <h3>📦 Material Inventory Report</h3>
            <button onclick="window.print();" class="btn btn-sm btn-primary">🖨️ Print</button>
        </div>
        <?php 
        $total_items = count($materials);
        $total_value = 0;
        $low_stock_items = 0;
        foreach ($materials as $material) {
            $total_value += $material['quantity'] * $material['unit_price'];
            if ($material['quantity'] <= $material['reorder_level'] && $material['reorder_level'] > 0) $low_stock_items++;
        }
        ?>
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
            <div class="stat-card stat-projects">
                <div class="stat-info">
                    <span class="stat-label">Total Items</span>
                    <span class="stat-number"><?php echo $total_items; ?></span>
                </div>
            </div>
            <div class="stat-card stat-expenses">
                <div class="stat-info">
                    <span class="stat-label">Total Value</span>
                    <span class="stat-number">KES <?php echo number_format($total_value, 0); ?></span>
                </div>
            </div>
            <div class="stat-card stat-tasks">
                <div class="stat-info">
                    <span class="stat-label">Low Stock Items</span>
                    <span class="stat-number"><?php echo $low_stock_items; ?></span>
                </div>
            </div>
        </div>
        <?php if (count($materials) > 0): ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Material Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                        <th>Supplier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($material['name']); ?></strong></td>
                            <td><?php echo $material['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($material['unit']); ?></td>
                            <td>KES <?php echo number_format($material['unit_price'], 0); ?></td>
                            <td>KES <?php echo number_format($material['quantity'] * $material['unit_price'], 0); ?></td>
                            <td><?php echo htmlspecialchars($material['supplier']); ?></td>
                            <td>
                                <?php if ($material['quantity'] <= $material['reorder_level'] && $material['reorder_level'] > 0): ?>
                                    <span class="badge badge-danger">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-completed">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No materials found.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js for Pie Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if ($tab == 'budget' && count($projects) > 0): ?>
// Pie Chart: Project Expenses Distribution
const ctx = document.getElementById('budgetChart').getContext('2d');
const projectNames = <?php echo json_encode($project_names); ?>;
const projectExpenses = <?php echo json_encode($project_expenses); ?>;

new Chart(ctx, {
    type: 'pie',
    data: {
        labels: projectNames,
        datasets: [{
            data: projectExpenses,
            backgroundColor: [
                '#f57c00', '#2e7d32', '#1565c0', '#e74c3c', 
                '#6a1b9a', '#f9a825', '#1abc9c', '#e67e22'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { size: 12 },
                    padding: 15
                }
            },
            title: {
                display: true,
                text: 'Expenses by Project',
                font: { size: 16, weight: 'bold' },
                color: '#1a1a1a'
            }
        }
    }
});
<?php endif; ?>
</script>

<style>
.reports-wrapper { max-width: 1200px; margin: 0 auto; padding: 10px 0; }
.reports-wrapper h2 { font-size: 26px; color: #1a1a1a; margin-bottom: 2px; }
.reports-wrapper > p { color: #777; font-size: 15px; margin-bottom: 25px; }

/* Tabs */
.reports-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.tab-btn {
    padding: 10px 25px;
    border-radius: 8px 8px 0 0;
    text-decoration: none;
    color: #555;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    background: transparent;
    border-bottom: 3px solid transparent;
}

.tab-btn:hover {
    color: #f57c00;
    background: rgba(245, 124, 0, 0.05);
}

.tab-btn.active {
    color: #f57c00;
    border-bottom: 3px solid #f57c00;
    background: rgba(245, 124, 0, 0.05);
}

/* Report Section */
.report-section {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.report-header h3 { font-size: 20px; color: #1a1a1a; }

/* Chart Container */
#budgetChart {
    max-width: 400px;
    max-height: 400px;
    margin: 0 auto;
}

/* Reuse existing styles */
.stats-grid { display: grid; gap: 20px; margin-bottom: 30px; }
.stat-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: transform 0.3s ease;
}
.stat-card:hover { transform: translateY(-4px); }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-number { font-size: 28px; font-weight: 700; color: #1a1a1a; }
.stat-projects .stat-number { color: #f57c00; }
.stat-expenses .stat-number { color: #e74c3c; }
.stat-users .stat-number { color: #1565c0; }
.stat-tasks .stat-number { color: #2e7d32; }

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
}
.modern-table tbody td { padding: 10px 15px; border-bottom: 1px solid #f0f0f0; }
.modern-table tbody tr:hover { background: rgba(245, 124, 0, 0.05); }

.badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}
.badge-planned { background: #6c757d; }
.badge-ongoing { background: linear-gradient(135deg, #f57c00, #e65100); }
.badge-completed { background: linear-gradient(135deg, #2e7d32, #1b5e20); }
.badge-on_hold { background: #f9a825; color: #1a1a1a; }
.badge-danger { background: #e74c3c; }
.badge-warning { background: #f9a825; color: #1a1a1a; }

.empty-state { color: #999; text-align: center; padding: 30px 0; }

@media (max-width: 768px) {
    .reports-tabs { flex-direction: column; align-items: stretch; }
    .tab-btn { text-align: center; border-radius: 8px; border-bottom: none; }
    .tab-btn.active { border-bottom: none; border-left: 4px solid #f57c00; }
    .report-header { flex-direction: column; gap: 10px; align-items: flex-start; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .modern-table { font-size: 12px; min-width: 500px; }
}
@media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } }
</style>

<?php include 'includes/footer.php'; ?>