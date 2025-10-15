<?php
session_start();
require_once('../php/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ./login/index.php");
    exit();
}

// Get user info from session
$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['name'];

// Fetch profile data from database
$profile_data = [];
$stmt = $conn->prepare("SELECT profile_image, first_name, last_name FROM employee_personal_details WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
}
$stmt->close();

// Determine display name and profile image
$display_name = '';
if (!empty($profile_data['first_name']) && !empty($profile_data['last_name'])) {
    $display_name = $profile_data['first_name'] . ' ' . $profile_data['last_name'];
} else {
    $display_name = $employee_name; // Fallback to session name
}

$profile_image_path = './placeholder.jpg'; // Default image
if (!empty($profile_data['profile_image'])) {
    $profile_image_path = './uploads/profile_images/' . $profile_data['profile_image'];
    // Check if file actually exists
    if (!file_exists($profile_image_path)) {
        $profile_image_path = './placeholder.jpg';
    }
}
include_once("../php/tasks_report.php");;
?>
<!-- Completed tasks page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <!-- FontAwesome cdn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Employee Dashboard</title>
</head>

<body>
    <!-- mobile protection -->
    <?php include_once("../components/mobile.php"); ?>
    <section id="container">
        <!-- Sidebar -->
        <?php include_once("../components/sidebar.php") ?>
        <main class="main">
            <!-- top bar -->
            <?php include_once("../components/topbar.php") ?>
            <div class="newTasksContainer">
                <!-- Running Task container -->
                <div class="runningTask">
                    <h3>Running Task:</h3>
                    <p>No pending tasks</p>
                </div>
                <!-- Completed task table -->
                <div class="givenTasks">
                    <div style="display: flex; align-items: center;justify-content: space-between;">
                        <h2>Tasks Report</h2>
                        <div class="filterAndPrint" style="display: flex; align-items: center; gap:10px">
                            <select name="filter" id="filter">
                                <option value="" style="display: none;">Filter by</option>
                                <option value="all">All</option>
                                <option value="today">Today</option>
                                <option value="week">This week</option>
                                <option value="month">This month</option>
                                <option value="year">This year</option>
                            </select>
                            <button id="printReportBtn" class="btn">Print</button>

                        </div>
                    </div>
                    <div id="printAreaReport">
                        <table style="width: 100%; text-align:center; margin-bottom: 20px" border="1" cellspacing='0'>
                            <thead>
                                <tr>
                                    <th>Total Tasks</th>
                                    <th>Pending</th>
                                    <th>In Progress</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td><?php echo $total_task ?></td>
                                    <td><?php echo $total_pending_task ?></td>
                                    <td><?php echo $total_progress_task ?></td>
                                    <td><?php echo $total_completed_task ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="width: 100%; text-align:center" border="1" cellspacing='0'>
                            <thead>
                                <tr>
                                    <th>Task ID</th>
                                    <th>Assign Date</th>
                                    <th>Deadline</th>
                                    <th>Completed date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($all_targeted_tasks) && count($all_targeted_tasks) > 0) {
                                    foreach ($all_targeted_tasks as $target_task) {
                                        $completed_date = $target_task["completed_date"] ?? "Incomplete";
                                        echo "
                                        <tr>
                                            <td>{$target_task["id"]}</td>
                                            <td>{$target_task["created_at"]}</td>
                                            <td>{$target_task["dead_line"]}</td>
                                            <td>$completed_date</td>
                                            <td style='text-transform: capitalize'>{$target_task["status"]}</td>
                                        </tr>
                                        
                                        ";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Task view popup -->
                <div id="taskViewPopup">
                    <div class="taskViewContainer">
                        <span id="taskViewCloseBtn"><i class="fa-solid fa-xmark"></i></span>
                        <p>hello</p>
                    </div>
                </div>
            </div>
        </main>
    </section>
    <!-- print script -->
    <script>
        const printAreaReport = document.getElementById("printAreaReport");
        const printReportBtn = document.getElementById("printReportBtn");
        const prevContent = document.body.innerHTML;

        printReportBtn.addEventListener("click", () => {
            document.body.innerHTML = printAreaReport.innerHTML;
            window.print();
            document.body.innerHTML = prevContent;
        })
    </script>
    <!-- Filter -->
    <script>
        document.getElementById("filter").addEventListener("change", (e) => {
            const value = e.target.value;
            if (value) {
                window.location.href = `./index.php?filter=${value}`;
            }
        })
    </script>
</body>

</html>