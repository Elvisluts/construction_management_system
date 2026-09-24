<?php
// includes/header.php - Shared application header

$current_dir = basename(dirname($_SERVER['PHP_SELF']));

if ($current_dir == 'admin' || $current_dir == 'manager' || $current_dir == 'worker') {
    $base_path = '../';
} else {
    $base_path = '';
}

$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
$dashboard_path = $base_path . 'index.php';
$navigation = [];

if ($is_logged_in) {
    switch ($role) {
        case 'admin':
            $dashboard_path = $base_path . 'admin/dashboard.php';
            $navigation = [
                ['label' => 'Users', 'icon' => 'fa-users', 'path' => $base_path . 'admin/users.php'],
                ['label' => 'Projects', 'icon' => 'fa-clipboard-list', 'path' => $base_path . 'manager/projects.php'],
                ['label' => 'Tasks', 'icon' => 'fa-list-check', 'path' => $base_path . 'manager/tasks.php'],
                ['label' => 'Workers', 'icon' => 'fa-hard-hat', 'path' => $base_path . 'manager/workers.php'],
                ['label' => 'Materials', 'icon' => 'fa-boxes-stacked', 'path' => $base_path . 'manager/materials.php'],
                ['label' => 'Budget', 'icon' => 'fa-wallet', 'path' => $base_path . 'manager/budget.php'],
                ['label' => 'Reports', 'icon' => 'fa-chart-column', 'path' => $base_path . 'reports.php']
            ];
            break;
        case 'manager':
            $dashboard_path = $base_path . 'manager/dashboard.php';
            $navigation = [
                ['label' => 'Projects', 'icon' => 'fa-clipboard-list', 'path' => $base_path . 'manager/projects.php'],
                ['label' => 'Tasks', 'icon' => 'fa-list-check', 'path' => $base_path . 'manager/tasks.php'],
                ['label' => 'Workers', 'icon' => 'fa-hard-hat', 'path' => $base_path . 'manager/workers.php'],
                ['label' => 'Materials', 'icon' => 'fa-boxes-stacked', 'path' => $base_path . 'manager/materials.php'],
                ['label' => 'Reports', 'icon' => 'fa-chart-column', 'path' => $base_path . 'reports.php']
            ];
            break;
        case 'worker':
            $dashboard_path = $base_path . 'worker/dashboard.php';
            $navigation = [
                ['label' => 'My Tasks', 'icon' => 'fa-list-check', 'path' => $base_path . 'worker/dashboard.php']
            ];
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Project Management System</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Left: Title -->
            <div class="header-left">
                <h1><a href="<?php echo $base_path; ?>index.php">🏗️ Construction Project Management System</a></h1>
            </div>

            <!-- Right: navigation and account actions -->
            <div class="header-right">
                <?php if ($is_logged_in): ?>
                    <nav class="header-nav" aria-label="Main navigation">
                        <a href="<?php echo $dashboard_path; ?>" class="header-home" title="Go to your dashboard">
                            <i class="fas fa-home"></i> <span>Dashboard</span>
                        </a>
                        <?php foreach ($navigation as $item): ?>
                            <a href="<?php echo $item['path']; ?>" class="header-link">
                                <i class="fas <?php echo $item['icon']; ?>"></i> <span><?php echo $item['label']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <div class="header-actions">
                        <a href="<?php echo $dashboard_path; ?>" class="header-back" title="Return to the previous page" onclick="if (document.referrer && document.referrer.indexOf(window.location.origin) === 0) { window.history.back(); return false; }">
                            <i class="fas fa-arrow-left"></i> <span>Back</span>
                        </a>
                        <a href="<?php echo $base_path; ?>logout.php" class="nav-btn nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>index.php" class="header-home" title="Go to Homepage">
                        <i class="fas fa-home"></i> <span>Home</span>
                    </a>
                <?php endif; ?>
                <?php if (!$is_logged_in): ?>
                    <a href="<?php echo $base_path; ?>login.php" class="nav-btn nav-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>