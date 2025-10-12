<?php
require_once('db_connect.php');

echo "<h2>Database Data Management Tool</h2>";

// Function to insert sample data
if (isset($_POST['insert_sample_data'])) {
    $employee_id = $_POST['employee_id'];
    
    // Insert sample personal details
    $stmt = $conn->prepare("INSERT INTO employee_personal_details (employee_id, first_name, last_name, fathers_name, present_city, present_state, present_zipcode, contact_number, status) VALUES (?, 'John', 'Doe', 'Robert Doe', 'New York', 'NY', '10001', '+1234567890', 'pending') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name)");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    
    // Insert sample documents
    $stmt = $conn->prepare("INSERT INTO employee_documents (employee_id, document_type, ssn_no, status) VALUES (?, 'passport', '123456789', 'pending') ON DUPLICATE KEY UPDATE ssn_no = VALUES(ssn_no)");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    
    // Insert sample emergency contacts
    $stmt = $conn->prepare("INSERT INTO employee_emergency_contacts (employee_id, contact_1_name, contact_1_relation, contact_1_phone, status) VALUES (?, 'Jane Doe', 'Wife', '+1987654321', 'pending') ON DUPLICATE KEY UPDATE contact_1_name = VALUES(contact_1_name)");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    
    echo "<p style='color: green;'>Sample data inserted for employee: " . htmlspecialchars($employee_id) . "</p>";
}

// Show current employees
$result = $conn->query("SELECT employee_id, name FROM employee_log");
echo "<h3>Current Employees:</h3>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p>ID: " . htmlspecialchars($row['employee_id']) . " - Name: " . htmlspecialchars($row['name']) . "</p>";
    }
} else {
    echo "<p>No employees found!</p>";
}

// Insert sample data form
echo "<h3>Insert Sample Profile Data:</h3>";
echo "<form method='post'>";
echo "<label>Employee ID: <input type='text' name='employee_id' required placeholder='e.g., test123'></label><br><br>";
echo "<button type='submit' name='insert_sample_data'>Insert Sample Data</button>";
echo "</form>";

// Show profile data counts
echo "<h3>Profile Data Summary:</h3>";
$tables = ['employee_personal_details', 'employee_documents', 'employee_emergency_contacts'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $result->fetch_assoc()['count'];
    echo "<p>$table: $count records</p>";
}

$conn->close();
?>