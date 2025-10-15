<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include_once("config.php");

$all_invoices = [];
// Get all invoices details
$sql = $conn->query("SELECT * FROM invoices");

if ($sql) {
    // Fetch all rows as an associative array
    $all_invoices = $sql->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Query failed: " . $conn->error;
}

// status requested invoices
$requested_invoices_filter = array_filter($all_invoices, function ($invoice) {
    if ($invoice["status"] != "requested") {
        return false;
    }
    return true;
});
$requested_invoices = array_values($requested_invoices_filter);


//status unpaid invoices
$unpaid_filter = array_filter($all_invoices, function ($invoice) {
    if ($invoice["status"] != "unpaid") {
        return false;
    }
    return true;
});

$unpaid_invoices = array_values($unpaid_filter);


// status pending invoices
$pending_filter = array_filter($all_invoices, function ($invoice) {
    if ($invoice["status"] != "pending") {
        return false;
    }
    return true;
});

$pending_invoices = array_values($pending_filter);

// status pending invoices
$paid_filter = array_filter($all_invoices, function ($invoice) {
    if ($invoice["status"] != "paid") {
        return false;
    }
    return true;
});

$paid_invoices = array_values($paid_filter);

// status pending invoices
$rejected_filter = array_filter($all_invoices, function ($invoice) {
    if ($invoice["status"] != "rejected") {
        return false;
    }
    return true;
});

$rejected_invoices = array_values($rejected_filter);




