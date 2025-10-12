<?php
echo "<h2>Testing Sidebar Include from Different Directories</h2>";

// Test 1: From root directory
echo "<h3>Test 1: From Root Directory</h3>";
try {
    ob_start();
    include_once('./components/sidebar.php');
    $output1 = ob_get_contents();
    ob_end_clean();
    echo "<p style='color: green;'>✓ Root directory test passed</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Root directory test failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Navigation Links:</h3>";
echo "<a href='index.php'>Main Page</a> | ";
echo "<a href='new-tasks/index.php'>New Tasks</a> | ";
echo "<a href='profile/index.php'>Profile</a>";

echo "<hr>";
echo "<h3>File Structure Check:</h3>";
$files_to_check = [
    './php/db_connect.php',
    './new-tasks/db_connect.php',
    './components/sidebar.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ Found: $file</p>";
    } else {
        echo "<p style='color: red;'>✗ Missing: $file</p>";
    }
}
?>