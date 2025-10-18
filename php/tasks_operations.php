<?php
$all_targeted_tasks = [];
// get all task based on id

$stmt = $conn->prepare("SELECT * FROM tasks WHERE employee_id=? ORDER BY created_at DESC");

if (!$stmt) {
    die("Preparing error: " . $conn->error);
}

$stmt->bind_param("s", $employee_id);

if (!$stmt->execute()) {
    die("Execution error: " . $stmt->error);
}
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_targeted_tasks[] = $row;
    }
}
$stmt->close();

//all assigned tasks
$assigned_tasks_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "assigned") {
        return false;
    }
    return true;
});
$assigned_tasks = array_values($assigned_tasks_filter);

//all pending tasks
$pending_tasks_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "pending") {
        return false;
    }
    return true;
});
$pending_tasks = array_values($pending_tasks_filter);

//all started tasks
$started_tasks_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "in-progress") {
        return false;
    }
    return true;
});
$started_tasks = array_values($started_tasks_filter);
//all started tasks

$completed_tasks_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "completed") {
        return false;
    }
    return true;
});
$completed_tasks = array_values($completed_tasks_filter);

// Accept task and status become pending
if (isset($_GET["accept_task"]) && $_GET["accept_task"] != '') {
    $accept_task_id = $_GET["accept_task"];
    $status = "pending";
    $stmt = $conn->prepare("UPDATE tasks SET status=? WHERE id=?");
    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("ss", $status, $accept_task_id);
    if (!$stmt->execute()) {
        die("Execution error: " . $stmt->error);
    }
    if ($stmt->affected_rows === 0) {
        die("No rows updated. Check if the task_id exists.");
    }
    echo "
       <script>
        alert('Task- $accept_task_id accepted');
        window.location.href = './index.php';
       </script>
    ";
    $stmt->close();
}

// Start task and status become in-progress
if (isset($_GET["start_task"]) && $_GET["start_task"] != '') {
    $start_task_id = $_GET["start_task"];
    $status = "in-progress";
    $stmt = $conn->prepare("UPDATE tasks SET status=? WHERE id=?");
    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("ss", $status, $start_task_id);
    if (!$stmt->execute()) {
        die("Execution error: " . $stmt->error);
    }
    if ($stmt->affected_rows === 0) {
        die("No rows updated. Check if the task_id exists.");
    }
    echo "
       <script>
        alert('Task- $start_task_id started');
        window.location.href = './index.php';
       </script>
    ";
    $stmt->close();
}
// Complete task and status become completed
if (isset($_GET["complete_task"]) && $_GET["complete_task"] != '') {
    $complete_task_id = $_GET["complete_task"];
    $status = "completed";
    $completed_date = date("Y-m-d");
    $stmt = $conn->prepare("UPDATE tasks SET status=?, completed_date=? WHERE id=?");
    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("sss", $status, $completed_date, $complete_task_id);
    if (!$stmt->execute()) {
        die("Execution error: " . $stmt->error);
    }
    if ($stmt->affected_rows === 0) {
        die("No rows updated. Check if the task_id exists.");
    }
    echo "
       <script>
        alert('Task- $complete_task_id completed');
        window.location.href = './index.php';
       </script>
    ";
    $stmt->close();
}
