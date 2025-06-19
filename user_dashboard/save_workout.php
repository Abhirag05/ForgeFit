<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$workout_id = $_POST['workout_id'];
$date = $_POST['date'];
$sets = $_POST['sets'];
$duration = $_POST['duration'];
$weight = $_POST['weight'];

// Get calories_per_min from the workouts table
$query = "SELECT calories_per_minute FROM workouts WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $workout_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $calories_per_min);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);


// Calculate calories burned
$calories_burned = $duration * $calories_per_min;

$sql = "INSERT INTO logged_workouts (user_id, workout_id, date, sets,duration, weight,calories_burned)
        VALUES (?, ?, ?, ?, ?, ?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisiiid", $user_id, $workout_id, $date, $sets, $duration, $weight,$calories_burned);

if ($stmt->execute()) {
    header("Location: user_workout.php");
} else {
    echo "Failed to save workout.";
}
?>