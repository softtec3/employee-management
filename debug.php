<?php
session_start();
require_once('php/db_connect.php');

echo "<h2>Debug Information</h2>";

// Check session
echo "<h3>Session Information:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if we can connect to database and if employee_log table exists
echo "<h3>Database Check:</h3>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Database connection successful!</p>";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'employee_log'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Table 'employee_log' exists!</p>";
        
        // Show all employees
        $result = $conn->query("SELECT employee_id, name FROM employee_log");
        if ($result->num_rows > 0) {
            echo "<h4>Employees in database:</h4>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>ID: " . htmlspecialchars($row['employee_id']) . " - Name: " . htmlspecialchars($row['name']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>No employees found in database!</p>";
        }
    } else {
        echo "<p style='color: red;'>Table 'employee_log' does not exist!</p>";
    }
}

// Test login form
echo "<h3>Test Login:</h3>";
if ($_POST) {
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];
    
    echo "<p>Attempting login with ID: " . htmlspecialchars($employee_id) . "</p>";
    
    $stmt = $conn->prepare("SELECT employee_id, password, name FROM employee_log WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        echo "<p>User found: " . htmlspecialchars($user['name']) . "</p>";
        
        if ($password === $user['password']) {
            echo "<p style='color: green;'>Password matches! Setting session...</p>";
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['logged_in'] = true;
            echo "<p>Session set. Reload page to see session data.</p>";
        } else {
            echo "<p style='color: red;'>Password does not match!</p>";
        }
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
}
?>

<form method="post">
    <label>Employee ID: <input type="text" name="employee_id" required></label><br><br>
    <label>Password: <input type="text" name="password" required></label><br><br>
    <button type="submit">Test Login</button>
</form>

<hr>
<p><a href="index.php">Go to Main Page</a></p>
<p><a href="logout.php">Logout</a></p>