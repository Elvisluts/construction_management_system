<?php
// includes/db_operations.php - Database Operations

/**
 * PROJECT OPERATIONS
 */

// Create a new project
function create_project($pdo, $name, $description, $start_date, $end_date, $budget, $created_by) {
    $stmt = $pdo->prepare("INSERT INTO projects (name, description, start_date, end_date, budget, created_by, status) VALUES (?, ?, ?, ?, ?, ?, 'planned')");
    return $stmt->execute([$name, $description, $start_date, $end_date, $budget, $created_by]);
}

// Get all projects
function get_all_projects($pdo) {
    $stmt = $pdo->query("SELECT p.*, u.fullname as manager_name FROM projects p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC");
    return $stmt->fetchAll();
}

// Get projects by manager
function get_projects_by_manager($pdo, $manager_id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->execute([$manager_id]);
    return $stmt->fetchAll();
}

// Get project by ID
function get_project_by_id($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    return $stmt->fetch();
}

// Update project
function update_project($pdo, $id, $name, $description, $start_date, $end_date, $status) {
    $stmt = $pdo->prepare("UPDATE projects SET name = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
    return $stmt->execute([$name, $description, $start_date, $end_date, $status, $id]);
}

// Delete project
function delete_project($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * TASK OPERATIONS
 */

// Create a new task
function create_task($pdo, $project_id, $title, $description, $assigned_to, $due_date) {
    $stmt = $pdo->prepare("INSERT INTO tasks (project_id, title, description, assigned_to, due_date, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    return $stmt->execute([$project_id, $title, $description, $assigned_to, $due_date]);
}

// Get tasks by project
function get_tasks_by_project($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT t.*, u.fullname as assigned_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.project_id = ? ORDER BY t.due_date ASC");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

// Get tasks by worker
function get_tasks_by_worker($pdo, $worker_id) {
    $stmt = $pdo->prepare("SELECT t.*, p.name as project_name FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE t.assigned_to = ? ORDER BY t.due_date ASC");
    $stmt->execute([$worker_id]);
    return $stmt->fetchAll();
}

// Update task status
function update_task_status($pdo, $task_id, $status) {
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $task_id]);
}

// Delete task
function delete_task($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * WORKER OPERATIONS
 */

// Create worker profile
function create_worker($pdo, $user_id, $national_id, $job_title, $department, $hire_date) {
    $stmt = $pdo->prepare("INSERT INTO workers (user_id, national_id, job_title, department, hire_date) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $national_id, $job_title, $department, $hire_date]);
}

// Get all workers
function get_all_workers($pdo) {
    $stmt = $pdo->query("SELECT w.*, u.fullname, u.email, u.phone FROM workers w LEFT JOIN users u ON w.user_id = u.id ORDER BY u.fullname ASC");
    return $stmt->fetchAll();
}

// Get workers by project
function get_workers_by_project($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT pa.*, w.*, u.fullname, u.email FROM project_assignments pa LEFT JOIN workers w ON pa.worker_id = w.id LEFT JOIN users u ON w.user_id = u.id WHERE pa.project_id = ?");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

// Assign worker to project
function assign_worker_to_project($pdo, $project_id, $worker_id, $role) {
    $stmt = $pdo->prepare("INSERT INTO project_assignments (project_id, worker_id, assigned_date, role) VALUES (?, ?, CURDATE(), ?)");
    return $stmt->execute([$project_id, $worker_id, $role]);
}

/**
 * MATERIAL OPERATIONS
 */

// Create material
function create_material($pdo, $name, $description, $quantity, $unit, $unit_price, $supplier, $reorder_level) {
    $stmt = $pdo->prepare("INSERT INTO materials (name, description, quantity, unit, unit_price, supplier, reorder_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $description, $quantity, $unit, $unit_price, $supplier, $reorder_level]);
}

// Get all materials
function get_all_materials($pdo) {
    $stmt = $pdo->query("SELECT * FROM materials ORDER BY name ASC");
    return $stmt->fetchAll();
}

// Update material stock
function update_material_stock($pdo, $id, $quantity) {
    $stmt = $pdo->prepare("UPDATE materials SET quantity = ? WHERE id = ?");
    return $stmt->execute([$quantity, $id]);
}

// Create material request
function create_material_request($pdo, $project_id, $material_id, $quantity_requested, $requested_by) {
    $stmt = $pdo->prepare("INSERT INTO material_requests (project_id, material_id, quantity_requested, request_date, status, requested_by) VALUES (?, ?, ?, CURDATE(), 'pending', ?)");
    return $stmt->execute([$project_id, $material_id, $quantity_requested, $requested_by]);
}

/**
 * EXPENSE OPERATIONS
 */

// Create expense
function create_expense($pdo, $project_id, $description, $amount, $expense_date, $category, $recorded_by) {
    $stmt = $pdo->prepare("INSERT INTO expenses (project_id, description, amount, expense_date, category, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$project_id, $description, $amount, $expense_date, $category, $recorded_by]);
}

// Get expenses by project
function get_expenses_by_project($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT e.*, u.fullname as recorded_name FROM expenses e LEFT JOIN users u ON e.recorded_by = u.id WHERE e.project_id = ? ORDER BY e.expense_date DESC");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

// Get total expenses by project
function get_total_expenses_by_project($pdo, $project_id) {
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}
?>