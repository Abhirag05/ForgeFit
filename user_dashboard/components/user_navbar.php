<?php
include '../db.php';
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch user profile image
$profile_img = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT profile_image FROM user_fitness_profiles WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($profile_img);
    $stmt->fetch();
    $stmt->close();
}
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
  <title>ForgeFit</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: "Poppins", sans-serif;
      background: #111;
    }

    /* ===== Navbar ===== */
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
      cursor: pointer;
      pointer-events: auto;
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

    /* ===== Logout Modal ===== */
    .logout-modal {
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.4);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .logout-modal-content {
      background: #222;
      color: #fff;
      border-radius: 12px;
      padding: 2rem 2.5rem 1.5rem 2.5rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.25);
      min-width: 300px;
      max-width: 90vw;
      text-align: center;
      animation: fadeIn 0.2s;
    }
    .logout-modal-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: block;
    }
    .logout-modal-actions {
      margin-top: 1.5rem;
      display: flex;
      gap: 1rem;
      justify-content: center;
    }
    .logout-modal-confirm {
      background: #a64aff;
      color: #fff;
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .logout-modal-confirm:hover {
      background: #7a2fd7;
    }
    .logout-modal-cancel {
      background: #fff;
      color: #222;
      border: 1px solid #ccc;
      padding: 0.5rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.2s;
    }
    .logout-modal-cancel:hover {
      background: #eee;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.98); }
      to { opacity: 1; transform: scale(1); }
    }

    /* ===== Hamburger ===== */
    .hamburger {
      display: none;
      font-size: 1.5rem;
      color: white;
      background: none;
      border: none;
      cursor: pointer;
    }

    /* ===== Sidebar ===== */
    .sidebar {
      position: fixed;
      top: 0;
      left: -250px;
      width: 250px;
      height: 100%;
      background: transparent; /* match navbar background */
      backdrop-filter: blur(10px);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      padding: 2rem 1rem;
      transition: left 0.3s ease;
      z-index: 2000;
    }

    .sidebar.show {
      left: 0;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
    }

    .sidebar-menu li {
      margin: 1rem 0;
    }

    .sidebar-menu a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      transition: all 0.3s ease;
      background: none;
    }
    .sidebar-menu a:hover {
      color: white;
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }
    .sidebar-menu a.active, .sidebar-menu a[aria-current="page"] {
      color: #a64aff;
      background: rgba(166, 74, 255, 0.1);
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
      .navbar {
        justify-content: center;
        align-items: center;
        position: sticky;
        min-height: 64px;
        height: 64px;
        padding-left: 0;
        padding-right: 0;
      }
      .navbar-menu {
        display: none;
      }
      .hamburger {
        display: block;
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
      }
      .navbar-brand {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        margin: 0 auto;
      }
      .user-profile {
        display: flex;
        align-items: center;
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        gap: 0.5rem;
      }
      .user-profile img {
        width: 32px;
        height: 32px;
      }
      .user-profile .name {
        display: none;
      }
      .navbar-brand {
        max-width: 60vw;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 0 1rem;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <a href="user_home.php" class="navbar-brand">
    <img src="/ForgeFit_mini_project/assets/images/logo1.png" alt="ForgeFit Logo" style="height:44px; width:auto; display:block; max-width:180px; object-fit:contain;" />
    </a>

    <!-- Desktop Menu -->
    <ul class="navbar-menu">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <li><a href="user_home.php" class="menu-item<?php if($current == 'user_home.php') echo ' active'; ?>"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
      <li><a href="user_workout.php" class="menu-item<?php if($current == 'user_workout.php') echo ' active'; ?>"><i class="fas fa-running"></i> <span>Workouts</span></a></li>
      <li><a href="user_meal.php" class="menu-item<?php if($current == 'user_meal.php') echo ' active'; ?>"><i class="fas fa-utensils"></i> <span>Nutrition</span></a></li>
      <li><a href="chat_bot.php" class="menu-item<?php if($current == 'chat_bot.php') echo ' active'; ?>"><i class="fas fa-crown"></i> <span>Chat Bot</span></a></li>
    </ul>

    <!-- Hamburger (Mobile) -->
    <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>

    <!-- User Profile -->
    <div class="user-profile">
      <a href="user_profile.php"><img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile"></a>
      <span class="name"><?php echo htmlspecialchars($_SESSION['n'] ?? 'User'); ?></span>
      <a href="#" class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </nav>

  <!-- Sidebar (Mobile) -->
  <div class="sidebar" id="sidebar">
    <button class="sidebar-close" id="sidebarClose" aria-label="Close Sidebar" style="position:absolute;top:1rem;right:1rem;background:none;border:none;color:white;font-size:1.5rem;z-index:2100;cursor:pointer;"><i class="fas fa-times"></i></button>
    <ul class="sidebar-menu">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <li><a href="user_home.php" class="<?php if($current == 'user_home.php') echo 'active'; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="user_workout.php" class="<?php if($current == 'user_workout.php') echo 'active'; ?>"><i class="fas fa-running"></i> Workouts</a></li>
      <li><a href="user_meal.php" class="<?php if($current == 'user_meal.php') echo 'active'; ?>"><i class="fas fa-utensils"></i> Nutrition</a></li>
      <li><a href="chat_bot.php" class="<?php if($current == 'chat_bot.php') echo 'active'; ?>"><i class="fas fa-crown"></i> Chat Bot</a></li>
      <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
    </ul>
  </div>

  <!-- Logout Modal -->
  <div id="logoutModal" class="logout-modal" style="display:none;">
    <div class="logout-modal-content">
      <span class="logout-modal-title">Confirm Logout</span>
      <p>Are you sure you want to logout?</p>
      <div class="logout-modal-actions">
        <button id="confirmLogout" class="logout-modal-confirm">Logout</button>
        <button id="cancelLogout" class="logout-modal-cancel">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Sidebar toggle
      const hamburger = document.getElementById('hamburger');
      const sidebar = document.getElementById('sidebar');
      if (hamburger && sidebar) {
        hamburger.addEventListener('click', function(e) {
          e.stopPropagation();
          sidebar.classList.toggle('show');
        });
        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
          if (sidebar.classList.contains('show')) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isHamburger = hamburger.contains(event.target);
            if (!isClickInsideSidebar && !isHamburger) {
              sidebar.classList.remove('show');
            }
          }
        });
        // Close sidebar with close button
        const sidebarClose = document.getElementById('sidebarClose');
        if (sidebarClose) {
          sidebarClose.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.remove('show');
          });
        }
      }

      // Logout modal
      var logoutBtn = document.getElementById('logoutBtn');
      var logoutModal = document.getElementById('logoutModal');
      var confirmLogout = document.getElementById('confirmLogout');
      var cancelLogout = document.getElementById('cancelLogout');
      if (logoutBtn && logoutModal && confirmLogout && cancelLogout) {
        logoutBtn.addEventListener('click', function(e) {
          e.preventDefault();
          logoutModal.style.display = 'flex';
        });
        confirmLogout.addEventListener('click', function() {
          window.location.href = '../logout.php';
        });
        cancelLogout.addEventListener('click', function() {
          logoutModal.style.display = 'none';
        });
        logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
            logoutModal.style.display = 'none';
          }
        });
      }
    });
  </script>
</body>
</html>
