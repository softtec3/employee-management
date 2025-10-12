<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to profile page
header("Location: ../profile/index.php");
exit();
?>