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
?>
<!-- Started Tasks page -->
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
     <?php include_once("../components/mobile.php");?>
    <section id="container">
        <!-- Sidebar -->
        <?php include_once("../components/sidebar.php")?>
        <main class="main">
             <?php include_once("../components/topbar.php")?>
             <!-- Running Task container -->
            <div class="newTasksContainer">
                <div class="runningTask">
                    <h3>Running Task:</h3>
                    <p>No pending tasks</p>
                </div>
                <!-- Running Task Table -->
                <div class="givenTasks">
                    <h2>Started Tasks</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Assign Date</th>
                                <th>Details</th>
                                <th>Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Singles task -->
                            <!-- <tr>
                                <td>01</td>
                                <td>9/16/2025 | 11:00 AM</td>
                                <td>Employee management website... <span class="viewButton">view</span></td>
                                <td>9/16/2025 To 9/30/2025</td>
                                <td>
                                    <select name="status" id="status">
                                        <option value="pending">pending</option>
                                        <option value="started">started</option>
                                        <option value="paused">paused</option>
                                        <option value="complete">complete</option>
                                    </select>
                                </td>
                            </tr> -->
                            <!-- Singles task -->
                            <!-- <tr>
                                <td>02</td>
                                <td>9/16/2025 | 11:00 AM</td>
                                <td>Employee management website... <span class="viewButton">view</span></td>
                                <td>9/16/2025 To 9/30/2025</td>
                                <td>
                                    <select name="status" id="status">
                                        <option value="pending">pending</option>
                                        <option value="started">started</option>
                                        <option value="paused">paused</option>
                                        <option value="complete">complete</option>
                                    </select>
                                </td>
                            </tr> -->
                            <tr>
                                <td colspan="5">Task Not Found</td>
                            </tr>
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