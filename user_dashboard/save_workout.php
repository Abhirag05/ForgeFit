<?php
session_start();
include '../db.php';
include_once 'xp-system.php';


if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert_message'] = "Unauthorized access.";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: signin.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert_message'] = "Invalid request method.";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_workout_history.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$workout_id = $_POST['workout_id'];
$date = date('Y-m-d H:i:s', strtotime($_POST['date']));
$sets = $_POST['sets'];
$reps = $_POST['reps']; // Array of reps
$weights = $_POST['weight']; // Array of weights
$time_per_rep = $_POST['time_per_rep'];

// 1. Get user's body weight
$weight_query = "SELECT weight_kg FROM user_fitness_profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $weight_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $body_weight);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$body_weight) {
    $_SESSION['alert_message'] = "Body weight not found. Please update your fitness profile.";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_workout.php");
    exit();
}

// 2. Get MET value
$query = "SELECT MET FROM all_workouts WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $workout_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $met_value);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$met_value) {
    $_SESSION['alert_message'] = "Workout not found.";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_workout.php");
    exit();
}

// 3. Calculate total reps & average weight
$total_reps = array_sum($reps);

// 4. Calculate calories burned
$duration = ($sets * $total_reps * $time_per_rep) / 60;
$calories_burned = (($met_value * 3.5 * $body_weight) / 200) * $duration;

// 5. Store set details as JSON
$set_details = [];
for ($i = 0; $i < $sets; $i++) {
    $set_details[] = [
        'set_number' => $i + 1,
        'reps' => $reps[$i],
        'weight' => $weights[$i]
    ];
}
$set_details_json = json_encode($set_details);

// Check if we're updating an existing workout
if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    
    // Verify the workout belongs to the user
    $verify_query = "SELECT id FROM all_logged_workouts WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($stmt, "ii", $edit_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) === 0) {
        $_SESSION['alert_message'] = "Workout not found or you don't have permission to edit it.";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_workout_history.php");
        exit();
    }
    mysqli_stmt_close($stmt);
    
    // Update the existing workout
    $sql = "UPDATE all_logged_workouts 
            SET workout_id = ?, 
                date = ?, 
                sets = ?, 
                calories_burned = ?, 
                set_details = ? 
            WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isidsii", $workout_id, $date, $sets, $calories_burned, $set_details_json, $edit_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['alert_message'] = "Workout updated successfully!";
        $_SESSION['alert_type'] = "success";
        $_SESSION['alert_title'] = "Success!";
        header("Location: user_workout_history.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Error updating workout: " . $conn->error;
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_workout.php?edit_id=" . $edit_id);
        exit();
    }
} else {
    // Insert new workout
    $sql = "INSERT INTO all_logged_workouts (user_id, workout_id, date, sets, calories_burned, set_details)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisids", $user_id, $workout_id, $date, $sets, $calories_burned, $set_details_json);

    if ($stmt->execute()) {
        // EXP logic: Only award for first 10 logs per day
        $today = date('Y-m-d');
        $exp_limit = 10;
        $exp_per_log = 10;
        // Count today's workouts
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM all_logged_workouts WHERE user_id = ? AND DATE(date) = ?");
        $count_stmt->bind_param("is", $user_id, $today);
        $count_stmt->execute();
        $count_stmt->bind_result($workout_count_today);
        $count_stmt->fetch();
        $count_stmt->close();
        if ($workout_count_today <= $exp_limit) {
            addExperience($user_id, $exp_per_log, $conn);
            $_SESSION['alert_message'] = "Workout logged successfully! (+10 EXP)";
            $_SESSION['alert_type'] = "success";
            $_SESSION['alert_title'] = "Success!";
        } else {
            $_SESSION['alert_message'] = "Workout logged! You're overworking! No more EXP for today.";
            $_SESSION['alert_type'] = "warning";
            $_SESSION['alert_title'] = "Overwork!";
        }
        header("Location: user_workout_history.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Failed to save workout: " . $conn->error;
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_workout.php");
        exit();
    }
}
?>