<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once("./db_connect.php");
include_once("./config.php");
$email_message_status = NULL;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product = $_POST['seSelect'];
    $to = $_POST['seEmail'];
    $subject = $_POST['seSubject'];
    $body = $_POST['seBody'];

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $credential["host"];
        $mail->SMTPAuth = true;
        $mail->Username = $credential["username"];
        $mail->Password = $credential["password"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $credential["port"];

        // Sender and recipient
        $mail->setFrom('sales1@soft-techtechnologyllc.com', 'SoftTech Technology LLC');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($body);

        $mail->send();

        // Save email info to database
        $sql = "CREATE TABLE IF NOT EXISTS emails(
            id INT AUTO_INCREMENT PRIMARY KEY,
            product VARCHAR(255) NOT NULL,
            recipent VARCHAR(100) NOT NULL,
            subject TEXT NOT NULL,
            body TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if ($conn->query($sql) != TRUE) {
            echo "Error creating emails table" . $conn->error;
        }

        $stmt = $conn->prepare("INSERT INTO emails(product, recipent, subject, body) VALUES (?,?,?,?)");

        if (!$stmt) {
            die("Preparing error: " . $conn->error);
        }
        $stmt->bind_param("ssss", $product, $to, $subject, $body);

        if (!$stmt->execute()) {
            die("Execution error:" . $stmt->error);
        }
        echo "<script>
                alert('✅ Message sent successfully! ');
                window.location.href = '../index.php';
        </script>";
    } catch (Exception $e) {
        // echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo} <br/>";
        // echo "Wait.. It will auto redirect to home page after 10 second";
        echo "<script>
        alert('❌ Message could not be sent.');
        window.location.href = '../index.php';
        </script>";
    }
}
