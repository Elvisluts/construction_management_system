<?php
// index.php - Landing Page (Pure HTML + CSS)
require_once 'config/database.php';

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] == 'manager') {
        header('Location: manager/dashboard.php');
    } else {
        header('Location: worker/dashboard.php');
    }
    exit();
}

include 'includes/header.php';
?>

<div class="landing-container">
    <div class="landing-box">
        <h1>🏗️ CONSTRUCTION PROJECT MANAGEMENT SYSTEM</h1>
        <p>A simple platform to manage construction projects, workers, materials, and budgets efficiently.</p>
        <hr>
        <div class="landing-buttons">
            <a href="login.php" class="btn btn-dark">Login</a>
            <a href="register.php" class="btn btn-blue">Register</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>