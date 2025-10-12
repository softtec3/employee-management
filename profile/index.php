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

// Success and error messages
$personal_message = "";
$documents_message = "";
$emergency_message = "";

// Handle Personal Details Form Submission
if (isset($_POST['personal_details'])) {
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $fathers_name = trim($_POST['fathersName']);
    $present_street_1 = trim($_POST['presentAddressStreetOne']);
    $present_street_2 = trim($_POST['presentAddressStreetTwo']);
    $present_city = trim($_POST['city']);
    $present_state = trim($_POST['state']);
    $present_zipcode = trim($_POST['zipcode']);
    $present_country = trim($_POST['country']);
    $permanent_street_1 = trim($_POST['permanentAddressStreetOne']);
    $permanent_street_2 = trim($_POST['permanentAddressStreetTwo']);
    $permanent_city = trim($_POST['permanentCity']);
    $permanent_state = trim($_POST['permanentState']);
    $permanent_zipcode = trim($_POST['permanentZipcode']);
    $contact_number = trim($_POST['contactNumber']);
    $number_type = $_POST['numberType'];

    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        $upload_dir = '../uploads/profile_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $profile_image = $employee_id . '_profile_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $profile_image;

            if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $upload_path)) {
                // File uploaded successfully
            } else {
                $personal_message = "Failed to upload profile image.";
            }
        } else {
            $personal_message = "Invalid image format. Please use JPG, JPEG, PNG, or GIF.";
        }
    }

    if (empty($personal_message)) {
        // Check if record exists
        $check_stmt = $conn->prepare("SELECT id FROM employee_personal_details WHERE employee_id = ?");
        $check_stmt->bind_param("s", $employee_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing record
            if ($profile_image) {
                $update_stmt = $conn->prepare("UPDATE employee_personal_details SET profile_image = ?, first_name = ?, last_name = ?, fathers_name = ?, present_street_1 = ?, present_street_2 = ?, present_city = ?, present_state = ?, present_zipcode = ?, present_country = ?, permanent_street_1 = ?, permanent_street_2 = ?, permanent_city = ?, permanent_state = ?, permanent_zipcode = ?, contact_number = ?, number_type = ?, status = 'review' WHERE employee_id = ?");
                $update_stmt->bind_param("ssssssssssssssssss", $profile_image, $first_name, $last_name, $fathers_name, $present_street_1, $present_street_2, $present_city, $present_state, $present_zipcode, $present_country, $permanent_street_1, $permanent_street_2, $permanent_city, $permanent_state, $permanent_zipcode, $contact_number, $number_type, $employee_id);
            } else {
                $update_stmt = $conn->prepare("UPDATE employee_personal_details SET first_name = ?, last_name = ?, fathers_name = ?, present_street_1 = ?, present_street_2 = ?, present_city = ?, present_state = ?, present_zipcode = ?, present_country = ?, permanent_street_1 = ?, permanent_street_2 = ?, permanent_city = ?, permanent_state = ?, permanent_zipcode = ?, contact_number = ?, number_type = ?, status = 'review' WHERE employee_id = ?");
                $update_stmt->bind_param("sssssssssssssssss", $first_name, $last_name, $fathers_name, $present_street_1, $present_street_2, $present_city, $present_state, $present_zipcode, $present_country, $permanent_street_1, $permanent_street_2, $permanent_city, $permanent_state, $permanent_zipcode, $contact_number, $number_type, $employee_id);
            }
        } else {
            // Insert new record
            $insert_stmt = $conn->prepare("INSERT INTO employee_personal_details (employee_id, profile_image, first_name, last_name, fathers_name, present_street_1, present_street_2, present_city, present_state, present_zipcode, present_country, permanent_street_1, permanent_street_2, permanent_city, permanent_state, permanent_zipcode, contact_number, number_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'review')");
            $insert_stmt->bind_param("ssssssssssssssssss", $employee_id, $profile_image, $first_name, $last_name, $fathers_name, $present_street_1, $present_street_2, $present_city, $present_state, $present_zipcode, $present_country, $permanent_street_1, $permanent_street_2, $permanent_city, $permanent_state, $permanent_zipcode, $contact_number, $number_type);
            $update_stmt = $insert_stmt;
        }

        if ($update_stmt->execute()) {
            $personal_message = "Personal details updated successfully!";
        } else {
            $personal_message = "Error updating personal details. Please try again.";
        }
        $update_stmt->close();
        $check_stmt->close();
    }
}

