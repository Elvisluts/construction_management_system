<?php
// admin/users.php - User Management (Admin Only)

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('admin');

// Handle Delete User
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Prevent admin from deleting themselves
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $success = "User deleted successfully!";
    } else {
        $error = "You cannot delete your own account!";
    }
    // Redirect to refresh page
    header('Location: users.php');
    exit();
}

// Get all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <h2>👥 User Management</h2>
        <p>Manage all users in the system.</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-header">
            <h3>All Users</h3>
            <span class="badge badge-primary">Total: <?php echo count($users); ?></span>
        </div>
        
        <?php if (count($users) > 0): ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php 
                                    echo $user['role'] == 'admin' ? 'badge-ongoing' : 
                                        ($user['role'] == 'manager' ? 'badge-warning' : 'badge-planned'); 
                                ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Delete this user? This action cannot be undone.');">
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-success">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No users found.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-wrapper { max-width: 1200px; margin: 0 auto; padding: 10px 0; }
.dashboard-header h2 { font-size: 26px; color: #1a1a1a; margin-bottom: 2px; }
.dashboard-header p { color: #777; font-size: 15px; margin-bottom: 25px; }

.table-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow-x: auto;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.table-header h3 { font-size: 18px; color: #1a1a1a; }

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.modern-table thead th {
    background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
    color: #fff;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
}

.modern-table tbody td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.modern-table tbody tr:hover { background: rgba(245, 124, 0, 0.05); }

.badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}

.badge-planned { background: #6c757d; }
.badge-ongoing { background: linear-gradient(135deg, #f57c00, #e65100); }
.badge-completed { background: linear-gradient(135deg, #2e7d32, #1b5e20); }
.badge-success { background: #2e7d32; }
.badge-danger { background: #e74c3c; }
.badge-warning { background: #f9a825; color: #1a1a1a; }
.badge-primary { background: #1565c0; }

.alert {
    padding: 12px 18px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #c8e6c9;
}

.alert-danger {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.btn {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-danger {
    background: #e74c3c;
    color: #fff;
}
.btn-danger:hover {
    background: #c0392b;
}

.btn-sm { padding: 4px 12px; font-size: 11px; }

.empty-state { color: #999; text-align: center; padding: 30px 0; }

@media (max-width: 768px) {
    .modern-table { font-size: 12px; min-width: 600px; }
    .table-card { padding: 15px; }
}
</style>

<?php include '../includes/footer.php'; ?>