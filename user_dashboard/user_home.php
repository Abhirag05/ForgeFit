<?php
session_start();

// Access control: Only logged-in users allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../signin.php");
    exit();
}
// Session timeout check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();     
    session_destroy();   
    header("Location: signin.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update time on activity

// Sample fitness data (in a real app, this would come from your database)
$workoutData = [
    'completed' => 18,
    'streak' => 5,
    'calories' => 12450,
    'water' => 28
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ForgeFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h1 {
            color: white;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-card-title {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-card-icon.primary { background: rgba(106, 0, 255, 0.2); color: #a64aff; }
        .stat-card-icon.success { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .stat-card-icon.warning { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .stat-card-icon.water { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }

        .stat-card-value {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }

        .stat-card-bg {
            position: absolute;
            bottom: -10px;
            right: -10px;
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.05);
        }

        /* Progress Section */
        .progress-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .progress-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .progress-item {
            margin-bottom: 1.5rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
        }

        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #a64aff, #6a00ff);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(166, 74, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #a64aff;
        }

        .action-label {
            font-weight: 500;
            font-size: 0.9rem;
        }

      
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="dashboard-container">
        <!-- Horizontal Navbar -->
        <nav class="navbar">
            <a href="#" class="navbar-brand">
                <i class="fas fa-dumbbell logo-icon"></i>
                <h2>ForgeFit</h2>
            </a>
            <ul class="navbar-menu">
                <li><a href="#" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a></li>
                <li><a href="user_workout.php" class="menu-item">
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['n'] ?? 'Champion'); ?>!</h1>
            </div>

            <!-- Fitness Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Workouts Completed</span>
                        <div class="stat-card-icon primary">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $workoutData['completed']; ?></div>
                    <div class="stat-card-description">This month</div>
                    <i class="fas fa-fire stat-card-bg"></i>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Current Streak</span>
                        <div class="stat-card-icon success">
                            <i class="fas fa-bolt"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $workoutData['streak']; ?> days</div>
                    <div class="stat-card-description">Keep it going!</div>
                    <i class="fas fa-bolt stat-card-bg"></i>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Calories Burned</span>
                        <div class="stat-card-icon warning">
                            <i class="fas fa-burn"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($workoutData['calories']); ?></div>
                    <div class="stat-card-description">Total calories</div>
                    <i class="fas fa-burn stat-card-bg"></i>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Water Intake</span>
                        <div class="stat-card-icon water">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $workoutData['water']; ?>L</div>
                    <div class="stat-card-description">This week</div>
                    <i class="fas fa-tint stat-card-bg"></i>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="progress-section">
                <div class="section-header">
                    <h2 class="section-title">Your Progress</h2>
                    <a href="#" style="color: var(--primary); text-decoration: none;">View Details</a>
                </div>
                <div class="progress-container">
                    <div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Weekly Goal</span>
                                <span>3/5 workouts</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Protein Intake</span>
                                <span>120/150g</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Sleep Quality</span>
                                <span>6.5/8 hours</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 81%"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Steps</span>
                                <span>8,500/10,000</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
               <a href="user_workout.php">
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <span class="action-label">Log Workout</span>
                </button></a>
              <a href="user_meal.php"> <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <span class="action-label">Add Meal</span>
                </button> </a> 
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <span class="action-label">Water Intake</span>
                </button>
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-weight"></i>
                    </div>
                    <span class="action-label">Record Weight</span>
                </button>
            </div>
        </main>
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
</body>
</html>