// Handle Essential Documents Form Submission
if (isset($_POST['essential'])) {
    $document_type = $_POST['documentType'];
    $passport_no = trim($_POST['passportNo'] ?? '');
    $driving_license_no = trim($_POST['drivingLicenseNo'] ?? '');
    $voter_card_no = trim($_POST['voterCardNo'] ?? '');
    $ssn_no = trim($_POST['ssnNo'] ?? '');
    $itin_no = trim($_POST['itinNo'] ?? '');

    // Handle file uploads
    $upload_dir = '../uploads/documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $uploaded_files = [];
    $file_fields = ['passportPhoto', 'drivingLicenseFrontPhoto', 'drivingLicenseBackPhoto', 'voterCardFrontPhoto', 'voterCardBackPhoto', 'ssnPhoto', 'itinPhoto'];

    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array($file_extension, $allowed_extensions)) {
                $filename = $employee_id . '_' . $field . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_path)) {
                    $uploaded_files[$field] = $filename;
                }
            }
        }
    }

    // Check if record exists
    $check_stmt = $conn->prepare("SELECT id FROM employee_documents WHERE employee_id = ?");
    $check_stmt->bind_param("s", $employee_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $passport_photo = $uploaded_files['passportPhoto'] ?? null;
        $driving_front = $uploaded_files['drivingLicenseFrontPhoto'] ?? null;
        $driving_back = $uploaded_files['drivingLicenseBackPhoto'] ?? null;
        $voter_front = $uploaded_files['voterCardFrontPhoto'] ?? null;
        $voter_back = $uploaded_files['voterCardBackPhoto'] ?? null;
        $ssn_photo = $uploaded_files['ssnPhoto'] ?? null;
        $itin_photo = $uploaded_files['itinPhoto'] ?? null;

        $update_stmt = $conn->prepare("UPDATE employee_documents SET document_type = ?, passport_no = ?, passport_photo = ?, driving_license_no = ?, driving_license_front = ?, driving_license_back = ?, voter_card_no = ?, voter_card_front = ?, voter_card_back = ?, ssn_no = ?, ssn_photo = ?, itin_no = ?, itin_photo = ?, status = 'review' WHERE employee_id = ?");
        $update_stmt->bind_param("ssssssssssssss", $document_type, $passport_no, $passport_photo, $driving_license_no, $driving_front, $driving_back, $voter_card_no, $voter_front, $voter_back, $ssn_no, $ssn_photo, $itin_no, $itin_photo, $employee_id);
    } else {
        // Insert new record
        $passport_photo = $uploaded_files['passportPhoto'] ?? null;
        $driving_front = $uploaded_files['drivingLicenseFrontPhoto'] ?? null;
        $driving_back = $uploaded_files['drivingLicenseBackPhoto'] ?? null;
        $voter_front = $uploaded_files['voterCardFrontPhoto'] ?? null;
        $voter_back = $uploaded_files['voterCardBackPhoto'] ?? null;
        $ssn_photo = $uploaded_files['ssnPhoto'] ?? null;
        $itin_photo = $uploaded_files['itinPhoto'] ?? null;

        $insert_stmt = $conn->prepare("INSERT INTO employee_documents (employee_id, document_type, passport_no, passport_photo, driving_license_no, driving_license_front, driving_license_back, voter_card_no, voter_card_front, voter_card_back, ssn_no, ssn_photo, itin_no, itin_photo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'review')");
        $insert_stmt->bind_param("sssssssssssssss", $employee_id, $document_type, $passport_no, $passport_photo, $driving_license_no, $driving_front, $driving_back, $voter_card_no, $voter_front, $voter_back, $ssn_no, $ssn_photo, $itin_no, $itin_photo);
        $update_stmt = $insert_stmt;
    }

    if ($update_stmt->execute()) {
        $documents_message = "Essential documents updated successfully!";
    } else {
        $documents_message = "Error updating documents. Please try again.";
    }
    $update_stmt->close();
    $check_stmt->close();
}

