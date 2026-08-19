<?php
// manager/projects.php - Project Management

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
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND created_by = ?");
    $stmt->execute([$_GET['delete'], $user_id]);
    header('Location: projects.php');
    exit();
}

$projects = get_projects_by_manager($pdo, $user_id);

include '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Projects</h2>
    <a href="project_create.php" class="btn btn-success">+ New Project</a>
</div>

<div class="table-container">
    <h3>My Projects</h3>
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
                    <th>Actions</th>
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
                        <td>
                            <a href="project_view.php?id=<?php echo $project['id']; ?>" class="btn btn-blue btn-sm">View</a>
                            <a href="project_edit.php?id=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="projects.php?delete=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this project?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No projects found. <a href="project_create.php">Create your first project</a>.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>