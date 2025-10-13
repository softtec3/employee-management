<?php
// Include global configuration
$config_paths = [
    'config.php',
    '../config.php',
    '../../config.php'
];

foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once($config_path);
        break;
    }
}

// Include database connection using the helper function
if (!include_db_connection()) {
    die("Error: Database connection file not found");
}

$passkey_error = "";
$passkey_success = "";
$change_passkey_error = "";
$change_passkey_success = "";

// Handle passkey verification form submission
if (isset($_POST['passkey'])) {
    $entered_passkey = trim($_POST['passkey']);
    $employee_id = $_SESSION['employee_id'];

    if (!empty($entered_passkey)) {
        // Check passkey in database
        $stmt = $conn->prepare("SELECT passkey FROM employee_log WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($entered_passkey === $user['passkey']) {
                // Passkey is correct, redirect to profile page
                header("Location: ../profile/index.php");
                exit();
            } else {
                $passkey_error = "Invalid passkey! Please try again.";
            }
        } else {
            $passkey_error = "User not found!";
        }
        $stmt->close();
    } else {
        $passkey_error = "Please enter your passkey!";
    }
}

// Handle change passkey form submission
if (isset($_POST['currentPasskey']) && isset($_POST['newPasskey']) && isset($_POST['confirmNewPasskey'])) {
    $current_passkey = trim($_POST['currentPasskey']);
    $new_passkey = trim($_POST['newPasskey']);
    $confirm_passkey = trim($_POST['confirmNewPasskey']);
    $employee_id = $_SESSION['employee_id'];

    if (!empty($current_passkey) && !empty($new_passkey) && !empty($confirm_passkey)) {
        // Check if new passkey and confirm passkey match
        if ($new_passkey === $confirm_passkey) {
            // Check if new passkey is exactly 5 digits
            if (preg_match('/^\d{5}$/', $new_passkey)) {
                // Verify current passkey
                $stmt = $conn->prepare("SELECT passkey FROM employee_log WHERE employee_id = ?");
                $stmt->bind_param("s", $employee_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if ($current_passkey === $user['passkey']) {
                        // Current passkey is correct, update with new passkey
                        $update_stmt = $conn->prepare("UPDATE employee_log SET passkey = ? WHERE employee_id = ?");
                        $update_stmt->bind_param("ss", $new_passkey, $employee_id);

                        if ($update_stmt->execute()) {
                            $change_passkey_success = "Passkey changed successfully!";
                        } else {
                            $change_passkey_error = "Failed to update passkey. Please try again.";
                        }
                        $update_stmt->close();
                    } else {
                        $change_passkey_error = "Current passkey is incorrect!";
                    }
                } else {
                    $change_passkey_error = "User not found!";
                }
                $stmt->close();
            } else {
                $change_passkey_error = "New passkey must be exactly 5 digits!";
            }
        } else {
            $change_passkey_error = "New passkey and confirm passkey do not match!";
        }
    } else {
        $change_passkey_error = "Please fill in all fields!";
    }
}

// Debug: Check session status
echo "<!-- Debug: Session status: " . session_status() . " -->";
echo "<!-- Debug: Session data: " . print_r($_SESSION, true) . " -->";

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If not logged in, redirect to login page
    header("Location: ./login/index.php");
    exit();
}

// Get user info from session
$employee_id = $_SESSION['employee_id'] ?? 'N/A';
$employee_name = $_SESSION['name'] ?? 'Unknown User';

// Fetch profile data from database for display
$profile_data = [];
// $stmt = $conn->prepare("SELECT profile_image, first_name, last_name, status FROM employee_personal_details WHERE employee_id = ?");
// selecting all because of appointment section
$stmt = $conn->prepare("SELECT * FROM employee_personal_details WHERE employee_id = ?");
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
$display_name = $employee_name; // Default to session name
if (!empty($profile_data['first_name']) && !empty($profile_data['last_name'])) {
    $display_name = $profile_data['first_name'] . ' ' . $profile_data['last_name'];
}

$profile_image_path = '../placeholder.jpg'; // Default image
if (!empty($profile_data['profile_image'])) {
    $profile_image_path = '../uploads/profile_images/' . $profile_data['profile_image'];
    // Check if file actually exists
    if (!file_exists($profile_image_path)) {
        $profile_image_path = '../placeholder.jpg';
    }
}

