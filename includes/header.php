<?php
// includes/header.php - Header with Home Icon + Label

$current_dir = basename(dirname($_SERVER['PHP_SELF']));

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
    <title>Construction Project Management System</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Left: Title -->
            <div class="header-left">
                <h1><a href="<?php echo $base_path; ?>index.php">🏗️ Construction Project Management System</a></h1>
            </div>

            <!-- Right: Home + Navigation -->
            <div class="header-right">
                <a href="<?php echo $base_path; ?>index.php" class="header-home" title="Go to Homepage">
                    <i class="fas fa-home"></i> <span>Home</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $base_path; ?>logout.php" class="nav-btn nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>login.php" class="nav-btn nav-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                   
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>