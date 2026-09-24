<?php
// manager/project_create.php - Create New Project

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $budget = $_POST['budget'] ?? '';

    if (empty($name)) {
        $error = 'Project name is required.';
    } elseif (empty($start_date) || empty($end_date)) {
        $error = 'Start date and end date are required.';
    } elseif ($end_date < $start_date) {
        $error = 'End date must be after start date.';
    } elseif (!is_numeric($budget) || $budget < 0) {
        $error = 'Budget must be a positive number.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, start_date, end_date, budget, created_by, status) VALUES (?, ?, ?, ?, ?, ?, 'planned')");
        if ($stmt->execute([$name, $description, $start_date, $end_date, $budget, $user_id])) {
            $success = 'Project created successfully!';
            // Reset form
            $name = $description = $start_date = $end_date = $budget = '';
        } else {
            $error = 'Failed to create project. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Create New Project</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?> <a href="projects.php">View all projects</a></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="name">Project Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" placeholder="e.g., Riverside Apartments" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Briefly describe the project (optional)"><?php echo htmlspecialchars($description ?? ''); ?></textarea>

            <label for="start_date">Start Date *</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date ?? ''; ?>" required>

            <label for="end_date">End Date *</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date ?? ''; ?>" required>

            <label for="budget">Budget (KES) *</label>
            <input type="number" id="budget" name="budget" step="0.01" value="<?php echo $budget ?? ''; ?>" placeholder="e.g., 2500000" required>

            <button type="submit">Create Project</button>
        </form>
        <p class="form-link"><a href="projects.php">← Back to Projects</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>