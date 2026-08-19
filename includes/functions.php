<?php
// includes/functions.php - Complete Reusable Functions

/**
 * Sanitize input data
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Generate a random string (for IDs or tokens)
 */
function generate_random_string($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

/**
 * Format currency
 */
function format_currency($amount) {
    return 'KES ' . number_format($amount, 2);
}

/**
 * Get project status badge color
 */
function get_status_badge($status) {
    $colors = [
        'planned' => 'planned',
        'ongoing' => 'ongoing',
        'completed' => 'completed',
        'on_hold' => 'on_hold'
    ];
    return $colors[$status] ?? 'planned';
}

/**
 * Get task status badge color
 */
function get_task_status_badge($status) {
    $colors = [
        'pending' => 'pending',
        'in_progress' => 'in_progress',
        'completed' => 'completed'
    ];
    return $colors[$status] ?? 'pending';
}

/**
 * Get expense category badge color
 */
function get_category_badge($category) {
    $colors = [
        'labour' => 'labour',
        'materials' => 'materials',
        'equipment' => 'equipment',
        'transport' => 'transport',
        'other' => 'other'
    ];
    return $colors[$category] ?? 'other';
}

/**
 * Calculate budget variance
 */
function calculate_variance($budget, $spent) {
    return $budget - $spent;
}

/**
 * Calculate percentage used
 */
function calculate_percentage($budget, $spent) {
    if ($budget <= 0) return 0;
    return round(($spent / $budget) * 100, 2);
}

/**
 * Get total expenses for a project
 */
function get_project_expenses($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total tasks for a project
 */
function get_project_tasks($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get completed tasks for a project
 */
function get_completed_tasks($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE project_id = ? AND status = 'completed'");
    $stmt->execute([$project_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total workers assigned to a project
 */
function get_project_workers($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM project_assignments WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get low stock materials
 */
function get_low_stock_materials($pdo) {
    $stmt = $pdo->query("SELECT * FROM materials WHERE quantity <= reorder_level AND reorder_level > 0");
    return $stmt->fetchAll();
}

/**
 * ============================================
 * DASHBOARD STATISTICS FUNCTIONS
 * ============================================
 */

/**
 * Get total number of users
 */
function get_total_users($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total number of projects
 */
function get_total_projects($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total number of ongoing projects
 */
function get_ongoing_projects($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE status = 'ongoing'");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total number of completed projects
 */
function get_completed_projects_count($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE status = 'completed'");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total number of workers
 */
function get_total_workers($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM workers");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total number of tasks
 */
function get_total_tasks($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tasks");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total number of pending tasks
 */
function get_pending_tasks($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tasks WHERE status = 'pending'");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total expenses (sum of all expenses)
 */
function get_total_expenses($pdo) {
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM expenses");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get total materials in stock (sum of all quantities)
 */
function get_total_materials($pdo) {
    $stmt = $pdo->query("SELECT SUM(quantity) as total FROM materials");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get low stock materials count
 */
function get_low_stock_count($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials WHERE quantity <= reorder_level AND reorder_level > 0");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get projects by manager
 */
function get_projects_by_manager($pdo, $manager_id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->execute([$manager_id]);
    return $stmt->fetchAll();
}

/**
 * Get recent projects (for dashboard) - FIXED
 */
function get_recent_projects($pdo, $limit = 5) {
    $limit = intval($limit);
    $stmt = $pdo->prepare("SELECT p.*, u.fullname as manager_name 
                           FROM projects p 
                           LEFT JOIN users u ON p.created_by = u.id 
                           ORDER BY p.created_at DESC 
                           LIMIT " . $limit);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get recent tasks (for dashboard) - FIXED
 */
function get_recent_tasks($pdo, $limit = 5) {
    $limit = intval($limit);
    $stmt = $pdo->prepare("SELECT t.*, p.name as project_name, u.fullname as assigned_name 
                           FROM tasks t 
                           LEFT JOIN projects p ON t.project_id = p.id 
                           LEFT JOIN users u ON t.assigned_to = u.id 
                           ORDER BY t.created_at DESC 
                           LIMIT " . $limit);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get recent expenses (for dashboard) - FIXED
 */
function get_recent_expenses($pdo, $limit = 5) {
    $limit = intval($limit);
    $stmt = $pdo->prepare("SELECT e.*, p.name as project_name, u.fullname as recorded_name 
                           FROM expenses e 
                           LEFT JOIN projects p ON e.project_id = p.id 
                           LEFT JOIN users u ON e.recorded_by = u.id 
                           ORDER BY e.created_at DESC 
                           LIMIT " . $limit);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get tasks by worker
 */
function get_tasks_by_worker($pdo, $worker_id) {
    $stmt = $pdo->prepare("SELECT t.*, p.name as project_name 
                           FROM tasks t 
                           LEFT JOIN projects p ON t.project_id = p.id 
                           WHERE t.assigned_to = ? 
                           ORDER BY t.due_date ASC");
    $stmt->execute([$worker_id]);
    return $stmt->fetchAll();
}
?>