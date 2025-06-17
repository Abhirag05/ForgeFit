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
$reps = $_POST['reps'];
$weight = $_POST['weight'];

$sql = "INSERT INTO logged_workouts (user_id, workout_id, date, sets, reps, weight)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisiid", $user_id, $workout_id, $date, $sets, $reps, $weight);

if ($stmt->execute()) {
    header("Location: user_workout.php");
} else {
    echo "Failed to save workout.";
}
?>