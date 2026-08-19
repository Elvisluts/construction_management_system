<?php
// manager/expense_add.php - Record New Expense

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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? 0;
    $description = trim($_POST['description'] ?? '');
    $amount = $_POST['amount'] ?? 0;
    $expense_date = $_POST['expense_date'] ?? '';
    $category = $_POST['category'] ?? 'other';

    if (empty($project_id) || $project_id == 0) {
        $error = 'Please select a project.';
    } elseif (empty($description)) {
        $error = 'Expense description is required.';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = 'Amount must be a positive number.';
    } elseif (empty($expense_date)) {
        $error = 'Expense date is required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO expenses (project_id, description, amount, expense_date, category, recorded_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$project_id, $description, $amount, $expense_date, $category, $user_id])) {
            $success = 'Expense recorded successfully!';

            // Check if project is now over budget
            $total_spent = get_project_expenses($pdo, $project_id);
            $stmt = $pdo->prepare("SELECT budget FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            $project = $stmt->fetch();
            if ($project && $total_spent > $project['budget']) {
                $success .= ' ⚠️ Warning: This project is now over budget!';
            }
        } else {
            $error = 'Failed to record expense.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Record New Expense</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?> <a href="budget.php">View budget summary</a></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="project_id">Project *</label>
            <select id="project_id" name="project_id" required>
                <option value="">-- Select Project --</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo $project['id']; ?>">
                        <?php echo htmlspecialchars($project['name']); ?> 
                        (Budget: KES <?php echo number_format($project['budget'], 0); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Expense Description *</label>
            <input type="text" id="description" name="description" placeholder="e.g., Cement purchase" required>

            <label for="amount">Amount (KES) *</label>
            <input type="number" id="amount" name="amount" step="0.01" required>

            <label for="expense_date">Expense Date *</label>
            <input type="date" id="expense_date" name="expense_date" required>

            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="materials">Materials</option>
                <option value="labour">Labour</option>
                <option value="equipment">Equipment</option>
                <option value="transport">Transport</option>
                <option value="other">Other</option>
            </select>

            <button type="submit">Record Expense</button>
        </form>
        <p class="form-link"><a href="budget.php">← Back to Budget Summary</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>