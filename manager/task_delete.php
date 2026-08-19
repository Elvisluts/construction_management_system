<?php
// manager/task_delete.php - Delete Task

require_once '../config/database.php';
require_once '../includes/auth.php';

require_login();
require_role('manager');

$user = get_logged_in_user($pdo);
$user_id = $user['id'] ?? 0;

$task_id = $_GET['id'] ?? 0;

// Verify task belongs to manager's project
$stmt = $pdo->prepare("SELECT t.id FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ? AND p.created_by = ?");
$stmt->execute([$task_id, $user_id]);
if ($stmt->fetch()) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
}

header('Location: tasks.php');
exit();