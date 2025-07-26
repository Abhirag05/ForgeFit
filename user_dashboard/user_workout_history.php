<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete request for entire day
if (isset($_GET['delete_date'])) {
    $delete_date = $_GET['delete_date'];
    $delete_query = "DELETE FROM all_logged_workouts WHERE user_id = $user_id AND DATE(date) = '$delete_date'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['alert_message'] = "Workout day has been successfully deleted from your history.";
        $_SESSION['alert_type'] = "success";
        $_SESSION['alert_title'] = "Success!";
        header("Location: user_workout_history.php");
        exit();
    } else {
        $_SESSION['alert_message'] = "Failed to delete workout day. Please try again.";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_workout_history.php");
        exit();
    }
}

// Handle date filter
$date_filter = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$where_clause = "WHERE lw.user_id = $user_id";
if ($date_filter) {
    $where_clause .= " AND DATE(lw.date) = '$date_filter'";
}

$query = "SELECT lw.id, lw.date, w.name AS workout, lw.sets, lw.calories_burned, lw.set_details
          FROM all_logged_workouts lw
          JOIN all_workouts w ON lw.workout_id = w.id
          $where_clause
          ORDER BY lw.date DESC";
$result = mysqli_query($conn, $query);

// Group workouts by date
$workoutsByDate = [];
$totalWorkouts = 0;
$totalCalories = 0;

