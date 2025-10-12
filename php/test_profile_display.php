<?php
session_start();
require_once('db_connect.php');

echo "<h2>Profile Data Display Test</h2>";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<p style='color: red;'>Please login first!</p>";
    echo "<a href='../login/index.php'>Go to Login</a>";
    exit();
}

$employee_id = $_SESSION['employee_id'];
echo "<h3>Current Session Data:</h3>";
echo "<p>Employee ID: " . htmlspecialchars($employee_id) . "</p>";
echo "<p>Session Name: " . htmlspecialchars($_SESSION['name']) . "</p>";

// Check profile data
$stmt = $conn->prepare("SELECT profile_image, first_name, last_name FROM employee_personal_details WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Profile Data from Database:</h3>";
if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
    echo "<p>First Name: " . htmlspecialchars($profile_data['first_name'] ?? 'Not set') . "</p>";
    echo "<p>Last Name: " . htmlspecialchars($profile_data['last_name'] ?? 'Not set') . "</p>";
    echo "<p>Profile Image: " . htmlspecialchars($profile_data['profile_image'] ?? 'Not set') . "</p>";
    
    // Check if profile image file exists
    if (!empty($profile_data['profile_image'])) {
        $image_path = '../uploads/profile_images/' . $profile_data['profile_image'];
        if (file_exists($image_path)) {
            echo "<p>Image file exists: <a href='$image_path' target='_blank'>View Image</a></p>";
            echo "<img src='$image_path' style='max-width: 150px; max-height: 150px; border-radius: 50%;' alt='Profile Picture'>";
        } else {
            echo "<p style='color: red;'>Image file does not exist: $image_path</p>";
        }
    }
    
    // Display name logic
    $display_name = $_SESSION['name'];
    if (!empty($profile_data['first_name']) && !empty($profile_data['last_name'])) {
        $display_name = $profile_data['first_name'] . ' ' . $profile_data['last_name'];
    }
    echo "<h4>Display Name: " . htmlspecialchars($display_name) . "</h4>";
    
} else {
    echo "<p style='color: orange;'>No profile data found. Please complete your profile first.</p>";
    echo "<a href='../profile/index.php'>Go to Profile Page</a>";
}

$stmt->close();

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<a href='../index.php'>Go to Main Page</a> | ";
echo "<a href='../profile/index.php'>Go to Profile</a> | ";
echo "<a href='manage_profile_data.php'>Manage Profile Data</a>";

$conn->close();
?>