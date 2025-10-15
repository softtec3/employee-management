<?php
// upload file and get name
function upload_file_get_name($name)
{
    if ($_FILES["$name"]) {
        $path = "../uploads/applications/" . $_FILES["$name"]["name"];
        $file_name = $_FILES["$name"]["name"];
        move_uploaded_file($_FILES["$name"]["tmp_name"], $path);
        return $file_name;
    } else {
        echo "Something went Wrong";
    };
};
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["from_date"])) {
    $employee_id = $_POST["employee_id"];
    $full_name = $_POST["full_name"];
    $from_date = $_POST["from_date"];
    $to_date = $_POST["to_date"];
    $reason = $_POST["reason"];
    $application = upload_file_get_name("application");

    $stmt = $conn->prepare("INSERT INTO leave_applications(employee_id, full_name, from_date, to_date, reason, application ) VALUES (?,?,?,?,?,?)");
    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("ssssss", $employee_id, $full_name, $from_date, $to_date, $reason, $application);

    if (!$stmt->execute()) {
        die("Execution problem: " . $stmt->error);
    }
    $stmt->close();
    echo "
        <script>
            alert('Application submitted');
        </script>
    ";
}

// get all leave application for logged in user
$stmt = $conn->prepare("SELECT * FROM leave_applications WHERE employee_id=? ORDER BY created_at DESC");
if (!$stmt) {
    die("Preparing error: " . $conn->error);
}
$stmt->bind_param("s", $employee_id);
if (!$stmt->execute()) {
    die("Execution problem: " . $stmt->error);
}
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_applications[] = $row;
    }
}
$stmt->close();
