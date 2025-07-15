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
    <title>Fitness Profile - Professional Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link rel="icon" href="../assets/images/logo.png" type="image/x-icon">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #e5e7eb;
            --accent-color: #10b981;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
            --shadow-heavy: 0 10px 25px rgba(0, 0, 0, 0.15), 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            overflow-x: hidden;
        }

        body {
        font-family: 'Inter', sans-serif;
        background-color: #0a0a12;
        color: #f0f0f0;
        min-height: 100vh;
        padding: 0px 20px; /* 👈 Added padding to prevent cutting off top */
        display: flex;
        justify-content: center; /* center horizontally only */
        /* Removed align-items: center to allow vertical scroll */
    }
    .container{
        margin-top:50px;
    }

        .profile-container {
            margin:auto;
            max-width: 700px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .profile-header > * {
            position: relative;
            z-index: 1;
        }

        .profile-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        .profile-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
            font-weight: 300;
        }

        .profile-image-container {
            position: relative;
            display: inline-block;
        }

        .profile-image {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-image:hover {
            transform: scale(1.05);
        }

        .image-edit-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            border: 3px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
        }

        .image-edit-btn:hover {
            background: #059669;
            transform: scale(1.1);
        }

        .profile-content {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
            padding: 30px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            transition: box-shadow 0.3s ease;
        }

        .form-section:hover {
            box-shadow: var(--shadow-medium);
        }

        .form-section h3 {
            color: var(--text-dark);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .required-field::after {
            content: " *";
            color: #ef4444;
            font-weight: 600;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 0.15em;
            border: 2px solid var(--border-color);
            border-radius: 50%;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-left: 8px;
        }

        .gender-options {
            display: flex;
            gap: 24px;
            margin-top: 12px;
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 16px 32px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            box-shadow: var(--shadow-medium);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 18px;
        }

        .measurement-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: none;
        }

        .error-message {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: none;
        }

        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
        }

        .form-floating {
            position: relative;
        }

        .form-floating > .form-control {
            padding-top: 20px;
            padding-bottom: 12px;
        }

        .form-floating > label {
            position: absolute;
            top: 0;
            left: 0;
            padding: 16px;
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 400;
            pointer-events: none;
            transform-origin: 0 0;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
            color: var(--primary-color);
        }
        .top-nav {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 10;
    }

    .back-home-btn {
      padding: 10px 16px;
      color: #ffffff;
      background: rgba(106, 0, 255, 0.2);
      border: 1px solid #6a00ff;
      border-radius: 8px;
      font-size: 14px;
      text-decoration: none;
      backdrop-filter: blur(5px);
      transition: all 0.3s ease;
    }

    .back-home-btn:hover {
      background: #6a00ff;
      color: white;
      box-shadow: 0 0 10px #6a00ff;
    }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="top-nav">
    <a href="user_home.php" class="back-home-btn">←</a>
  </div>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h1><?= htmlspecialchars($heading_message) ?></h1>
                <p><?= htmlspecialchars($heading_text) ?></p>
                <div class="profile-image-container">
                    <img id="profilePreview" src="../uploads/<?= htmlspecialchars($profile_image) ?>" 
                         class="profile-image" alt="Profile Image" 
                         onerror="this.src='../uploads/default-avatar.png'">
                    <label for="profileImage" class="image-edit-btn" title="Edit Profile Photo">
                        <i class="bi bi-camera-fill text-white"></i>
                    </label>
                </div>
            </div>
            
            <div class="profile-content">
                <form action="" method="POST" enctype="multipart/form-data" id="profileForm">
                    <input type="file" id="profileImage" name="profileImage" accept="image/*" 
                           style="display: none;" onchange="previewImage(event)">
                    
                    <div class="success-message" id="successMessage">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Profile saved successfully!
                    </div>
                    
                    <div class="error-message" id="errorMessage">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <span id="errorText"></span>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h3>
                            <span class="section-icon">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            Personal Information
                        </h3>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstName" name="firstName" 
                                           placeholder="Full Name" disabled value="<?= htmlspecialchars($fullName) ?>">
                                    <label for="firstName">Full Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="dob" name="dob" 
                                           placeholder="Age" required min="10" max="120" value="<?= htmlspecialchars($age) ?>">
                                    <label for="dob" class="required-field">Age</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label required-field">Gender</label>
                            <div class="gender-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="male" 
                                           value="male" <?= $gender === 'male' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="male">
                                        <i class="bi bi-gender-male me-2"></i>Male
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="female" 
                                           value="female" <?= $gender === 'female' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="female">
                                        <i class="bi bi-gender-female me-2"></i>Female
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="other" 
                                           value="other" <?= $gender === 'other' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="other">
                                        <i class="bi bi-gender-ambiguous me-2"></i>Other
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fitness Goals Section -->
                    <div class="form-section">
                        <h3>
                            <span class="section-icon">
                                <i class="bi bi-activity"></i>
                            </span>
                            Fitness Goals
                        </h3>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="primaryGoal" name="primaryGoal" required>
                                        <option value="" disabled <?= $primary_goal == '' ? 'selected' : '' ?>>Select your primary goal</option>
                                        <option value="weight_loss" <?= $primary_goal == 'weight_loss' ? 'selected' : '' ?>>Weight Loss</option>
                                        <option value="muscle_gain" <?= $primary_goal == 'muscle_gain' ? 'selected' : '' ?>>Muscle Gain</option>
                                        <option value="maintenance" <?= $primary_goal == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                        <option value="endurance" <?= $primary_goal == 'endurance' ? 'selected' : '' ?>>Endurance Training</option>
                                        <option value="sport_specific" <?= $primary_goal == 'sport_specific' ? 'selected' : '' ?>>Sport-Specific Training</option>
                                    </select>
                                    <label for="primaryGoal" class="required-field">Primary Fitness Goal</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="activityLevel" name="activityLevel" required>
                                        <option value="" disabled <?= $activity_level == '' ? 'selected' : '' ?>>Select your activity level</option>
                                        <option value="1.2" <?= $activity_level == 1.2 ? 'selected' : '' ?>>Sedentary (little or no exercise)</option>
                                        <option value="1.375" <?= $activity_level == 1.375 ? 'selected' : '' ?>>Lightly active (light exercise 1–3 days/week)</option>
                                        <option value="1.55" <?= $activity_level == 1.55 ? 'selected' : '' ?>>Moderately active (moderate exercise 3–5 days/week)</option>
                                        <option value="1.725" <?= $activity_level == 1.725 ? 'selected' : '' ?>>Very active (hard exercise 6–7 days/week)</option>
                                        <option value="1.9" <?= $activity_level == 1.9 ? 'selected' : '' ?>>Extremely active (very hard exercise & physical job)</option>
                                    </select>
                                    <label for="activityLevel" class="required-field">Activity Level</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Body Measurements Section -->
                    <div class="form-section">
                        <h3>
                            <span class="section-icon">
                                <i class="bi bi-rulers"></i>
                            </span>
                            Body Measurements
                        </h3>
                        
                        <div class="measurement-row">
                            <div class="input-group">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="height" name="height" 
                                           placeholder="Height (cm)" step="0.1" min="100" max="250" 
                                           required value="<?= htmlspecialchars($height_cm) ?>">
                                    <label for="height" class="required-field">Height (cm)</label>
                                </div>
                                <span class="input-icon">
                                    <i class="bi bi-arrows-vertical"></i>
                                </span>
                            </div>
                            <div class="input-group">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="weight" name="weight" 
                                           placeholder="Weight (kg)" step="0.1" min="30" max="300" 
                                           required value="<?= htmlspecialchars($weight_kg) ?>">
                                    <label for="weight" class="required-field">Current Weight (kg)</label>
                                </div>
                                <span class="input-icon">
                                    <i class="bi bi-speedometer2"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <?= htmlspecialchars($button_label) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('profilePreview');
                preview.src = reader.result;
                
                // Add a subtle animation
                preview.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    preview.style.transform = 'scale(1)';
                }, 100);
            };
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        // Form validation enhancement
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let allValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    allValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!allValid) {
                e.preventDefault();
                document.getElementById('errorText').textContent = 'Please fill in all required fields.';
                document.getElementById('errorMessage').style.display = 'block';
                
                // Scroll to error message
                document.getElementById('errorMessage').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        });

        // Real-time validation
        document.querySelectorAll('input[required], select[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });
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