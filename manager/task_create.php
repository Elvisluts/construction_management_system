<?php
// manager/task_create.php - Create Task with Assignment

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

// Get projects created by this manager
$projects = get_projects_by_manager($pdo, $user_id);

// Get all workers (users with role 'worker')
$stmt = $pdo->query("
    SELECT u.id, u.fullname, u.email, w.job_title 
    FROM users u 
    LEFT JOIN workers w ON u.id = w.user_id 
    WHERE u.role = 'worker'
    ORDER BY u.fullname ASC
");
$workers = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $assigned_to = $_POST['assigned_to'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    // Validation
    if (empty($title)) {
        $error = 'Task title is required.';
    } elseif (empty($project_id) || $project_id == 0) {
        $error = 'Please select a project.';
    } elseif (!get_accessible_project($pdo, $project_id, $user_id)) {
        $error = 'You do not have access to that project.';
    } elseif (empty($due_date)) {
        $error = 'Due date is required.';
    } else {
        // If no worker selected, set to NULL
        $assigned_to = !empty($assigned_to) ? $assigned_to : null;

        $stmt = $pdo->prepare("
            INSERT INTO tasks (project_id, title, description, assigned_to, due_date, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");

        if ($stmt->execute([$project_id, $title, $description, $assigned_to, $due_date])) {
            $success = 'Task created successfully!';

            // If task was assigned, show who it was assigned to
            if ($assigned_to) {
                // Get worker name
                $stmt2 = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
                $stmt2->execute([$assigned_to]);
                $worker = $stmt2->fetch();
                $success .= ' Assigned to: ' . htmlspecialchars($worker['fullname']);
            } else {
                $success .= ' (Unassigned)';
            }
        } else {
            $error = 'Failed to create task. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Create New Task</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?> <a href="tasks.php">View all tasks</a></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">

            <label for="project_id">Select Project *</label>
            <select id="project_id" name="project_id" required>
                <option value="">-- Select Project --</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo $project['id']; ?>">
                        <?php echo htmlspecialchars($project['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="title">Task Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter task title" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Enter task description (optional)"></textarea>

            <label for="assigned_to">Assign To</label>
            <select id="assigned_to" name="assigned_to">
                <option value="">-- Unassigned --</option>
                <?php foreach ($workers as $worker): ?>
                    <option value="<?php echo $worker['id']; ?>">
                        <?php echo htmlspecialchars($worker['fullname']); ?>
                        <?php if ($worker['job_title']): ?>
                            (<?php echo htmlspecialchars($worker['job_title']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="due_date">Due Date *</label>
            <input type="date" id="due_date" name="due_date" required>

            <button type="submit">Create Task</button>
        </form>
        <p class="form-link"><a href="tasks.php">← Back to Tasks</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>