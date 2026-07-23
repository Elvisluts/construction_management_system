<?php
// test.php - Full Database Connection Test (Fixed)

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Construction Management System - Database Test</h1>";

// Test 1: Check PHP is working
echo "<h3>Test 1: PHP Status</h3>";
echo "<p style='color:green;'>✓ PHP is working correctly</p>";

// Test 2: Test Database Connection
echo "<h3>Test 2: Database Connection</h3>";

$host = 'localhost';
$dbname = 'construction_management_system';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<p style='color:green;'>✓ Database connected successfully!</p>";
    
    // Test 3: Count users
    echo "<h3>Test 3: Users Table</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "<p>Total users: " . $result['total'] . "</p>";
    
    // Test 4: List all tables (FIXED)
    echo "<h3>Test 4: All Tables in Database</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM); // Use FETCH_NUM instead of default
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $row) {
            // Use $row[0] which is now safe with FETCH_NUM
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
        echo "<p style='color:green;'>✓ Found " . count($tables) . " tables</p>";
    } else {
        echo "<p style='color:red;'>✗ No tables found. Please run the SQL schema.</p>";
    }
    
    // Test 5: Server info
    echo "<h3>Test 5: Server Information</h3>";
    echo "<p>Database Server: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
    echo "<h3>Test 6: All Systems Go!</h3>";
    echo "<p style='color:green;font-weight:bold;'>✓ Your database is set up correctly and ready for development!</p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>✗ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Possible causes:</p>";
    echo "<ul>";
    echo "<li>Database does not exist</li>";
    echo "<li>Incorrect database name</li>";
    echo "<li>Incorrect username or password</li>";
    echo "<li>MySQL/MariaDB is not running</li>";
    echo "</ul>";
}
?>