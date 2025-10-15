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
include_once("../php/tasks_operations.php");
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
                    <h2>Completed Tasks</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Task ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Document</th>
                                <th>Assign Date</th>
                                <th>Deadline</th>
                                <th>Completed date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($completed_tasks) && count($completed_tasks) > 0) {
                                foreach ($completed_tasks as $completed_task) {
                                    $s_doc_link = "../uploads/" . $completed_task["task_document"];
                                    $short_s_t_description = substr($completed_task["task_description"], 0, 50);
                                    echo "
                                <tr>
                                <td>{$completed_task['id']}</td>
                                <td>{$completed_task['task_title']}</td>
                                <td title='{$completed_task['task_description']}'>$short_s_t_description ...</td>
                                <td><a href='$s_doc_link' target='_blank' class='docViewBtn'>View</a></td>
                                <td>{$completed_task['created_at']}</td>
                                <td>{$completed_task['dead_line']}</td>
                                <td>{$completed_task['completed_date']}</td>
                                <td style='text-transform:capitalize'>{$completed_task['status']}</td>
                            </tr>";
                                }
                            }
                            ?>

                        </tbody>
                    </table>
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
    <!-- Js codes for popup open and close -->
    <script>
        const viewButtons = document.querySelectorAll(".viewButton");
        const taskViewPopup = document.getElementById("taskViewPopup");
        const taskViewCloseBtn = document.getElementById("taskViewCloseBtn");

        viewButtons.forEach(button => {
            button.addEventListener("click", () => {
                taskViewPopup.style.display = "flex";
            });
        });

        taskViewCloseBtn.addEventListener("click", () => {
            taskViewPopup.style.display = "none";
        });
    </script>
</body>

</html>