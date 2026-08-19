<?php
// manager/project_edit.php - Edit Project

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

$error = '';
$success = '';

// Get project ID
$project_id = $_GET['id'] ?? 0;
if (!is_numeric($project_id) || $project_id <= 0) {
    header('Location: projects.php');
    exit();
}

// Fetch project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND created_by = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: projects.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $status = $_POST['status'] ?? 'planned';

    if (empty($name)) {
        $error = 'Project name is required.';
    } elseif (empty($start_date) || empty($end_date)) {
        $error = 'Start date and end date are required.';
    } elseif ($end_date < $start_date) {
        $error = 'End date must be after start date.';
    } elseif (!is_numeric($budget) || $budget < 0) {
        $error = 'Budget must be a positive number.';
    } else {
        $stmt = $pdo->prepare("UPDATE projects SET name = ?, description = ?, start_date = ?, end_date = ?, budget = ?, status = ? WHERE id = ? AND created_by = ?");
        if ($stmt->execute([$name, $description, $start_date, $end_date, $budget, $status, $project_id, $user_id])) {
            $success = 'Project updated successfully!';
            // Refresh project data
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND created_by = ?");
            $stmt->execute([$project_id, $user_id]);
            $project = $stmt->fetch();
        } else {
            $error = 'Failed to update project. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Edit Project</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="name">Project Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($project['description']); ?></textarea>

            <label for="start_date">Start Date *</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $project['start_date']; ?>" required>

            <label for="end_date">End Date *</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $project['end_date']; ?>" required>

            <label for="budget">Budget (KES) *</label>
            <input type="number" id="budget" name="budget" step="0.01" value="<?php echo $project['budget']; ?>" required>

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="planned" <?php echo $project['status'] == 'planned' ? 'selected' : ''; ?>>Planned</option>
                <option value="ongoing" <?php echo $project['status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                <option value="completed" <?php echo $project['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="on_hold" <?php echo $project['status'] == 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
            </select>

            <button type="submit">Update Project</button>
        </form>
        <p class="form-link"><a href="projects.php">← Back to Projects</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>