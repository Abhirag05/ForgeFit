
<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if we're in edit mode
$edit_mode = isset($_GET['edit_id']);
$workout_data = null;

if ($edit_mode) {
    $edit_id = $_GET['edit_id'];
    $user_id = $_SESSION['user_id'];
    
    // Fetch the workout to edit
    $query = "SELECT lw.*, w.name AS workout_name 
              FROM all_logged_workouts lw
              JOIN all_workouts w ON lw.workout_id = w.id
              WHERE lw.id = $edit_id AND lw.user_id = $user_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $workout_data = mysqli_fetch_assoc($result);
        $set_details = json_decode($workout_data['set_details'], true);
    } else {
        // Workout not found or doesn't belong to user
        $_SESSION['alert_message'] = "Workout not found or you don't have permission to edit it.";
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_title'] = "Error!";
        header("Location: user_workout_history.php");
        exit();
    }
}

// Fetch workout options
$workoutOptions = "";
$sql = "SELECT id, name FROM all_workouts";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $selected = ($edit_mode && $workout_data['workout_id'] == $row['id']) ? 'selected' : '';
    $workoutOptions .= "<option value='{$row['id']}' $selected>{$row['name']}</option>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Log' ?> Workout | ForgeFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="shared_style.css">
    <style>
        .dashboard-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
        }
        
        .log-header h2 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .log-header h2 i {
            color: #a64aff;
            font-size: 1.8rem;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .log-form {
            background: rgba(255, 255, 255, 0.03);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 700px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ddd;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #a64aff;
            box-shadow: 0 0 0 2px rgba(166, 74, 255, 0.2);
        }
        
        .input-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 25px;
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
        
        .set-container {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .set-header {
            font-weight: 600;
            margin-bottom: 10px;
            color: #a64aff;
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
        
        @media (max-width: 768px) {
            .input-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
            }
            
            .log-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                text-align: left;
            }

            .log-header h2 {
                font-size: 1.8rem;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
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
            .dashboard-container {
                max-width: 100vw;
                padding: 8px 0;
                margin: 0;
            }
            .container {
                padding: 0 2px;
            }
            .log-header {
                margin-bottom: 16px;
            }
            .log-header h2 {
                font-size: 1.1rem;
                gap: 8px;
            }
            .log-form {
                width: 100%;
                max-width: 99vw;
                padding: 12px 20px;
            }
            .form-group label {
                font-size: 0.95rem;
            }
            .form-group input,
            .form-group select {
                font-size: 0.95rem;
                padding: 8px 8px;
            }
            .input-grid {
                gap: 10px;
            }
            .btn {
                padding: 10px 10px;
                font-size: 0.95rem;
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
        <div class="log-header">
            <h2>
                <i class="fas fa-<?= $edit_mode ? 'edit' : 'plus-circle' ?>"></i>
                <?= $edit_mode ? 'Edit Workout' : 'Log Workout' ?>
            </h2>
            <div class="header-actions">
                <a href="user_workout_history.php" class="btn btn-secondary">
                    <i class="fas fa-history"></i> Workout History
                </a>
            </div>
        </div>
        
        <div class="log-form">
            <form action="save_workout.php" method="POST" id="workoutForm">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="edit_id" value="<?= $workout_data['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="workout">Workout Type</label>
                    <select name="workout_id" id="workout" required>
                        <option value="">-- Select Workout --</option>
                        <?= $workoutOptions ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="datetime-local" name="date" id="date" required 
                           value="<?= $edit_mode ? date('Y-m-d\TH:i', strtotime($workout_data['date'])) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="sets">Number of Sets</label>
                    <select name="sets" id="sets" required onchange="generateSetFields()">
                        <option value="">-- Select Number of Sets --</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= ($edit_mode && $workout_data['sets'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?> Set<?= $i > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div id="setsContainer"></div>

                <div class="form-group">
                    <label for="time_per_rep">Time per Rep</label>
                    <select name="time_per_rep" id="time_per_rep" required>
                        <option value="3" <?= ($edit_mode && $workout_data['time_per_rep'] == 3) ? 'selected' : '' ?>>3 sec - Fast</option>
                        <option value="4" <?= (!$edit_mode || ($edit_mode && $workout_data['time_per_rep'] == 4)) ? 'selected' : '' ?>>4 sec - Moderate</option>
                        <option value="5" <?= ($edit_mode && $workout_data['time_per_rep'] == 5) ? 'selected' : '' ?>>5 sec - Controlled</option>
                        <option value="6" <?= ($edit_mode && $workout_data['time_per_rep'] == 6) ? 'selected' : '' ?>>6 sec - Slow/Heavy</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Workout' : 'Save Workout' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

    // Global variables to store edit mode data
    const isEditMode = <?= $edit_mode ? 'true' : 'false' ?>;
    <?php if ($edit_mode && isset($set_details)): ?>
        const originalSetDetails = <?= json_encode($set_details) ?>;
        const originalNumSets = <?= $workout_data['sets'] ?>;
    <?php else: ?>
        const originalSetDetails = null;
        const originalNumSets = 0;
    <?php endif; ?>

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

    // Unified function to generate set fields - works for both new and edit mode
    function generateSetFields() {
        const numSets = parseInt(document.getElementById('sets').value);
        const container = document.getElementById('setsContainer');
        container.innerHTML = '';
        
        if (!numSets || numSets <= 0) {
            return;
        }
        
        for (let i = 0; i < numSets; i++) {
            const setDiv = document.createElement('div');
            setDiv.className = 'set-container';
            
            const setNumber = i + 1;
            let reps = '';
            let weight = '';
            
            // If in edit mode and we have original data for this set, use it
            // Otherwise, leave fields empty for new sets
            if (isEditMode && originalSetDetails && i < originalSetDetails.length) {
                reps = originalSetDetails[i]?.reps || '';
                weight = originalSetDetails[i]?.weight || '';
            }
            
            setDiv.innerHTML = `
                <div class="set-header">Set ${setNumber}</div>
                <div class="input-grid">
                    <div class="form-group">
                        <label for="reps_${setNumber}">Reps</label>
                        <input type="number" name="reps[]" id="reps_${setNumber}" min="1" required value="${reps}">
                    </div>
                    <div class="form-group">
                        <label for="weight_${setNumber}">Weight (kg)</label>
                        <input type="number" name="weight[]" id="weight_${setNumber}" min="0" step="0.1" required value="${weight}">
                    </div>
                </div>
            `;
            
            container.appendChild(setDiv);
        }
    }

    // Initialize the form when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Set current date/time if not in edit mode
        if (!isEditMode) {
            const dateInput = document.getElementById('date');
            const now = new Date();
            
            // Format the date for datetime-local input
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            dateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
        
        // Generate initial set fields if we have a selected number of sets
        const setsSelect = document.getElementById('sets');
        if (setsSelect.value) {
            generateSetFields();
        }
    });
</script>
</body>
</html>