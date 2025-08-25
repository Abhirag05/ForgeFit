<?php
session_start();
include '../../db.php';

// Get the user ID
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Mark user as premium
    $stmt = $conn->prepare("UPDATE users SET premium = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $_SESSION['premium'] = true;
    echo "✅ Payment successful! You are now Premium. <a href='chatbot.php'>Go to Chatbot</a>";
} else {
    echo "Something went wrong. User not logged in.";
}
