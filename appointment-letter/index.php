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
// If status != approved
if ($profile_data["status"] != "approved") {
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
            <div style="display: <?php
                                    if ($profile_data["status"] && $profile_data["status"] != "approved") {
                                        echo "none";
                                    }
                                    ?>;margin-top: -15px;">
                <button id="appointmentPrintBtn" class="btn"><i class="fa-solid fa-print"></i> Print</button>
            </div>
            <section style="display: <?php
                                        if ($profile_data["status"] && $profile_data["status"] != "approved") {
                                            echo "none";
                                        }
                                        ?>;" id="mainAppointmentContainer">
                <section id="printAppointmentArea">


                    <div class="container">
                        <div class="content">
                            <h1>Appointment Letter</h1>
                            <p class="date">Date: 01-06-2025</p>
                            <div class="contact">
                                <p>To</p>
                                <p><?php echo htmlspecialchars($profile_data["first_name"]);
                                    echo " " . htmlspecialchars($profile_data["last_name"]) ?></p>
                                <?php
                                echo htmlspecialchars($profile_data["permanent_street_1"]) . ", ";
                                echo htmlspecialchars($profile_data["permanent_city"]) . ", ";
                                echo htmlspecialchars($profile_data["permanent_state"]) . ", ";
                                echo htmlspecialchars($profile_data["permanent_zipcode"]) . ", ";
                                echo htmlspecialchars($profile_data["permanent_country"]);

                                ?>
                                <p>Mobile No. <?php echo htmlspecialchars($profile_data["contact_number"]) ?></p>
                            </div>
                            <p class="dear"><?php echo htmlspecialchars($profile_data["first_name"]);
                                            echo " " . htmlspecialchars($profile_data["last_name"]) ?></p>
                            <p>Please refer in the meeting we had with you. We are pleased to offer you an appointment in our company as “Graphic Designer” with “Soft-Tech Technology”. You will be initially at Jashore on joining. Your appointment will be subject to the terms and conditions and the Rules and Regulations of the company prevailing from time to time Details and other allowances & perquisites are indicated. <br>
                                We hope to provide you a challenging and rewarding Career ensuring a high level of job satisfaction and sample opportunities for career development.
                            </p>
                            <p>
                                Please return the duplicate copy of this letter duty signed as an acceptance of our offer of appointment.
                            </p>
                            <p>With best wishes,</p>
                            <div class="termsContainer">
                                <p class="terms"><b>Terms of Appointment</b></p>
                                <p>Every employment will commence from the date of joining as mentioned in the Appointment letter. There will be a Probation period of three months and confirmation of employment will be based on satisfactory performance during this period.</p>
                                <p>During the probation period, the employment can be terminated by either party by giving one week’s notice. After the confirmation of employment the same may be terminated by either party by giving one month’s notice in writing or one month’s basic salary in lieu of notice should the circumstances warrant it.</p>
                                <p style="margin-top: 10px;"><b>Whilst employed with the company:</b></p>
                                <ul>
                                    <li>The staff member shall not undertake any other employment or engage in any external activities without prior written approval of the company.</li>
                                    <li>The staff member shall carry out all duties and responsibilities assigned from time to time by the management and/or others authorized by the company to assign such duties and responsibilities.</li>
                                    <li>The staff member shall not at any time or times without the consent of the company in writing disclose divulge or make public except under legal obligations any of the process accounts transactions and dealings to the company whether the same is communicated and/or becomes known to the staff member in the course of services or otherwise. All information that comes to the knowledge of the staff member by reason of the employment with the company shall deem to be confidential.</li>


                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- second page -->
                    <div style="margin-top: 50px;" class="container">
                        <div class="content">
                            <div class="termsContainer">
                                <ul>
                                    <li>The staff member will be responsible for the safe keeping and return in good condition in of all company’s belongings which may be in your use custody or charge including proper handing over of the assignment (s) at hand.</li>
                                    <li>The staff member will keep us informed of any charge in the residential address as, the address mentioned in over CV will be deemed as residential address unless there is a written communication from you.</li>
                                    <li>All payments will be made in accordance with the income tax laws.</li>
                                    <li>The staff member shall be required to apply and maintain the highest standards of person conduct and integrity with all company policies and procedures.</li>
                                    <li>If the staff member wanna get resign within 6 months from the appointed date He/she would be responsible to pay the whole costs that company expended for him/her.</li>
                                </ul>
                                <p>I shall abide by the above terms and conditions. </p>
                            </div>
                            <div class="details">
                                <p><b>Details of Perquisites & Allowances:</b></p>
                                <div class="detailsMain">
                                    <table>
                                        <tr>
                                            <td>Name</td>
                                            <td>-</td>
                                            <td><?php echo htmlspecialchars($profile_data["first_name"]);
                                                echo " " . htmlspecialchars($profile_data["last_name"]) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Designation</td>
                                            <td>-</td>
                                            <td>Graphic Designer</td>
                                        </tr>
                                        <tr>
                                            <td>Location</td>
                                            <td>-</td>
                                            <td><?php
                                                echo htmlspecialchars($profile_data["permanent_street_1"]) . ", ";
                                                echo htmlspecialchars($profile_data["permanent_city"]) . ", ";
                                                echo htmlspecialchars($profile_data["permanent_state"]) . ", ";
                                                echo htmlspecialchars($profile_data["permanent_zipcode"]) . ", ";
                                                echo htmlspecialchars($profile_data["permanent_country"]);

                                                ?></td>
                                        </tr>
                                        <tr>
                                            <td>Date of Joining</td>
                                            <td>-</td>
                                            <td>01-06-2025</td>
                                        </tr>
                                        <tr>
                                            <td>Probation </td>
                                            <td>-</td>
                                            <td>Three months from the date of joining.</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="signature">
                                <div class="ceo">
                                    (---------------------------)
                                    <p>Khalid Mahamud</p>
                                    <p>Chief Executive Officer</p>
                                    <p>Soft-Tech Technology</p>
                                    <p>IT Park, Jashore-7400</p>

                                </div>
                                <div class="employee">
                                    (---------------------------)
                                    <p>Signature of employee</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </section>
        </main>
    </section>
    <!-- Js codes print -->
    <script>
        const appointmentPrintBtn = document.getElementById("appointmentPrintBtn");
        const printAppointmentArea = document.getElementById("printAppointmentArea").innerHTML;
        const prevHtml = document.body.innerHTML;

        appointmentPrintBtn.addEventListener("click", () => {
            document.body.innerHTML = printAppointmentArea;
            window.print();
            document.body.innerHTML = prevHtml;
            window.location.reload();
        })
    </script>

</body>

</html>