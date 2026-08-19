<?php
// manager/worker_edit.php - Edit Worker

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$worker_id = $_GET['id'] ?? 0;

// Get worker with user details
$stmt = $pdo->prepare("
    SELECT w.*, u.id as user_id, u.fullname, u.email, u.phone 
    FROM workers w 
    LEFT JOIN users u ON w.user_id = u.id 
    WHERE w.id = ?
");
$stmt->execute([$worker_id]);
$worker = $stmt->fetch();

if (!$worker) {
    header('Location: workers.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');
    $job_title = trim($_POST['job_title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $hire_date = $_POST['hire_date'] ?? '';

    if (empty($fullname) || empty($email)) {
        $error = 'Full name and email are required.';
    } else {
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullname, $email, $phone, $worker['user_id']]);

        // Update worker
        $stmt = $pdo->prepare("UPDATE workers SET national_id = ?, job_title = ?, department = ?, hire_date = ? WHERE id = ?");
        if ($stmt->execute([$national_id, $job_title, $department, $hire_date, $worker_id])) {
            $success = 'Worker updated successfully!';
            // Refresh data
            $stmt = $pdo->prepare("
                SELECT w.*, u.id as user_id, u.fullname, u.email, u.phone 
                FROM workers w 
                LEFT JOIN users u ON w.user_id = u.id 
                WHERE w.id = ?
            ");
            $stmt->execute([$worker_id]);
            $worker = $stmt->fetch();
        } else {
            $error = 'Failed to update worker.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Edit Worker</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <h4 style="margin-bottom:10px; color:#1a252f;">Account Details</h4>
            <label for="fullname">Full Name *</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($worker['fullname']); ?>" required>

            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($worker['email']); ?>" required>

            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($worker['phone']); ?>">

            <hr>

            <h4 style="margin-bottom:10px; color:#1a252f;">Worker Details</h4>
            <label for="national_id">National ID</label>
            <input type="text" id="national_id" name="national_id" value="<?php echo htmlspecialchars($worker['national_id']); ?>">

            <label for="job_title">Job Title</label>
            <input type="text" id="job_title" name="job_title" value="<?php echo htmlspecialchars($worker['job_title']); ?>">

            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($worker['department']); ?>">

            <label for="hire_date">Hire Date</label>
            <input type="date" id="hire_date" name="hire_date" value="<?php echo $worker['hire_date']; ?>">

            <button type="submit">Update Worker</button>
        </form>
        <p class="form-link"><a href="workers.php">← Back to Workers</a></p>
    </div>
</div>
