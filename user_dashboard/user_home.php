<?php
// Include security headers and session validation
require_once 'security_headers.php';

include '../db.php';
// Fetch user level and experience
$user_level = 1;
$user_exp = 0;
$next_level_exp = 100;
$level_progress_percent = 0;
$level_badge = '';
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id) {
    $stmt = $conn->prepare("SELECT level, experience FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_level, $user_exp);
    $stmt->fetch();
    $stmt->close();
    // Get next level's required EXP
    $stmt = $conn->prepare("SELECT required_exp FROM levels WHERE level = ? LIMIT 1");
    $next_level = $user_level + 1;
    $stmt->bind_param("i", $next_level);
    $stmt->execute();
    $stmt->bind_result($next_level_exp);
    if (!$stmt->fetch()) {
        // Max level, set next_level_exp to current
        $next_level_exp = $user_exp;
    }
    $stmt->close();
    // Get current level's required EXP
    $stmt = $conn->prepare("SELECT required_exp FROM levels WHERE level = ? LIMIT 1");
    $stmt->bind_param("i", $user_level);
    $stmt->execute();
    $stmt->bind_result($current_level_exp);
    if (!$stmt->fetch()) {
        $current_level_exp = 0;
    }
    $stmt->close();
    // Calculate progress percent
    $exp_for_this_level = $next_level_exp - $current_level_exp;
    $exp_earned_this_level = $user_exp - $current_level_exp;
    $level_progress_percent = $exp_for_this_level > 0 ? round(($exp_earned_this_level / $exp_for_this_level) * 100) : 100;
    // Assign badge/rank based on level (example)
    if ($user_level >= 10) {
        $level_badge = 'S';
    } elseif ($user_level >= 7) {
        $level_badge = 'A';
    } elseif ($user_level >= 4) {
        $level_badge = 'B';
    } elseif ($user_level >= 2) {
        $level_badge = 'C';
    } else {
        $level_badge = 'D';
    }
}

// Additional session validation (already checked at top, but keeping for redundancy)
// Session timeout check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();     
    session_destroy();   
    header("Location: signin.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update time on activity

// Sample fitness data (in a real app, this would come from your database)
$user_id = $_SESSION['user_id'];
// Fetch user profile image
$profile_img = null;
$stmt = $conn->prepare("SELECT profile_image FROM user_fitness_profiles WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_img);
$stmt->fetch();
$stmt->close();
if (!$profile_img) {
    $profile_img = '../uploads/default-avatar.png';
} else if (strpos($profile_img, 'uploads/') === false) {
    $profile_img = '../uploads/' . $profile_img;
}
$workoutData = [
    'completed' => 0,
    'streak' => 0,
    'calories' => 0,
    'water' => 0
];
// Fetch completed workouts (this month)
$month_start = date('Y-m-01');
$stmt = $conn->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(calories_burned),0) as total_cal FROM all_logged_workouts WHERE user_id = ? AND date>= ?");
$stmt->bind_param("is", $user_id, $month_start);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$workoutData['completed'] = (int)($res['cnt'] ?? 0);
$workoutData['calories'] = (int)($res['total_cal'] ?? 0);
$stmt->close();
// Fetch current streak (consecutive workout days)
$streak = 0;
$prev = null;
$stmt = $conn->prepare("SELECT DISTINCT DATE(date) as d FROM all_logged_workouts WHERE user_id = ? ORDER BY d DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $date = $row['d'];
    if ($prev === null) {
        $streak = 1;
    } else {
        $expected = date('Y-m-d', strtotime($prev . ' -1 day'));
        if ($date == $expected) {
            $streak++;
        } else {
            break;
        }
    }
    $prev = $date;
}
$workoutData['streak'] = $streak;
$stmt->close();
// Fetch user fitness profile for nutrient calculations
$user_profile = null;
$stmt = $conn->prepare("SELECT maintenance_calories, primary_goal FROM user_fitness_profiles WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_profile = $result->fetch_assoc();
$stmt->close();

// Calculate daily nutrient goals based on fitness goal and maintenance calories
$nutrient_goals = [
    'calories' => 0,
    'protein' => 0,
    'carbs' => 0,
    'fat' => 0,
    'fiber' => 25// Standard fiber goal
];

if ($user_profile && $user_profile['maintenance_calories']) {
    $maintenance_calories = $user_profile['maintenance_calories'];
    $fitness_goal = $user_profile['primary_goal'];
    
    // Adjust calories based on fitness goal
   switch ($fitness_goal) {
    case 'weight_loss':
        $nutrient_goals['calories'] = $maintenance_calories - 500; // 500 cal deficit
        // Higher protein for muscle preservation during weight loss
        $nutrient_goals['protein'] = round(($nutrient_goals['calories'] * 0.35) / 4); // 35% of calories
        $nutrient_goals['carbs'] = round(($nutrient_goals['calories'] * 0.35) / 4); // 35% of calories
        $nutrient_goals['fat'] = round(($nutrient_goals['calories'] * 0.30) / 9); // 30% of calories
        break;
        
    case 'muscle_gain':
        $nutrient_goals['calories'] = $maintenance_calories + 300; // 300 cal surplus
        // Higher protein and carbs for muscle building
        $nutrient_goals['protein'] = round(($nutrient_goals['calories'] * 0.30) / 4); // 30% of calories
        $nutrient_goals['carbs'] = round(($nutrient_goals['calories'] * 0.45) / 4); // 45% of calories
        $nutrient_goals['fat'] = round(($nutrient_goals['calories'] * 0.25) / 9); // 25% of calories
        break;
        
    case 'endurance_training':
        $nutrient_goals['calories'] = $maintenance_calories + 200; // 200 cal surplus for training demands
        // Higher carbs for endurance performance, moderate protein
        $nutrient_goals['protein'] = round(($nutrient_goals['calories'] * 0.20) / 4); // 20% of calories
        $nutrient_goals['carbs'] = round(($nutrient_goals['calories'] * 0.60) / 4); // 60% of calories
        $nutrient_goals['fat'] = round(($nutrient_goals['calories'] * 0.20) / 9); // 20% of calories
        break;
        
    case 'sport_specific_training':
        $nutrient_goals['calories'] = $maintenance_calories + 250; // 250 cal surplus for training intensity
        // Balanced but performance-focused macros
        $nutrient_goals['protein'] = round(($nutrient_goals['calories'] * 0.25) / 4); // 25% of calories
        $nutrient_goals['carbs'] = round(($nutrient_goals['calories'] * 0.50) / 4); // 50% of calories
        $nutrient_goals['fat'] = round(($nutrient_goals['calories'] * 0.25) / 9); // 25% of calories
        break;
        
    case 'maintenance':
    default:
        $nutrient_goals['calories'] = $maintenance_calories;
        // Balanced macro distribution
        $nutrient_goals['protein'] = round(($nutrient_goals['calories'] * 0.25) / 4); // 25% of calories
        $nutrient_goals['carbs'] = round(($nutrient_goals['calories'] * 0.45) / 4); // 45% of calories
        $nutrient_goals['fat'] = round(($nutrient_goals['calories'] * 0.30) / 9); // 30% of calories
        break;
} 
} else {
    // Default values if no profile found
    $nutrient_goals['calories'] = 2000;
    $nutrient_goals['protein'] = 125;
    $nutrient_goals['carbs'] = 225;
    $nutrient_goals['fat'] = 67;
}
// Fetch today's consumed nutrients (you'll need to implement this based on your meal logging system)
$today = date('Y-m-d');
$consumed_nutrients = [
    'calories' => 0,
    'protein' => 0,
    'carbs' => 0,
    'fat' => 0,
    'fiber' => 0
];

// Sample query - adjust table names and columns based on your actual database structure
$stmt = $conn->prepare("SELECT SUM(total_calories) as total_calories, SUM(total_protein) as total_protein, SUM(total_carbs) as total_carbs, SUM(total_fat) as total_fat,SUM(total_fibre) as total_fibre FROM logged_meals WHERE user_id = ? AND DATE(date) = ?");
if ($stmt) {
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $consumed_nutrients['calories'] = (int)($row['total_calories'] ?? 0);
        $consumed_nutrients['protein'] = (int)($row['total_protein'] ?? 0);
        $consumed_nutrients['carbs'] = (int)($row['total_carbs'] ?? 0);
        $consumed_nutrients['fat'] = (int)($row['total_fat'] ?? 0);
        $consumed_nutrients['fiber'] = (int)($row['total_fibre'] ?? 0);
    }
    $stmt->close();
}

// Calculate calorie percentage for circular progress
$calorie_percentage = min(100, ($consumed_nutrients['calories'] / max(1, $nutrient_goals['calories'])) * 100);

// ===== PROGRESS SECTION DATA =====
$progress_data = [
    'daily_workouts' => ['current' => 0, 'goal' => 1, 'percentage' => 0], // 1 workout per day goal
    'daily_calories' => ['current' => 0, 'goal' => 0, 'percentage' => 0],
    'daily_water' => ['current' => 0, 'goal' => 8, 'percentage' => 0], // 8 glasses of water per day
    'workout_consistency' => ['current' => 0, 'goal' => 7, 'percentage' => 0] // days with workouts in last week
];

// 1. Daily Workout Goal (today)
$stmt = $conn->prepare("SELECT COUNT(*) as workout_count FROM all_logged_workouts WHERE user_id = ? AND DATE(date) = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$progress_data['daily_workouts']['current'] = (int)($result['workout_count'] ?? 0);
$progress_data['daily_workouts']['percentage'] = min(100, ($progress_data['daily_workouts']['current'] / $progress_data['daily_workouts']['goal']) * 100);
$stmt->close();

// 2. Daily Calorie Goal (today)
$progress_data['daily_calories']['current'] = $consumed_nutrients['calories'];
$progress_data['daily_calories']['goal'] = $nutrient_goals['calories'];
$progress_data['daily_calories']['percentage'] = min(100, ($consumed_nutrients['calories'] / max(1, $nutrient_goals['calories'])) * 100);

// 3. Daily Water Intake (today)
$stmt = $conn->prepare("SELECT COALESCE(SUM(glasses_count), 0) as total_glasses FROM user_water_intake WHERE user_id = ? AND date_recorded = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$progress_data['daily_water']['current'] = (int)($result['total_glasses'] ?? 0);
$stmt->close();
$progress_data['daily_water']['percentage'] = min(100, ($progress_data['daily_water']['current'] / $progress_data['daily_water']['goal']) * 100);

// 4. Workout Consistency (last 7 days)
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$stmt = $conn->prepare("SELECT COUNT(DISTINCT DATE(date)) as consistent_days FROM all_logged_workouts WHERE user_id = ? AND date >= ?");
$stmt->bind_param("is", $user_id, $seven_days_ago);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$progress_data['workout_consistency']['current'] = (int)($result['consistent_days'] ?? 0);
$progress_data['workout_consistency']['percentage'] = min(100, ($progress_data['workout_consistency']['current'] / $progress_data['workout_consistency']['goal']) * 100);
$stmt->close();
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
     <Link rel="stylesheet" href="user_home.css">
</head>
<body>
    <div id="particles-js"></div>
     <?php include('components/user_navbar.php'); ?>
    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['n'] ?? 'Champion'); ?>!</h1>
            </div>
            <!-- Level & EXP Section -->
            <div class="level-exp-section">
                <div class="level-badge-container">
                    <div class="level-badge level-badge-<?php echo strtolower($level_badge); ?>">
                        <span class="level-badge-rank"><?php echo $level_badge; ?></span>
                        <span class="level-number">Lv <?php echo $user_level; ?></span>
                    </div>
                </div>
                <div class="exp-progress-bar-container">
                    <div class="exp-labels">
                        <span>EXP</span>
                        <span><?php echo $user_exp; ?> / <?php echo $next_level_exp; ?></span>
                    </div>
                    <div class="exp-progress-bar">
                        <div class="exp-progress-fill" style="width: <?php echo $level_progress_percent; ?>%"></div>
                    </div>
                    <div class="exp-progress-percent"><?php echo $level_progress_percent; ?>%</div>
                </div>
            </div>
            <!-- Daily Nutrients Section -->
            <div class="nutrients-section">
                <div class="nutrients-header">
                    <div class="nutrients-icon">
                        <svg class="circular-progress" width="60" height="60">
                            <circle class="circular-bg" cx="30" cy="30" r="26" stroke-width="6" fill="none" />
                            <circle
                                class="circular-bar"
                                cx="30"
                                cy="30"
                                r="26"
                                stroke-width="3"
                                fill="none"
                                stroke-dasharray="<?php echo 2 * M_PI * 26; ?>"
                                stroke-dashoffset="<?php echo (1 - $calorie_percentage / 100) * (2 * M_PI * 26); ?>"
                            />
                        </svg>
                        <i class="fas fa-utensils" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);font-size:1.2rem;color:#ff9800;"></i>
                    </div>
                    <div class="nutrients-title">
                        <h2>Nutrients Goal</h2>
                        <p><?php echo number_format($consumed_nutrients['calories']); ?>/<?php echo number_format($nutrient_goals['calories']); ?> cal (<?php echo round($calorie_percentage); ?>%)</p>
                    </div>
                    <div class="nutrients-actions">
                        <a href="user_meal.php" style="text-decoration: none;">
                            <div class="btn-icon add">
                                <i class="fas fa-plus"></i>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="nutrients-grid">
                    <div class="nutrient-item">
                        <div class="nutrient-label">
                            <span class="nutrient-name">Protein:</span>
                            <span class="nutrient-values"><?php echo $consumed_nutrients['protein']; ?>/<?php echo $nutrient_goals['protein']; ?>g</span>
                        </div>
                        <div class="nutrient-progress">
                            <div class="nutrient-fill protein" style="width: <?php echo min(100, ($consumed_nutrients['protein'] / max(1, $nutrient_goals['protein'])) * 100); ?>%"></div>
                        </div>
                        <div class="nutrient-percentage"><?php echo round(($consumed_nutrients['protein'] / max(1, $nutrient_goals['protein'])) * 100); ?>%</div>
                    </div>
                    
                    <div class="nutrient-item">
                        <div class="nutrient-label">
                            <span class="nutrient-name">Carb:</span>
                            <span class="nutrient-values"><?php echo $consumed_nutrients['carbs']; ?>/<?php echo $nutrient_goals['carbs']; ?>g</span>
                        </div>
                        <div class="nutrient-progress">
                            <div class="nutrient-fill carbs" style="width: <?php echo min(100, ($consumed_nutrients['carbs'] / max(1, $nutrient_goals['carbs'])) * 100); ?>%"></div>
                        </div>
                        <div class="nutrient-percentage"><?php echo round(($consumed_nutrients['carbs'] / max(1, $nutrient_goals['carbs'])) * 100); ?>%</div>
                    </div>
                    
                    <div class="nutrient-item">
                        <div class="nutrient-label">
                            <span class="nutrient-name">Fat:</span>
                            <span class="nutrient-values"><?php echo $consumed_nutrients['fat']; ?>/<?php echo $nutrient_goals['fat']; ?>g</span>
                        </div>
                        <div class="nutrient-progress">
                            <div class="nutrient-fill Fat" style="width: <?php echo min(100, ($consumed_nutrients['fat'] / max(1, $nutrient_goals['fat'])) * 100); ?>%"></div>
                        </div>
                        <div class="nutrient-percentage"><?php echo round(($consumed_nutrients['fat'] / max(1, $nutrient_goals['fat'])) * 100); ?>%</div>
                    </div>
                    
                    <div class="nutrient-item">
                        <div class="nutrient-label">
                            <span class="nutrient-name">Fibre:</span>
                            <span class="nutrient-values"><?php echo $consumed_nutrients['fiber']; ?>/<?php echo $nutrient_goals['fiber']; ?>g</span>
                        </div>
                        <div class="nutrient-progress">
                            <div class="nutrient-fill fiber" style="width: <?php echo min(100, ($consumed_nutrients['fiber'] / max(1, $nutrient_goals['fiber'])) * 100); ?>%"></div>
                        </div>
                        <div class="nutrient-percentage"><?php echo round(($consumed_nutrients['fiber'] / max(1, $nutrient_goals['fiber'])) * 100); ?>%</div>
                    </div>
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
                    <div class="stat-card-description">This month</div>
                    <i class="fas fa-burn stat-card-bg"></i>
                </div>
                
            </div>

            <!-- Progress Section -->
            <div class="progress-section">
                <div class="section-header">
                    <h2 class="section-title">Your Progress</h2>
                    <a href="#" style="color: #a64aff; text-decoration: none; font-weight: 500; font-size: 0.9rem;">View Details</a>
                </div>
                <div class="progress-container">
                    <div class="progress-item" data-type="workout" data-status="<?php echo $progress_data['daily_workouts']['percentage'] >= 80 ? 'excellent' : ($progress_data['daily_workouts']['percentage'] >= 60 ? 'good' : ($progress_data['daily_workouts']['percentage'] >= 40 ? 'warning' : 'poor')); ?>">
                        <div class="progress-label">
                            <span>Daily Workout Goal</span>
                            <span><?php echo $progress_data['daily_workouts']['current']; ?>/<?php echo $progress_data['daily_workouts']['goal']; ?> workout</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress_data['daily_workouts']['percentage']; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item" data-type="calories" data-status="<?php echo $progress_data['daily_calories']['percentage'] >= 80 ? 'excellent' : ($progress_data['daily_calories']['percentage'] >= 60 ? 'good' : ($progress_data['daily_calories']['percentage'] >= 40 ? 'warning' : 'poor')); ?>">
                        <div class="progress-label">
                            <span>Daily Calorie Target</span>
                            <span><?php echo number_format($progress_data['daily_calories']['current']); ?>/<?php echo number_format($progress_data['daily_calories']['goal']); ?> cal</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress_data['daily_calories']['percentage']; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item" data-type="water" data-status="<?php echo $progress_data['daily_water']['percentage'] >= 80 ? 'excellent' : ($progress_data['daily_water']['percentage'] >= 60 ? 'good' : ($progress_data['daily_water']['percentage'] >= 40 ? 'warning' : 'poor')); ?>">
                        <div class="progress-label">
                            <span>Daily Water Intake</span>
                            <span><?php echo $progress_data['daily_water']['current']; ?>/<?php echo $progress_data['daily_water']['goal']; ?> glasses</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress_data['daily_water']['percentage']; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item" data-type="consistency" data-status="<?php echo $progress_data['workout_consistency']['percentage'] >= 80 ? 'excellent' : ($progress_data['workout_consistency']['percentage'] >= 60 ? 'good' : ($progress_data['workout_consistency']['percentage'] >= 40 ? 'warning' : 'poor')); ?>">
                        <div class="progress-label">
                            <span>Workout Consistency</span>
                            <span><?php echo $progress_data['workout_consistency']['current']; ?>/<?php echo $progress_data['workout_consistency']['goal']; ?> days</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress_data['workout_consistency']['percentage']; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
               <a href="user_workout.php" style="text-decoration:none">
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <span class="action-label">Log Workout</span>
                </button></a>
              <a href="user_meal.php" style="text-decoration:none"> <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <span class="action-label">Add Meal</span>
                </button> </a> 
                <a href="user_water.php" style="text-decoration:none">      
                <button class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <span class="action-label">Water Intake</span>
                </button>
                </a>
            </div>
        </main>
    </div>
    <script src="prevent_back_button.js"></script>
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