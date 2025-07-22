<?php
session_start();
include '../db.php';

if (!isset($_GET['id'])) {
    echo "User ID not provided.";
    exit();
}

$user_id = $_GET['id'];

$sql = "SELECT u.id, u.fullname, u.email, u.role, u.joined_date, f.profile_image, f.age, f.gender, f.height_cm, f.weight_kg, f.primary_goal, f.activity_level 
        FROM users u
        LEFT JOIN user_fitness_profiles f ON u.id = f.user_id
        WHERE u.id = $user_id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "User not found.";
    exit();
}

$user = mysqli_fetch_assoc($result);
// Total Workouts
$workoutQuery = "SELECT COUNT(*) as total FROM all_logged_workouts WHERE user_id = $user_id";
$workoutResult = mysqli_query($conn, $workoutQuery);
$workouts = mysqli_fetch_assoc($workoutResult)['total'] ?? 0;

// Total Meals
$mealQuery = "SELECT COUNT(*) as total FROM logged_meals WHERE user_id = $user_id";
$mealResult = mysqli_query($conn, $mealQuery);
$meals = mysqli_fetch_assoc($mealResult)['total'] ?? 0;

?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        font-family: 'Segoe UI', 'Poppins', sans-serif;
        background: transparent;
        overflow-x: hidden;
    }

    #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: -1;
        background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
    }

    .user-profile-container {
        max-width: 900px;
        margin: 60px auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.25);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: #fff;
        overflow: hidden;
        animation: fadeIn 0.6s ease-in-out;
    }

    .user-profile-container h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 28px;
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 12px;
        letter-spacing: 1px;
    }

    .user-profile-container table {
        width: 100%;
        border-collapse: collapse;
        color: #f1f1f1;
    }

    .user-profile-container td {
        padding: 14px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 16px;
    }

    .user-profile-container tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .label {
        font-weight: 600;
        color: #ccc;
        width: 40%;
        text-align: left;
    }

    .user-image {
        text-align: center;
        margin-bottom: 25px;
    }

    .user-image img {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        transition: transform 0.3s ease;
    }

    .user-image img:hover {
        transform: scale(1.05);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
 .back-btn-container {
    position: absolute;
    top: 30px;
    left: 30px;
    z-index: 10;
}

.back-btn {
    display: inline-block;
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(255,255,255,0.05);
    backdrop-filter: blur(4px);
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #fff;
}


</style>

<div id="particles-js"></div>
<div class="back-btn-container">
    <a href="admin_layout.php?page=user_details" class="back-btn">← Back to Users</a>
</div>


<div class="user-profile-container">
    <h2>User Profile: <?php echo htmlspecialchars($user['fullname']); ?></h2>

    <?php if (!empty($user['profile_image'])): ?>
        <div class="user-image">
            <img src="../uploads/<?php echo $user['profile_image']; ?>" alt="User Image">
        </div>
    <?php endif; ?>

    <table>
        <tr><td class="label">User ID</td><td><?php echo $user['id']; ?></td></tr>
        <tr><td class="label">Full Name</td><td><?php echo $user['fullname']; ?></td></tr>
        <tr><td class="label">Email</td><td><?php echo $user['email']; ?></td></tr>
        <tr><td class="label">Role</td><td><?php echo $user['role']; ?></td></tr>
        <tr><td class="label">Joined Date</td><td><?php echo $user['joined_date']; ?></td></tr>
        <tr><td class="label">Age</td><td><?php echo $user['age'] ?? 'N/A'; ?></td></tr>
        <tr><td class="label">Gender</td><td><?php echo $user['gender'] ?? 'N/A'; ?></td></tr>
        <tr><td class="label">Height (cm)</td><td><?php echo $user['height_cm'] ?? 'N/A'; ?></td></tr>
        <tr><td class="label">Weight (kg)</td><td><?php echo $user['weight_kg'] ?? 'N/A'; ?></td></tr>
        <tr><td class="label">Primary Goal</td><td><?php echo $user['primary_goal'] ?? 'N/A'; ?></td></tr>
        <tr><td class="label">Activity Level</td><td><?php echo $user['activity_level'] ?? 'N/A'; ?></td></tr>
        <tr><td class="label">Total Workouts Logged</td><td><?php echo $workouts; ?></td></tr>
        <tr><td class="label">Total Meals Logged</td><td><?php echo $meals; ?></td></tr>
    </table>
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