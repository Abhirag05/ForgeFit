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
// Fetch logged meal history
$user_id = $_SESSION['user_id'];
$historyQuery = "SELECT lm.date, m.food_name, m.unit, lm.quantity, lm.total_calories, lm.total_protein, lm.total_carbs, lm.total_fat 
                 FROM logged_meals lm 
                 JOIN meals m ON lm.meal_id = m.meal_id 
                 WHERE lm.user_id = $user_id 
                 ORDER BY lm.date DESC";
$historyResult = mysqli_query($conn, $historyQuery);
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
    .navbar {
            background: transparent;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
    }
    .navbar-brand {
      display: flex;
      align-items: center;
      color: white;
      text-decoration: none;
      gap: 10px;
    }
    .logo-icon {
      font-size: 1.5rem;
      color: #a64aff;
    }
    .navbar-brand h2 {
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
    }
    .navbar-menu {
      display: flex;
      list-style: none;
      gap: 2rem;
    }
    .menu-item {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    .menu-item:hover {
      color: white;
      background: rgba(255, 255, 255, 0.1);
    }
    .menu-item.active {
      color: #a64aff;
      background: rgba(166, 74, 255, 0.1);
    }
    .user-profile {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .user-profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }
    .logout-btn {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      padding: 0.5rem;
      border-radius: 8px;
    }
    .logout-btn:hover {
      color: #ff6b6b;
      background: rgba(255, 107, 107, 0.1);
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
    }
    
    .form-card h1 {
      color: #fff;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      text-align: left;
      position: relative;
      padding-bottom: 0.5rem;
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
    .form-group input[type="date"] {
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
    .form-group input[type="date"]:focus {
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
    }
    
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(166, 74, 255, 0.4);
    }
    .history-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }
    
    .history-card h2 {
      color: #fff;
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
      position: relative;
      padding-bottom: 0.5rem;
    }
    
    .history-card h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, #a64aff, #6a00ff);
      border-radius: 3px;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      border-radius: 12px;
      overflow: hidden;
    }
    
    thead {
      background: linear-gradient(135deg, rgba(166, 74, 255, 0.2), rgba(106, 0, 255, 0.2));
    }
    
    th {
      padding: 1rem;
      text-align: left;
      color: #fff;
      font-weight: 500;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    td {
      padding: 1rem;
      color: rgba(255, 255, 255, 0.8);
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      font-size: 0.9rem;
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover {
      background: rgba(255, 255, 255, 0.03);
    }
    
    .empty-state {
      text-align: center;
      padding: 2rem;
      color: rgba(255, 255, 255, 0.5);
    }
    
    .nutrition-highlight {
      font-weight: 600;
      color: #a64aff;
    }
    
  </style>
</head>
<body>
   <div id="particles-js"></div>
  <div class="dashboard-container">
    <nav class="navbar">
      <a href="#" class="navbar-brand">
        <i class="fas fa-dumbbell logo-icon"></i>
        <h2>ForgeFit</h2>
      </a>
      <ul class="navbar-menu">
        <li><a href="user_home.php" class="menu-item"><i class="fas fa-home"></i>Dashboard</a></li>
        <li><a href="user_workout.php" class="menu-item"><i class="fas fa-running"></i>Workouts</a></li>
        <li><a href="user_meal.php" class="menu-item active"><i class="fas fa-utensils"></i>Nutrition</a></li>
        <li><a href="#" class="menu-item"><i class="fas fa-chart-line"></i>Progress</a></li>
        <li><a href="#" class="menu-item"><i class="fas fa-cog"></i>Settings</a></li>
      </ul>
      <div class="user-profile">
                <a href="user_profile.php"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['n'] ?? 'User'); ?>&background=random" alt="Profile"></a>
                <span class="name"><?php echo htmlspecialchars($_SESSION['n'] ?? 'User'); ?></span>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
      </div>
    </nav>

    <div class="container">
      <div class="form-card">
          <h1>Log New Meal</h1>
          <form action="save_meal.php" method="POST">
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
              <label for="date"><i class="far fa-calendar"></i> Date</label>
              <input type="date" name="date" id="date" required>
            </div>
            <button type="submit" class="submit-btn">
              <i class="fas fa-plus-circle"></i> Add Meal
            </button>
          </form>
        </div>
    </div>
     <!-- Meal History -->
     <div class="history-card">
          <h2>Recent Meal History</h2>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Food</th>
                  <th>Qty</th>
                  <th>Calories</th>
                  <th>Protein</th>
                  <th>Carbs</th>
                  <th>Fat</th>
                </tr>
              </thead>
              <tbody>
              <?php if (mysqli_num_rows($historyResult) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($historyResult)): ?>
                  <tr>
                    <td><?= date("M j", strtotime($row['date'])) ?></td>
                    <td><?= htmlspecialchars($row['food_name']) ?> <span class="unit">(<?= $row['unit'] ?>)</span></td>
                    <td><?= $row['quantity'] ?></td>
                    <td class="nutrition-highlight"><?= round($row['total_calories'], 1) ?></td>
                    <td><?= round($row['total_protein'], 1) ?>g</td>
                    <td><?= round($row['total_carbs'], 1) ?>g</td>
                    <td><?= round($row['total_fat'], 1) ?>g</td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="empty-state">
                    <i class="far fa-clipboard" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>No meals logged yet. Start tracking your nutrition!</p>
                  </td>
                </tr>
              <?php endif; ?>
              </tbody>
            </table>
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
    });
    </script>
  <script>
    document.getElementById('date').value = new Date().toISOString().split('T')[0];

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
</body>
</html>
