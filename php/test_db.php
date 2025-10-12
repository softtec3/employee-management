<?php
require_once('db_connect.php');

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Connection successful!</p>";
    
    // Test if employee_log table exists
    $result = $conn->query("SHOW TABLES LIKE 'employee_log'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Table 'employee_log' exists!</p>";
        
        // Show structure of the table
        $result = $conn->query("DESCRIBE employee_log");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data
        $result = $conn->query("SELECT * FROM employee_log LIMIT 5");
        if ($result->num_rows > 0) {
            echo "<h3>Sample Data (first 5 rows):</h3>";
            echo "<table border='1'>";
            $first = true;
            while ($row = $result->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<th>" . $key . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $key => $value) {
                    if ($key === 'password') {
                        echo "<td>" . (strlen($value) > 20 ? "HASHED" : "PLAIN") . " (length: " . strlen($value) . ")</td>";
                    } else {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>Table exists but no data found!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>Table 'employee_log' does not exist!</p>";
        echo "<p>You need to create the table first. Here's a sample SQL:</p>";
        echo "<pre>
CREATE TABLE employee_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO employee_log (employee_id, password, name) VALUES 
('EMP001', '123456', 'John Doe'),
('EMP002', 'password', 'Jane Smith');
</pre>";
    }
}

$conn->close();
?>