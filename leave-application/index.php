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
include_once("../php/leave_applications.php");
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
                <div class="leaveTitleAndBtn">
                    <h2>Leave applications</h2>
                    <button id="applyForLeave" style="margin-top: 0;" class="btn">Apply for leave</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Application</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($all_applications) && count($all_applications) > 0) {
                            foreach ($all_applications as $s_application) {
                                $pdf_url = "../uploads/applications/" . $s_application["application"];
                                echo "
                            <tr>
                            <td>{$s_application["employee_id"]}</td>
                            <td>{$s_application["full_name"]}</td>
                            <td>{$s_application["from_date"]}</td>
                            <td>{$s_application["to_date"]}</td>
                            <td>{$s_application["reason"]}</td>
                            <td><a href='$pdf_url' class='viewDocLink' target='_blank'>View application</a></td>
                            <td style='text-transform: Capitalize'>{$s_application["status"]}</td>
                            </tr>
                                
                                ";
                            }
                        } else {
                            echo "<tr><td colspan='12'>0 application</td></tr>";
                        }
                        ?>

                        <!-- <tr>
                            <td colspan="12">0 Application</td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
        </main>
        <!-- Leave application popup -->
        <div id="leaveApplicationContainer">
            <form action="" method="post" enctype="multipart/form-data" class="leaveApplicationContent">
                <span id="leaveApplicationContainerClose"><i class="fa fa-solid fa-xmark"></i></span>
                <h2>Leave Application</h2>
                <div class="formElement">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" name="employee_id" value="<?php echo htmlspecialchars(($employee_id)) ?>" readonly>
                </div>
                <div class="formElement">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars(($profile_data["first_name"])) . " " . htmlspecialchars(($profile_data["last_name"])) ?>" readonly>
                </div>
                <div class="formElement">
                    <label for="from_date">From Date</label>
                    <input type="date" name="from_date" required>
                </div>
                <div class="formElement">
                    <label for="to_date">To Date</label>
                    <input type="date" name="to_date" required>
                </div>
                <div class="formElement">
                    <label for="reason">Reason</label>
                    <textarea name="reason" id="" style="min-height: 100px;resize:none"></textarea>
                </div>
                <div class="formElement">
                    <label for="application">Application</label>
                    <input type="file" name="application" required accept=".pdf">
                </div>
                <div class="formElement">
                    <button type="submit" class="btn">Submit</button>
                </div>
            </form>
        </div>
    </section>
    <!-- Js codes print -->
    <!-- leave application container js -->
    <script>
        // open
        document.getElementById("applyForLeave").addEventListener("click", () => {
            document.getElementById("leaveApplicationContainer").style.display = "flex";
        });
        //close
        document.getElementById("leaveApplicationContainerClose").addEventListener("click", () => {
            document.getElementById("leaveApplicationContainer").style.display = "none";
        })
    </script>
</body>

</html>