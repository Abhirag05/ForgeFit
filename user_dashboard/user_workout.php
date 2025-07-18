<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch workouts from DB
$workoutOptions = "";
$sql = "SELECT id, name FROM all_workouts";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $workoutOptions .= "<option value='{$row['id']}'>{$row['name']}</option>";
}

// Fetch workout history
$user_id = $_SESSION['user_id'];
$query = "SELECT lw.date, w.name AS workout, lw.sets, lw.reps, lw.weight, lw.calories_burned
          FROM all_logged_workouts lw
          JOIN all_workouts w ON lw.workout_id = w.id
          WHERE lw.user_id = $user_id
          ORDER BY lw.date DESC";
$historyResult = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Log | ForgeFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .dashboard-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Horizontal Navbar Styles */
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
            gap: 10px;
            color: white;
            text-decoration: none;
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
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .menu-item {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .menu-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .menu-item.active {
            color: #a64aff;
            background: rgba(166, 74, 255, 0.1);
        }

        .menu-item i {
            font-size: 1rem;
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
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-profile .name {
            color: white;
            font-weight: 500;
            display: none;
        }

        .logout-btn {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
        }

        :root {
            --primary: #ff4d4d;
            --secondary: #6a00ff;
            --dark: #0a0a12;
            --light: #f0f0f0;
            --accent: #00f0ff;
            --card-bg: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 77, 77, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1, h2 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        h1 {
            font-size: 2.5rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            display: inline-block;
        }

        h2 {
            font-size: 2rem;
            margin-top: 40px;
        }

        .log-form {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--accent);
        }

        select, input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: all 0.3s;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 77, 77, 0.3);
        }

        .btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 77, 77, 0.4);
        }

        /* Modern Workout History Styles */
        .history-section {
            margin-top: 3rem;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .history-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .workout-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }

        .workout-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .workout-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
        }

        .workout-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 77, 77, 0.2);
            border-color: var(--primary);
        }

        .workout-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .workout-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 0.25rem;
        }

        .workout-date {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }

        .workout-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .workout-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-item-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.25rem;
        }

        .stat-item-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .calories-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .log-form {
                padding: 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }

            .workout-grid {
                grid-template-columns: 1fr;
            }

            .history-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-controls {
                flex-wrap: wrap;
            }

            .navbar-menu {
                display: none;
            }

            .user-profile .name {
                display: none;
            }
        }

        @media (min-width: 769px) {
            .user-profile .name {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="dashboard-container">
        <!-- Horizontal Navbar -->
        <nav class="navbar">
            <a href="user_home.php" class="navbar-brand">
                <i class="fas fa-dumbbell logo-icon"></i>
                <h2>ForgeFit</h2>
            </a>
            <ul class="navbar-menu">
                <li><a href="user_home.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a></li>
                <li><a href="user_workout.php" class="menu-item active">
                    <i class="fas fa-running"></i>
                    <span>Workouts</span>
                </a></li>
                <li><a href="user_meal.php" class="menu-item">
                    <i class="fas fa-utensils"></i>
                    <span>Nutrition</span>
                </a></li>
                <li><a href="#" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Progress</span>
                </a></li>
                <li><a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a></li>
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
            <h1>Workout Log</h1>
            
            <div class="log-form">
                <form action="save_workout.php" method="POST">
                    <div class="form-group">
                        <label for="workout">Workout Type</label>
                        <select name="workout_id" id="workout" required>
                            <option value="">-- Select Workout --</option>
                            <?= $workoutOptions ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label for="sets">Sets</label>
                            <input type="number" name="sets" id="sets" min="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reps">Avg Reps</label>
                            <input type="number" name="reps" id="reps" min="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="weight">Weight Lifted (kg)</label>
                            <input type="number" name="weight" id="weight" min="0" step="0.1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="time_per_rep">Time per Rep (seconds)</label>
                            <select name="time_per_rep" id="time_per_rep" required>
                                <option value="3">3 sec - Fast</option>
                                <option value="4" selected>4 sec - Moderate</option>
                                <option value="5">5 sec - Controlled</option>
                                <option value="6">6 sec - Slow/Heavy</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save Workout
                    </button>
                </form>
            </div>

            <!-- Modern Workout History Section -->
            <div class="history-section">
                <div class="history-header">
                    <h2>Your Workout History</h2>
                </div>

                <?php if (mysqli_num_rows($historyResult) > 0): ?>
                    <?php
                    // Calculate stats
                    $totalWorkouts = mysqli_num_rows($historyResult);
                    $totalCalories = 0;
                    $totalWeight = 0;
                    $workouts = [];
                    
                    mysqli_data_seek($historyResult, 0);
                    while ($log = mysqli_fetch_assoc($historyResult)) {
                        $workouts[] = $log;
                        $totalCalories += $log['calories_burned'];
                    }
                    ?>

                    <!-- Stats Cards -->
                    <div class="history-stats">
                        <div class="stat-card">
                            <div class="stat-value"><?= $totalWorkouts ?></div>
                            <div class="stat-label">Total Workouts</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?= number_format($totalCalories) ?></div>
                            <div class="stat-label">Calories Burned</div>
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="filter-controls">
                        <button class="filter-btn active" onclick="filterWorkouts('all')">All Workouts</button>
                        <button class="filter-btn" onclick="filterWorkouts('week')">This Week</button>
                        <button class="filter-btn" onclick="filterWorkouts('month')">This Month</button>
                    </div>

                    <!-- Workout Cards -->
                    <div class="workout-grid">
                        <?php foreach ($workouts as $log): ?>
                            <div class="workout-card" data-date="<?= $log['date'] ?>">
                                <div class="calories-badge">
                                    <i class="fas fa-fire"></i>
                                    <?= $log['calories_burned'] ?> kcal
                                </div>
                                
                                <div class="workout-header">
                                    <div>
                                        <div class="workout-name"><?= htmlspecialchars($log['workout']) ?></div>
                                        <div class="workout-date"><?= date('M j, Y', strtotime($log['date'])) ?></div>
                                    </div>
                                    <div class="workout-icon">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                </div>

                                <div class="workout-stats">
                                    <div class="stat-item">
                                        <div class="stat-item-value"><?= $log['sets'] ?></div>
                                        <div class="stat-item-label">Sets</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-item-value"><?= $log['reps'] ?></div>
                                        <div class="stat-item-label">Reps</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-item-value"><?= $log['weight'] ?> kg</div>
                                        <div class="stat-item-label">Weight</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-item-value"><?= $log['sets'] * $log['reps'] * $log['weight'] ?> kg</div>
                                        <div class="stat-item-label">Total Volume</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-dumbbell"></i>
                        <h3>No Workouts Yet</h3>
                        <p>Start logging your workouts to see your progress here!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Set default date to today
        document.getElementById('date').valueAsDate = new Date();
        
        // Animate workout cards on load
        const workoutCards = document.querySelectorAll('.workout-card');
        workoutCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease ${index * 0.1}s`;
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });

        // Filter functionality
        function filterWorkouts(period) {
            const cards = document.querySelectorAll('.workout-card');
            const buttons = document.querySelectorAll('.filter-btn');
            const now = new Date();
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                const cardDate = new Date(card.dataset.date);
                let showCard = true;
                
                if (period === 'week') {
                    const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                    showCard = cardDate >= weekAgo;
                } else if (period === 'month') {
                    const monthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate());
                    showCard = cardDate >= monthAgo;
                }
                
                if (showCard) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        }
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