// Handle Emergency Contacts Form Submission
if (isset($_POST['emergency_contacts'])) {
    $contact_1_name = trim($_POST['emergencyName1']);
    $contact_1_relation = trim($_POST['emergencyRelation1']);
    $contact_1_phone = trim($_POST['emergencyPhone1']);
    $contact_2_name = trim($_POST['emergencyName2']);
    $contact_2_relation = trim($_POST['emergencyRelation2']);
    $contact_2_phone = trim($_POST['emergencyPhone2']);
    $contact_3_name = trim($_POST['emergencyName3']);
    $contact_3_relation = trim($_POST['emergencyRelation3']);
    $contact_3_phone = trim($_POST['emergencyPhone3']);

    // Check if record exists
    $check_stmt = $conn->prepare("SELECT id FROM employee_emergency_contacts WHERE employee_id = ?");
    $check_stmt->bind_param("s", $employee_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $update_stmt = $conn->prepare("UPDATE employee_emergency_contacts SET contact_1_name = ?, contact_1_relation = ?, contact_1_phone = ?, contact_2_name = ?, contact_2_relation = ?, contact_2_phone = ?, contact_3_name = ?, contact_3_relation = ?, contact_3_phone = ?, status = 'review' WHERE employee_id = ?");
        $update_stmt->bind_param("ssssssssss", $contact_1_name, $contact_1_relation, $contact_1_phone, $contact_2_name, $contact_2_relation, $contact_2_phone, $contact_3_name, $contact_3_relation, $contact_3_phone, $employee_id);
    } else {
        // Insert new record
        $insert_stmt = $conn->prepare("INSERT INTO employee_emergency_contacts (employee_id, contact_1_name, contact_1_relation, contact_1_phone, contact_2_name, contact_2_relation, contact_2_phone, contact_3_name, contact_3_relation, contact_3_phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'review')");
        $insert_stmt->bind_param("ssssssssss", $employee_id, $contact_1_name, $contact_1_relation, $contact_1_phone, $contact_2_name, $contact_2_relation, $contact_2_phone, $contact_3_name, $contact_3_relation, $contact_3_phone);
        $update_stmt = $insert_stmt;
    }

    if ($update_stmt->execute()) {
        $emergency_message = "Emergency contacts updated successfully!";
    } else {
        $emergency_message = "Error updating emergency contacts. Please try again.";
    }
    $update_stmt->close();
    $check_stmt->close();
}

// Fetch existing data from database
$personal_data = [];
$documents_data = [];
$emergency_data = [];

// Fetch personal details
$stmt = $conn->prepare("SELECT * FROM employee_personal_details WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $personal_data = $result->fetch_assoc();
}
$stmt->close();

// Fetch documents data
$stmt = $conn->prepare("SELECT * FROM employee_documents WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $documents_data = $result->fetch_assoc();
}
$stmt->close();

// Fetch emergency contacts
$stmt = $conn->prepare("SELECT * FROM employee_emergency_contacts WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $emergency_data = $result->fetch_assoc();
}
$stmt->close();

