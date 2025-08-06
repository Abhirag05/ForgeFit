<?php
session_start();
include '../db.php';
include_once 'xp-system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}



$user_id = $_SESSION['user_id'];
$meal_id = $_POST['meal_id'] ?? '';
$quantity = floatval($_POST['quantity'] ?? '');
$datetime = $_POST['datetime'] ?? '';
$meal_time = $_POST['meal_time'] ?? 'breakfast';
$is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] == '1';
$meal_log_id = $_POST['meal_log_id'] ?? '';

// Validate input
if (!$meal_id || !$quantity || !$datetime || !$meal_time) {
    $_SESSION['alert_message'] = "Please fill all required fields";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_meal.php" . ($is_edit ? "?edit=$meal_log_id" : ""));
    exit();
}

// Additional validation for edit mode
if ($is_edit && !$meal_log_id) {
    $_SESSION['alert_message'] = "Invalid meal record for update";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_meal.php");
    exit();
}

// Convert datetime to MySQL format
$datetime_mysql = date('Y-m-d H:i:s', strtotime($datetime));

// Validate datetime conversion
if (!$datetime_mysql || $datetime_mysql == '1970-01-01 00:00:00') {
    $_SESSION['alert_message'] = "Invalid date and time format";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_meal.php" . ($is_edit ? "?edit=$meal_log_id" : ""));
    exit();
}

// Fetch meal data
$sql = "SELECT calories, protein, carbs, fat, fibre, unit FROM meals WHERE meal_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['alert_message'] = "Meal not found in database";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Error!";
    header("Location: user_meal.php" . ($is_edit ? "?edit=$meal_log_id" : ""));
    exit();
}

$meal = $result->fetch_assoc();
$unit = strtolower($meal['unit']);

// Extract base quantity from unit
preg_match('/^(\d+(?:\.\d+)?)\s?(\w+)?/', $unit, $matches);
$base_value = isset($matches[1]) ? floatval($matches[1]) : 1;

// Calculate nutritional values
$multiplier = $quantity / $base_value;
$total_calories = $meal['calories'] * $multiplier;
$total_protein  = $meal['protein']  * $multiplier;
$total_carbs    = $meal['carbs']    * $multiplier;
$total_fat      = $meal['fat']      * $multiplier;
$total_fibre    = $meal['fibre']    * $multiplier;

if ($is_edit) {
    // Verify that the logged meal belongs to the current user
    $verify_sql = "SELECT user_id FROM logged_meals WHERE meal_log_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $meal_log_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $_SESSION['alert_message'] = "Meal record not found";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: meal_history.php");
        exit();
    }
    
    $verify_data = $verify_result->fetch_assoc();
    if ($verify_data['user_id'] != $user_id) {
        $_SESSION['alert_message'] = "You don't have permission to edit this meal";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: meal_history.php");
        exit();
    }
    
    // Update existing meal
    $update_sql = "UPDATE logged_meals SET 
                   meal_id = ?, 
                   quantity = ?, 
                   total_calories = ?, 
                   total_protein = ?, 
                   total_carbs = ?, 
                   total_fat = ?, 
                   total_fibre = ?, 
                   date = ?, 
                   meal_time = ? 
                   WHERE meal_log_id = ? AND user_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iidddddssii", 
        $meal_id, $quantity, 
        $total_calories, $total_protein, $total_carbs, 
        $total_fat, $total_fibre, $datetime_mysql, $meal_time,
        $meal_log_id, $user_id
    );
    
    if ($update_stmt->execute()) {
        $_SESSION['alert_message'] = "Meal updated successfully!";
        $_SESSION['alert_type'] = "success";
        $_SESSION['alert_title'] = "Success!";
        
        // Redirect back to meal history
        header("Location: meal_history.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Error updating meal: " . $conn->error;
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_meal.php?edit=$meal_log_id");
        exit();
    }
    
} else {
    // Insert new meal
    $insert_sql = "INSERT INTO logged_meals 
                   (user_id, meal_id, quantity, total_calories, total_protein, total_carbs, total_fat, total_fibre, date, meal_time) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iidddddsss", 
        $user_id, $meal_id, $quantity, 
        $total_calories, $total_protein, $total_carbs, 
        $total_fat, $total_fibre, $datetime_mysql, $meal_time
    );
    
    if ($insert_stmt->execute()) {
        // EXP logic: Only award if total calories for today (including this meal) <= maintenance calories
        $today = date('Y-m-d', strtotime($datetime_mysql));
        // Get user's maintenance calories
        $maint_sql = "SELECT maintenance_calories FROM user_fitness_profiles WHERE user_id = ? LIMIT 1";
        $maint_stmt = $conn->prepare($maint_sql);
        $maint_stmt->bind_param("i", $user_id);
        $maint_stmt->execute();
        $maint_stmt->bind_result($maintenance_calories);
        $maint_stmt->fetch();
        $maint_stmt->close();
        // Sum calories for today (including this meal)
        $cal_sql = "SELECT SUM(total_calories) FROM logged_meals WHERE user_id = ? AND DATE(date) = ?";
        $cal_stmt = $conn->prepare($cal_sql);
        $cal_stmt->bind_param("is", $user_id, $today);
        $cal_stmt->execute();
        $cal_stmt->bind_result($total_cals_today);
        $cal_stmt->fetch();
        $cal_stmt->close();
        if ($maintenance_calories && $total_cals_today <= $maintenance_calories) {
            addExperience($user_id, 5, $conn);
            $_SESSION['alert_message'] = "Meal logged successfully! (+5 EXP)";
            $_SESSION['alert_type'] = "success";
            $_SESSION['alert_title'] = "Success!";
        } else {
            $_SESSION['alert_message'] = "Meal logged! You are overeating! No more EXP for today.";
            $_SESSION['alert_type'] = "warning";
            $_SESSION['alert_title'] = "Overeating!";
        }
        header("Location: user_meal.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Error logging meal: " . $conn->error;
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_meal.php");
        exit();
    }
}
?>