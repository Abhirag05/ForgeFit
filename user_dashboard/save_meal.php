<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$meal_id = $_POST['meal_id'] ?? '';
$quantity = floatval($_POST['quantity'] ?? '');
$date = $_POST['date'] ?? '';

// Validate input
if (!$meal_id || !$quantity || !$date) {
    die("Invalid input.");
}

// Fetch meal data
$sql = "SELECT calories, protein, carbs, fat,fibre, unit FROM meals WHERE meal_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Meal not found.");
}

$meal = $result->fetch_assoc();
$unit = strtolower($meal['unit']);

// Extract base quantity from unit (e.g., "100g", "1 slice", "1 egg")
preg_match('/^(\d+(?:\.\d+)?)\s?(\w+)?/', $unit, $matches);
$base_value = isset($matches[1]) ? floatval($matches[1]) : 1;

// Actual multiplier = user input / base_value
$multiplier = $quantity / $base_value;

// Final calculated values
$total_calories = $meal['calories'] * $multiplier;
$total_protein  = $meal['protein']  * $multiplier;
$total_carbs    = $meal['carbs']    * $multiplier;
$total_fat      = $meal['fat']      * $multiplier;
$total_fibre      = $meal['fibre']      * $multiplier;

// Insert into log
$insert = "INSERT INTO logged_meals 
(user_id, meal_id, quantity, total_calories, total_protein, total_carbs, total_fat,total_fibre,date)
VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";

$stmt = $conn->prepare($insert);
$stmt->bind_param("iidddddds", $user_id, $meal_id, $quantity, $total_calories, $total_protein, $total_carbs, $total_fat,$total_fibre, $date);

if ($stmt->execute()) {
    header("Location: user_meal.php?success=1");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>
