<?php
// manager/materials.php - Materials Management (Styled)

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: materials.php');
    exit();
}

// Get all materials
$materials = $pdo->query("SELECT * FROM materials ORDER BY name ASC")->fetchAll();

// Count low stock items
$low_stock_count = 0;
foreach ($materials as $material) {
    if ($material['quantity'] <= $material['reorder_level'] && $material['reorder_level'] > 0) {
        $low_stock_count++;
    }
}

include '../includes/header.php';
?>

<div class="materials-wrapper">
    <!-- Header -->
    <div class="materials-header">
        <div>
            <h2>📦 Materials</h2>
            <p>Manage your construction materials inventory.</p>
        </div>
        <a href="material_create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Material
        </a>
    </div>

    <!-- Stats -->
    <div class="materials-stats">
        <div class="stat-box">
            <span class="stat-box-label">Total Materials</span>
            <span class="stat-box-number"><?php echo count($materials); ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">In Stock</span>
            <span class="stat-box-number" style="color:#2e7d32;">
                <?php 
                    $in_stock = 0;
                    foreach ($materials as $m) {
                        if ($m['quantity'] > $m['reorder_level'] || $m['reorder_level'] == 0) $in_stock++;
                    }
                    echo $in_stock;
                ?>
            </span>
        </div>
        <div class="stat-box">
            <span class="stat-box-label">Low Stock</span>
            <span class="stat-box-number" style="color:#e74c3c;"><?php echo $low_stock_count; ?></span>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <?php if ($low_stock_count > 0): ?>
        <div class="alert-stock">
            <span class="alert-icon">⚠️</span>
            <div>
                <strong>Low Stock Alert</strong>
                <ul>
                    <?php foreach ($materials as $material): ?>
                        <?php if ($material['quantity'] <= $material['reorder_level'] && $material['reorder_level'] > 0): ?>
                            <li>
                                <?php echo htmlspecialchars($material['name']); ?> — 
                                Stock: <?php echo $material['quantity']; ?> <?php echo $material['unit']; ?> 
                                (Reorder Level: <?php echo $material['reorder_level']; ?>)
                                <a href="material_edit.php?id=<?php echo $material['id']; ?>" style="color:#e65100; margin-left:10px;">Update Stock</a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>All Materials</h3>
            <span class="badge badge-primary"><?php echo count($materials); ?> items</span>
        </div>

        <?php if (count($materials) > 0): ?>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Material Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Supplier</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                            <tr>
                                <td><?php echo $material['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($material['name']); ?></strong></td>
                                <td><?php echo $material['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($material['unit']); ?></td>
                                <td>KES <?php echo number_format($material['unit_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($material['supplier']); ?></td>
                                <td><?php echo $material['reorder_level']; ?></td>
                                <td>
                                    <?php if ($material['quantity'] <= $material['reorder_level'] && $material['reorder_level'] > 0): ?>
                                        <span class="badge badge-danger">Low Stock</span>
                                    <?php elseif ($material['quantity'] == 0): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="material_edit.php?id=<?php echo $material['id']; ?>" class="btn-action edit" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="materials.php?delete=<?php echo $material['id']; ?>" class="btn-action delete" title="Delete" onclick="return confirm('Delete this material?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <p>No materials found.</p>
                <a href="material_create.php" class="btn btn-primary" style="margin-top:10px;">
                    <i class="fas fa-plus"></i> Add Your First Material
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ========== MATERIALS PAGE STYLES ========== */
.materials-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0;
}

/* Header */
.materials-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.materials-header h2 {
    font-size: 26px;
    color: #1a1a1a;
    margin: 0;
}

.materials-header p {
    color: #777;
    font-size: 15px;
    margin: 2px 0 0 0;
}

/* Stats */
.materials-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-box {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 18px 22px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    text-align: center;
}

.stat-box-label {
    display: block;
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-box-number {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
}

/* Alert */
.alert-stock {
    background: rgba(255, 248, 225, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-left: 5px solid #f57c00;
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 25px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.alert-stock ul {
    margin: 5px 0 0 0;
    padding-left: 20px;
    color: #555;
}

.alert-stock ul li {
    margin-bottom: 3px;
}

.alert-icon { font-size: 22px; }

/* Table */
.table-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.table-header h3 {
    font-size: 18px;
    color: #1a1a1a;
    margin: 0;
}

.table-responsive {
    overflow-x: auto;
}

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
    white-space: nowrap;
}

.modern-table tbody td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.modern-table tbody tr:hover {
    background: rgba(245, 124, 0, 0.04);
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}

.badge-success { background: #2e7d32; }
.badge-danger { background: #e74c3c; }
.badge-primary { background: #1565c0; }

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
}

.btn-action.edit {
    background: rgba(245, 124, 0, 0.1);
    color: #f57c00;
}
.btn-action.edit:hover {
    background: #f57c00;
    color: #fff;
    transform: translateY(-2px);
}

.btn-action.delete {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}
.btn-action.delete:hover {
    background: #e74c3c;
    color: #fff;
    transform: translateY(-2px);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #f57c00, #e65100);
    color: #fff;
    box-shadow: 0 4px 15px rgba(245, 124, 0, 0.3);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(245, 124, 0, 0.4);
}

.btn i { font-size: 14px; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-state p {
    color: #888;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 992px) {
    .materials-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .materials-header {
        flex-direction: column;
        align-items: stretch;
    }
    .materials-header h2 { font-size: 22px; }
    .materials-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .stat-box { padding: 14px 16px; }
    .stat-box-number { font-size: 22px; }
    .table-card { padding: 15px; }
    .modern-table { font-size: 13px; min-width: 700px; }
    .action-buttons { gap: 4px; }
    .btn-action { width: 28px; height: 28px; font-size: 11px; }
}

@media (max-width: 480px) {
    .materials-stats {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .stat-box { padding: 10px 12px; }
    .stat-box-number { font-size: 18px; }
    .btn { padding: 8px 16px; font-size: 13px; }
}
</style>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>