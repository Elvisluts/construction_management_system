<?php
// manager/material_create.php - Add New Material

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = $_POST['quantity'] ?? 0;
    $unit = trim($_POST['unit'] ?? '');
    $unit_price = $_POST['unit_price'] ?? 0;
    $supplier = trim($_POST['supplier'] ?? '');
    $reorder_level = $_POST['reorder_level'] ?? 0;

    // Validation
    if (empty($name)) {
        $error = 'Material name is required.';
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error = 'Quantity must be a non-negative number.';
    } elseif (!is_numeric($unit_price) || $unit_price < 0) {
        $error = 'Unit price must be a positive number.';
    } elseif (!is_numeric($reorder_level) || $reorder_level < 0) {
        $error = 'Reorder level must be a non-negative number.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO materials (name, description, quantity, unit, unit_price, supplier, reorder_level) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$name, $description, $quantity, $unit, $unit_price, $supplier, $reorder_level])) {
            $success = 'Material added successfully!';
        } else {
            $error = 'Failed to add material.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Add New Material</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?> <a href="materials.php">View all materials</a></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="name">Material Name *</label>
            <input type="text" id="name" name="name" placeholder="e.g., Cement" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Optional description"></textarea>

            <label for="quantity">Quantity *</label>
            <input type="number" id="quantity" name="quantity" step="1" value="0" placeholder="e.g., 100" required>

            <label for="unit">Unit *</label>
            <input type="text" id="unit" name="unit" placeholder="e.g., bags, pieces, kg, litres" required>

            <label for="unit_price">Unit Price (KES) *</label>
            <input type="number" id="unit_price" name="unit_price" step="0.01" value="0.00" placeholder="e.g., 850.00" required>

            <label for="supplier">Supplier</label>
            <input type="text" id="supplier" name="supplier" placeholder="Supplier name (optional)">

            <label for="reorder_level">Reorder Level *</label>
            <input type="number" id="reorder_level" name="reorder_level" step="1" value="0" required>
            <small>Low stock alert triggers when quantity falls below this level</small>

            <button type="submit">Add Material</button>
        </form>
        <p class="form-link"><a href="materials.php">← Back to Materials</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>