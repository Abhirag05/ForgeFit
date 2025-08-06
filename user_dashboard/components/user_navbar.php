<?php
include '../db.php';
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
?>
 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Document</title>
    <style>
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

 </style>
 </head>
 <body>
    <nav class="navbar">
            <a href="user_home.php" class="navbar-brand">
                <i class="fas fa-dumbbell logo-icon"></i>
                <h2>ForgeFit</h2>
            </a>
            <ul class="navbar-menu">
                <li><a href="user_home.php" class="menu-item active">
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
                <li><a href="chat_bot.php" class="menu-item">
                <i class="fas fa-crown"></i>
                    <span>Chat Bot</span>
                </a></li>
                <li><a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a></li>
            </ul>
            <div class="user-profile">
                <a href="user_profile.php"><img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile"></a>
                <span class="name"><?php echo htmlspecialchars($_SESSION['n'] ?? 'User'); ?></span>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </nav>
 </body>
 </html>
 
 