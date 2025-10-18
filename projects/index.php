<?php
session_start();
require_once('../php/db_connect.php');


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
$stmt = $conn->prepare("SELECT profile_image, first_name, last_name, status FROM employee_personal_details WHERE employee_id = ?");
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
// check permission for access of this page
include_once("../php/projects.php");
if (!isset($project_access) || $project_access == NULL) {
    header("Location: ../index/index.php");
}
?>
<!-- Started Tasks page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
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
            <?php include_once("../components/topbar.php") ?>
            <div class="givenTasks">
                <div class="projectSelect">
                    <h2>Projects tasks</h2>
                </div>
                <!-- Specific project -->
                <table>
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>Project Title</th>
                            <th>Task Title</th>
                            <th>Task Description</th>
                            <th>Attachment</th>
                            <th>Assign To</th>
                            <th>Assign Date</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Correction</th>
                            <th>Completed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($all_projects_tasks) && count($all_projects_tasks) > 0) {
                            foreach ($all_projects_tasks as $s_p_task) {
                                if ($s_p_task["completed_date"]) {
                                    $is_completed = $s_p_task["completed_date"];
                                } else {
                                    $is_completed = "---";
                                }
                                if ($s_p_task["correction"]) {
                                    $correction = $s_p_task["correction"];
                                } else {
                                    $correction = "---";
                                }
                                if ($s_p_task["status"] == "pending") {
                                    $act_btn = "<a href='./index.php?start_project_task={$s_p_task["id"]}' class='linkBtn' style='background-color: green;'>Start</a>";
                                } else if ($s_p_task["status"] == "in-progress") {
                                    $act_btn = "<a href='./index.php?complete_project_task={$s_p_task["id"]}' class='linkBtn' style='background-color: green;'>Complete</a>";
                                } else {
                                    $act_btn = "---";
                                }
                                echo "
                            <tr>
                            <td>{$s_p_task["project_id"]}</td>
                            <td>{$s_p_task["project_title"]}</td>
                            <td>{$s_p_task["title"]}</td>
                            <td>{$s_p_task["description"]}</td>
                            <td><a href='../uploads/{$s_p_task["attachment"]}' target='_blank' class='linkBtn' style='background-color: orange;'>View</a></td>
                            <td>{$s_p_task["assign_to"]}</td>
                            <td>{$s_p_task["created_at"]}</td>
                            <td>{$s_p_task["deadline"]}</td>
                            <td style='text-transform:capitalize'>{$s_p_task["status"]}</td>
                            <td>$act_btn</td>
                            <td>$correction</td>
                            <td>$is_completed</td>

                        </tr>
                                ";
                            }
                        }
                        ?>


                    </tbody>
                </table>
            </div>
        </main>
    </section>
    <!-- Js codes print -->

</body>

</html>