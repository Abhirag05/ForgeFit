<?php
session_start();
include '../db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $user_id = intval($_GET['id']);
    $current_status = intval($_GET['status']);

    $new_status = $current_status == 1 ? 0 : 1;

    $sql = "UPDATE users SET is_banned = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_status, $user_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = $new_status ? "User banned successfully." : "User unbanned successfully.";
        $_SESSION['msg_type'] = $new_status ? "error" : "success";
    } else {
        $_SESSION['message'] = "Failed to update user status.";
    }
}

header("Location: admin_layout.php?page=user_details");
exit();
?>
