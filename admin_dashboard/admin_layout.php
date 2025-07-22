<?php
session_start();

// Access control: Only logged-in users allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../signin.php");
    exit();
}
// Session timeout check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();     
    session_destroy();   
    header("Location:signin.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update time on activity

// Database connection
include('../db.php');

// Get statistics for dashboard
$users_count = 0;
$meals_count = 0;
$workouts_count = 0;

try {
    // User statistics
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $row = $stmt->fetch_row();
    $users_count = $row[0];
    
    // Meal statistics
    $stmt = $conn->query("SELECT COUNT(*) FROM logged_meals");
    $row = $stmt->fetch_row();
    $meals_count = $row[0];
    
    // Workout statistics
    $stmt = $conn->query("SELECT COUNT(*) FROM all_logged_workouts");
    $row = $stmt->fetch_row();
    $workouts_count = $row[0];
    
    // No active/new users stat (no created_at or last_login field)
} catch (Exception $e) {
    // Handle error
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <!-- Bootstrap and FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
     <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
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
        .main-content {
            margin-left: 250px;    /* sidebar width */
            overflow-y: auto;
            padding: 20px;
        }
    </style>
</head>
<body>
     <!-- Sidebar include -->
    <div class="sidebar-wrapper">
    <?php include './components/sidebar.php'; ?>
    </div>
    <div id="particles-js"></div>
    <!-- Common Top Navigation -->
    <?php include './components/topnavbar.php'; ?>
   
   

    <!-- Main content area -->
    <div class="main-content">
        <?php
         $page = $_GET['page'] ?? 'admin_home';
        $allowed = ['admin_home', 'user_details', 'admin_meals', 'admin_workout', 'xp-system', 'settings','admin_add_update_meal','admin_add_update_workout'];
            $pageFile = "$page.php";

            if (in_array($page, $allowed) && file_exists($pageFile)) {
                include $pageFile;
            } else {
                echo "<h2>404 - Page Not Found</h2>";
            }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
    // Particle.js config (Solo Leveling mana effect)
    particlesJS("particles-js", {
      "particles": {
        "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
        "color": { "value": "#ff4d4d" },
        "shape": { "type": "circle" },
        "opacity": { "value": 0.5, "random": true },
        "size": { "value": 3, "random": true },
        "line_linked": { "enable": true, "distance": 150, "color": "#6a00ff", "opacity": 0.2, "width": 1 },
        "move": { "enable": true, "speed": 2, "direction": "none", "random": true, "straight": false, "out_mode": "out" }
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
