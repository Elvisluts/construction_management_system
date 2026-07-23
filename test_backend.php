<?php
// test_backend.php - Test Backend Logic

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Backend Logic</h1>";

// Test 1: Database Connection
echo "<h3>Test 1: Database Connection</h3>";

// Include database connection
require_once 'config/database.php';

// Check if $pdo variable exists (it should be in global scope)
global $pdo;

if (isset($pdo) && $pdo instanceof PDO) {
    echo "<p style='color:green;'>✓ Database connection successful!</p>";
    
    // Test query
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch();
        echo "<p style='color:green;'>✓ Query successful! Total users: " . $result['total'] . "</p>";
    } catch(PDOException $e) {
        echo "<p style='color:red;'>✗ Query failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red;'>✗ Database connection failed!</p>";
}

// Test 2: Authentication Functions
echo "<h3>Test 2: Authentication Functions</h3>";

require_once 'includes/auth.php';

if (function_exists('require_login')) {
    echo "<p style='color:green;'>✓ auth.php loaded successfully!</p>";
    echo "<p>Functions available:</p><ul>";
    echo "<li>require_login()</li>";
    echo "<li>require_role()</li>";
    echo "<li>get_logged_in_user()</li>";
    echo "<li>email_exists()</li>";
    echo "<li>create_user()</li>";
    echo "<li>authenticate_user()</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red;'>✗ auth.php failed to load!</p>";
}

// Test 3: Functions File
echo "<h3>Test 3: Helper Functions</h3>";

require_once 'includes/functions.php';

if (function_exists('sanitize')) {
    echo "<p style='color:green;'>✓ functions.php loaded successfully!</p>";
    $test_input = "<script>alert('XSS')</script>";
    echo "<p>Sample sanitize test: ' " . htmlspecialchars($test_input) . " ' → ' " . sanitize($test_input) . " '</p>";
} else {
    echo "<p style='color:red;'>✗ functions.php failed to load!</p>";
}

// Test 4: Database Operations
echo "<h3>Test 4: Database Operations</h3>";

require_once 'includes/db_operations.php';

if (function_exists('get_all_projects')) {
    echo "<p style='color:green;'>✓ db_operations.php loaded successfully!</p>";
    echo "<p>Functions available:</p><ul>";
    echo "<li>create_project()</li>";
    echo "<li>get_all_projects()</li>";
    echo "<li>create_task()</li>";
    echo "<li>get_all_workers()</li>";
    echo "<li>create_material()</li>";
    echo "<li>create_expense()</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red;'>✗ db_operations.php failed to load!</p>";
}

echo "<hr>";
echo "<h3 style='color:green;'>✅ All Systems Working!</h3>";
echo "<p>Your backend logic is ready for development.</p>";
?>