// get create id and store details
if (isset($_GET["create_id"]) && $_GET["create_id"] != "") {
    $create_id = $_GET["create_id"];

    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id=?");
    if (!$stmt) {
        die("Preparing problem" . $conn->error);
    }
    $stmt->bind_param("i", $create_id);
    if (!$stmt->execute()) {
        die("SQL execution problem" . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $target_invoice = $row;
    }
}


// Update invoice

if (isset($_POST["invoice_purpose"]) && $_POST["invoice_purpose"] != "") {
    $invoice_id = $_POST["invoice_id"];
    $invoice_number = $_POST["invoice_number"];
    $invoice_link = $_POST["invoice_link"];
    $invoice_purpose = $_POST["invoice_purpose"];
    $invoice_amount = $_POST["invoice_amount"];
    $cost = $_POST["cost"];
    $payable_amount = $_POST["payable_amount"];
    $due_date = $_POST["due_date"];
    $status = "unpaid";

    $stmt = $conn->prepare("UPDATE invoices SET invoice_number=?, invoice_link=?, invoice_purpose=?, cost=?,payable_amount=?, due_date=?, status=? WHERE id=?");

    if (!$stmt) {
        die("Preparing problem" . $conn->error);
    }
    $stmt->bind_param(
        "sssddssi",
        $invoice_number,
        $invoice_link,
        $invoice_purpose,
        $cost,
        $payable_amount,
        $due_date,
        $status,
        $invoice_id
    );

    if (!$stmt->execute()) {
        die("SQL execution problem" . $stmt->error);
    }
    header("Location: ./");
}

// Approve invoice

if (isset($_GET["approveId"]) && $_GET["approveId"]) {
    $invoice_id = $_GET["approveId"];
    $customer_email = $_GET["customer_email"];
    $status = "paid";
    $stmt = $conn->prepare("UPDATE invoices SET status=? WHERE id=?");
    if (!$stmt) {
        die("Preparing problem" . $conn->error);
    }
    $stmt->bind_param("si", $status, $invoice_id,);

    if (!$stmt->execute()) {
        die("SQL execution problem" . $stmt->error);
    }

    $stmt->close();


    // email sent logics
    $product = $_GET["product"];
    $to = $customer_email;
    $subject = "";
    $body = "body";

    if ($product == "product_1") {
        $subject = "Thank you for purchasing Single Product E-commerce Landing page (Core codeing)";
        $body = "
            <html>
  <head>
    <meta charset='UTF-8'>
    <title>Product Purchase Confirmation</title>
  </head>
  <body style='font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;'>
    <table cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.08); border-collapse: collapse;'>
      <tr>
        <td style='padding: 20px; text-align: left;'>
          <h2 style='color: #333333;font-size: 18px;'>Product Description</h2><p style='color: #555555;margin-top:-15px'>This project is a single-product e-commerce landing page with a front-end and an admin dashboard, all built using HTML, Tailwind CSS, and JavaScript.The front-end offers a smooth shopping experience with responsive product listings and a shopping cart, while the integrated dashboard provides a streamlined interface for managing products and orders.
          </p> <br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Template Features</h3><span style='color: #555555;'>Single Product E-commerce Landing Page</span><br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Layout Features</h3><span style='color: #555555;'>Front-End</span>
          <h3 style='color: #333333; margin: 15px 0 5px 0; font-size: 16px;'>Support</h3><span style='color: #555555;'>N/A</span> <br/>
          <h4 style='color: #333333; margin: 0px; font-size: 15px;'>Best regards,</h4><span style='margin: 0px; color: #555555;'>Soft-Tech Technology LLC</span>
            <a href='https://soft-techtechnologyllc.com/' style='color: #007BFF; text-decoration: none;'>Visit Our Website</a> <a href='mailto:contact@soft-techtechnologyllc.com' style='color: #007BFF; text-decoration: none;'>
              contact@soft-techtechnologyllc.com
            </a>
        </td>
      </tr>
    </table>
  </body>
</html>
        ";
    }
    if ($product == "product_2") {
        $subject = "Thank you for purchasing Multi-Products E-commerce Site (Front-End with Dashboard)";
        $body = "
        <html>
  <head>
    <meta charset='UTF-8'>
    <title>Product Purchase Confirmation</title>
  </head>
  <body style='font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;'>
    <table cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.08); border-collapse: collapse;'>
      <tr>
        <td style='padding: 20px; text-align: left;'>
          <h2 style='color: #333333;font-size: 18px;'>Product Description</h2><p style='color: #555555;margin-top:-15px'>This project is a multi-product e-commerce site with a front-end and an admin dashboard, all built using HTML, Tailwind CSS, and JavaScript. The front-end offers a smooth shopping experience with responsive product listings and a shopping cart, while the integrated dashboard provides a streamlined interface for managing products and orders. The use of Tailwind CSS ensures a modern, mobile-first design, making the entire platform both visually appealing and highly functional across all devices.
          </p> <br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Template Features</h3><span style='color: #555555;'>Multi-Products E-commerce Site</span>
          <span style='color: #555555;'>Front-End</span>
          <span style='color: #555555;'>Admin Panel</span>
          <span style='color: #555555;'>One-page E-commerce Landing page</span><br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Layout Features</h3><span style='color: #555555;'>Responsive Design</span>
          <h3 style='color: #333333; margin: 15px 0 5px 0; font-size: 16px;'>Support</h3><span style='color: #555555;'>N/A</span> <br/>
          <h4 style='color: #333333; margin: 0px; font-size: 15px;'>Best regards,</h4><span style='margin: 0px; color: #555555;'>Soft-Tech Technology LLC</span>
            <a href='https://soft-techtechnologyllc.com/' style='color: #007BFF; text-decoration: none;'>Visit Our Website</a> <a href='mailto:contact@soft-techtechnologyllc.com' style='color: #007BFF; text-decoration: none;'>
              contact@soft-techtechnologyllc.com
            </a>
        </td>
      </tr>
    </table>
  </body>
</html>

        ";
    }
    if ($product == "product_3") {
        $subject = "Thank you for purchasing Complete E-commerce Website with Admin Panel";
        $body = "<html>
  <head>
    <meta charset='UTF-8'>
    <title>Product Purchase Confirmation</title>
  </head>
  <body style='font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;'>
    <table cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.08); border-collapse: collapse;'>
      <tr>
        <td style='padding: 20px; text-align: left;'>
          <h2 style='color: #333333;font-size: 18px;'>Product Description</h2><p style='color: #555555;margin-top:-15px'>This comprehensive e-commerce website with an integrated admin panel is your complete solution for online retail. The platform provides a seamless shopping experience for customers and gives you full control through an intuitive backend. Manage products, process orders, track inventory, and analyze sales effortlessly. Launch your online store quickly and professionally.</p> <br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Template Features</h3><span style='color: #555555;'>Attractive Design</span>
          <span style='color: #555555;'>Front-End and Back-End</span>
          <span style='color: #555555;'>Admin panel</span><br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Layout Features</h3><span style='color: #555555;'>Responsive Design</span>
          <h3 style='color: #333333; margin: 15px 0 5px 0; font-size: 16px;'>Support</h3><span style='color: #555555;'>Custome Database configuration.</span>
          <span style='color: #555555;'>Website Customization</span><br/>
          <h4 style='color: #333333; margin: 0px; font-size: 15px;'>Best regards,</h4><span style='margin: 0px; color: #555555;'>Soft-Tech Technology LLC</span>
            <a href='https://soft-techtechnologyllc.com/' style='color: #007BFF; text-decoration: none;'>Visit Our Website</a> <a href='mailto:contact@soft-techtechnologyllc.com' style='color: #007BFF; text-decoration: none;'>
              contact@soft-techtechnologyllc.com
            </a>
        </td>
      </tr>
    </table>
  </body>
