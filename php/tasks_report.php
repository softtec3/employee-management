<?php
$all_targeted_tasks = [];
// get all task based on id

$stmt = $conn->prepare("SELECT id, created_at, dead_line, completed_date, status FROM tasks WHERE employee_id=? ORDER BY created_at DESC");

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

// Filter 
$filter = $_GET['filter'] ?? 'all';
date_default_timezone_set('Asia/Dhaka');

$today = date("Y-m-d");
$startOfWeek = date("Y-m-d", strtotime('monday this week'));
$startOfMonth = date("Y-m-01");
$startOfYear = date("Y-01-01");

$filtered_tasks = [];

foreach ($all_targeted_tasks as $task) {
    $created = date("Y-m-d", strtotime($task['created_at']));

    switch ($filter) {
        case 'today':
            if ($created === $today) {
                $filtered_tasks[] = $task;
            }
            break;

        case 'week':
            if ($created >= $startOfWeek && $created <= $today) {
                $filtered_tasks[] = $task;
            }
            break;

        case 'month':
            if ($created >= $startOfMonth && $created <= $today) {
                $filtered_tasks[] = $task;
            }
            break;

        case 'year':
            if ($created >= $startOfYear && $created <= $today) {
                $filtered_tasks[] = $task;
            }
            break;

        default: // 'all'
            $filtered_tasks[] = $task;
            break;
    }
}

$all_targeted_tasks = $filtered_tasks;

$total_task = count($all_targeted_tasks);

// total pending tasks
$total_pending_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "pending") {
        return false;
    }
    return true;
});
$total_pending_task = count(array_values($total_pending_filter));
// total inprogress tasks
$total_progress_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "inprogress") {
        return false;
    }
    return true;
});
$total_progress_task = count(array_values($total_progress_filter));
// total completed tasks
$total_completed_filter = array_filter($all_targeted_tasks, function ($task) {
    if ($task["status"] != "completed") {
        return false;
    }
    return true;
});
$total_completed_task = count(array_values($total_completed_filter));
