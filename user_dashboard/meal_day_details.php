<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['date'])) {
    header("Location: meal_history.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$date = $_GET['date'];

// Handle delete request
if (isset($_POST['delete_meal']) && isset($_POST['meal_log_id'])) {
    $logged_meal_id = $_POST['meal_log_id'];
    $deleteQuery = "DELETE FROM logged_meals WHERE meal_log_id = $logged_meal_id AND user_id = $user_id";
   if (mysqli_query($conn,$deleteQuery)) {
        $_SESSION['alert_message'] = "Meals have been successfully deleted from your history.";
        $_SESSION['alert_type'] = "success";
        $_SESSION['alert_title'] = "Success!";
        header("Location: meal_day_details.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Failed to delete the meal. Please try again.";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: meal_day_details.php");
        exit();
    }
}

// Fetch day summary
$dayQuery = "SELECT 
    DATE(date) as meal_date,
    COUNT(*) as meal_count,
    SUM(total_calories) as total_calories,
    SUM(total_protein) as total_protein,
    SUM(total_carbs) as total_carbs,
    SUM(total_fat) as total_fat,
    SUM(total_fibre) as total_fibre
FROM logged_meals 
WHERE user_id = $user_id AND DATE(date) = '$date'";
$dayResult = mysqli_query($conn, $dayQuery);
$dayData = mysqli_fetch_assoc($dayResult);

// Fetch meals grouped by meal time with logged_meal_id and time
$mealsQuery = "SELECT 
    lm.meal_log_id,
    lm.meal_time,
    lm.date,
    TIME(lm.date) as logged_time,
    m.food_name,
    m.unit,
    lm.quantity,
    lm.total_calories,
    lm.total_protein,
    lm.total_carbs,
    lm.total_fat,
    lm.total_fibre
FROM logged_meals lm 
JOIN meals m ON lm.meal_id = m.meal_id 
WHERE lm.user_id = $user_id AND DATE(lm.date) = '$date'
ORDER BY lm.date ASC";
$mealsResult = mysqli_query($conn, $mealsQuery);
$mealsByTime = [];
while ($meal = mysqli_fetch_assoc($mealsResult)) {
    $mealsByTime[$meal['meal_time']][] = $meal;
}

// Define meal order for consistent display
$mealOrder = ['breakfast', 'lunch', 'snack', 'dinner'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Details | ForgeFit</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0a0a12;
            color: white;
            line-height: 1.6;
        }
    
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
        }
        
        .container {
            max-width: 1200px;
            margin:  auto;
            padding: 2rem;
            border-radius: 12px;
        }
        
        .details-card {
           
            border-radius: 16px;
            padding: 2rem;
           
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .header-left {
            flex: 1;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, #a64aff, #6a00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .meal-heading {
            font-size: 1.4rem;
            font-weight: 600;
            color: #fff;
        }
        
        .header-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.8rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #a64aff, #6a00ff);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #9438ff, #5a00e6);
        }

    /* Summary section */    
     .summary-section {
           background: rgba(166, 74, 255, 0.1);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid rgba(166, 74, 255, 0.2);
        }
        
        .summary-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .summary-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        
        .summary-card:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-2px);
        }
        
        .summary-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, #a64aff, #6a00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    /* Summary section responsiveness code */
     @media (max-width: 480px) {
        .summary-section {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .summary-title {
            font-size: 1.05rem;
            gap: 0.3rem;
        }
        .summary-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.7rem;
        }
        .summary-card {
            padding: 0.7rem;
            border-radius: 7px;
        }
        .summary-value {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 360px) {
        .summary-section {
            padding: 5px;
            border-radius: 5px;
        }
        .summary-title {
            font-size: 0.95rem;
        }
        .summary-grid {
            grid-template-columns: 1fr;
            gap: 0.4rem;
        }
        .summary-card {
            padding: 0.4rem;
            border-radius: 4px;
        }
        .summary-value {
            font-size: 0.95rem;
        }
    }
        
        .meals-section {
            margin-top: 2rem;
        }
        
        .meals-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .meal-type-section {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .meal-type-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .meal-type-badge {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: capitalize;
            margin-right: 1rem;
        }
        
        .breakfast-badge {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .lunch-badge {
            background-color: rgba(0, 150, 136, 0.2);
            color: #009688;
        }
        
        .dinner-badge {
            background-color: rgba(103, 58, 183, 0.2);
            color: #673ab7;
        }
        
        .snack-badge {
            background-color: rgba(233, 30, 99, 0.2);
            color: #e91e63;
        }
        
        .meal-type-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
        }
        
        .food-items-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .food-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        
        .food-card:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-2px);
        }
        
        .food-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .food-info {
            flex: 1;
        }
        
        .food-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.3rem;
        }
        
        .food-quantity {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .food-time {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            font-style: italic;
        }
        
        .food-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .food-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .food-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn-edit {
            background: rgba(52, 152, 219, 0.2);
            border-color: rgba(52, 152, 219, 0.3);
            color: #3498db;
        }
        
        .btn-delete {
            background: rgba(231, 76, 60, 0.2);
            border-color: rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }
        
        .btn-delete:hover {
            background: rgba(231, 76, 60, 0.3);
        }
        
        .food-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .food-stat {
            text-align: center;
        }
        
        .food-stat-value {
            font-weight: 600;
            color: #a64aff;
            font-size: 1rem;
        }
        
        .food-stat-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.2rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
/* Meal Section responsiveness later need to change more accurately*/
     @media (max-width: 480px) {
        .meals-section {
            margin-top: 1rem;
        }
        .meals-grid {
            gap: 0.7rem;
        }
        .meal-type-section {
            padding: 0.8rem;
            border-radius: 8px;
        }
        .meal-type-header {
            padding-bottom: 0.5rem;
            margin-bottom: 0.7rem;
        }
        .meal-type-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.7rem;
            border-radius: 8px;
            margin-right: 0.5rem;
        }
    }

    @media (max-width: 360px) {
        .meals-section {
            margin-top: 0.5rem;
        }
        .meals-grid {
            gap: 0.3rem;
        }
        .meal-type-section {
            padding: 0.4rem;
            border-radius: 5px;
        }
        .meal-type-header {
            padding-bottom: 0.2rem;
            margin-bottom: 0.3rem;
        }
        .meal-type-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            border-radius: 5px;
            margin-right: 0.2rem;
        }
    }
       

        
        /* Modal styles for delete confirmation */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            margin: 15% auto;
            padding: 2rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        
        .modal-title {
            color: #e74c3c;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        
        .modal-text {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-confirm {
            background: #e74c3c;
            color: white;
        }
        
        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .header-right {
                width: 100%;
                justify-content: flex-start;
            }
            
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .food-stats {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .food-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .food-actions {
                align-self: flex-start;
            }
        }
         /* Modern Alert System */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            width: 100%;
        }

        .modern-alert {
            background: rgba(30, 30, 30, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }

        .modern-alert.show {
            transform: translateX(0);
            opacity: 1;
        }

        .modern-alert.slide-out {
            transform: translateX(120%);
            opacity: 0;
        }

        .modern-alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            border-radius: 16px 16px 0 0;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: rgba(76, 175, 80, 0.15);
            border-radius: 50%;
            color: #4CAF50;
            font-size: 20px;
            flex-shrink: 0;
        }

        .alert-text {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            color: #fff;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .alert-message {
            color: #b0b0b0;
            font-size: 14px;
            line-height: 1.4;
        }

        .alert-close {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .alert-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .alert-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            background: #4CAF50;
            border-radius: 0 0 16px 16px;
            animation: alertProgress 5s linear forwards;
        }

        @keyframes alertProgress {
            from { width: 100%; }
            to { width: 0%; }
        }

        /* Different alert types */
        .modern-alert.success {
            border-color: rgba(76, 175, 80, 0.3);
        }

        .modern-alert.success::before {
            background: linear-gradient(90deg, #4CAF50, #45a049);
        }

        .modern-alert.success .alert-icon {
            background: rgba(76, 175, 80, 0.15);
            color: #4CAF50;
        }

        .modern-alert.success .alert-progress {
            background: #4CAF50;
        }

        .modern-alert.error {
            border-color: rgba(244, 67, 54, 0.3);
        }

        .modern-alert.error::before {
            background: linear-gradient(90deg, #f44336, #d32f2f);
        }

        .modern-alert.error .alert-icon {
            background: rgba(244, 67, 54, 0.15);
            color: #f44336;
        }

        .modern-alert.error .alert-progress {
            background: #f44336;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?php include('components/user_navbar.php'); ?>
    <div class="alert-container" id="alertContainer"></div>
    <div class="container">
        <div class="details-card">
            <div class="page-header">
                <div class="header-left">
                    <div class="page-title"><?= date("l", strtotime($date)) ?>'s Meal Details</div>
                    <div class="page-date"><?= date("l, F j, Y", strtotime($date)) ?></div>
                </div>
                <div class="header-right">
                    <a href="meal_history.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i> Back to History
                    </a>
                    <a href="user_meal.php" class="action-btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Meal
                    </a>
                </div>
            </div>
            
            <div class="summary-section">
                
                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="summary-value"><?= $dayData['meal_count'] ?: 0 ?></div>
                        <div class="summary-label">Total Meals</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?= round($dayData['total_calories'] ?: 0) ?></div>
                        <div class="summary-label">Total Calories</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?= round($dayData['total_protein'] ?: 0) ?></div>
                        <div class="summary-label">Protein (g)</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?= round($dayData['total_carbs'] ?: 0) ?></div>
                        <div class="summary-label">Carbs (g)</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?= round($dayData['total_fat'] ?: 0) ?></div>
                        <div class="summary-label">Fat (g)</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?= round($dayData['total_fibre'] ?: 0) ?></div>
                        <div class="summary-label">Fibre (g)</div>
                    </div>
                </div>
            </div>
            
            <div class="meals-section">
                <?php if (!empty($mealsByTime)): ?>
                    <div class="meals-grid">
                        <?php foreach ($mealOrder as $mealTime): ?>
                            <?php if (isset($mealsByTime[$mealTime])): ?>
                                <div class="meal-type-section">
                                    <div class="meal-type-header">
                                        <span class="meal-type-badge <?= $mealTime ?>-badge">
                                            <?= ucfirst($mealTime) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="food-items-grid">
                                        <?php foreach ($mealsByTime[$mealTime] as $meal): ?>
                                            <div class="food-card">
                                                <div class="food-header">
                                                    <div class="food-info">
                                                        <div class="food-name"><?= htmlspecialchars($meal['food_name']) ?></div>
                                                        <div class="food-quantity"><?= $meal['quantity'] ?></div>
                                                        <div class="food-time">Logged at: <?= date("g:i A", strtotime($meal['logged_time'])) ?></div>
                                                    </div>
                                                    <div class="food-actions">
                                                        <button class="food-btn btn-edit" onclick="editMeal(<?= $meal['meal_log_id'] ?>)">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="food-btn btn-delete" onclick="showDeleteModal(<?= $meal['meal_log_id'] ?>, '<?= htmlspecialchars($meal['food_name']) ?>')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="food-stats">
                                                    <div class="food-stat">
                                                        <div class="food-stat-value"><?= round($meal['total_calories'], 1) ?></div>
                                                        <div class="food-stat-label">Calories</div>
                                                    </div>
                                                    <div class="food-stat">
                                                        <div class="food-stat-value"><?= round($meal['total_protein'], 1) ?></div>
                                                        <div class="food-stat-label">Protein (g)</div>
                                                    </div>
                                                    <div class="food-stat">
                                                        <div class="food-stat-value"><?= round($meal['total_carbs'], 1) ?></div>
                                                        <div class="food-stat-label">Carbs (g)</div>
                                                    </div>
                                                    <div class="food-stat">
                                                        <div class="food-stat-value"><?= round($meal['total_fat'], 1) ?></div>
                                                        <div class="food-stat-label">Fat (g)</div>
                                                    </div>
                                                    <div class="food-stat">
                                                        <div class="food-stat-value"><?= round($meal['total_fibre'], 1) ?></div>
                                                        <div class="food-stat-label">Fibre (g)</div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-clipboard"></i>
                        <h3>No meals found for this day</h3>
                        <p>Start tracking your meals by adding your first meal of the day!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Confirm Delete
            </div>
            <div class="modal-text">
                Are you sure you want to delete "<span id="mealName"></span>"? This action cannot be undone.
            </div>
            <div class="modal-actions">
                <button class="modal-btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="delete_meal" value="1">
                    <input type="hidden" name="meal_log_id" id="mealIdToDelete">
                    <button type="submit" class="modal-btn btn-confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize particles.js
            particlesJS("particles-js", {
                "particles": {
                    "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
                    "color": { "value": "#a64aff" },
                    "shape": { "type": "circle" },
                    "opacity": { "value": 0.3, "random": true },
                    "size": { "value": 3, "random": true },
                    "line_linked": { "enable": true, "distance": 120, "color": "#6a00ff", "opacity": 0.2, "width": 1 },
                    "move": { "enable": true, "speed": 1.5, "direction": "none", "random": true, "out_mode": "out" }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": { "enable": true, "mode": "repulse" },
                        "onclick": { "enable": true, "mode": "push" }
                    }
                }
            });
        });
        
        function showDeleteModal(mealId, mealName) {
            document.getElementById('mealIdToDelete').value = mealId;
            document.getElementById('mealName').textContent = mealName;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        function editMeal(mealId) {
            // Redirect to edit meal page with the meal_log_id
            window.location.href = 'user_meal.php?edit=' + mealId;
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
    <script>
    // Check for session alerts and display them
    <?php if (isset($_SESSION['alert_message'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const alertContainer = document.getElementById('alertContainer');
        const alertType = '<?= $_SESSION['alert_type'] ?>';
        const alertTitle = '<?= addslashes($_SESSION['alert_title']) ?>';
        const alertMessage = '<?= addslashes($_SESSION['alert_message']) ?>';
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const alertId = 'alert_' + Date.now();
        const alertHTML = `
            <div class="modern-alert ${alertType}" id="${alertId}">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="${icons[alertType]}"></i>
                    </div>
                    <div class="alert-text">
                        <div class="alert-title">${alertTitle}</div>
                        <div class="alert-message">${alertMessage}</div>
                    </div>
                    <button class="alert-close" onclick="document.getElementById('${alertId}').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="alert-progress"></div>
            </div>
        `;

        alertContainer.insertAdjacentHTML('beforeend', alertHTML);
        
        // Show alert
        setTimeout(() => {
            document.getElementById(alertId).classList.add('show');
        }, 100);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.classList.add('slide-out');
                setTimeout(() => alert.remove(), 400);
            }
        }, 5000);
    });
    <?php 
        // Clear session alerts after displaying
        unset($_SESSION['alert_message'], $_SESSION['alert_type'], $_SESSION['alert_title']); 
    ?>
    <?php endif; ?>
    </script>
</body>
</html>