while ($log = mysqli_fetch_assoc($result)) {
    // Use only the date (ignore time) for grouping
    $dateKey = date('Y-m-d', strtotime($log['date']));
    if (!isset($workoutsByDate[$dateKey])) {
        $workoutsByDate[$dateKey] = [];
    }
    $workoutsByDate[$dateKey][] = $log;
    $totalWorkouts++;
    $totalCalories += $log['calories_burned'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workout History | ForgeFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="shared_style.css">
    <style>
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #a64aff 0%, #6a00ff 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #6a00ff 0%, #a64aff 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(106, 0, 255, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
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

        /* Date filter styles */
        .filter-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .date-input {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .date-input:focus {
            outline: none;
            border-color: #a64aff;
            box-shadow: 0 0 0 2px rgba(166, 74, 255, 0.2);
        }

        .filter-btn {
            padding: 10px 20px;
            font-size: 0.9rem;
        }

        .clear-filter {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .clear-filter:hover {
            text-decoration: underline;
        }

        /* Stats section */
        .history-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(166, 74, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(166, 74, 255, 0.2);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: #a64aff;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #ccc;
        }

        /* Date-based grid layout styles */
        .workout-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 25px;
            margin-top: 30px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
        }

        .history-header h2 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .history-header h2 i {
            color: #a64aff;
            font-size: 1.8rem;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Date card styles */
        .date-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .date-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(166, 74, 255, 0.2);
            border-color: rgba(166, 74, 255, 0.3);
        }

        .date-card-content {
            cursor: pointer;
        }

        .date-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .date-info {
            flex: 1;
        }

        .date-day {
            font-size: 1.4rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }

        .date-full {
            font-size: 0.9rem;
            color: #bbb;
        }

        .date-icon {
            font-size: 2rem;
            color: #a64aff;
            opacity: 0.8;
            margin-top:20px;
        }

        /* Delete button for day */
        .delete-day-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(244, 67, 54, 0.2);
            border: none;
           color: #f44336;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            
            
        }

        .delete-day-btn:hover {
            background: rgba(244, 67, 54, 0.4);
            transform: scale(1.1);
        }

        .workout-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 15px 0;
        }

        .summary-item {
            text-align: center;
        }

        .summary-value {
            font-size: 1.3rem;
            font-weight: 600;
            color: #a64aff;
        }

        .summary-label {
            font-size: 0.8rem;
            color: #ccc;
            margin-top: 2px;
        }

        .workout-list {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .workout-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .workout-item:last-child {
            border-bottom: none;
        }

        .workout-name {
            font-weight: 500;
            color: #fff;
            font-size: 0.9rem;
        }

        .workout-sets {
            font-size: 0.8rem;
            color: #a64aff;
        }

        .view-details {
            margin-top: 15px;
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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

        /* Empty state styles */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #ccc;
        }

        .empty-state i {
            font-size: 4rem;
            color: #a64aff;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .empty-state p {
            font-size: 1rem;
            line-height: 1.6;
            max-width: 500px;
            margin: 0 auto;
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

        /* Responsive design */
        @media (max-width: 1200px) {
            .workout-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .workout-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .history-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                text-align: left;
            }

            .history-header h2 {
                font-size: 1.8rem;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .filter-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .history-stats {
                grid-template-columns: 1fr;
            }

            .alert-container {
                right: 10px;
                left: 10px;
                max-width: none;
            }

            .modern-alert {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            .history-header h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .alert-container {
                top: 10px;
            }
        }
    </style>
</head>
<body>
<div id="particles-js"></div>
<?php include('components/user_navbar.php'); ?>

<!-- Modern Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<div class="dashboard-container">
    <div class="container">
        <div class="history-header">
            <h2>
                <i class="fas fa-history"></i>
                Your Workout History
            </h2>
            <div class="header-actions">
                <a href="user_workout.php" class="btn btn-secondary">
                    <i class="fas fa-plus-circle"></i> Log New Workout
                </a>
            </div>
        </div>

        <?php if (!empty($workoutsByDate)): ?>
            <div class="history-stats">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalWorkouts ?></div>
                    <div class="stat-label">
                        <?= $date_filter ? 'Workouts on ' . date('M j, Y', strtotime($date_filter)) : 'Total Workouts' ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($totalCalories) ?></div>
                    <div class="stat-label">
                        <?= $date_filter ? 'Calories on Selected Date' : 'Total Calories Burned' ?>
                    </div>
                </div>
            </div>
            <!-- Date Filter Section -->
        <div class="filter-section">
            <div class="filter-header">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filter by Date
                </div>
                <?php if ($date_filter): ?>
                    <a href="user_workout_history.php" class="clear-filter">
                        <i class="fas fa-times"></i> Clear Filter
                    </a>
                <?php endif; ?>
            </div>
            <form method="GET" action="" class="filter-controls">
                <input type="date" name="filter_date" class="date-input" 
                       value="<?= htmlspecialchars($date_filter) ?>" 
                       placeholder="Select date">
                <button type="submit" class="btn btn-primary filter-btn">
                    <i class="fas fa-search"></i> Filter
                </button>
            </form>
        </div>

            <div class="workout-grid">
                <?php foreach ($workoutsByDate as $date => $dayWorkouts): 
                    $dayTotalCalories = array_sum(array_column($dayWorkouts, 'calories_burned'));
                    $dayTotalWorkouts = count($dayWorkouts);
                    $dayName = date('l', strtotime($date)); // Get day name (Monday, Tuesday, etc.)
                    $formattedDate = date('M j, Y', strtotime($date));
                ?>
                    <div class="date-card">
                        <button class="delete-day-btn" title="Delete all workouts for this day" 
                                onclick="confirmDeleteDay('<?= $date ?>', '<?= $formattedDate ?>', <?= $dayTotalWorkouts ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                        
                        <div class="date-card-content" onclick="window.location.href='workout_day_details.php?date=<?= $date ?>'">
                            <div class="date-header">
                                <div class="date-info">
                                    <div class="date-day"><?= $dayName ?></div>
                                    <div class="date-full"><?= $formattedDate ?></div>
                                </div>
                                <div class="date-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>

                            <div class="workout-summary">
                                <div class="summary-item">
                                    <div class="summary-value"><?= $dayTotalWorkouts ?></div>
                                    <div class="summary-label">Workouts</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-value"><?= round($dayTotalCalories) ?></div>
                                    <div class="summary-label">Calories</div>
                                </div>
                            </div>

                            <div class="workout-list">
                                <?php foreach ($dayWorkouts as $workout): ?>
                                    <div class="workout-item">
                                        <div class="workout-name"><?= htmlspecialchars($workout['workout']) ?></div>
                                        <div class="workout-sets"><?= $workout['sets'] ?> sets</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="view-details">
                                <div class="view-details-text">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-dumbbell"></i>
                <h3><?= $date_filter ? 'No Workouts Found' : 'No Workouts Yet' ?></h3>
                <p>
                    <?= $date_filter 
                        ? 'No workouts were logged on ' . date('M j, Y', strtotime($date_filter)) . '.' 
                        : 'Start logging your workouts to see your progress here!' ?>
                </p>
                <?php if ($date_filter): ?>
                    <a href="user_workout_history.php" class="btn btn-secondary" style="margin-top: 20px;">
                        </i> View All Workouts
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
            <p>Are you sure you want to delete all workouts for <strong id="dateToDelete"></strong>?</p>
            <p>This will permanently delete <strong id="workoutCount"></strong> workout(s) and cannot be undone.</p>
        </div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn confirm" id="confirmDeleteBtn">Delete All</button>
        </div>
    </div>
</div>

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

// Delete confirmation functionality
let dateToDelete = null;

function confirmDeleteDay(date, formattedDate, workoutCount) {
    dateToDelete = date;
    document.getElementById('dateToDelete').textContent = formattedDate;
    document.getElementById('workoutCount').textContent = workoutCount;
    document.getElementById('deleteModal').style.display = 'flex';
    
    // Set up the confirm button
    document.getElementById('confirmDeleteBtn').onclick = function() {
        // Show loading state
        const btnText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        this.disabled = true;
        
        // Navigate to delete URL
        window.location.href = `user_workout_history.php?delete_date=${dateToDelete}`;
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

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
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
</script>
</body>
</html>