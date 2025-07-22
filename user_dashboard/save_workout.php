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
$reps=$_POST['reps'];
$weight = $_POST['weight']; // weight lifted, not body weight
$time_per_rep=$_POST['time_per_rep'];
// 1. Get user's body weight from fitness_profiles
$weight_query = "SELECT weight_kg FROM user_fitness_profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $weight_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $body_weight);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$body_weight) {
    die("Body weight not found. Please update your fitness profile.");
}

// 2. Get MET value from workouts table
$query = "SELECT MET FROM all_workouts WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $workout_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $met_value);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$met_value) {
    die("Workout not found.");
}

// 3. Calculate calories burned
$duration = ($sets * $reps * $time_per_rep) / 60;
$calories_burned = (($met_value * 3.5 * $body_weight) / 200) * $duration;

// 4. Insert into logged_workouts table
$sql = "INSERT INTO all_logged_workouts (user_id, workout_id, date, sets, reps, weight, calories_burned)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisiiid", $user_id, $workout_id, $date, $sets, $reps, $weight, $calories_burned);

if ($stmt->execute()) {
    header("Location: user_workout.php");
} else {
    echo "Failed to save workout.";
}
?>
