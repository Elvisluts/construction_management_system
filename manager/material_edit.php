<?php
// manager/material_edit.php - Edit Material

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
require_role('manager');

$material_id = $_GET['id'] ?? 0;

// Get material
$stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
$stmt->execute([$material_id]);
$material = $stmt->fetch();

if (!$material) {
    header('Location: materials.php');
    exit();
}

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
            UPDATE materials 
            SET name = ?, description = ?, quantity = ?, unit = ?, unit_price = ?, supplier = ?, reorder_level = ? 
            WHERE id = ?
        ");
        if ($stmt->execute([$name, $description, $quantity, $unit, $unit_price, $supplier, $reorder_level, $material_id])) {
            $success = 'Material updated successfully!';
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
            $stmt->execute([$material_id]);
            $material = $stmt->fetch();
        } else {
            $error = 'Failed to update material.';
        }
    }
}

include '../includes/header.php';
?>

<h2>Edit Material</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?></div>
<?php endif; ?>

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="">
            <label for="name">Material Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($material['name']); ?>" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($material['description']); ?></textarea>

            <label for="quantity">Quantity *</label>
            <input type="number" id="quantity" name="quantity" step="1" value="<?php echo $material['quantity']; ?>" required>

            <label for="unit">Unit *</label>
            <input type="text" id="unit" name="unit" value="<?php echo htmlspecialchars($material['unit']); ?>" required>

            <label for="unit_price">Unit Price (KES) *</label>
            <input type="number" id="unit_price" name="unit_price" step="0.01" value="<?php echo $material['unit_price']; ?>" required>

            <label for="supplier">Supplier</label>
            <input type="text" id="supplier" name="supplier" value="<?php echo htmlspecialchars($material['supplier']); ?>">

            <label for="reorder_level">Reorder Level *</label>
            <input type="number" id="reorder_level" name="reorder_level" step="1" value="<?php echo $material['reorder_level']; ?>" required>

            <button type="submit">Update Material</button>
        </form>
        <p class="form-link"><a href="materials.php">← Back to Materials</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>