<?php
session_start();
require_once('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/index.php");
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

$profile_image_path = '../placeholder.jpg'; // Default image
if (!empty($profile_data['profile_image'])) {
    $profile_image_path = '../uploads/profile_images/' . $profile_data['profile_image'];
    // Check if file actually exists
    if (!file_exists($profile_image_path)) {
        $profile_image_path = '../placeholder.jpg';
    }
}
include_once("../php/tasks_operations.php");
?>
<!-- New Tasks page -->
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
                <!-- Running task container -->
                <div class="runningTask">
                    <h3>Running Task:</h3>
                    <p>No pending tasks</p>
                </div>
                <!-- New Tasks table -->
                <div class="givenTasks">
                    <h2>Given Tasks</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Task ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Document</th>
                                <th>Assign Date</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($assigned_tasks) && count($assigned_tasks) > 0) {
                                foreach ($assigned_tasks as $assigned_task) {
                                    $a_doc_link = "../uploads/" . $assigned_task["task_document"];
                                    $short_a_t_description = substr($assigned_task["task_description"], 0, 50);
                                    echo "
                                <tr>
                                <td>{$assigned_task['id']}</td>
                                <td>{$assigned_task['task_title']}</td>
                                <td title='{$assigned_task['task_description']}'>$short_a_t_description ...</td>
                                <td><a href='$a_doc_link' target='_blank'  class='docViewBtn'>View</a></td>
                                <td>{$assigned_task['created_at']}</td>
                                <td>{$assigned_task['dead_line']}</td>
                                <td style='text-transform:capitalize'>{$assigned_task['status']}</td>
                                <td><a href='./index.php?accept_task={$assigned_task['id']}' class='taskActionBtn' style='background-color: green;'>Accept</a></td>
                            </tr>
                                    
                                    ";
                                }
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
                <!-- task view popup -->
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