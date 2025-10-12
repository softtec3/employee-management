<?php
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
    $status = "paid";
    $stmt = $conn->prepare("UPDATE invoices SET status=? WHERE id=?");
    if (!$stmt) {
        die("Preparing problem" . $conn->error);
    }
    $stmt->bind_param("si", $status, $invoice_id,);

    if (!$stmt->execute()) {
        die("SQL execution problem" . $stmt->error);
    }
    header("Location: ./");
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
