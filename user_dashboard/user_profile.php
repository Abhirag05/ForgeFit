<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

include '../db.php';

$fullName = $_SESSION['n'];
$user_id = $_SESSION['user_id'];

// Initialize variables
$age = $gender = $primary_goal = $activity_level = $height_cm = $weight_kg = "";
$button_label = "Save Profile";
$heading_message = "Complete Your Fitness Profile";
$heading_text = "By filling the details you can find your maintenance calorie";
$profile_image = "default-avatar.png";

// Check if user already has a profile
$sql = "SELECT * FROM user_fitness_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    $age = $profile['age'];
    $gender = $profile['gender'];
    $primary_goal = $profile['primary_goal'];
    $activity_level = $profile['activity_level'];
    $height_cm = $profile['height_cm'];
    $weight_kg = $profile['weight_kg'];
    $button_label = "Update Profile";
    $heading_message = "Your Profile is Already Set";
    $heading_text = "By updating details you can find your new maintenance calorie";
    $profile_image = $profile['profile_image'] ?? "default-avatar.png";
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $age = intval($_POST['dob']);
    $gender = $_POST['gender'];
    $primary_goal = $_POST['primaryGoal'];
    $activity_level = floatval($_POST['activityLevel']);
    $height_cm = floatval($_POST['height']);
    $weight_kg = floatval($_POST['weight']);

    // Calculate BMR and maintenance calories
    $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + ($gender === 'male' ? 5 : -161);
    $maintenance_calories = $bmr * $activity_level;

    // Handle file upload
    $uploadSuccess = true;
    $newProfileImage = $profile_image; // Default to existing image
    
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profileImage']['type'];
        $file_size = $_FILES['profileImage']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size < 2000000) { // 2MB max
            // Generate unique filename
            $file_ext = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $image_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $image_path)) {
                $newProfileImage = $new_filename;
                
                // Delete old image if it's not the default
                if ($profile_image !== "default-avatar.png" && file_exists($upload_dir . $profile_image)) {
                    unlink($upload_dir . $profile_image);
                }
            } else {
                $uploadSuccess = false;
                echo "<script>alert('Error uploading profile image.');</script>";
            }
        } else {
            $uploadSuccess = false;
            echo "<script>alert('Invalid image file. Please upload a JPEG, PNG, or GIF under 2MB.');</script>";
        }
    }

    // Only proceed with database update if upload was successful or no upload was attempted
    if ($uploadSuccess) {
        // Check if profile exists
        $check = $conn->prepare("SELECT profile_id FROM user_fitness_profiles WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE user_fitness_profiles SET 
                age=?, gender=?, primary_goal=?, activity_level=?, height_cm=?, weight_kg=?, 
                maintenance_calories=?, profile_image=?
                WHERE user_id=?");
            $stmt->bind_param("isssdddsi", $age, $gender, $primary_goal, $activity_level, 
                $height_cm, $weight_kg, $maintenance_calories, $newProfileImage, $user_id);
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO user_fitness_profiles 
                (user_id, age, gender, primary_goal, activity_level, height_cm, weight_kg, 
                maintenance_calories, profile_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssddds", $user_id, $age, $gender, $primary_goal, 
                $activity_level, $height_cm, $weight_kg, $maintenance_calories, $newProfileImage);
        }

        if ($stmt->execute()) {
            $profile_image = $newProfileImage; // Update for display
            echo "<script>alert('Profile saved successfully!'); window.location.href='user_home.php';</script>";
        } else {
            echo "<script>alert('Error saving profile: " . $stmt->error . "');</script>";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #0a0a12;
            color: #f0f0f0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .profile-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        input[type="file"]::-webkit-file-upload-button {
            visibility: hidden;
        }
        input[type="file"]::before {
            content: 'Choose image';
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 5px 8px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: -1;
        background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <div class="profile-container">    
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="profile-header">
                <h1><?= htmlspecialchars($heading_message) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($heading_text) ?></p>
                <div class="text-center position-relative mb-3">
                    <img id="profilePreview" src="../uploads/<?= htmlspecialchars($profile_image) ?>" 
                         class="rounded-circle border" alt="Profile Image" width="120" height="120" 
                         style="object-fit: cover;" onerror="this.src='../uploads/default-avatar.png'">
                    <label for="profileImage" class="position-absolute top-0 end-0 translate-middle badge rounded-circle bg-primary" 
                           style="cursor:pointer; width:30px; height:30px;" title="Edit Photo">
                        <i class="bi bi-pencil-fill text-white" style="font-size: 0.9rem;"></i>
                    </label>
                    <input type="file" id="profileImage" name="profileImage" accept="image/*" 
                           style="display: none;" onchange="previewImage(event)">
                </div>
            </div>
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label required-field">Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" disabled 
                                   value="<?= htmlspecialchars($fullName) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="dob" class="form-label required-field">Age</label>
                        <input type="number" class="form-control" id="dob" name="dob" required 
                               min="10" max="120" value="<?= htmlspecialchars($age) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required-field">Gender</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="male" 
                                       value="male" <?= $gender === 'male' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="male">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="female" 
                                       value="female" <?= $gender === 'female' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="female">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="other" 
                                       value="other" <?= $gender === 'other' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="other">Other</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fitness Goals Section -->
                <div class="form-section">
                    <h3>Fitness Goals</h3>
                    <div class="mb-3">
                        <label for="primaryGoal" class="form-label required-field">Primary Fitness Goal</label>
                        <select class="form-select" id="primaryGoal" name="primaryGoal" required>
                            <option value="" disabled <?= $primary_goal == '' ? 'selected' : '' ?>>Select your primary goal</option>
                            <option value="weight_loss" <?= $primary_goal == 'weight_loss' ? 'selected' : '' ?>>Weight Loss</option>
                            <option value="muscle_gain" <?= $primary_goal == 'muscle_gain' ? 'selected' : '' ?>>Muscle Gain</option>
                            <option value="maintenance" <?= $primary_goal == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            <option value="endurance" <?= $primary_goal == 'endurance' ? 'selected' : '' ?>>Endurance Training</option>
                            <option value="sport_specific" <?= $primary_goal == 'sport_specific' ? 'selected' : '' ?>>Sport-Specific Training</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required-field">Activity Level</label>
                        <select class="form-select" id="activityLevel" name="activityLevel" required>
                            <option value="" disabled <?= $activity_level == '' ? 'selected' : '' ?>>Select your activity level</option>
                            <option value="1.2" <?= $activity_level == 1.2 ? 'selected' : '' ?>>Sedentary (little or no exercise)</option>
                            <option value="1.375" <?= $activity_level == 1.375 ? 'selected' : '' ?>>Lightly active (light exercise 1–3 days/week)</option>
                            <option value="1.55" <?= $activity_level == 1.55 ? 'selected' : '' ?>>Moderately active (moderate exercise 3–5 days/week)</option>
                            <option value="1.725" <?= $activity_level == 1.725 ? 'selected' : '' ?>>Very active (hard exercise 6–7 days/week)</option>
                            <option value="1.9" <?= $activity_level == 1.9 ? 'selected' : '' ?>>Extremely active (very hard exercise & physical job)</option>
                        </select>
                    </div>
                </div>

                <!-- Body Measurements Section -->
                <div class="form-section">
                    <h3>Body Measurements</h3>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="height" class="form-label required-field">Height (cm)</label>
                            <input type="number" class="form-control" id="height" name="height" step="0.1" 
                                   min="100" max="250" required value="<?= htmlspecialchars($height_cm) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="weight" class="form-label required-field">Current Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" step="0.1" 
                                   min="30" max="300" required value="<?= htmlspecialchars($weight_kg) ?>">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg"><?= htmlspecialchars($button_label) ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('profilePreview');
                preview.src = reader.result;
            };
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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