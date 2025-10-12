<?php
echo "<h1>Testing Path Resolution System</h1>";

// Test from root directory
echo "<h2>Testing from Root Directory (htdocs)</h2>";
echo "Current directory: " . getcwd() . "<br>";

// Test configuration loading
require_once('config.php');
echo "Config loaded successfully<br>";

// Test database connection
if (include_db_connection()) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Test asset paths
echo "Logo path: " . get_asset_path('logo.png') . "<br>";
echo "Logo exists: " . (file_exists(get_asset_path('logo.png')) ? "✅ Yes" : "❌ No") . "<br>";

echo "<hr>";

// Test from new-tasks directory
echo "<h2>Testing from new-tasks Directory</h2>";
chdir('new-tasks');
echo "Current directory: " . getcwd() . "<br>";

// Include configuration from subdirectory
$config_paths = [
    'config.php',
    '../config.php',
    '../../config.php'
];

$config_loaded = false;
foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once($config_path);
        $config_loaded = true;
        echo "Config loaded from: $config_path<br>";
        break;
    }
}

if (!$config_loaded) {
    echo "❌ Config not found<br>";
} else {
    echo "✅ Config loaded successfully<br>";
    
    // Test database connection from subdirectory
    if (include_db_connection()) {
        echo "✅ Database connection successful from subdirectory<br>";
    } else {
        echo "❌ Database connection failed from subdirectory<br>";
    }
    
    // Test asset paths from subdirectory
    echo "Logo path from subdirectory: " . get_asset_path('logo.png') . "<br>";
    echo "Logo exists from subdirectory: " . (file_exists(get_asset_path('logo.png')) ? "✅ Yes" : "❌ No") . "<br>";
}

echo "<hr>";
echo "<h2>Testing Component Includes</h2>";

// Test sidebar include from new-tasks
echo "Testing sidebar.php include from new-tasks:<br>";
ob_start();
try {
    include('../components/sidebar.php');
    echo "✅ Sidebar included successfully<br>";
} catch (Exception $e) {
    echo "❌ Sidebar include failed: " . $e->getMessage() . "<br>";
}
ob_end_clean();

// Test topbar include from new-tasks
echo "Testing topbar.php include from new-tasks:<br>";
ob_start();
try {
    include('../components/topbar.php');
    echo "✅ Topbar included successfully<br>";
} catch (Exception $e) {
    echo "❌ Topbar include failed: " . $e->getMessage() . "<br>";
}
ob_end_clean();

echo "<p><strong>Path Resolution System Test Complete</strong></p>";
?>