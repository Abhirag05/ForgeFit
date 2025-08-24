<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch meals from database
$mealOptions = "";
$sql = "SELECT meal_id, food_name, unit, calories FROM meals";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $mealOptions .= "<option value='{$row['meal_id']}' data-unit='" . htmlspecialchars($row['unit']) . "'>" .
                    htmlspecialchars($row['food_name']) . 
                    "</option>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Log Meal | ForgeFit</title>
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
      max-width: 700px;
      margin: 3rem auto;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    .form-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      position: relative;
    }
    
    .form-header-flex {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
      gap: 1rem;
    }
    
    .form-card h1 {
      color: #fff;
      margin-bottom: 0;
      padding-bottom: 0.5rem;
      font-size: 1.8rem;
      text-align: left;
      position: relative;
    }
    
    .form-card h1::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, #a64aff, #6a00ff);
      border-radius: 3px;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .form-group select, 
    .form-group input[type="datetime-local"] {
      width: 100%;
      padding: 0.75rem 1rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      color: #fff;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }
    
    .form-group select:focus, 
    .form-group input[type="datetime-local"]:focus {
      outline: none;
      border-color: #a64aff;
      box-shadow: 0 0 0 2px rgba(166, 74, 255, 0.2);
    }
    
    .form-group select option {
      background: #1a1a2e;
      color: #fff;
    }
    
    .submit-btn {
      background: linear-gradient(135deg, #a64aff, #6a00ff);
      color: white;
      padding: 0.8rem 2rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(166, 74, 255, 0.3);
      width: 100%;
      justify-content: center;
    }
    
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(166, 74, 255, 0.4);
    }
    
    .view-history-btn {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      padding: 0.8rem 2rem;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      text-decoration: none;
      white-space: nowrap;
      margin-bottom: 0;
      margin-left: auto;
    }
    
    .view-history-btn:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-2px);
    }
    
    .time-options {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .time-option {
      flex: 1;
      text-align: center;
    }
    
    .time-option input[type="radio"] {
      display: none;
    }
    
    .time-option label {
      display: block;
      padding: 0.75rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .time-option input[type="radio"]:checked + label {
      background: rgba(166, 74, 255, 0.2);
      border-color: #a64aff;
      color: #a64aff;
    }
    
    .time-option label i {
      display: block;
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
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

    @media (max-width: 500px) {
      .container {
        padding: 3rem 2rem;
        margin: 0.5rem auto;
      }
      .form-card {
        padding: 2rem 1rem;
        border-radius: 7px;
      }
      .form-header-flex {
        gap: 0.2rem;
        margin-bottom: 0.5rem;
      }
      .form-card h1 {
        font-size: 1rem;
      }
      .view-history-btn {
        font-size: 0.85rem;
        padding: 0.3rem 0.7rem;
      }
      .form-group label {
        font-size: 0.8rem;
      }
      .form-group select, .form-group input[type="datetime-local"] {
        font-size: 0.8rem;
        padding: 0.3rem 0.4rem;
      }
      .submit-btn, .cancel-btn {
        font-size: 0.85rem;
        padding: 0.4rem 0.7rem;
      }
      .time-options {
        flex-direction: column;
        gap: 0.3rem;
      }
      .time-option label {
        font-size: 0.8rem;
        padding: 0.3rem;
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
      <div class="form-card">
        <div class="form-header-flex">
          <h1>Log New Meal</h1>
          <a href="meal_history.php" class="view-history-btn">
            <i class="fas fa-history"></i> View Meal History
          </a>
        </div>
        <form action="save_meal.php" method="POST">
            <div class="form-group">
              <label><i class="fas fa-clock"></i> Meal Time</label>
              <div class="time-options">
                <div class="time-option">
                  <input type="radio" id="breakfast" name="meal_time" value="breakfast" checked>
                  <label for="breakfast">
                    <i class="fas fa-sun"></i>
                    Breakfast
                  </label>
                </div>
                <div class="time-option">
                  <input type="radio" id="lunch" name="meal_time" value="lunch">
                  <label for="lunch">
                    <i class="fas fa-utensils"></i>
                    Lunch
                  </label>
                </div>
                <div class="time-option">
                  <input type="radio" id="snack" name="meal_time" value="snack">
                  <label for="snack">
                    <i class="fas fa-cookie"></i>
                    Snack
                  </label>
                </div>
                <div class="time-option">
                  <input type="radio" id="dinner" name="meal_time" value="dinner">
                  <label for="dinner">
                    <i class="fas fa-moon"></i>
                    Dinner
                  </label>
                </div>
                
              </div>
            </div>
            
            <div class="form-group">
              <label for="meal"><i class="fas fa-utensils"></i> Food Item</label>
              <select name="meal_id" id="meal" required>
                <option value="">Select a food item</option>
                <?= $mealOptions ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="quantity"><i class="fas fa-scale-balanced"></i> Quantity</label>
              <select name="quantity" id="quantity" required>
                <option value="">Select quantity</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="datetime"><i class="far fa-calendar"></i> Date & Time</label>
              <input type="datetime-local" name="datetime" id="datetime" required>
            </div>
            
            <button type="submit" class="submit-btn">
              <i class="fas fa-plus-circle"></i> Add Meal
            </button>
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
      
      // Set default datetime to now
      const now = new Date();
      // Format as YYYY-MM-DDTHH:MM (datetime-local input format)
      // Adjust for timezone offset
      const timezoneOffset = now.getTimezoneOffset() * 60000;
      const localISOTime = new Date(now - timezoneOffset).toISOString().slice(0, 16);
      document.getElementById('datetime').value = localISOTime;
    });
    </script>
  <script>
    const mealSelect = document.getElementById('meal');
    const quantitySelect = document.getElementById('quantity');

    function generateQuantityOptions(unit) {
      const unitLower = unit.toLowerCase().trim();
      let options = [];

      if (unitLower.match(/^\d*\s*g$/) || unitLower.includes('gram')) {
        const baseAmount = parseInt(unitLower.match(/\d+/)?.[0]) || 100;
        options = [1, 1.5, 2, 2.5, 3, 4, 5].map(mult => ({
          value: Math.round(baseAmount * mult),
          label: `${Math.round(baseAmount * mult)}g`
        }));
      } else if (unitLower.includes('cup')) {
        options = [1, 1.5, 2, 2.5, 3].map(mult => ({
          value: mult,
          label: `${mult} cup${mult !== 1 ? 's' : ''}`
        }));
      } else if (unitLower.includes('tbsp') || unitLower.includes('tablespoon')) {
        options = [1, 1.5, 2, 2.5, 3, 4, 5].map(amt => ({
          value: amt,
          label: `${amt} tbsp`
        }));
      } else if (unitLower.includes('slice')) {
        options = [1, 2, 3, 4, 5, 6].map(count => ({
          value: count,
          label: `${count} slice${count !== 1 ? 's' : ''}`
        }));
      } else if (unitLower.includes('egg')) {
        options = [1, 2, 3, 4, 5, 6].map(count => ({
          value: count,
          label: `${count} egg${count !== 1 ? 's' : ''}`
        }));
      } else if (unitLower.match(/^\d+\s+\w+/)) {
        const match = unitLower.match(/^(\d+)\s+(\w+)/);
        if (match) {
          const base = parseInt(match[1]);
          const item = match[2];
          options = [ 1, 1.5, 2, 2.5, 3].map(mult => ({
            value: Math.round(base * mult),
            label: `${Math.round(base * mult)} ${item}${Math.round(base * mult) !== 1 ? 's' : ''}`
          }));
        }
      } else {
        options = [ 1, 1.5, 2, 2.5, 3].map(amt => ({
          value: amt,
          label: `${amt} ${unit}`
        }));
      }

      return options;
    }

    mealSelect.addEventListener('change', function () {
      const unit = this.options[this.selectedIndex].dataset.unit || '';
      quantitySelect.innerHTML = "<option value=''>-- Select quantity --</option>";

      if (unit) {
        const options = generateQuantityOptions(unit);
        options.forEach(opt => {
          const el = document.createElement('option');
          el.value = opt.value;
          el.textContent = opt.label;
          quantitySelect.appendChild(el);
        });
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