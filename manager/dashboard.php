<?php
// manager/dashboard.php - Manager Dashboard with Materials Card (No Sidebar)

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$fullname = $user['fullname'] ?? 'Manager';
$user_id = $user['id'] ?? 0;

include '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <h2>Manager Dashboard</h2>
        <p>Welcome, <strong><?php echo htmlspecialchars($fullname); ?></strong>!</p>
    </div>

    <div class="stats-grid">
        <a href="projects.php" class="stat-card stat-projects" style="text-decoration:none;">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <span class="stat-label">My Projects</span>
                <span class="stat-number"><?php echo count(get_projects_by_manager($pdo, $user_id)); ?></span>
            </div>
        </a>
        <a href="projects.php" class="stat-card stat-ongoing" style="text-decoration:none;">
            <div class="stat-icon">⚡</div>
            <div class="stat-info">
                <span class="stat-label">Ongoing Projects</span>
                <span class="stat-number"><?php echo get_ongoing_projects($pdo); ?></span>
            </div>
        </a>
        <a href="tasks.php" class="stat-card stat-tasks" style="text-decoration:none;">
            <div class="stat-icon">📌</div>
            <div class="stat-info">
                <span class="stat-label">Pending Tasks</span>
                <span class="stat-number"><?php echo get_pending_tasks($pdo); ?></span>
            </div>
        </a>
        <a href="workers.php" class="stat-card stat-workers" style="text-decoration:none;">
            <div class="stat-icon">👷</div>
            <div class="stat-info">
                <span class="stat-label">Total Workers</span>
                <span class="stat-number"><?php echo get_total_workers($pdo); ?></span>
            </div>
        </a>
        <!-- MATERIALS CARD - ADDED -->
        <a href="materials.php" class="stat-card stat-materials" style="text-decoration:none;">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <span class="stat-label">Materials</span>
                <span class="stat-number">View Stock</span>
            </div>
        </a>
        <a href="../reports.php" class="stat-card stat-reports" style="text-decoration:none;">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <span class="stat-label">Reports</span>
                <span class="stat-number">View All</span>
            </div>
        </a>
    </div>

    <?php $low_stock = get_low_stock_materials($pdo); ?>
    <?php if (count($low_stock) > 0): ?>
        <div class="alert-stock">
            <span class="alert-icon">⚠️</span>
            <div>
                <strong>Low Stock Alert</strong>
                <ul>
                    <?php foreach ($low_stock as $material): ?>
                        <li><?php echo htmlspecialchars($material['name']); ?> — Stock: <?php echo $material['quantity']; ?> <?php echo $material['unit']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-header">
            <h3>📋 Recent Projects</h3>
        </div>
        <?php $projects = get_recent_projects($pdo, 5); ?>
        <?php if (count($projects) > 0): ?>
            <table class="modern-table">
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
                            <td><strong><?php echo htmlspecialchars($project['name']); ?></strong></td>
                            <td><span class="badge badge-<?php echo get_status_badge($project['status']); ?>"><?php echo ucfirst($project['status']); ?></span></td>
                            <td>KES <?php echo number_format($project['budget'], 0); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($project['start_date'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($project['end_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No projects found.</p>
        <?php endif; ?>
    </div>
</div>

<style>
/* Materials Card */
.stat-materials {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(232, 245, 233, 0.6));
    border-left: 4px solid #2e7d32;
}
.stat-materials .stat-number { color: #2e7d32; }

.stat-reports {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(243, 229, 245, 0.6));
    border-left: 4px solid #6a1b9a;
}
.stat-reports .stat-number { color: #6a1b9a; }

.dashboard-wrapper { max-width: 1200px; margin: 0 auto; padding: 10px 0; }
.dashboard-header h2 { font-size: 26px; color: #1a1a1a; margin-bottom: 2px; }
.dashboard-header p { color: #777; font-size: 15px; margin-bottom: 25px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
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
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-icon { font-size: 32px; line-height: 1; transition: transform 0.3s ease; }
.stat-card:hover .stat-icon { transform: scale(1.2) rotate(-5deg); }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-number { font-size: 28px; font-weight: 700; color: #1a1a1a; }

.stat-projects .stat-number { color: #f57c00; }
.stat-ongoing .stat-number { color: #2e7d32; }
.stat-tasks .stat-number { color: #e74c3c; }
.stat-workers .stat-number { color: #1565c0; }
.stat-materials .stat-number { color: #2e7d32; }
.stat-reports .stat-number { color: #6a1b9a; }

.alert-stock {
    background: rgba(255, 248, 225, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-left: 5px solid #f57c00;
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 25px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.alert-stock ul { margin: 5px 0 0 0; padding-left: 20px; color: #555; }
.alert-icon { font-size: 22px; }

.table-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow-x: auto;
}

.table-header h3 { font-size: 18px; color: #1a1a1a; margin-bottom: 15px; }

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

.modern-table tbody td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

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

.empty-state { color: #999; text-align: center; padding: 20px 0; }

@media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { 
    .stats-grid { grid-template-columns: 1fr; }
    .stat-card { padding: 15px 20px; }
    .stat-number { font-size: 22px; }
    .table-card { padding: 15px; overflow-x: auto; }
    .modern-table { font-size: 13px; min-width: 500px; }
}
</style>

<?php include '../includes/footer.php'; ?>