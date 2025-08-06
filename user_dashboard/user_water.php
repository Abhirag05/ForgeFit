<?php
session_start();
include '../db.php'; // Assuming this is your database connection file
include_once 'xp-system.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$current_glasses = 0;
$history = [];

// Get today's total glasses
$stmt = $conn->prepare("SELECT SUM(glasses_count) as total FROM user_water_intake WHERE user_id = ? AND date_recorded = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$current_glasses = $result['total'] ?? 0;
$stmt->close();

// Get today's history
$stmt = $conn->prepare("SELECT glasses_count, intake_time FROM user_water_intake WHERE user_id = ? AND date_recorded = ? ORDER BY intake_time DESC");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();

// Handle add/remove glass actions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO user_water_intake (user_id, glasses_count, date_recorded) VALUES (?, 1, ?)");
            $stmt->bind_param("is", $user_id, $today);
            $stmt->execute();
            // EXP logic for water intake
            // Count glasses after logging
            $stmt2 = $conn->prepare("SELECT SUM(glasses_count) as total FROM user_water_intake WHERE user_id = ? AND date_recorded = ?");
            $stmt2->bind_param("is", $user_id, $today);
            $stmt2->execute();
            $result2 = $stmt2->get_result()->fetch_assoc();
            $total_glasses_today = $result2['total'] ?? 0;
            $stmt2->close();
            if ($total_glasses_today <= 12) {
                addExperience($user_id, 2, $conn);
                $_SESSION['alert_message'] = "Glass of water logged! (+2 EXP)";
                $_SESSION['alert_type'] = "success";
                $_SESSION['alert_title'] = "Hydrated!";
            } else {
                $_SESSION['alert_message'] = "Glass of water logged! You are overdrinking! No more EXP for today.";
                $_SESSION['alert_type'] = "warning";
                $_SESSION['alert_title'] = "Overdrinking!";
            }
            header("Location: user_water.php");
            exit();
            $stmt->close();
            $current_glasses++;
        } elseif ($action === 'remove' && $current_glasses > 0) {
            // Find the most recent entry to remove
            $stmt = $conn->prepare("SELECT water_id FROM user_water_intake WHERE user_id = ? AND date_recorded = ? ORDER BY intake_time DESC LIMIT 1");
            $stmt->bind_param("is", $user_id, $today);
            $stmt->execute();
            $res = $stmt->get_result();
            $last_entry = $res->fetch_assoc();
            $stmt->close();
            if ($last_entry) {
                $stmt = $conn->prepare("DELETE FROM user_water_intake WHERE water_id = ?");
                $stmt->bind_param("i", $last_entry['water_id']);
                $stmt->execute();
                $stmt->close();
                $current_glasses = max(0, $current_glasses - 1);
            }
        }
        // Refresh history after update
        $history = [];
        $stmt = $conn->prepare("SELECT glasses_count, intake_time FROM user_water_intake WHERE user_id = ? AND date_recorded = ? ORDER BY intake_time DESC");
        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Intake | ForgeFit</title>
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
            margin: 0 auto;
            padding: 2rem;
        }
        
        .water-tracker {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            margin-top: 2rem;
            width: 700px;
            margin: auto;
        }

        .water-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .water-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
        }

        .water-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .water-progress {
            margin-bottom: 2rem;
            text-align: center;
        }

        .water-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

       .water-circle-fill {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 0%; /* Default fill height */
                background: rgba(74, 107, 255, 0.3);
                transition: height 0.5s ease;
                z-index: 0;
                border-bottom-left-radius: 50%;
                border-bottom-right-radius: 50%;
            }


        .water-circle-inner {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .water-glasses {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4a6bff;
        }

        .water-goal-text {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
        }

        .water-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .water-btn {
            background: #4a6bff;
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .water-btn:hover {
            background: #3a5bef;
            transform: translateY(-2px);
        }

        .water-btn i {
            font-size: 1.2rem;
        }

        .water-btn.secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .water-btn.secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .water-history {
            margin-top: 2rem;
        }

        .history-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            text-align: center; /* Add this */
            margin-bottom: 1rem; /* Optional: add spacing below title */
        }


        .history-table {
            width: 50%;
            border-collapse: collapse;
            margin:auto;
        }

        .history-table th {
            text-align: left;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .history-table td {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .water-circle {
                width: 150px;
                height: 150px;
            }
            
            .water-circle-inner {
                width: 130px;
                height: 130px;
            }
            
            .water-glasses {
                font-size: 2rem;
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
    <!-- Modern Alert Container -->
    <div class="alert-container" id="alertContainer"></div>
    
    <div class="container">
        <main>
            <div class="water-tracker">
                <div class="water-header">
                    <h2 class="water-title">Water Intake Tracker</h2>
                    <span class="water-date" id="current-date"><?php echo date('F j, Y'); ?></span>
                </div>

                <div class="water-progress">
                    <div class="water-circle">
                        <div class="water-circle-fill" id="water-fill"></div>
                        <div class="water-circle-inner">
                            <span class="water-glasses" id="water-glasses"><?php echo $current_glasses; ?></span>
                            <span class="water-goal-text">of 12 glasses</span>
                        </div>
                    </div>
                    <div class="water-goal">Goal: <span id="water-goal">12</span> glasses</div>
                </div>

                <div class="water-buttons">
                    <form method="post" class="water-form">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="water-btn">
                            <i class="fas fa-plus"></i>
                            <span>Add Glass</span>
                        </button>
                    </form>
                    <form method="post" class="water-form">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="water-btn secondary">
                            <i class="fas fa-minus"></i>
                            <span>Remove</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="water-history">
                <h3 class="history-title">Today's History</h3>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Action</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                        <?php if (count($history) > 0): ?>
                            <?php foreach ($history as $entry): ?>
                                <tr>
                                    <td><?php echo date('h:i A', strtotime($entry['intake_time'])); ?></td>
                                    <td><?php echo $entry['glasses_count'] > 0 ? 'Added glass' : 'Removed glass'; ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No entries yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const MAX_GLASSES = 12;
        let currentGlasses = <?php echo $current_glasses; ?>;
        
        // DOM elements
        const glassesEl = document.getElementById('water-glasses');
        const fillEl = document.getElementById('water-fill');
        const historyBodyEl = document.getElementById('history-body');
        const currentDateEl = document.getElementById('current-date');

        // Update the display
             function updateDisplay() {
                glassesEl.textContent = currentGlasses;

                const percentage = (currentGlasses / MAX_GLASSES) * 100;
                fillEl.style.height = `${percentage}%`;
            }


        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDisplay();
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
                <div class="alert-icon"><i class="${icons[alertType] || icons.info}"></i></div>
                <div class="alert-text">
                    <div class="alert-title">${alertTitle}</div>
                    <div class="alert-message">${alertMessage}</div>
                </div>
                <button class="alert-close" onclick="document.getElementById('${alertId}').remove()"><i class="fas fa-times"></i></button>
                <div class="alert-progress"></div>
            </div>
        `;
        alertContainer.insertAdjacentHTML('beforeend', alertHTML);
        setTimeout(() => {
            document.getElementById(alertId).classList.add('show');
        }, 100);
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
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
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
    });
    </script>
</body>
</html>