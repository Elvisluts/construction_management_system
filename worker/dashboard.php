<?php
// worker/dashboard.php - Worker Dashboard (Styled)

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

<div class="worker-wrapper">
    <!-- Welcome -->
    <div class="worker-welcome">
        <div>
            <h2>👋 Worker Dashboard</h2>
            <p>Welcome back, <strong><?php echo htmlspecialchars($fullname); ?></strong>!</p>
        </div>
        <div class="welcome-badge">
            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($fullname); ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="worker-stats">
        <div class="stat-box">
            <span class="stat-box-label">Total Tasks</span>
            <span class="stat-box-number"><?php echo $total_tasks; ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">Completed</span>
            <span class="stat-box-number" style="color:#2e7d32;"><?php echo $completed_tasks; ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">In Progress</span>
            <span class="stat-box-number" style="color:#f57c00;"><?php echo $in_progress_tasks; ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">Pending</span>
            <span class="stat-box-number" style="color:#e74c3c;"><?php echo $pending_tasks; ?></span>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>📋 My Assigned Tasks</h3>
            <span class="badge badge-primary"><?php echo $total_tasks; ?> tasks</span>
        </div>

        <?php if (count($tasks) > 0): ?>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task Title</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($task['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo get_task_status_badge($task['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></td>
                                <td>
                                    <a href="task_update.php?id=<?php echo $task['id']; ?>" class="btn-action update" title="Update Status">
                                        <i class="fas fa-pen"></i> Update
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <p>No tasks assigned to you yet.</p>
                <p style="font-size:13px; color:#999;">Check back later or contact your project manager.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ========== WORKER DASHBOARD STYLES ========== */
.worker-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0;
}

/* Welcome */
.worker-welcome {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.worker-welcome h2 {
    font-size: 26px;
    color: #1a1a1a;
    margin: 0;
}

.worker-welcome p {
    color: #777;
    font-size: 15px;
    margin: 2px 0 0 0;
}

.welcome-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(245, 124, 0, 0.1), rgba(245, 124, 0, 0.05));
    color: #f57c00;
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid rgba(245, 124, 0, 0.15);
}

.welcome-badge i {
    font-size: 18px;
}

/* Stats */
.worker-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-box {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 18px 22px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    text-align: center;
}

.stat-box-label {
    display: block;
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-box-number {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
}

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

.badge-pending { background: #f9a825; color: #1a1a1a; }
.badge-in_progress { background: linear-gradient(135deg, #f57c00, #e65100); }
.badge-completed { background: linear-gradient(135deg, #2e7d32, #1b5e20); }
.badge-primary { background: #1565c0; }

/* Action Button */
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    background: rgba(245, 124, 0, 0.1);
    color: #f57c00;
}

.btn-action.update:hover {
    background: #f57c00;
    color: #fff;
    transform: translateY(-2px);
}

.btn-action i {
    font-size: 12px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-state p {
    color: #888;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 992px) {
    .worker-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .worker-welcome {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .worker-welcome h2 { font-size: 22px; }
    .welcome-badge { justify-content: center; }
    .worker-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .stat-box { padding: 14px 16px; }
    .stat-box-number { font-size: 22px; }
    .table-card { padding: 15px; }
    .modern-table { font-size: 13px; min-width: 550px; }
}

@media (max-width: 480px) {
    .worker-stats {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .stat-box { padding: 10px 12px; }
    .stat-box-number { font-size: 18px; }
    .btn-action { padding: 4px 12px; font-size: 12px; }
    .empty-icon { font-size: 32px; }
}
</style>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>