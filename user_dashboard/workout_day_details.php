
<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['date'])) {
    header("Location: user_workout_history.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_date = $_GET['date'];

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM all_logged_workouts WHERE id = $delete_id AND user_id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['alert_message'] = "Workout deleted successfully!";
        $_SESSION['alert_type'] = "success";
        $_SESSION['alert_title'] = "Success!";
        header("Location: workout_day_details.php?date=$selected_date");
        exit();
    } else {
        $_SESSION['alert_message'] = "Failed to delete workout. Please try again.";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: workout_day_details.php?date=$selected_date");
        exit();
    }
}

$query = "SELECT lw.id, lw.date, w.name AS workout, lw.sets, lw.calories_burned, lw.set_details, lw.workout_id, lw.time_per_rep
          FROM all_logged_workouts lw
          JOIN all_workouts w ON lw.workout_id = w.id
          WHERE lw.user_id = $user_id 
            AND DATE(lw.date) = '$selected_date'
          ORDER BY lw.id ASC";
$result = mysqli_query($conn, $query);

$dayName = date('l', strtotime($selected_date));
$formattedDate = date('M j, Y', strtotime($selected_date));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workouts for <?= $dayName ?> | ForgeFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="shared_style.css">
    <style>
        .set-details {
            margin-top: 15px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 15px;
            display: none;
        }
        
        .set-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .set-row:last-child {
            border-bottom: none;
        }
        
        .set-row-header {
            font-weight: 600;
            color: #a64aff;
        }
        
        .toggle-sets {
            color: #a64aff;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .toggle-sets:hover {
            text-decoration: underline;
        }
        
        .stat-item-value {
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .total-volume {
            font-weight: 600;
            color: #a64aff;
        }
          
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

        /* Workout grid layout styles */
        .workout-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 30px;
        }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
        }

        .day-header h2 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .day-header h2 i {
            color: #a64aff;
            font-size: 1.8rem;
        }

        .day-subtitle {
            font-size: 1rem;
            color: #bbb;
            margin-top: 5px;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .day-summary {
            background: rgba(166, 74, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(166, 74, 255, 0.2);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }

        .summary-item {
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .summary-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #a64aff;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #ccc;
        }

        /* Workout card styles */
        .workout-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .workout-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(166, 74, 255, 0.2);
            border-color: rgba(166, 74, 255, 0.3);
        }

        .workout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .workout-name {
            font-weight: 600;
            color: #fff;
            font-size: 1.1rem;
        }

        .workout-date {
            font-size: 0.8rem;
            color: #bbb;
            margin-top: 5px;
        }

        .workout-icon {
            font-size: 1.8rem;
            color: #a64aff;
            opacity: 0.8;
        }

        .workout-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 15px 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item-label {
            font-size: 0.8rem;
            color: #ccc;
            margin-top: 2px;
        }

        /* Action buttons */
        .workout-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            z-index: 2;
        }

        .action-btn {
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: #ccc;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top:30px;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn.edit:hover {
            background: rgba(76, 175, 80, 0.3);
            color: #4CAF50;
        }

        .action-btn.delete:hover {
            background: rgba(244, 67, 54, 0.3);
            color: #f44336;
        }

        /* Confirmation modal */
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

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }

        .modal-btn.cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .modal-btn.confirm {
            background: #f44336;
            color: white;
        }

        /* Responsive design */
        @media (max-width: 1200px) {
            .workout-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .day-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                text-align: left;
            }

            .day-header h2 {
                font-size: 1.8rem;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
                gap: 15px;
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
            .day-header h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .alert-container {
                top: 10px;
            }
            .day-summary {
                padding: 10px;
                border-radius: 8px;
                margin-bottom: 18px;
            }
            .summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .summary-item {
                padding: 8px;
                border-radius: 6px;
            }
            .summary-value {
                font-size: 1.1rem;
            }
            .summary-label {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 360px) {
            .day-summary {
                padding: 5px;
                border-radius: 5px;
            }
            .summary-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            .summary-item {
                padding: 5px;
                border-radius: 4px;
            }
            .summary-value {
                font-size: 0.95rem;
            }
            .summary-label {
                font-size: 0.7rem;
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
        <div class="day-header">
            <div>
                <h2>
                    <i class="fas fa-calendar-day"></i>
                    <?= $dayName ?> Workouts
                </h2>
                <div class="day-subtitle"><?= $formattedDate ?></div>
            </div>
            <div class="header-actions">
                <a href="user_workout_history.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
                <a href="user_workout.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Log New Workout
                </a>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php
            $workouts = [];
            $totalCalories = 0;
            $totalSets = 0;
            $totalReps = 0;
            $totalVolume = 0;

            while ($log = mysqli_fetch_assoc($result)) {
                $workouts[] = $log;
                $totalCalories += $log['calories_burned'];
                $totalSets += $log['sets'];
                
                // Calculate reps and volume for this workout
                $set_details = json_decode($log['set_details'], true);
                if (is_array($set_details) && !empty($set_details)) {
                    $workout_reps = array_sum(array_column($set_details, 'reps'));
                    $workout_weight = array_sum(array_column($set_details, 'weight'));
                    $avg_weight = $workout_weight / count($set_details);
                    $workout_volume = $workout_reps * $avg_weight;
                    
                    $totalReps += $workout_reps;
                    $totalVolume += $workout_volume;
                }
            }
            ?>

            <div class="day-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value"><?= count($workouts) ?></div>
                        <div class="summary-label">Total Workouts</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?= $totalSets ?></div>
                        <div class="summary-label">Total Sets</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?= $totalReps ?></div>
                        <div class="summary-label">Total Reps</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?= round($totalCalories) ?></div>
                        <div class="summary-label">Calories Burned</div>
                    </div>
                </div>
            </div>

            <div class="workout-grid">
                <?php foreach ($workouts as $log): 
                    $set_details = json_decode($log['set_details'], true);
                    
                    // Initialize default values
                    $total_reps = 0;
                    $total_weight = 0;
                    $avg_weight = 0;
                    $total_volume = 0;
                    
                    // Only process if JSON is valid
                    if (is_array($set_details) && !empty($set_details)) {
                        $total_reps = array_sum(array_column($set_details, 'reps'));
                        $total_weight = array_sum(array_column($set_details, 'weight'));
                        $avg_weight = $total_weight / count($set_details);
                        $total_volume = $total_reps * $avg_weight;
                    }
                ?>
                    <div class="workout-card">
                        <div class="workout-actions">
                            <button class="action-btn edit" title="Edit Workout" 
                                    onclick="window.location.href='user_workout.php?edit_id=<?= $log['id'] ?>'">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" title="Delete Workout" 
                                    onclick="confirmDelete(<?= $log['id'] ?>, '<?= htmlspecialchars($log['workout']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <div class="workout-header">
                            <div>
                                <div class="workout-name"><?= htmlspecialchars($log['workout']) ?></div><div class="calories-badge">
                            <i class="fas fa-fire"></i>
                            <?= round($log['calories_burned']) ?> cal
                        </div>
                                <div class="workout-date"><?= date('g:i A', strtotime($log['date'])) ?></div>
                            </div>
                        </div>
                        <div class="workout-stats">
                            <div class="stat-item"><div class="stat-item-value"><?= $log['sets'] ?></div><div class="stat-item-label">Sets</div></div>
                            <div class="stat-item"><div class="stat-item-value"><?= $total_reps ?></div><div class="stat-item-label">Total Reps</div></div>
                            <div class="stat-item"><div class="stat-item-value"><?= round($avg_weight, 1) ?> kg</div><div class="stat-item-label">Avg Weight</div></div>
                            <div class="stat-item"><div class="stat-item-value total-volume"><?= round($total_volume) ?> kg</div><div class="stat-item-label">Total Volume</div></div>
                        </div>
                        
                        <div class="toggle-sets" onclick="toggleSetDetails(this)">
                            <i class="fas fa-chevron-down"></i> Show Set Details
                        </div>
                        <div class="set-details">
                            <div class="set-row set-row-header">
                                <div>Set</div>
                                <div>Reps</div>
                                <div>Weight (kg)</div>
                            </div>
                            <?php if (is_array($set_details) && !empty($set_details)): ?>
                                <?php foreach ($set_details as $set): ?>
                                    <div class="set-row">
                                        <div>Set <?= $set['set_number'] ?></div>
                                        <div><?= $set['reps'] ?></div>
                                        <div><?= $set['weight'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-dumbbell"></i>
                <h3>No Workouts Found</h3>
                <p>No workouts were logged for <?= $formattedDate ?>.</p>
                <a href="user_workout_history.php" class="btn btn-secondary" style="margin-top: 20px;">
                    </i> Back to History
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete "<span id="workoutToDelete"></span>" workout?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn confirm" id="confirmDeleteBtn">Delete</button>
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

function toggleSetDetails(button) {
    const details = button.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (details.style.display === 'none' || !details.style.display) {
        details.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
        button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Set Details';
    } else {
        details.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
        button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Set Details';
    }
}

let workoutIdToDelete = null;

function confirmDelete(id, workoutName) {
    workoutIdToDelete = id;
    document.getElementById('workoutToDelete').textContent = workoutName;
    document.getElementById('deleteModal').style.display = 'flex';
    
    // Set up the confirm button
    document.getElementById('confirmDeleteBtn').onclick = function() {
        window.location.href = `workout_day_details.php?date=<?= $selected_date ?>&delete_id=${workoutIdToDelete}`;
    };
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
    workoutIdToDelete = null;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeModal();
    }
}
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