<?php
session_start();
include '../db.php';
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
            width: 250px;
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
         /* Daily Nutrients Section */
        .nutrients-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .nutrients-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        
        .nutrients-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 152, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .circular-progress {
            position: relative;
            z-index: 1;
        }
        .circular-bg {
            stroke: #fff2e0;
            opacity: 0.3;
        }
        .circular-bar {
            stroke: #ff9800;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .nutrients-title {
            flex: 1;
        }

        .nutrients-title h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .nutrients-title p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin: 0;
        }

        .nutrients-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
        }

        .btn-icon.add {
            background: rgba(255, 152, 0, 0.2);
            border-color: rgba(255, 152, 0, 0.3);
            color: #ff9800;
        }

        .btn-icon.add:hover {
            background: rgba(255, 152, 0, 0.3);
        }

        .nutrients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .nutrient-item {
            text-align: left;
        }

        .nutrient-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
        }

        .nutrient-name {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            font-weight: 500;
        }

        .nutrient-values {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }

        .nutrient-progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .nutrient-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .nutrient-fill.protein { background: linear-gradient(90deg, #e74c3c, #c0392b); }
        .nutrient-fill.carbs { background: linear-gradient(90deg, #e74c3c, #c0392b); }
        .nutrient-fill.Fat { background: linear-gradient(90deg, #e74c3c, #c0392b);}
        .nutrient-fill.fiber { background: linear-gradient(90deg, #e74c3c, #c0392b);}

        .nutrient-percentage {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
            font-weight: 500;
        }
      
    </style>
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
                        <div class="btn-icon">
                            <i class="fas fa-camera"></i>
                        </div>
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
                 <a href="user_water.php" style="text-decoration:none"> 
                <button class="action-btn">
                    <div class="action-icon">
                    <i class="fas fa-shoe-prints"></i>  
                    </div>
                    <span class="action-label">Add Steps</span>
                </button>
                </a>
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