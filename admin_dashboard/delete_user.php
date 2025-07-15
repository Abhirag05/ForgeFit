<?php
session_start();
include '../db.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    $sql = "DELETE FROM users WHERE id = $user_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "User deleted successfully.";
        $_SESSION['msg_type'] = "success";
        header("Location: admin_layout.php?page=user_details");
        exit();
    } else {
        $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "error";
        header("Location: admin_layout.php?page=user_details");
        exit();
    }
} else {
    $_SESSION['message'] = "User ID not provided.";
    $_SESSION['msg_type'] = "error";
    header("Location: admin_layout.php?page=user_details");
    exit();
}
?>
