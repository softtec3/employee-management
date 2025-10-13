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
$stmt = $conn->prepare("SELECT profile_image, first_name, last_name, status FROM employee_personal_details WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
}
$stmt->close();

// Fetch document data from database
$document_data = [];
$stmt = $conn->prepare("SELECT * FROM employee_documents WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $document_data = $result->fetch_assoc();
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

// if status != "approved"
if ($document_data["status"] != "approved") {
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
            <h2>Documents</h2>
            <div style="display: <?php
                                    if ($document_data["status"] && $document_data["status"] != "approved") {
                                        echo "none";
                                    }
                                    ?>;" id="documentContainer">
                <!--passport  -->
                <?php
                if ($document_data["passport_no"] && $document_data["passport_photo"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["passport_photo"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>Passport - {$document_data["passport_no"]}</p>
                        <a href='../uploads/documents/{$document_data["passport_photo"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>
                <!-- ssn -->
                <?php
                if ($document_data["ssn_no"] && $document_data["ssn_photo"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["ssn_photo"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>SSN - {$document_data["ssn_no"]}</p>
                        <a href='../uploads/documents/{$document_data["ssn_photo"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>
                <!-- driving license front -->
                <?php
                if ($document_data["driving_license_no"] && $document_data["driving_license_front"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["driving_license_front"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>Driving License Front - {$document_data["driving_license_no"]}</p>
                        <a href='../uploads/documents/{$document_data["driving_license_front"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>
                <!-- driving license back -->
                <?php
                if ($document_data["driving_license_no"] && $document_data["driving_license_back"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["driving_license_back"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>Driving License Back - {$document_data["driving_license_no"]}</p>
                        <a href='../uploads/documents/{$document_data["driving_license_back"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>

                <!-- Voter card front -->
                <?php
                if ($document_data["voter_card_no"] && $document_data["voter_card_front"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["voter_card_front"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>Voter Card Front - {$document_data["voter_card_no"]}</p>
                        <a href='../uploads/documents/{$document_data["voter_card_front"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>
                <!-- Voter card back -->
                <?php
                if ($document_data["voter_card_no"] && $document_data["voter_card_back"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["voter_card_back"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>Voter Card Back - {$document_data["voter_card_no"]}</p>
                        <a href='../uploads/documents/{$document_data["voter_card_back"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>
                <!-- ITIN -->
                <?php
                if ($document_data["itin_no"] && $document_data["itin_photo"]) {
                    echo "
                    <div class='document'>
                    <div class='docPreview'>
                        <iframe src='../uploads/documents/{$document_data["itin_photo"]}' frameborder='0' style='width: 100%; height:100%'></iframe>
                    </div>
                    <div class='docDescription'>
                        <p>ITIN - {$document_data["voter_card_no"]}</p>
                        <a href='../uploads/documents/{$document_data["itin_photo"]}' class='btn' target='_blank'>Full view</a>
                    </div>
                </div>
                    ";
                }
                ?>

            </div>
        </main>
    </section>
    <!-- Js codes for popup open and close -->

</body>

</html>