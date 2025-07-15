<style>
       /* wrapper for the sidebar so it doesn’t overlap the topnav */
        .sidebar-wrapper {
            position: fixed;
            top: 72px;            /* height of #topnavbar  */
            bottom: 0;
            left: 0;
            width: 250px;
            z-index: 1040;       /* above particles & content */
            overflow-y: auto;
        }

        /* the actual sidebar */
        .sidebar {
            height: 100%;
            background: rgba(255,255,255,0.13);
            backdrop-filter: blur(8px);
            padding-top: 1rem;
            box-shadow: 2px 0 18px rgba(0,0,0,0.18);
        }
        .sidebar {
                    background: rgba(255,255,255,0.13) !important;
                    color: #fff;
                    height: 100vh;
                    position: fixed;
                    padding-top: 20px;
                    box-shadow: 2px 0 18px rgba(0,0,0,0.18);
                    backdrop-filter: blur(8px);
                }
                
                .sidebar .nav-link {
                    color: #fff;
                    margin-bottom: 5px;
                    border-radius: 5px;
                    padding: 10px 15px;
                    font-weight: 500;
                    letter-spacing: 0.5px;
                    transition: background 0.2s, color 0.2s;
                }
                
                .sidebar .nav-link:hover, .sidebar .nav-link.active {
                    background: rgba(106, 0, 255, 0.18);
                    color: #a64aff;
                }
                
                .sidebar .nav-link i {
                    margin-right: 10px;
                    width: 20px;
                    text-align: center;
                }
                /* Unique accent for sidebar heading */
                .sidebar h4 {
                    font-weight: 700;
                    letter-spacing: 1.5px;
                    color: #a64aff;
                    text-shadow: 0 2px 8px rgba(166,74,255,0.18);
                }

                .main-content {
                    margin-left: 250px;
                    padding: 20px;
                    padding-top: 92px; /* 72px navbar height + 20px padding */
                }
</style>
   
   
   <!-- Sidebar -->
        <div class="sidebar d-none d-md-block col-md-3 col-lg-2"> 
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=admin_home">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=user_details">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=admin_meals">
                        <i class="fas fa-utensils"></i> Meals
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=workouts">
                        <i class="fas fa-dumbbell"></i> Workouts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=nutrition">
                        <i class="fas fa-apple-alt"></i> Nutrition Plans
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=xp-system">
                        <i class="fas fa-trophy"></i> XP System
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_layout.php?page=settings">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>