<?php
// includes/auth.php - Authentication Functions

/**
 * Check if user is logged in
 * Redirects to login page if not
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Check if user has specific role
 * Redirects to dashboard if not
 */
function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        // Redirect based on role
        switch ($_SESSION['role']) {
            case 'admin':
                header('Location: admin/dashboard.php');
                break;
            case 'manager':
                header('Location: manager/dashboard.php');
                break;
            case 'worker':
                header('Location: worker/dashboard.php');
                break;
            default:
                header('Location: index.php');
        }
        exit();
    }
}

/**
 * Get current user information
 */
function get_logged_in_user($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

/**
 * Check if email already exists
 */
function email_exists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() ? true : false;
}

/**
 * Create new user
 */
function create_user($pdo, $fullname, $email, $password, $role, $phone = '') {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$fullname, $email, $hashed_password, $role, $phone]);
}

/**
 * Authenticate user login
 */
function authenticate_user($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

/**
 * Redirect user based on role
 */
function redirect_by_role($role) {
    switch ($role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'manager':
            header('Location: manager/dashboard.php');
            break;
        case 'worker':
            header('Location: worker/dashboard.php');
            break;
        default:
            header('Location: dashboard.php');
    }
    exit();
}

/**
 * Logout user
 */
function logout_user() {
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>