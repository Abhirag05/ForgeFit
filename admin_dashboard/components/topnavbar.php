<!-- Top Navigation (COMMON) -->
<style>
    /* ---------- Top-bar styles ---------- */
.navbar {
    background: rgba(255,255,255,0.13) !important;
    border: none;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.13);
    backdrop-filter: blur(4px);
}

.navbar .logo {
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: 1px;
    background:white;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.navbar .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* dropdown inside navbar */
.navbar .dropdown-menu {
    background: rgba(44, 62, 80, 0.95);
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.18);
    max-height: 100px;   /* or whatever fits */
    overflow-y: auto;
}

.navbar .dropdown-item {
    color: #fff;
}

.navbar .dropdown-item:hover {
    background: rgba(106, 0, 255, 0.18);
    color: #a64aff;
}
  .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .dropdown-menu {
            background: rgba(44, 62, 80, 0.95);
            color: #fff;
            border: none;
            box-shadow: 0 4px 18px rgba(0,0,0,0.18);
        }
        .dropdown-item {
            color: #fff;
        }
        .dropdown-item:hover {
            background: rgba(106, 0, 255, 0.18);
            color: #a64aff;
        }
        
         .logo {
      font-size: 1.8rem;
      font-weight: 700;
      background:black;
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      letter-spacing: 1px;
    }
    .top-navbar-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 72px;          /* navbar height */
    z-index: 1030;
    }
 
</style>
<div class="top-navbar-wrapper">
<nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm">
    <div class="container-fluid">
        <button class="navbar-toggler d-md-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#sidebarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="logo" style="padding:0; margin:0; display:flex; align-items:center;">
        <img src="/ForgeFit_mini_project/assets/images/logo1.png" alt="ForgeFit Logo" style="height:44px; width:auto; display:block; max-width:180px; object-fit:contain;" />
    </div>

        <div class="d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                   id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../assets/images/admin.jpg" alt="Admin" class="user-avatar me-2">
                    <span style="color: white;">Admin</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
</div>
