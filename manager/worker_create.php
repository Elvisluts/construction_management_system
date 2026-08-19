<?php
// manager/worker_create.php - Register New Worker

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');
    $job_title = trim($_POST['job_title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $hire_date = $_POST['hire_date'] ?? '';

    // Validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'Full name, email, and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Insert user with role 'worker'
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, phone) VALUES (?, ?, ?, 'worker', ?)");
            if ($stmt->execute([$fullname, $email, $hashed, $phone])) {
                $user_id = $pdo->lastInsertId();

                // Insert worker profile
                $stmt = $pdo->prepare("INSERT INTO workers (user_id, national_id, job_title, department, hire_date) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $national_id, $job_title, $department, $hire_date])) {
                    $success = 'Worker registered successfully!';
                } else {
                    $error = 'Failed to create worker profile.';
                }
            } else {
                $error = 'Failed to create user account.';
            }
        }
    }
}

include '../includes/header.php';
?>

<h2>Register New Worker</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?> <a href="workers.php">View all workers</a></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">

            <h4 style="margin-bottom:10px; color:#1a252f;">Account Details</h4>
            <label for="fullname">Full Name *</label>
            <input type="text" id="fullname" name="fullname" required>

            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required>
            <small>Minimum 6 characters</small>

            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone">

            <hr>

            <h4 style="margin-bottom:10px; color:#1a252f;">Worker Details</h4>
            <label for="national_id">National ID</label>
            <input type="text" id="national_id" name="national_id">

            <label for="job_title">Job Title</label>
            <input type="text" id="job_title" name="job_title">

            <label for="department">Department</label>
            <input type="text" id="department" name="department">

            <label for="hire_date">Hire Date</label>
            <input type="date" id="hire_date" name="hire_date">

            <button type="submit">Register Worker</button>
        </form>
        <p class="form-link"><a href="workers.php">← Back to Workers</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>