<?php
// manager/projects.php - Project Management (Styled)

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
    if (($_SESSION['role'] ?? '') === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND created_by = ?");
        $stmt->execute([$_GET['delete'], $user_id]);
    }
    header('Location: projects.php');
    exit();
}

// Get all projects
$projects = get_projects_by_manager($pdo, $user_id);

include '../includes/header.php';
?>

<div class="projects-wrapper">
    <!-- Header -->
    <div class="projects-header">
        <div>
            <h2>📋 Projects</h2>
            <p>Manage all your construction projects from one place.</p>
        </div>
        <a href="project_create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Project
        </a>
    </div>

    <!-- Stats -->
    <div class="projects-stats">
        <div class="stat-box">
            <span class="stat-box-label">Total Projects</span>
            <span class="stat-box-number"><?php echo count($projects); ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">Ongoing</span>
            <span class="stat-box-number" style="color:#f57c00;">
                <?php echo count(array_filter($projects, function($p) { return $p['status'] == 'ongoing'; })); ?>
            </span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">Completed</span>
            <span class="stat-box-number" style="color:#2e7d32;">
                <?php echo count(array_filter($projects, function($p) { return $p['status'] == 'completed'; })); ?>
            </span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">On Hold</span>
            <span class="stat-box-number" style="color:#e74c3c;">
                <?php echo count(array_filter($projects, function($p) { return $p['status'] == 'on_hold'; })); ?>
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>My Projects</h3>
            <span class="badge badge-primary"><?php echo count($projects); ?> total</span>
        </div>

        <?php if (count($projects) > 0): ?>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>Status</th>
                            <th>Budget</th>
                            <th>Start</th>
                            <th>End</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo $project['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($project['name']); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo get_status_badge($project['status']); ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td>KES <?php echo number_format($project['budget'], 0); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($project['start_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($project['end_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="project_view.php?id=<?php echo $project['id']; ?>" class="btn-action view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="project_edit.php?id=<?php echo $project['id']; ?>" class="btn-action edit" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="projects.php?delete=<?php echo $project['id']; ?>" class="btn-action delete" title="Delete" onclick="return confirm('Delete this project?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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
                    <i class="fas fa-plus"></i> Create Your First Project
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ========== PROJECTS PAGE STYLES ========== */
.projects-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0;
}

/* Header */
.projects-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.projects-header h2 {
    font-size: 26px;
    color: #1a1a1a;
    margin: 0;
}

.projects-header p {
    color: #777;
    font-size: 15px;
    margin: 2px 0 0 0;
}

/* Stats */
.projects-stats {
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

.badge-planned { background: #6c757d; }
.badge-ongoing { background: linear-gradient(135deg, #f57c00, #e65100); }
.badge-completed { background: linear-gradient(135deg, #2e7d32, #1b5e20); }
.badge-on_hold { background: #f9a825; color: #1a1a1a; }
.badge-primary { background: #1565c0; }

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
}

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
}

.btn-action.view {
    background: rgba(21, 101, 192, 0.1);
    color: #1565c0;
}
.btn-action.view:hover {
    background: #1565c0;
    color: #fff;
    transform: translateY(-2px);
}

.btn-action.edit {
    background: rgba(245, 124, 0, 0.1);
    color: #f57c00;
}
.btn-action.edit:hover {
    background: #f57c00;
    color: #fff;
    transform: translateY(-2px);
}

.btn-action.delete {
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
    .projects-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .projects-header {
        flex-direction: column;
        align-items: stretch;
    }
    .projects-header h2 { font-size: 22px; }
    .projects-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .stat-box { padding: 14px 16px; }
    .stat-box-number { font-size: 22px; }
    .table-card { padding: 15px; }
    .modern-table { font-size: 13px; min-width: 600px; }
    .action-buttons { gap: 4px; }
    .btn-action { width: 28px; height: 28px; font-size: 11px; }
}

@media (max-width: 480px) {
    .projects-stats {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .stat-box { padding: 10px 12px; }
    .stat-box-number { font-size: 18px; }
    .btn { padding: 8px 16px; font-size: 13px; }
}
</style>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>