// Get user info from employee_log table
$stmt = $conn->prepare("SELECT * FROM employee_log WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
?>
<!-- Profile page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Profile</title>
</head>

<body>
    <!-- mobile protection -->
    <?php include_once("../components/mobile.php"); ?>
    <section id="container">
        <!-- profile page top bar -->
        <div class="topBar">
            <h3>Welcome <?php echo htmlspecialchars($personal_data['first_name'] ?? ''); ?></h3>
            <form action="../index/index.php" method="post">
                <h3>Account status: <span style="color: orange">Pending</span></h3>
                <button type="submit" class="btn">Logout</button>
            </form>
        </div>
        <!-- warning container -->
        <div class="warningContainer">
            <p class="warning"><i class="fa-solid fa-triangle-exclamation"></i>
                This page of our system is governed by local state government. Your information maybe accessed by the local government at any time for verification or necessity.
                <br>
                Please provide all your information accurately. Otherwise, your account maybe rejected.
            </p>
        </div>
        <main class="main">
            <!-- Personal Details Form -->
            <div class="detailsForm">
                <div class="headingDiv">
                    <h3>Personal details</h3>
                    <p>Status: <span id="infoStatus" style="color:<?php echo isset($personal_data['status']) ? ($personal_data['status'] == 'approved' ? 'green' : 'orange') : 'gray'; ?>"><?php echo isset($personal_data['status']) ? ucfirst($personal_data['status']) : 'Not Submitted'; ?></span></p>
                </div>

                <?php if (!empty($personal_message)): ?>
                    <div class="message" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; <?php echo strpos($personal_message, 'successfully') !== false ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                        <?php echo htmlspecialchars($personal_message); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data">
                    <div class="profileImageUpload">
                        <div class="profileImageContainer">
                            <img id='imgPreview' src='<?php echo !empty($personal_data['profile_image']) ? "../uploads/profile_images/" . $personal_data['profile_image'] : "./placeholder.jpg"; ?>'>
                        </div>
                        <div class="formField">
                            <label for="profileImage">Upload your photo <span class="required">*</span></label>
                            <input id="imgPreviewInput" type="file" name="profileImage" accept="image/*">
                        </div>
                    </div>
                    <div class="formFlex">
                        <div class="formField">
                            <label for="firstName">First name <span class="required">*</span></label>
                            <input type="text" name="firstName" required value="<?php echo htmlspecialchars($personal_data['first_name'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="lastName">Last name <span class="required">*</span></label>
                            <input type="text" name="lastName" required value="<?php echo htmlspecialchars($personal_data['last_name'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="formField">
                        <label for="fathersName">Father's name <span class="required">*</span></label>
                        <input type="text" name="fathersName" required value="<?php echo htmlspecialchars($personal_data['fathers_name'] ?? ''); ?>">
                    </div>

                    <div class="presentAddressContainer">
                        <h4>Present address</h4>

                        <div class="formField">
                            <label for="presentAddressStreetOne">Street address 1 <span class="required">*</span></label>
                            <input id="presentAddressStreetOne" type="text" name="presentAddressStreetOne" required value="<?php echo htmlspecialchars($personal_data['present_street_1'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="presentAddressStreetTwo">Street address 2 <span class="optional">(Optional)</span></label>
                            <input id="presentAddressStreetTwo" type="text" name="presentAddressStreetTwo" value="<?php echo htmlspecialchars($personal_data['present_street_2'] ?? ''); ?>">
                        </div>

                        <div class="formFlex">
                            <div class="formField">
                                <label for="city">City <span class="required">*</span></label>
                                <input id="city" type="text" name="city" required value="<?php echo htmlspecialchars($personal_data['present_city'] ?? ''); ?>">
                            </div>
                            <div class="formField">
                                <label for="state">State <span class="required">*</span></label>
                                <input id="state" type="text" name="state" required value="<?php echo htmlspecialchars($personal_data['present_state'] ?? ''); ?>">
                            </div>
                            <div class="formField">
                                <label for="zipcode">Zipcode <span class="required">*</span></label>
                                <input id="zipcode" type="text" name="zipcode" required value="<?php echo htmlspecialchars($personal_data['present_zipcode'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="formField">
                            <label for="country">Country <span class="required">*</span></label>
                            <input id="country" type="text" name="country" value="<?php echo htmlspecialchars($personal_data['present_country'] ?? 'United States'); ?>" readonly>
                        </div>
                    </div>
                    <div class="sameAs">
                        <input id="sameAsPresent" type="checkbox" name="sameAsPresent" onchange="handleCheckbox(this)">
                        <label for="sameAsPresent">Same as present address </label>
                    </div>
                    <!-- permanent address -->
                    <div class="permanentAddressContainer">
                        <h4>Permanent address</h4>

                        <div class="formField">
                            <label for="permanentAddressStreetOne">Street address 1 <span class="required">*</span></label>
                            <input id="permanentAddressStreetOne" type="text" name="permanentAddressStreetOne" required value="<?php echo htmlspecialchars($personal_data['permanent_street_1'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="permanentAddressStreetTwo">Street address 2 <span class="optional"><span class="optional">(Optional)</span></span></label>
                            <input id="permanentAddressStreetTwo" type="text" name="permanentAddressStreetTwo" value="<?php echo htmlspecialchars($personal_data['permanent_street_2'] ?? ''); ?>">
                        </div>
                        <div class="formFlex">
                            <div class="formField">
                                <label for="permanentCity">City <span class="required">*</span></label>
                                <input id="permanentCity" type="text" name="permanentCity" required value="<?php echo htmlspecialchars($personal_data['permanent_city'] ?? ''); ?>">
                            </div>
                            <div class="formField">
                                <label for="permanentState">State <span class="required">*</span></label>
                                <input id="permanentState" type="text" name="permanentState" required value="<?php echo htmlspecialchars($personal_data['permanent_state'] ?? ''); ?>">
                            </div>
                            <div class="formField">
                                <label for="permanentZipcode">Zipcode <span class="required">*</span></label>
                                <input id="permanentZipcode" type="text" name="permanentZipcode" required value="<?php echo htmlspecialchars($personal_data['permanent_zipcode'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="formField">
                            <label for="permanentCountry">Country <span class="required">*</span></label>
                            <input id="permanentCountry" type="text" name="permanentCountry" value="<?php echo htmlspecialchars($personal_data['permanent_country'] ?? 'United States'); ?>" readonly>
                        </div>
                    </div>

                    <div class="formFlex2">
                        <div class="formField">
                            <div id="numberCode">
                                <label for="contactNumber" style="min-width: 90px;">Contact no <span class="required">*</span></label>
                                <select name="numberType" id="numberType">
                                    <option value="usa" <?php echo (isset($personal_data['number_type']) && $personal_data['number_type'] == 'usa') ? 'selected' : ''; ?>>USA +1</option>
                                    <option value="bangladesh" <?php echo (isset($personal_data['number_type']) && $personal_data['number_type'] == 'bangladesh') ? 'selected' : ''; ?>>BD+880</option>
                                </select>
                            </div>
                        </div>
                        <div class="formField">
                            <input type="text" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($personal_data['contact_number'] ?? '+1'); ?>" required>
                        </div>
                    </div>

                    <div class="formField">
                        <button type="submit" name="personal_details" class="saveChangesBtn">Update</button>
                    </div>
                </form>
            </div>
            <!-- Essential Documents Form -->
            <div class="detailsForm">
                <div class="headingDiv">
                    <h3>Essential documents</h3>
                    <p>Status: <span id="infoStatus" style="color:<?php echo isset($documents_data['status']) ? ($documents_data['status'] == 'approved' ? 'green' : 'orange') : 'gray'; ?>"><?php echo isset($documents_data['status']) ? ucfirst($documents_data['status']) : 'Not Submitted'; ?></span></p>
                </div>

                <?php if (!empty($documents_message)): ?>
                    <div class="message" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; <?php echo strpos($documents_message, 'successfully') !== false ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                        <?php echo htmlspecialchars($documents_message); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data">
                    <div class="formField">
                        <label for="addMore">Document type </label>
                        <?php $docType = isset($documents_data['document_type']) && $documents_data['document_type'] !== '' ? $documents_data['document_type'] : 'passport'; ?>
                        <select name="documentType" id="documentType" required>
                            <option style="display: none;" value="">Select Type</option>
                            <option value="passport" <?php echo ($docType === 'passport') ? 'selected' : ''; ?>>passport</option>
                            <option value="drivingLicense" <?php echo ($docType === 'drivingLicense') ? 'selected' : ''; ?>>driving License</option>
                            <option value="voterCard" <?php echo ($docType === 'voterCard') ? 'selected' : ''; ?>>Voter Card</option>
                        </select>
                    </div>

                    <div id="passportContainer" style="<?php echo ($docType === 'passport' ? 'display:block;' : 'display:none;'); ?>">
                        <div class="formField">
                            <label for="passportNo">Passport no <span class="required">*</span></label>
                            <input type="text" name="passportNo" value="<?php echo htmlspecialchars($documents_data['passport_no'] ?? ''); ?>" <?php echo ($docType === 'passport') ? 'required' : ''; ?>>
                        </div>
                        <div class="formField">
                            <label for="passportPhoto">Passport information page picture <span class="required">*</span></label>
                            <!-- passport image preview -->
                            <div id="passportPreview">
                                <div style="display: flex;align-items: center; justify-content: center;">
                                    <img id="passportPreviewImg" src="<?php
                                                                        $img_url = "./documentPlaceholder.jpg";
                                                                        if (!empty($documents_data['passport_photo'])) {
                                                                            $img_url = "../uploads/documents/" . htmlspecialchars($documents_data['passport_photo']);
                                                                            echo $img_url;
                                                                        } else {
                                                                            echo $img_url;
                                                                        }

                                                                        ?>" alt="">
                                </div>

                            </div>
                            <input id="passportInput" type="file" name="passportPhoto" accept="image/*" <?php echo ($docType === 'passport') ? 'required' : ''; ?>>
                            <?php if (!empty($documents_data['passport_photo'])): ?>
                                <small>Current file: <?php echo htmlspecialchars($documents_data['passport_photo']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="drivingLicenseContainer" style="<?php echo ($docType === 'drivingLicense' ? 'display:block;' : 'display:none;'); ?>">
                        <div class="formField">
                            <label for="drivingLicenseNo">Driving license no <span class="required">*</span></label>
                            <input type="text" name="drivingLicenseNo" value="<?php echo htmlspecialchars($documents_data['driving_license_no'] ?? ''); ?>" <?php echo ($docType === 'drivingLicense') ? 'required' : ''; ?>>
                        </div>
                        <div class="formFlex" style="justify-content: center;">


                            <!-- Img preview -->
                            <div class="docPreview">
                                <div style="display: flex;align-items: center; justify-content: center;">
                                    <img id="drivingFrontImg" class="docPreviewImg" src="./documentPlaceholder.jpg" alt="">
                                </div>

                            </div>
                            <!-- Img preview -->
                            <!-- Img preview 2 -->
                            <div class="docPreview">
                                <div style="display: flex;align-items: center; justify-content: center;">
                                    <img id="drivingBackImg" class="docPreviewImg" src="./documentPlaceholder.jpg" alt="">
                                </div>

                            </div>
                        </div>
                        <!-- Img preview -->
                        <div class="formFlex">
                            <div class="formField">
                                <label for="drivingLicenseFrontPhoto">Driving license front photo <span class="required">*</span></label>

                                <input id="drivingFrontInput" type="file" name="drivingLicenseFrontPhoto" accept="image/*" <?php echo ($docType === 'drivingLicense') ? 'required' : ''; ?>>
                                <?php if (!empty($documents_data['driving_license_front'])): ?>
                                    <small>Current file: <?php echo htmlspecialchars($documents_data['driving_license_front']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="formField">
                                <label for="drivingLicenseBackPhoto">Driving license back photo <span class="required">*</span></label>
                                <input id="drivingBackInput" type="file" name="drivingLicenseBackPhoto" accept="image/*" <?php echo ($docType === 'drivingLicense') ? 'required' : ''; ?>>
                                <?php if (!empty($documents_data['driving_license_back'])): ?>
                                    <small>Current file: <?php echo htmlspecialchars($documents_data['driving_license_back']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div id="voterIdCardContainer" style="<?php echo ($docType === 'voterCard' ? 'display:block;' : 'display:none;'); ?>">
                        <div class="formField">
                            <label for="voterCardNo">Voter card no <span class="required">*</span></label>
                            <input type="text" name="voterCardNo" value="<?php echo htmlspecialchars($documents_data['voter_card_no'] ?? ''); ?>" <?php echo ($docType === 'voterCard') ? 'required' : ''; ?>>
                        </div>
                        <div class="formFlex" style="justify-content: center;">


                            <!-- Img preview -->
                            <div class="docPreview">
                                <div style="display: flex;align-items: center; justify-content: center;">
                                    <img id="voterFrontImg" class="docPreviewImg" src="./documentPlaceholder.jpg" alt="">
                                </div>

                            </div>
                            <!-- Img preview -->
                            <!-- Img preview 2 -->
                            <div class="docPreview">
                                <div style="display: flex;align-items: center; justify-content: center;">
                                    <img id="voterBackImg" class="docPreviewImg" src="./documentPlaceholder.jpg" alt="">
                                </div>

                            </div>
                        </div>
                        <div class="formFlex">
                            <div class="formField">
                                <label for="voterCardFrontPhoto">Voter card front photo <span class="required">*</span></label>
                                <input id="voterFrontInput" type="file" name="voterCardFrontPhoto" accept="image/*" <?php echo ($docType === 'voterCard') ? 'required' : ''; ?>>
                                <?php if (!empty($documents_data['voter_card_front'])): ?>
                                    <small>Current file: <?php echo htmlspecialchars($documents_data['voter_card_front']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="formField">
                                <label for="voterCardBackPhoto">Voter card back photo <span class="required">*</span></label>
                                <input id="voterBackInput" type="file" name="voterCardBackPhoto" accept="image/*" <?php echo ($docType === 'voterCard') ? 'required' : ''; ?>>
                                <?php if (!empty($documents_data['voter_card_back'])): ?>
                                    <small>Current file: <?php echo htmlspecialchars($documents_data['voter_card_back']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SSN -->
                    <div id="ssnContainer">
                        <div class="formField">
                            <label for="ssnNo">SSN no <span class="required">*</span></label>
                            <input type="text" name="ssnNo" value="<?php echo htmlspecialchars($documents_data['ssn_no'] ?? ''); ?>" required maxlength="9">
                        </div>
                        <div class="formField">
                            <label for="ssnPhoto">Photo of SSN card (front side) <span class="required">*</span></label>
                            <!-- Img preview -->
                            <div class="docPreview" style="width:50%; margin:0 auto;">
                                <div style="display: flex;align-items: center; justify-content: center;">
                                    <img id="ssnImgImg" class="docPreviewImg" src="./documentPlaceholder.jpg" alt="">
                                </div>

                            </div>
                            <!-- Img preview -->
                            <input id="ssnImgInput" type="file" name="ssnPhoto" accept="image/*" required>
                            <?php if (!empty($documents_data['ssn_photo'])): ?>
                                <small>Current file: <?php echo htmlspecialchars($documents_data['ssn_photo']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ITIN -->
                    <div id="itinContainer">
                        <div class="formField">
                            <label for="itinNo">ITIN no <span class="optional">(Optional)</span></label>
                            <input type="text" name="itinNo" value="<?php echo htmlspecialchars($documents_data['itin_no'] ?? ''); ?>" maxlength="9">
                        </div>
                        <div class="formField">
                            <label for="itinPhoto">Photo of ITIN letter <span class="optional">(Optional)</span></label>
                            <input type="file" name="itinPhoto" accept="image/*">
                            <?php if (!empty($documents_data['itin_photo'])): ?>
                                <small>Current file: <?php echo htmlspecialchars($documents_data['itin_photo']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="formField">
                        <button type="submit" name="essential" value="essential" class="saveChangesBtn">Update</button>
                    </div>
                </form>
            </div>
            <!-- Emergency Contact Form -->
            <div class="detailsForm" id="emergency">
                <div class="headingDiv">
                    <h3>Emergency contact</h3>
                    <p>Status: <span id="infoStatus" style="color:<?php echo isset($emergency_data['status']) ? ($emergency_data['status'] == 'approved' ? 'green' : 'orange') : 'gray'; ?>"><?php echo isset($emergency_data['status']) ? ucfirst($emergency_data['status']) : 'Not Submitted'; ?></span></p>
                </div>

                <?php if (!empty($emergency_message)): ?>
                    <div class="message" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; <?php echo strpos($emergency_message, 'successfully') !== false ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                        <?php echo htmlspecialchars($emergency_message); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" class="contactForm">
                    <!-- Contact 1 -->
                    <div class="contactContainer">
                        <h4>Contact 1 <span class="required">*</span></h4>
                        <div class="formField">
                            <label for="emergencyName1">Full name <span class="required">*</span></label>
                            <input type="text" name="emergencyName1" value="<?php echo htmlspecialchars($emergency_data['contact_1_name'] ?? ''); ?>" required>
                        </div>
                        <div class="formField">
                            <label for="emergencyRelation1">Relationship <span class="required">*</span></label>
                            <input type="text" name="emergencyRelation1" value="<?php echo htmlspecialchars($emergency_data['contact_1_relation'] ?? ''); ?>" required>
                        </div>
                        <div class="formField">
                            <label for="emergencyPhone1">Contact number <span class="required">*</span></label>
                            <input type="text" name="emergencyPhone1" value="<?php echo htmlspecialchars($emergency_data['contact_1_phone'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="contactContainer">
                        <!-- Contact 2 -->
                        <h4>Contact 2 <span class="optional">(Optional)</span></h4>
                        <div class="formField">
                            <label for="emergencyName2">Full name </label>
                            <input type="text" name="emergencyName2" value="<?php echo htmlspecialchars($emergency_data['contact_2_name'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="emergencyRelation2">Relationship</label>
                            <input type="text" name="emergencyRelation2" value="<?php echo htmlspecialchars($emergency_data['contact_2_relation'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="emergencyPhone2">Contact number</label>
                            <input type="text" name="emergencyPhone2" value="<?php echo htmlspecialchars($emergency_data['contact_2_phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="contactContainer">


                        <!-- Contact 3 -->
                        <h4>Contact 3 <span class="optional">(Optional)</span></h4>
                        <div class="formField">
                            <label for="emergencyName3">Full name</label>
                            <input type="text" name="emergencyName3" value="<?php echo htmlspecialchars($emergency_data['contact_3_name'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="emergencyRelation3">Relationship</label>
                            <input type="text" name="emergencyRelation3" value="<?php echo htmlspecialchars($emergency_data['contact_3_relation'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <label for="emergencyPhone3">Contact number</label>
                            <input type="text" name="emergencyPhone3" value="<?php echo htmlspecialchars($emergency_data['contact_3_phone'] ?? ''); ?>">
                        </div>
                        <div class="formField">
                            <button type="submit" name="emergency_contacts" class="saveChangesBtn">Update</button>
                        </div>
                    </div>
                </form>
            </div>

        </main>
    </section>
    <script>
        const imgPreview = document.getElementById("imgPreview");
        const imgPreviewInput = document.getElementById("imgPreviewInput");
        imgPreviewInput.addEventListener("change", (e) => {
            const url = URL.createObjectURL(e.target.files[0]);
            imgPreview.src = url;
        })
        // Container
        const documentType = document.getElementById("documentType");
        const passportContainer = document.getElementById("passportContainer");
        const drivingLicenseContainer = document.getElementById("drivingLicenseContainer");
        const voterIdCardContainer = document.getElementById("voterIdCardContainer");

        function updateDocumentSectionVisibility(selected) {
            [passportContainer, drivingLicenseContainer, voterIdCardContainer].forEach(container => {
                container.style.display = "none";
                container.querySelectorAll("input").forEach(input => input.required = false);
            });

            if (selected === "passport") {
                passportContainer.style.display = "block";
                passportContainer.querySelectorAll("input").forEach(input => input.required = true);
            } else if (selected === "drivingLicense") {
                drivingLicenseContainer.style.display = "block";
                drivingLicenseContainer.querySelectorAll("input").forEach(input => input.required = true);
            } else if (selected === "voterCard") {
                voterIdCardContainer.style.display = "block";
                voterIdCardContainer.querySelectorAll("input").forEach(input => input.required = true);
            }
        }

        // Init on load based on current selection (defaults to passport server-side)
        updateDocumentSectionVisibility(documentType.value || "passport");

        documentType.addEventListener("change", (e) => {
            updateDocumentSectionVisibility(e.target.value);
        });
        // permanent and present address
        const permanentAddress = document.getElementById("permanentAddress");
        const sameAsPresent = document.getElementById("sameAsPresent");
        // const presentAddress = document.getElementById("presentAddress");
        const permanentAddressStreetOne = document.getElementById("permanentAddressStreetOne");
        const permanentAddressStreetTwo = document.getElementById("permanentAddressStreetTwo");
        const permanentCity = document.getElementById("permanentCity");
        const permanentState = document.getElementById("permanentState");
        const permanentZipcode = document.getElementById("permanentZipcode");
        const presentAddressStreetOne = document.getElementById("presentAddressStreetOne");
        const presentAddressStreetTwo = document.getElementById("presentAddressStreetTwo");
        const city = document.getElementById("city");
        const state = document.getElementById("state");
        const zipcode = document.getElementById("zipcode");

        function handleCheckbox(checkbox) {
            if (checkbox.checked) {
                permanentAddressStreetOne.value = presentAddressStreetOne.value;
                permanentAddressStreetTwo.value = presentAddressStreetTwo.value;
                permanentCity.value = city.value;
                permanentState.value = state.value;
                permanentZipcode.value = zipcode.value;
            }
        };

        const contactInput = document.getElementById("contactNumber");
        const numberType = document.getElementById("numberType");

        numberType.addEventListener("change", (e) => {
            const value = e.target.value;
            if (value === "bangladesh") {
                contactInput.value = "+880";
            } else {
                contactInput.value = "+1"
            }

        });
        // Passport preview
        const passportPreviewImg = document.getElementById("passportPreviewImg");
        const passportPreview = document.getElementById("passportPreview");
        const passportInput = document.getElementById("passportInput");
        const passportWarning = document.getElementById("passportWarning");
        passportInput.addEventListener("change", (e) => {
            let imgURL = URL.createObjectURL(e.target.files[0]);
            passportPreviewImg.src = imgURL;
            // Do not hide warning here; it will be hidden after successful update when status becomes Review.
        })

        // ssn image preview
        document.getElementById("ssnImgInput").addEventListener("change", (e) => {
            let file = e.target.files[0];
            if (file) {
                let imgUrl = URL.createObjectURL(file);
                document.getElementById("ssnImgImg").src = imgUrl;
            }
        });
        // driving image preview
        document.getElementById("drivingFrontInput").addEventListener("change", (e) => {
            let file = e.target.files[0];
            if (file) {
                let imgUrl = URL.createObjectURL(file);
                document.getElementById("drivingFrontImg").src = imgUrl;
            }
        });
        document.getElementById("drivingBackInput").addEventListener("change", (e) => {
            let file = e.target.files[0];
            if (file) {
                let imgUrl = URL.createObjectURL(file);
                document.getElementById("drivingBackImg").src = imgUrl;
            }
        });
        // voter image preview
        document.getElementById("voterFrontInput").addEventListener("change", (e) => {
            let file = e.target.files[0];
            if (file) {
                let imgUrl = URL.createObjectURL(file);
                document.getElementById("voterFrontImg").src = imgUrl;
            }
        });
        document.getElementById("voterBackInput").addEventListener("change", (e) => {
            let file = e.target.files[0];
            if (file) {
                let imgUrl = URL.createObjectURL(file);
                document.getElementById("voterBackImg").src = imgUrl;
            }
        });
    </script>
</body>

</html>