</html>
";
    }
    if ($product == "product_4") {
        $subject = "Thank you for purchasing Complete E-commerce Website without support";
        $body = "
        <html>
  <head>
    <meta charset='UTF-8'>
    <title>Product Purchase Confirmation</title>
  </head>
  <body style='font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;'>
    <table cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.08); border-collapse: collapse;'>
      <tr>
        <td style='padding: 20px; text-align: left;'>
          <h2 style='color: #333333;font-size: 18px;'>Product Description</h2><span style='color: #555555;margin-top:-15px'>This comprehensive e-commerce website with an integrated admin panel is your complete solution for online retail. The platform provides a seamless shopping experience for customers and gives you full control through an intuitive backend. Manage products, process orders, track inventory, and analyze sales effortlessly. Launch your online store quickly and professionally.</span> <br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Template Features</h3><span style='color: #555555;'>E-Commerce</span>
          <span style='color: #555555;'>Front-End</span>
          <span style='color: #555555;'>Back-End</span>
          <span style='color: #555555;'>Admin Panel</span><br/>
          <h3 style='color: #333333; margin: 0px; font-size: 16px;'>Layout Features</h3><span style='color: #555555;'>Responsive Design</span>
          <h3 style='color: #333333; margin: 15px 0 5px 0; font-size: 16px;'>Support</h3><span style='color: #555555;'>N/A</span> <br/>
          <h4 style='color: #333333; margin: 0px; font-size: 15px;'>Best regards,</h4><span style='margin: 0px; color: #555555;'>Soft-Tech Technology LLC</span>
            <a href='https://soft-techtechnologyllc.com/' style='color: #007BFF; text-decoration: none;'>Visit Our Website</a> <a href='mailto:contact@soft-techtechnologyllc.com' style='color: #007BFF; text-decoration: none;'>
              contact@soft-techtechnologyllc.com
            </a>
        </td>
      </tr>
    </table>
  </body>
</html>
";
    }









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

        echo "✅ Message sent successfully! <br/>";
        echo "Successfully saved to database. <br/>";
        echo "Wait....";
        echo "<script>
            setTimeout(()=>{
                window.location.href = '../invoices/index.php';
            },1000)
        </script>";
    } catch (Exception $e) {
        echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo} <br/>";
        echo "Wait.. It will auto redirect to home page after 10 second";
        echo "<script>
            setTimeout(()=>{
                window.location.href = '../invoices/index.php';
            },9000)
        </script>";
    }



    // email sent logics

    // header("Location: ./");
}

// Reject invoice

if (isset($_POST["reject_id"]) && $_POST["reject_id"]) {
    $invoice_id = $_POST["reject_id"];
    $invoice_remark = $_POST["remark"];

    $status = "rejected";
    $stmt = $conn->prepare("UPDATE invoices SET remark=?, status=? WHERE id=?");
    if (!$stmt) {
        die("Preparing problem" . $conn->error);
    }
    $stmt->bind_param("ssi", $invoice_remark, $status, $invoice_id,);

    if (!$stmt->execute()) {
        die("SQL execution problem" . $stmt->error);
    }
    header("Location: ./");
}


// Create custom invoice

if (isset($_POST["custom_customer_email"]) && $_POST["custom_customer_email"] != "") {
    $custom_customer_email = $_POST["custom_customer_email"];
    $custom_customer_name = $_POST["custom_customer_name"];
    $custom_invoice_number = $_POST["custom_invoice_number"];
    $custom_invoice_link = $_POST["custom_invoice_link"];
    $custom_invoice_purpose = $_POST["custom_invoice_purpose"];
    $custom_desired_product = $_POST["custom_desired_product"];
    $custom_invoice_amount = $_POST["custom_invoice_amount"];
    $custom_cost = $_POST["custom_cost"];
    $custom_payable_amount = $_POST["custom_payable_amount"];
    $custom_due_date = $_POST["custom_due_date"];
    $status = "unpaid";

    $stmt = $conn->prepare("INSERT INTO invoices (customer_email, customer_name, desired_product, invoice_amount, invoice_number, invoice_link, invoice_purpose, cost, payable_amount, due_date, status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

    if (!$stmt) {
        die("Preparing error: " . $conn->error);
    }
    $stmt->bind_param("sssdsssddss", $custom_customer_email, $custom_customer_name, $custom_desired_product, $custom_invoice_amount, $custom_invoice_number, $custom_invoice_link, $custom_invoice_purpose, $custom_cost, $custom_payable_amount, $custom_due_date, $status);

    if (!$stmt->execute()) {
        die("Execution error: " . $stmt->error);
    } else {
        header("Location: ./");
    }
}
