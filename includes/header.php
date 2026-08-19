<?php
// includes/header.php - Complete Header with Navigation

// Detect current directory
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Set base path
if ($current_dir == 'admin' || $current_dir == 'manager' || $current_dir == 'worker') {
    $base_path = '../';
} else {
    $base_path = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Management System</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1><a href="<?php echo $base_path; ?>index.php" style="color:#fff; text-decoration:none;">🏗️ Construction Management System</a></h1>
            <nav>
                <a href="<?php echo $base_path; ?>index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $base_path; ?>logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>login.php">Login</a>
                    <a href="<?php echo $base_path; ?>register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>