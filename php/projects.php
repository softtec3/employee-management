<?php
// all projects tasks
$all_projects_tasks = [];
$assign_to = $employee_id;
$project_access = NULL;


$stmt = $conn->prepare("SELECT * FROM projects_tasks WHERE assign_to=? ORDER BY id DESC");
if (!$stmt) {
    die("Preparing error: " . $conn->error);
}
$stmt->bind_param("s", $assign_to);
if (!$stmt->execute()) {
    die("execution error: " . $stmt->error);
}
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $project_access = "granted";
        $all_projects_tasks[] = $row;
    }
} else {
    $project_access = NULL;
}
$stmt->close();

// start a task
if (isset($_GET["start_project_task"]) && $_GET["start_project_task"] != "") {
    $start_project_task_id = (int) $_GET["start_project_task"];
    $status = "in-progress";
    $stmt = $conn->prepare("UPDATE projects_tasks SET status=? WHERE id=?");
    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("si", $status, $start_project_task_id);
    if (!$stmt->execute()) {
        die("execution error: " . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        echo "
        <script>
            alert('Successfully started project task');
            window.location.href= './index.php';
        </script>
    ";
    }
    $stmt->close();
}

// complete a task
if (isset($_GET["complete_project_task"]) && $_GET["complete_project_task"] != "") {
    $complete_project_task_id = (int) $_GET["complete_project_task"];
    $status = "completed";
    $completed_date = date("Y-m-d");
    $stmt = $conn->prepare("UPDATE projects_tasks SET status=?, completed_date=? WHERE id=?");
    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("ssi", $status, $completed_date, $complete_project_task_id);
    if (!$stmt->execute()) {
        die("execution error: " . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        echo "
        <script>
            alert('Successfully completed project task');
            window.location.href= './index.php';
        </script>
    ";
    }
    $stmt->close();
}
