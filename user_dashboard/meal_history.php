<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_date = isset($_GET['date']) ? $_GET['date'] : null;

// Fetch meal history grouped by date
$historyQuery = "SELECT 
    DATE(lm.date) as meal_date,
    COUNT(*) as meal_count,
    SUM(lm.total_calories) as total_calories,
    SUM(lm.total_protein) as total_protein,
    SUM(lm.total_carbs) as total_carbs,
    SUM(lm.total_fat) as total_fat
FROM logged_meals lm 
WHERE lm.user_id = $user_id " . 
($selected_date ? " AND DATE(lm.date) = '$selected_date' " : "") . 
" GROUP BY DATE(lm.date) 
ORDER BY meal_date DESC";
$historyResult = mysqli_query($conn, $historyQuery);

// Fetch all dates with meals for the calendar
$datesQuery = "SELECT DISTINCT DATE(date) as meal_date FROM logged_meals WHERE user_id = $user_id";
$datesResult = mysqli_query($conn, $datesQuery);
$mealDates = [];
while ($row = mysqli_fetch_assoc($datesResult)) {
    $mealDates[] = $row['meal_date'];
}

// Handle delete request for entire day
if (isset($_GET['delete_date'])) {
    $delete_date = $_GET['delete_date'];
    $delete_query = "DELETE FROM logged_meals WHERE user_id = $user_id AND DATE(date) = '$delete_date'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['alert_message'] = "Meals for this day have been successfully deleted from your history.";
        $_SESSION['alert_type'] = "success";
        $_SESSION['alert_title'] = "Success!";
        header("Location: meal_history.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Failed to delete meals for this day. Please try again.";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: meal_history.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal History | ForgeFit</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            margin: auto;
            padding: 2rem;
            border-radius: 12px;
        }
        
        .history-card {
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        
        .page-title {
            color: #fff;
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
            position: relative;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #a64aff, #6a00ff);
            border-radius: 3px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #a64aff, #6a00ff);
            color: white;
            padding: 0.8rem 1.8rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(166, 74, 255, 0.3);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(166, 74, 255, 0.4);
        }
        
        .filter-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: rgba(166, 74, 255, 0.2);
            color: #a64aff;
            border: 1px solid rgba(166, 74, 255, 0.3);
            padding: 0.7rem 1.3rem;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: rgba(166, 74, 255, 0.3);
            transform: translateY(-1px);
        }
        
        .date-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.7rem 1.2rem;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }
        
        .clear-filter {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        .clear-filter:hover {
            color: #a64aff;
            background: rgba(166, 74, 255, 0.1);
        }
        
        /* Grid Layout for Day Cards */
        .days-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-top: 1rem;
        }
        
        @media (min-width: 1200px) {
            .days-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .days-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
        
        .day-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            backdrop-filter: blur(10px);
            height: fit-content;
            position: relative;
        }
        
        .day-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(166, 74, 255, 0.2);
            border-color: rgba(166, 74, 255, 0.3);
        }
        
        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .day-info {
            flex: 1;
        }
        
        .day-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.3rem;
        }
        
        .day-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .meal-count-badge {
            background: linear-gradient(135deg, #a64aff, #6a00ff);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
           
        }
        
        .day-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            background: rgba(166, 74, 255, 0.1);
            padding: 0.8rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            border: 1px solid rgba(166, 74, 255, 0.15);
        }
        
        .stat-value {
            font-weight: 700;
            color: #a64aff;
            font-size: 1rem;
        }
        
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }
        
        .meals-container {
            margin-top: 1.2rem;
        }
        
        .meals-header {
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .meal-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .meal-item:last-child {
            border-bottom: none;
        }
        
        .meal-time-section {
            min-width: 80px;
        }
        
        .meal-time-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .breakfast-badge {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .lunch-badge {
            background-color: rgba(0, 150, 136, 0.2);
            color: #009688;
            border: 1px solid rgba(0, 150, 136, 0.3);
        }
        
        .dinner-badge {
            background-color: rgba(103, 58, 183, 0.2);
            color: #673ab7;
            border: 1px solid rgba(103, 58, 183, 0.3);
        }
        
        .snack-badge {
            background-color: rgba(233, 30, 99, 0.2);
            color: #e91e63;
            border: 1px solid rgba(233, 30, 99, 0.3);
        }
        
        .meal-foods {
            flex: 1;
            margin-left: 1rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.6);
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: rgba(166, 74, 255, 0.5);
        }
        
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        /* Delete button for day */
        .delete-day-btn {
            position: absolute;
            top: 40px;
            right: 10px;
            background:black;
            border: 1px solid rgba(244, 67, 54, 0.2);
            color: #f44336;
            border-radius: 0%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 2;
        }

        .delete-day-btn:hover {
            background: rgba(244, 67, 54, 0.2);
            transform: scale(1.1);
        }
        
        .view-details {
            margin-top: 15px;
            text-align: center;
        }

        .view-details-text {
            color: #a64aff;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        /* Confirmation modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .modal-header i {
            color: #f44336;
            font-size: 1.5rem;
        }

        .modal-header h3 {
            margin: 0;
            color: #fff;
        }

        .modal-body {
            color: #ccc;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .modal-btn.cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .modal-btn.cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-btn.confirm {
            background: #f44336;
            color: white;
        }

        .modal-btn.confirm:hover {
            background: #d32f2f;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 1rem auto;
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .day-stats {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .delete-day-btn {
                top: 15px;
                right: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .day-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .meal-count-badge {
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
        <div class="history-card">
            <div class="page-header">
                <h1 class="page-title">Meal History</h1>
                <div class="header-actions">
                    <a href="user_meal.php" class="back-btn">
                        <i class="fas fa-plus"></i> Log New Meal
                    </a>
                </div>
            </div>
            
            <div class="filter-container">
                <button class="filter-btn" id="dateFilterBtn">
                    <i class="far fa-calendar-alt"></i> Filter by Date
                </button>
                <input type="text" class="date-input" id="datePicker" placeholder="Select date" style="display: none;">
                <?php if ($selected_date): ?>
                    <span class="clear-filter" onclick="clearFilter()">
                        <i class="fas fa-times"></i> Clear filter
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="days-grid">
                <?php if (mysqli_num_rows($historyResult) > 0): ?>
                    <?php while ($day = mysqli_fetch_assoc($historyResult)): ?>
                        <?php 
                        // Fetch meals for this specific day
                        $date = $day['meal_date'];
                        $formattedDate = date("F j, Y", strtotime($date));
                        $mealsQuery = "SELECT 
                            lm.meal_time,
                            m.food_name,
                            lm.quantity,
                            m.unit
                        FROM logged_meals lm 
                        JOIN meals m ON lm.meal_id = m.meal_id 
                        WHERE lm.user_id = $user_id AND DATE(lm.date) = '$date'
                        ORDER BY lm.date ASC";
                        $mealsResult = mysqli_query($conn, $mealsQuery);
                        $mealsByTime = [];
                        while ($meal = mysqli_fetch_assoc($mealsResult)) {
                            $mealsByTime[$meal['meal_time']][] = $meal;
                        }
                        ?>
                         <?php
                        // Sort meals by meal time order
                        $mealTimeOrder = ['breakfast', 'lunch', 'dinner', 'snack'];
                        uksort($mealsByTime, function($a, $b) use ($mealTimeOrder) {
                            $posA = array_search(strtolower($a), $mealTimeOrder);
                            $posB = array_search(strtolower($b), $mealTimeOrder);
                            $posA = $posA === false ? 99 : $posA;
                            $posB = $posB === false ? 99 : $posB;
                            return $posA - $posB;
                        });
                        ?>
                        
                        <div class="day-card">
                            <button class="delete-day-btn" title="Delete all meals for this day" 
                                onclick="event.stopPropagation(); confirmDeleteDay('<?= $date ?>', '<?= $formattedDate ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            
                            <div onclick="window.location='meal_day_details.php?date=<?= $date ?>'">
                                <div class="day-header">
                                    <div class="day-info">
                                        <div class="day-title"><?= date("l", strtotime($date)) ?></div>
                                        <div class="day-date"><?= $formattedDate ?></div>
                                    </div>
                                     <div class="meal-count-badge">
                                        <i class="fas fa-utensils"></i>
                                        <span><?= $day['meal_count'] ?> meals</span>
                                    </div>
                                </div>
                                
                                <div class="day-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-fire" style="color: #ff6b6b;"></i>
                                        <div>
                                            <div class="stat-value"><?= round($day['total_calories']) ?></div>
                                            <div class="stat-label">calories</div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-dumbbell" style="color: #4ecdc4;"></i>
                                        <div>
                                            <div class="stat-value"><?= round($day['total_protein']) ?>g</div>
                                            <div class="stat-label">protein</div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-bread-slice" style="color: #feca57;"></i>
                                        <div>
                                            <div class="stat-value"><?= round($day['total_carbs']) ?>g</div>
                                            <div class="stat-label">carbs</div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-bacon" style="color: #ff9ff3;"></i>
                                        <div>
                                            <div class="stat-value"><?= round($day['total_fat']) ?>g</div>
                                            <div class="stat-label">fat</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="meals-container">
                                    <?php foreach ($mealsByTime as $mealTime => $meals): ?>
                                        <div class="meal-item">
                                            <div class="meal-time-section">
                                                <span class="meal-time-badge <?= $mealTime ?>-badge">
                                                    <?= $mealTime ?>
                                                </span>
                                            </div>
                                            <div class="meal-foods">
                                                <?php foreach ($meals as $meal): ?>
                                                    <?= htmlspecialchars($meal['food_name']) ?> (<?= $meal['quantity'] ?>)
                                                    <?php if ($meal !== end($meals)) echo " , "; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="view-details">
                                        <div class="view-details-text">
                                            <i class="fas fa-eye"></i>
                                            View Details
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-clipboard"></i>
                        <p>No meals logged yet.</p>
                        <p>Start tracking your nutrition journey!</p>
                        <?php if ($selected_date): ?>
                            <p style="margin-top: 1rem; color: rgba(255, 255, 255, 0.5);">
                                No meals found for <?= date("F j, Y", strtotime($selected_date)) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Confirm Deletion</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete all meals for <strong id="dateToDelete"></strong>?</p>
                <p>This will permanently delete <span id="mealCount"></span> and cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
                <button class="modal-btn confirm" id="confirmDeleteBtn">Delete All</button>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize particles.js
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 100, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#a64aff" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.4, "random": true },
                "size": { "value": 3, "random": true },
                "line_linked": { "enable": true, "distance": 120, "color": "#6a00ff", "opacity": 0.3, "width": 1 },
                "move": { "enable": true, "speed": 2, "direction": "none", "random": true, "out_mode": "out" }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "repulse" },
                    "onclick": { "enable": true, "mode": "push" }
                }
            }
        });
        
        // Initialize date picker
        const datePicker = flatpickr("#datePicker", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            disableMobile: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    window.location.href = `meal_history.php?date=${dateStr}`;
                }
            }
        });
        
        // Show date picker when filter button is clicked
        document.getElementById('dateFilterBtn').addEventListener('click', function() {
            datePicker.open();
        });
    });
    
    function clearFilter() {
        window.location.href = 'meal_history.php';
    }
    
    // Delete confirmation functionality
    let dateToDelete = null;
    let mealCount = 0;

    function confirmDeleteDay(date, formattedDate) {
        event.stopPropagation();
        dateToDelete = date;
        document.getElementById('dateToDelete').textContent = formattedDate;
        
        // Get the meal count from the badge in the card
        const card = event.target.closest('.day-card');
        mealCount = card.querySelector('.meal-count-badge span').textContent.split(' ')[0];
        document.getElementById('mealCount').textContent = mealCount + ' meal' + (mealCount > 1 ? 's' : '');
        
        document.getElementById('deleteModal').style.display = 'flex';
        
        // Set up the confirm button
        document.getElementById('confirmDeleteBtn').onclick = function() {
            // Show loading state
            const btnText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            this.disabled = true;
            
            // Navigate to delete URL
            window.location.href = `meal_history.php?delete_date=${dateToDelete}`;
        };
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
        dateToDelete = null;
        
        // Reset confirm button
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.innerHTML = 'Delete All';
        confirmBtn.disabled = false;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
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