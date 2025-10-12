<?php
session_start();
require_once('php/db_connect.php');

$error_message = "";

if ($_POST) {
    $employee_id = trim($_POST['employee_id']);
    $password = trim($_POST['password']);
    
    if (!empty($employee_id) && !empty($password)) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT employee_id, password, name FROM employee_log WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Check if password is plain text first, then try hashed
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['employee_id'] = $user['employee_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard or main page
                header("Location: ../index/index.php");
                exit();
            } else {
                $error_message = "Invalid employee ID or password!";
            }
        } else {
            $error_message = "Invalid employee ID or password!";
        }
        
        $stmt->close();
    } else {
        $error_message = "Please fill in all fields!";
    }
}
?>
<!-- Login page -->

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
     <?php include_once("./components/mobile.php");?>
    <section id="loginContainer">
        <!-- login form -->
        <form action="" method="post" class="loginForm">
            <h1>Login</h1>
            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px; padding: 10px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 4px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <div class="formContainer">
                <div>
                    <label for="employee_id">Employee id</label>
                    <input type="text" name="employee_id" placeholder="Enter your employee id" required value="<?php echo isset($_POST['employee_id']) ? htmlspecialchars($_POST['employee_id']) : ''; ?>">
                </div>
                <div>
                    <label for="password">Password</label>
                    <div class="passField">
                        <input type="password" name="password" placeholder="Enter your password" id="passField" required>
                        <span id="eyeIcon"><i class="fa-solid fa-eye"></i></span>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn">Login</button>
                </div>
            </div>
        </form>
    </section>
    <script>
        const passField =document.getElementById("passField");
        const eyeIcon = document.getElementById("eyeIcon");
        eyeIcon.addEventListener("click",()=>{
              if (passField.type === "password") {
                passField.type = "text";
              } else {
                 passField.type = "password";
            }
        })
    </script>
</body>
</html>