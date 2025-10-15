<?php
session_start();
require_once('../php/db_connect.php');
require_once("../php/invoices_details.php");
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
<!-- Pending task page -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./invoices.css">
  <link rel="stylesheet" href="../style.css">
  <!-- FontAwesome cdn -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Invoices</title>
</head>

<body>
  <!-- mobile protection -->
  <?php include_once("../components/mobile.php"); ?>
  <section id="container">
    <!-- Sidebar -->
    <?php include_once("../components/sidebar.php") ?>
    <main class="main">
      <!-- Top navbar -->
      <div id="invoicesTop">
        <ul id="navLinks">
          <li class="navLink" data-id="request">
            <i class="fa-solid fa-paper-plane"></i> Request invoice
          </li>
          <li class="navLink" data-id="unpaid">
            <i class="fa-solid fa-wallet"></i> Unpaid invoices
          </li>
          <li class="navLink" data-id="pending">
            <i class="fa-solid fa-hourglass-half"></i> Pending invoices
          </li>
          <li class="navLink" data-id="paid">
            <i class="fa-solid fa-circle-check"></i> Paid invoices
          </li>
          <li class="navLink" data-id="rejected">
            <i class="fa-solid fa-circle-xmark"></i> Rejected invoices
          </li>
          <li class="navLink" data-id="customInvoice">
            <i class="fa-solid fa-file-circle-plus"></i> Custom Invoice
          </li>
        </ul>
      </div>
      <div id="mainContent">
        <!-- Request section -->
        <section class="section" id="request">
          <div class="sectionHeader">Requests invoices</div>
          <div class="loaderTable">
            <table>
              <thead>
                <th>SL</th>
                <th>Customer email</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Action</th>
              </thead>
              <tbody>
                <?php
                if ($requested_invoices && count($requested_invoices) > 0) {

                  foreach ($requested_invoices as $invoice) {
                    echo "
                    <tr>
                  <td>{$invoice["id"]}</td>
                  <td>{$invoice["customer_email"]}</td>
                  <td>{$invoice["customer_name"]}</td>
                  <td>$ {$invoice["invoice_amount"]}</td>
                  <td><a href='./index.php?create_id={$invoice["id"]}' class='payNowBtn'>Create</a></td>
                </tr>
                    ";
                  }
                } else {
                  echo "<tr><td colspan='12'>0 Request invoices</td></tr>";
                }

                ?>

              </tbody>
            </table>
          </div>
        </section>
        <!-- Unpaid section -->
        <section class="section" id="unpaid" style="display: none">
          <div class="sectionHeader">Unpaid invoices</div>
          <div class="loaderTable">
            <table>
              <thead>
                <th>SL</th>
                <th>Invoice Number</th>
                <th>Customer email</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Status</th>
              </thead>
              <tbody>
                <?php
                if ($unpaid_invoices  && count($unpaid_invoices) > 0) {
                  foreach ($unpaid_invoices  as $unpaid) {
                    echo "<tr>
                <td>{$unpaid["id"]}</td>
                <td>{$unpaid["invoice_number"]}</td>
                <td>{$unpaid["customer_email"]}</td>
                <td>{$unpaid["customer_name"]}</td>
                <td>$ {$unpaid["payable_amount"]}</td>
                <td style='color: orangered; font-weight: bold'>Unpaid</td>
              </tr>";
                  }
                } else {
                  echo "<tr><td colspan='12'>0 Unpaid invoices</td></tr>";
                }
                ?>

              </tbody>
            </table>
          </div>
        </section>
        <!-- Pending section -->
        <section class="section" id="pending" style="display: none">
          <div class="sectionHeader">Pending invoices</div>
          <div class="loaderTable">
            <table>
              <thead>
                <th>SL</th>
                <th>Invoice Number</th>
                <th>Customer email</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
              </thead>
              <tbody>
                <?php
                if ($pending_invoices  && count($pending_invoices) > 0) {
                  foreach ($pending_invoices  as $pending) {
                    echo "<tr>
                <td>{$pending["id"]}</td>
                <td>{$pending["invoice_number"]}</td>
                <td>{$pending["customer_email"]}</td>
                <td>{$pending["customer_name"]}</td>
                <td>$ {$pending["payable_amount"]}</td>
                <td style='color: orangered; font-weight: bold'>Pending</td>
                <td class='pendingActions'><a href='./index.php?approveId={$pending["id"]}&customer_email={$pending["customer_email"]}&product={$pending["desired_product"]}'>Approve</a><button onClick='handleReject({$pending["id"]})'>Reject</button></td>
              </tr>";
                  }
                } else {
                  echo "<tr><td colspan='12'>0 Pending invoices</td></tr>";
                }
                ?>

              </tbody>
            </table>
          </div>
        </section>
        <!-- Paid section -->
        <section class="section" id="paid" style="display: none">
          <div class="sectionHeader">Paid invoices</div>
          <div class="loaderTable">
            <table>
              <thead>
                <th>SL</th>
                <th>Invoice Number</th>
                <th>Purpose/Product details</th>
                <th>Customer Name</th>
                <th>Amount</th>
                <th>Status</th>
              </thead>
              <tbody>
                <?php
                if ($paid_invoices  && count($paid_invoices) > 0) {
                  foreach ($paid_invoices  as $paid) {
                    echo "<tr>
                <td>{$paid["id"]}</td>
                <td>{$paid["invoice_number"]}</td>
                <td>{$paid["invoice_purpose"]}</td>
                <td>$ {$paid["customer_name"]}</td>
                <td>$ {$paid["payable_amount"]}</td>
                <td style='color: green; font-weight: bold; text-transform: Capitalize;'>{$paid["status"]}</td>
              </tr>";
                  }
                } else {
                  echo "<tr><td colspan='12'>0 Paid invoices</td></tr>";
                }
                ?>

              </tbody>
            </table>
          </div>
        </section>
        <!-- Rejected section -->
        <section class="section" id="rejected" style="display: none">
          <div class="sectionHeader">Rejected invoices</div>
          <div class="loaderTable">
            <table>
              <thead>
                <th>SL</th>
                <th>Invoice Number</th>
                <th>Purpose/Product details</th>
                <th>Customer Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Remark</th>
              </thead>
              <tbody>
                <?php
                if ($rejected_invoices  && count($rejected_invoices) > 0) {
                  foreach ($rejected_invoices  as $rejected) {
                    echo "<tr>
                <td>{$rejected["id"]}</td>
                <td>{$rejected["invoice_number"]}</td>
                <td>{$rejected["invoice_purpose"]}</td>
                <td>$ {$rejected["customer_name"]}</td>
                <td>$ {$rejected["payable_amount"]}</td>
                <td style='color: red; font-weight: bold; text-transform: Capitalize;'>{$rejected["status"]}</td>
                <td>{$rejected["remark"]}</td>
              </tr>";
                  }
                } else {
                  echo "<tr><td colspan='12'>0 Rejected invoices</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </section>
        <!-- Custom invoice section -->
        <section class="section" id="customInvoice" style="display: none;">
          <form class="defaultForm" action="" method="post">
            <h2>Create custom Invoice</h2>
            <div class="formElement">
              <label for="custom_customer_email">Customer Email</label>
              <input type="email" name="custom_customer_email" required />
            </div>
            <div class="formElement">
              <label for="custom_customer_name">Customer Name</label>
              <input type="text" name="custom_customer_name" required />
            </div>

            <div class="formElement">
              <label for="custom_invoice_number">Invoice Number</label>
              <input type="text" name="custom_invoice_number" required />
            </div>
            <div class="formElement">
              <label for="custom_invoice_link">Invoice Link</label>
              <input type="text" name="custom_invoice_link" required />
            </div>
            <div class="formElement">
              <label for="custom_invoice_purpose">Invoice Purpose</label>
              <input type="text" name="custom_invoice_purpose" required />
            </div>
            <div class="formElement">
              <label for="desired_product">Select Product</label>
              <select name="custom_desired_product" id="desiredProduct" required>
                <option value="" style="display: none">Select</option>
                <option value="custom">Custom Amount</option>
                <option value="product_1">
                  Single Product E-commerce Landing Page (Core coding) -> Price
                  - $150
                </option>
                <option value="product_2">
                  Multi Product E-commerce website (Frontend with Dashboard) ->
                  Price - $320
                </option>
                <option value="product_3">
                  Complete E-commerce Website with Admin Panel -> Price - $1080
                </option>
                <option value="product_4">
                  Complete E-commerce Website without support -> Price - $750
                </option>
              </select>
            </div>
            <div class="formElement">
              <label for="custom_invoice_amount">Invoice Amount</label>
              <input
                type="number"
                step="any"
                name="custom_invoice_amount"
                id="custom_invoice_amount"
                min="1"
                readonly />
            </div>
            <div class="formElement">
              <p>Tax + System cost - 9% (<span id="custom_costPlaceholder">amount will here</span>)</p>
            </div>
            <input type="hidden" name="custom_cost" id="custom_cost">
            <div class="formElement">
              <label for="custom_payable_amount">Payable Amount</label>
              <input type="number" step="any" id="custom_payable_amount" name="custom_payable_amount" readonly />
            </div>
            <div class="formElement">
              <label for="custom_due_date">Due Amount</label>
              <input type="date" name="custom_due_date" />
            </div>


            <div class="formElement">
              <button type="submit">Create</button>
            </div>
          </form>

        </section>
        <!-- Insert invoice popup -->
        <div id="insertInvoice">
          <div class="insertInvoiceContent">
            <form class="defaultForm" action="" method="post">
              <a href="./index.php" class="insertInvoiceClose"><i class="fa fa-solid fa-xmark"></i></a>
              <h2>Create Invoice</h2>
              <div class="formElement">
                <label for="invoice_number">Invoice Number</label>
                <input type="text" name="invoice_number" required />
              </div>
              <div class="formElement">
                <label for="invoice_link">Invoice Link</label>
                <input type="text" name="invoice_link" required />
              </div>
              <div class="formElement">
                <label for="invoice_purpose">Invoice Purpose</label>
                <input type="text" name="invoice_purpose" required />
              </div>
              <div class="formElement">
                <label for="invoice_amount">Invoice Amount</label>
                <input type="number" step="any" id="invoice_amount" name="invoice_amount" readonly value="<?php
                                                                                                          if ($target_invoice["invoice_amount"] ?? NULL) {
                                                                                                            echo $target_invoice["invoice_amount"];
                                                                                                          } else {
                                                                                                            echo "";
                                                                                                          }
                                                                                                          ?>" />
              </div>
              <div class="formElement">
                <p>Tax + System cost - 9% (<span id="costPlaceholder">amount will here</span>)</p>
              </div>
              <input type="hidden" name="cost" id="cost">
              <input type="hidden" name="invoice_id" value="<?php
                                                            if ($target_invoice["id"] ?? NULL) {
                                                              echo $target_invoice["id"];
                                                            } else {
                                                              echo "";
                                                            }
                                                            ?>">
              <div class="formElement">
                <label for="payable_amount">Payable Amount</label>
                <input type="number" step="any" id="payable_amount" name="payable_amount" readonly />
              </div>
              <div class="formElement">
                <label for="due_date">Due Amount</label>
                <input type="date" name="due_date" />
              </div>
              <div class="formElement">
                <label for="customer_email">Email</label>
                <input type="email" name="customer_email" readonly value="<?php
                                                                          if ($target_invoice["customer_email"] ?? NULL) {
                                                                            echo $target_invoice["customer_email"];
                                                                          } else {
                                                                            echo "";
                                                                          }
                                                                          ?>" />
              </div>


              <div class="formElement">
                <button type="submit">Create</button>
              </div>
            </form>
          </div>
        </div>
        <!-- Reject popup -->
        <div id="rejectPopup">
          <div class="rejectPopupContent">
            <form class="defaultForm" action="" method="post">
              <a href="./index.php" class="insertInvoiceClose"><i class="fa fa-solid fa-xmark"></i></a>
              <h2>Reject Invoice</h2>
              <input id="reject_id" type="hidden" name="reject_id">
              <div class="formElement">
                <label for="remark">Remark</label>
                <input type="text" name="remark" required />
              </div>
              <div class="formElement">
                <button type="submit" style="background-color:red">Reject</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </section>
  <!-- Content hide and show -->
  <script>
    document.querySelectorAll(".navLink").forEach((nav) => {
      nav.addEventListener("click", () => {
        document.querySelectorAll(".navLink").forEach((nav) => {
          document.getElementById(nav.dataset.id).style.display = "none";
        });
        document.getElementById(nav.dataset.id).style.display = "flex";
      });
    });
  </script>

  <!-- insert invoice popup -->
  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const createId = urlParams.get("create_id");
    if (createId) {
      document.getElementById("insertInvoice").style.display = "flex";
    }
  </script>
  <!-- reject popup -->
  <script>
    const handleReject = id => {
      document.getElementById("reject_id").value = id;
      document.getElementById("rejectPopup").style.display = "flex";
    }
  </script>
  <!-- Insert invoice price logic -->
  <script>
    const invoice_amount = document.getElementById("invoice_amount");
    const cost = document.getElementById("cost");
    const payable_amount = document.getElementById("payable_amount");
    const costPlaceholder = document.getElementById("costPlaceholder");
    const total_cost = (invoice_amount.value * 9) / 100;
    costPlaceholder.innerHTML = total_cost;
    cost.value = total_cost;
    payable_amount.value = invoice_amount.value - total_cost;
  </script>
  <!-- Select product and show price -->
  <script>
    const desiredProduct = document.getElementById("desiredProduct");
    const invoiceAmount = document.getElementById("custom_invoice_amount");
    desiredProduct.addEventListener("change", (e) => {
      console.log("Changed");
      const value = e.target.value;
      if (value === "custom") {
        invoiceAmount.removeAttribute("readonly");
        invoiceAmount.required = true;
      }
      if (value === "product_1") {
        invoiceAmount.value = "150";
        const custom_total_cost = (150 * 9) / 100;
        document.getElementById("custom_cost").value = custom_total_cost;
        document.getElementById("custom_costPlaceholder").innerHTML = custom_total_cost;
        document.getElementById("custom_payable_amount").value = 150 - custom_total_cost;

      } else if (value === "product_2") {
        invoiceAmount.value = "320";
        const custom_total_cost = (320 * 9) / 100;
        document.getElementById("custom_cost").value = custom_total_cost;
        document.getElementById("custom_costPlaceholder").innerHTML = custom_total_cost;
        document.getElementById("custom_payable_amount").value = 320 - custom_total_cost;
      } else if (value === "product_3") {
        invoiceAmount.value = "1018";
        const custom_total_cost = (1018 * 9) / 100;
        document.getElementById("custom_cost").value = custom_total_cost;
        document.getElementById("custom_costPlaceholder").innerHTML = custom_total_cost;
        document.getElementById("custom_payable_amount").value = 1018 - custom_total_cost;
      } else if (value === "product_4") {
        invoiceAmount.value = "750";
        const custom_total_cost = (750 * 9) / 100;
        document.getElementById("custom_cost").value = custom_total_cost;
        document.getElementById("custom_costPlaceholder").innerHTML = custom_total_cost;
        document.getElementById("custom_payable_amount").value = 750 - custom_total_cost;
      }
    });
  </script>
  <!-- custom invoice price logic -->
  <script>
    document.getElementById("custom_invoice_amount").addEventListener("change", (e) => {
      const t_cost = (e.target.value * 9) / 100;
      document.getElementById("custom_cost").value = t_cost;
      const final_pay = e.target.value - t_cost;
      document.getElementById("custom_payable_amount").value = final_pay;
      document.getElementById("custom_costPlaceholder").innerHTML = t_cost;
    })
  </script>
</body>

</html>