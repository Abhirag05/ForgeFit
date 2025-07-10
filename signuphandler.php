<?php
session_start();
include 'db.php'; // DB connection
header('Content-Type: application/json');

// Trimmed inputs
$fullname = isset($_POST['n']) ? trim($_POST['n']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$checkEmailOnly = isset($_POST['check_email']) && $_POST['check_email'] == 'true';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    exit();
}

// ✅ Handle email availability check only
if ($checkEmailOnly) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode(["exists" => $result->num_rows > 0]);
    $stmt->close();
    $conn->close();
    exit();
}

// ✅ Handle full signup
if (empty($fullname) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
} elseif (strlen($password) < 6) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
    exit();
} else {
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $fullname, $email, $password_hashed);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Signup successful. Redirecting..."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
    }
    $stmt->close();
}
$conn->close();