// Debug: Print the variables
echo "<!-- Debug: employee_id = " . $employee_id . " -->";
echo "<!-- Debug: employee_name = " . $employee_name . " -->";
echo "<!-- Debug: display_name = " . $display_name . " -->";
echo "<!-- Debug: profile_image_path = " . $profile_image_path . " -->";
?>
<!-- This is sidebar -->
<aside class="sidebar">
    <!-- Profile section -->
    <div class="profile">
        <div class="proImg">
            <img src='<?php echo htmlspecialchars($profile_image_path); ?>' alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>
        <div class="proDesc">
            <table>
                <tr>
                    <th>Employee ID <span>:</span></th>
                    <td><?php echo htmlspecialchars($employee_id); ?></td>
                </tr>
                <tr>
                    <th>Name <span>:</span></th>
                    <td><?php echo htmlspecialchars($display_name); ?></td>
                </tr>
                <tr>
                    <th>Department <span>:</span></th>
                    <td>Management</td>
                </tr>
                <tr>
                    <th>Designation <span>:</span></th>
                    <td style="font-size: 14px;">Chief Operating Officer (USA)</td>
                </tr>
            </table>
            <button id="loginBtn" class="loginBtn">Profile Login</button>

        </div>
    </div>
    <!-- navigation links -->
    <div class="links">
        <a href="../index/index.php"><i class="fas fa-house"></i> Home</a>
        <a href="../new-tasks/index.php"><i class="fas fa-plus-circle"></i> New Tasks</a>
        <a href="../completed-tasks/index.php"><i class="fas fa-check-circle"></i> Completed Tasks</a>
        <a href="../started-tasks/index.php"><i class="fas fa-play-circle"></i> Started Tasks</a>
        <a href="../pending-tasks/index.php"><i class="fas fa-hourglass-half"></i> Pending Tasks</a>
        <a href="../invoices/index.php"><i class="fa-solid fa-file-invoice-dollar"></i></i> Invoices</a>
        <a style="<?php
                    if ($profile_data["status"] && $profile_data["status"] != "approved") {
                        echo "pointer-events:none";
                    }
                    ?>" href="../appointment-letter/index.php"><i class="fa-solid fa-file-signature"></i> Appointment Letter</a>
        <a style="<?php
                    if ($document_data["status"] && $document_data["status"] != "approved") {
                        echo "pointer-events:none";
                    }
                    ?>" href="../documents/index.php"><i class="fa-solid fa-folder-open"></i> Documents</a>
    </div>
    <!-- Profile login popup -->
    <div id="loginPopup" style="display:none">
        <div class="loginPopupContainer">
            <span id="loginPopupCloseBtn"><i class="fa-solid fa-xmark"></i></span>
            <!-- Profile login form -->
            <div id="loginDefault" class="loginDefault">
                <p> <i class="fa-solid fa-triangle-exclamation" style="color: red;"></i> Your profile page contains very important and confidential information. So, Don't share your profile passkey with others.</p>

                <?php if (!empty($passkey_error)): ?>
                    <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px; padding: 8px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 4px;">
                        <?php echo htmlspecialchars($passkey_error); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" class="passkeyContainer">
                    <label for="passkey">Passkey</label>
                    <div class="inputField">
                        <input type="text" name="passkey" placeholder="5 Digits Numbers only" required maxlength="5" id="onlyNumber">
                        <button type="submit">Submit</button>
                    </div>
                </form>
                <button id="changePasskeyBtn" class="btn changePasskeyBtn">Change Passkey</button>
            </div>
            <!-- Change passkey form -->
            <form action="" method="post" id="changePasskey" class="changePasskey">
                <h3>Set New Passkey</h3>

                <?php if (!empty($change_passkey_error)): ?>
                    <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px; padding: 8px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 4px;">
                        <?php echo htmlspecialchars($change_passkey_error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($change_passkey_success)): ?>
                    <div class="success-message" style="color: green; text-align: center; margin-bottom: 10px; padding: 8px; background-color: #e8f5e8; border: 1px solid #4caf50; border-radius: 4px;">
                        <?php echo htmlspecialchars($change_passkey_success); ?>
                    </div>
                <?php endif; ?>

                <div class="cpDiv">
                    <label for="currentPasskey">Current Passkey</label>
                    <input type="password" name="currentPasskey" required maxlength="5">
                </div>
                <div class="cpDiv">
                    <label for="newPasskey">New Passkey</label>
                    <input type="password" name="newPasskey" id="newPasskey" required maxlength="5">
                </div>
                <div class="cpDiv">
                    <label for="confirmNewPasskey">Confirm New Passkey</label>
                    <input type="password" name="confirmNewPasskey" id="confirmNewPasskey" required maxlength="5">
                </div>
                <span id="errorSpan"></span>
                <div class="cpActionsBtns">
                    <button type="submit" id="changeBtn" class="btn">Change</button>
                    <button type="button" id="cancelBtn" class="btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</aside>

<script>
    const loginPopup = document.getElementById("loginPopup");
    const loginPopupCloseBtn = document.getElementById("loginPopupCloseBtn")
    const loginBtn = document.getElementById("loginBtn");
    const loginDefault = document.getElementById("loginDefault");
    const changePasskey = document.getElementById("changePasskey");
    const changePasskeyBtn = document.getElementById("changePasskeyBtn");
    const cancelBtn = document.getElementById("cancelBtn")
    changePasskeyBtn.addEventListener("click", () => {
        loginDefault.style.display = "none";
        changePasskey.style.display = "flex"
    })
    cancelBtn.addEventListener("click", () => {
        loginDefault.style.display = "block";
        changePasskey.style.display = "none"
    })
    loginPopupCloseBtn.addEventListener("click", () => {
        loginPopup.style.display = "none";
    })
    loginBtn.addEventListener("click", () => {
        loginPopup.style.display = "flex";
    })

    // newPasskey and confirmNewPasskey matched
    const newPasskey = document.getElementById("newPasskey");
    const confirmNewPasskey = document.getElementById("confirmNewPasskey");
    const errorSpan = document.getElementById("errorSpan");
    const changeBtn = document.getElementById("changeBtn");

    confirmNewPasskey.addEventListener("input", (e) => {
        let value = e.target.value;
        if (value !== newPasskey.value) {
            changeBtn.disabled = true;
            errorSpan.innerText = "Confirm password not matched with new password"
        } else {
            errorSpan.innerText = "";
            changeBtn.disabled = false;
        }
    })
    // number only for passkey inputs
    const onlyNumberInputs = document.querySelectorAll("#onlyNumber, input[name='currentPasskey'], input[name='newPasskey'], input[name='confirmNewPasskey']");
    onlyNumberInputs.forEach(input => {
        input.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, "");
        });
    });
</script>