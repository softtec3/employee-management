<?php
/**
 * Global Configuration File
 * This file helps resolve path issues across different directories
 */

// Define the root directory of the application
define('APP_ROOT', dirname(__FILE__));

// Define common paths
define('PHP_DIR', APP_ROOT . '/php');
define('COMPONENTS_DIR', APP_ROOT . '/components');
define('UPLOADS_DIR', APP_ROOT . '/uploads');

/**
 * Include database connection safely
 */
function include_db_connection() {
    global $conn;
    
    // If connection already exists, return true
    if (isset($conn) && $conn instanceof mysqli) {
        return true;
    }
    
    // Try to include database connection from multiple paths
    $db_paths = [
        PHP_DIR . '/db_connect.php',
        'db_connect.php',
        './php/db_connect.php',
        '../php/db_connect.php',
        '../../php/db_connect.php'
    ];
    
    foreach ($db_paths as $db_file) {
        if (file_exists($db_file)) {
            require_once($db_file);
            if (isset($conn) && $conn instanceof mysqli) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get the correct path for assets based on current directory
 */
function get_asset_path($asset) {
    $possible_paths = [
        './' . $asset,
        '../' . $asset,
        '../../' . $asset
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return './' . $asset; // fallback
}

/**
 * Get the correct path for navigation
 */
function get_nav_path($page) {
    $possible_paths = [
        './' . $page,
        '../' . $page,
        '../../' . $page
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return './' . $page; // fallback
}
?>