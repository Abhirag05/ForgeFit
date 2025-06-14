<?php
session_start();

// Access control: Only logged-in users allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../signin.php");
    exit();
}
// Session timeout check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();     
    session_destroy();   
    header("Location:signin.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update time on activity


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin page</title>
</head>
<body>
    <h1>
        Admin Dashboard
    </h1>
    <a href="../logout.php">Logout</a> 


</body>
</html>