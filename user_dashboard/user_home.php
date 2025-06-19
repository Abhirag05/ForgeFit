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
     <link rel="stylesheet" href="user_home.css">
</head>
<body>
    <div id="particles-js"></div>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-dumbbell logo-icon"></i>
                <h2>ForgeFit</h2>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="user_workout.php" class="menu-item">
                    <i class="fas fa-running"></i>
                    <span>Workouts</span>
                </a>
                <a href="user_meal.php" class="menu-item">
                    <i class="fas fa-utensils"></i>
                    <span>Nutrition</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Progress</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['n'] ?? 'Champion'); ?>!</h1>
                <div class="user-profile">
                    <a href="user_profile.php"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['n'] ?? 'User'); ?>&background=random" alt="Profile"></a>
                    <span class="name"><?php echo htmlspecialchars($_SESSION['n'] ?? 'User'); ?></span>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
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
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <span class="action-label">Log Workout</span>
                </button>
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <span class="action-label">Add Meal</span>
                </button>
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