<?php
/**
 * Fitness Profile Management System
 * Handles user fitness profile creation and updates with BMR calculation
 * 
 * Features:
 * - User authentication check
 * - Profile image upload with validation
 * - BMR and maintenance calorie calculation
 * - Responsive design for all screen sizes
 * - Form validation and error handling
 */

session_start();

// Authentication check - redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

include '../db.php';

// Get user data from session
$fullName = $_SESSION['n'];
$user_id = $_SESSION['user_id'];

// Initialize form variables with default values
$age = $gender = $primary_goal = $activity_level = $height_cm = $weight_kg = "";
$button_label = "Save Profile";
$heading_message = "Complete Your Fitness Profile";
$heading_text = "By filling the details you can find your maintenance calorie";
$profile_image = "default-avatar.png";

/**
 * Check if user already has an existing profile
 * If profile exists, populate form fields with existing data
 */
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

/**
 * Handle form submission for profile creation/update
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate form data
    $age = intval($_POST['dob']);
    $gender = $_POST['gender'];
    $primary_goal = $_POST['primaryGoal'];
    $activity_level = floatval($_POST['activityLevel']);
    $height_cm = floatval($_POST['height']);
    $weight_kg = floatval($_POST['weight']);

    /**
     * Calculate BMR using Mifflin-St Jeor Equation
     * BMR = (10 × weight in kg) + (6.25 × height in cm) - (5 × age in years) + gender constant
     * Gender constant: +5 for males, -161 for females
     */
    $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + ($gender === 'male' ? 5 : -161);
    $maintenance_calories = $bmr * $activity_level;

    /**
     * Handle profile image upload
     */
    $uploadSuccess = true;
    $newProfileImage = $profile_image; // Default to existing image
    
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profileImage']['type'];
        $file_size = $_FILES['profileImage']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size < 2000000) { // 2MB max
            // Generate unique filename to prevent conflicts
            $file_ext = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $image_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $image_path)) {
                $newProfileImage = $new_filename;
                
                // Clean up old image file (except default)
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

    /**
     * Database operation - Insert or Update profile
     */
    if ($uploadSuccess) {
        // Check if profile already exists
        $check = $conn->prepare("SELECT profile_id FROM user_fitness_profiles WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            // UPDATE existing profile
            $stmt = $conn->prepare("UPDATE user_fitness_profiles SET 
                age=?, gender=?, primary_goal=?, activity_level=?, height_cm=?, weight_kg=?, 
                maintenance_calories=?, profile_image=?
                WHERE user_id=?");
            $stmt->bind_param("isssdddsi", $age, $gender, $primary_goal, $activity_level, 
                $height_cm, $weight_kg, $maintenance_calories, $newProfileImage, $user_id);
        } else {
            // INSERT new profile
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
    
    <!-- External CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /**
         * CSS VARIABLES AND GLOBAL STYLES
         * Define reusable color scheme and design tokens
         */
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
            
            /* Responsive spacing variables */
            --container-padding: 20px;
            --section-padding: 30px;
            --form-spacing: 24px;
        }

        /**
         * GLOBAL RESET AND BASE STYLES
         */
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
            padding: 20px var(--container-padding);
            display: flex;
            justify-content: center;
            line-height: 1.6;
        }

        /**
         * MAIN CONTAINER STYLES
         * Responsive container with proper spacing
         */
        .container {
            margin-top: 50px;
            width: 100%;
            max-width: 700px;
        }

        .profile-container {
            margin: auto;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /**
         * HEADER SECTION STYLES
         * Gradient header with profile image
         */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 40px var(--section-padding);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background pattern */
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

        /**
         * PROFILE IMAGE STYLES
         * Circular profile image with edit functionality
         */
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

        /**
         * FORM CONTENT STYLES
         */
        .profile-content {
            padding: 40px var(--section-padding);
        }

        .form-section {
            margin-bottom: 40px;
            padding: var(--section-padding);
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
            margin-bottom: var(--form-spacing);
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
            flex-shrink: 0;
        }

        /**
         * FORM INPUT STYLES
         * Consistent styling for all form elements
         */
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
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /**
         * RADIO BUTTON STYLES
         */
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
            font-size: 0.95rem;
        }

        .gender-options {
            display: flex;
            gap: 20px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        /**
         * BUTTON STYLES
         */
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
            width: 100%;
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

        /**
         * INPUT GROUP STYLES
         */
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
            pointer-events: none;
        }

        .measurement-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /**
         * FLOATING LABEL STYLES
         */
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

        /**
         * NOTIFICATION STYLES
         */
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

        /**
         * BACKGROUND ANIMATION STYLES
         */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
        }

        /**
         * NAVIGATION STYLES
         */
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-home-btn:hover {
            background: #6a00ff;
            color: white;
            box-shadow: 0 0 10px #6a00ff;
        }

        /**
         * RESPONSIVE DESIGN - TABLET STYLES
         * Adjustments for tablet screens (768px and below)
         */
        @media (max-width: 768px) {
            :root {
                --container-padding: 15px;
                --section-padding: 20px;
                --form-spacing: 20px;
            }

            .profile-header {
                padding: 30px 20px;
            }

            .profile-header h1 {
                font-size: 2rem;
            }

            .profile-header p {
                font-size: 1rem;
            }

            .profile-image {
                width: 120px;
                height: 120px;
            }

            .profile-content {
                padding: 30px 20px;
            }

            .form-section {
                padding: 20px;
                margin-bottom: 30px;
            }

            .form-section h3 {
                font-size: 1.3rem;
            }

            .measurement-row {
                gap: 15px;
            }
        }

        /**
         * RESPONSIVE DESIGN - MOBILE STYLES
         * Critical adjustments for mobile screens (480px and below)
         */
        @media (max-width: 480px) {
            :root {
                --container-padding: 10px;
                --section-padding: 15px;
                --form-spacing: 16px;
            }

            body {
                padding: 10px;
            }

            .container {
                margin-top: 60px; /* Account for back button */
            }

            .profile-container {
                border-radius: 16px;
                margin: 0;
            }

            /* Header adjustments for mobile */
            .profile-header {
                padding: 25px 15px;
            }

            .profile-header h1 {
                font-size: 1.6rem;
                margin-bottom: 8px;
                line-height: 1.2;
            }

            .profile-header p {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }

            /* Smaller profile image for mobile */
            .profile-image {
                width: 100px;
                height: 100px;
                border-width: 4px;
            }

            .image-edit-btn {
                width: 32px;
                height: 32px;
                bottom: 2px;
                right: 2px;
            }

            .image-edit-btn i {
                font-size: 12px;
            }

            /* Content area adjustments */
            .profile-content {
                padding: 20px 15px;
            }

            .form-section {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 12px;
            }

            .form-section h3 {
                font-size: 1.2rem;
                margin-bottom: 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                text-align: left;
            }

            .section-icon {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            /* Form input adjustments for mobile */
            .form-control, .form-select {
                padding: 12px 14px;
                font-size: 16px; /* Prevent zoom on iOS */
                border-radius: 10px;
            }

            .form-floating > .form-control {
                padding-top: 18px;
                padding-bottom: 10px;
            }

            .form-floating > label {
                padding: 14px;
                font-size: 0.85rem;
            }

            /* Gender options layout for mobile */
            .gender-options {
                gap: 12px;
                flex-direction: column;
                align-items: flex-start;
            }

            .form-check-label {
                font-size: 0.9rem;
            }

            /* Measurement row becomes single column on mobile */
            .measurement-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            /* Button adjustments */
            .btn-primary {
                padding: 14px 24px;
                font-size: 1rem;
                border-radius: 10px;
            }

            /* Navigation adjustments */
            .top-nav {
                top: 15px;
                left: 15px;
            }

            .back-home-btn {
                padding: 8px 12px;
                font-size: 12px;
                border-radius: 6px;
            }

            /* Input icons adjustment */
            .input-icon {
                font-size: 16px;
                right: 14px;
            }

            /* Row spacing adjustments */
            .row.mb-4 {
                margin-bottom: 1.5rem !important;
            }

            .col-md-6 {
                margin-bottom: 16px;
            }

            /* Notification message adjustments */
            .success-message,
            .error-message {
                padding: 12px 16px;
                font-size: 0.9rem;
                border-radius: 10px;
                margin-bottom: 16px;
            }
        }

        /**
         * RESPONSIVE DESIGN - VERY SMALL MOBILE STYLES
         * Ultra-compact adjustments for very small screens (320px and below)
         */
        @media (max-width: 320px) {
            :root {
                --container-padding: 8px;
                --section-padding: 12px;
                --form-spacing: 12px;
            }

            body {
                padding: 8px;
            }

            .profile-header {
                padding: 20px 12px;
            }

            .profile-header h1 {
                font-size: 1.4rem;
                line-height: 1.3;
            }

            .profile-header p {
                font-size: 0.85rem;
                margin-bottom: 15px;
            }

            .profile-image {
                width: 80px;
                height: 80px;
                border-width: 3px;
            }

            .image-edit-btn {
                width: 28px;
                height: 28px;
            }

            .profile-content {
                padding: 16px 12px;
            }

            .form-section {
                padding: 12px;
                margin-bottom: 16px;
            }

            .form-section h3 {
                font-size: 1.1rem;
                margin-bottom: 12px;
            }

            .section-icon {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }

            .form-control, .form-select {
                padding: 10px 12px;
                font-size: 16px;
                border-radius: 8px;
            }

            .form-floating > .form-control {
                padding-top: 16px;
                padding-bottom: 8px;
            }

            .form-floating > label {
                padding: 12px;
                font-size: 0.8rem;
            }

            .btn-primary {
                padding: 12px 20px;
                font-size: 0.95rem;
                border-radius: 8px;
            }

            .measurement-row {
                gap: 12px;
            }

            .gender-options {
                gap: 8px;
            }

            .form-check-label {
                font-size: 0.85rem;
            }
        }

        /**
         * VALIDATION STYLES
         * Visual feedback for form validation
         */
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.25) !important;
        }

        .is-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.25) !important;
        }

        /**
         * UTILITY CLASSES
         */
        .text-center {
            text-align: center;
        }

        .d-grid {
            display: grid;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        /**
         * LOADING STATES AND INTERACTIONS
         */
        .form-control:disabled {
            background-color: #f8f9fa;
            opacity: 0.8;
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary:disabled:hover {
            transform: none;
            box-shadow: var(--shadow-medium);
        }
    </style>
</head>
<body>
    <!-- Animated background particles -->
    <div id="particles-js"></div>
    
    <!-- Navigation back button -->
    <div class="top-nav">
        <a href="user_home.php" class="back-home-btn">
            <i class="bi bi-arrow-left"></i>
            <span class="d-none d-sm-inline">Back</span>
        </a>
    </div>
    
    <!-- Main content container -->
    <div class="container">
        <div class="profile-container">
            <!-- Profile header with user info and profile image -->
            <div class="profile-header">
                <h1><?= htmlspecialchars($heading_message) ?></h1>
                <p><?= htmlspecialchars($heading_text) ?></p>
                <div class="profile-image-container">
                    <img id="profilePreview" src="../uploads/<?= htmlspecialchars($profile_image) ?>" 
                         class="profile-image" alt="Profile Image" 
                         onerror="this.src='../uploads/default-avatar.png'">
                    <!-- Edit profile photo button -->
                    <label for="profileImage" class="image-edit-btn" title="Edit Profile Photo">
                        <i class="bi bi-camera-fill text-white"></i>
                    </label>
                </div>
            </div>
            
            <!-- Main form content area -->
            <div class="profile-content">
                <form action="" method="POST" enctype="multipart/form-data" id="profileForm">
                    <!-- Hidden file input for profile image -->
                    <input type="file" id="profileImage" name="profileImage" accept="image/*" 
                           style="display: none;" onchange="previewImage(event)">
                    
                    <!-- Success notification message -->
                    <div class="success-message" id="successMessage">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Profile saved successfully!
                    </div>
                    
                    <!-- Error notification message -->
                    <div class="error-message" id="errorMessage">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <span id="errorText"></span>
                    </div>

                    <!-- 
                    PERSONAL INFORMATION SECTION
                    Contains: Full Name (readonly), Age, Gender selection
                    -->
                    <div class="form-section">
                        <h3>
                            <span class="section-icon">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            Personal Information
                        </h3>
                        
                        <div class="row mb-4">
                            <!-- Full Name Field (Read-only) -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstName" name="firstName" 
                                           placeholder="Full Name" disabled value="<?= htmlspecialchars($fullName) ?>">
                                    <label for="firstName">Full Name</label>
                                </div>
                            </div>
                            
                            <!-- Age Input Field -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="dob" name="dob" 
                                           placeholder="Age" required min="10" max="120" value="<?= htmlspecialchars($age) ?>">
                                    <label for="dob" class="required-field">Age</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gender Selection Radio Buttons -->
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

                    <!-- 
                    FITNESS GOALS SECTION
                    Contains: Primary Fitness Goal, Activity Level
                    These are used for calorie calculation
                    -->
                    <div class="form-section">
                        <h3>
                            <span class="section-icon">
                                <i class="bi bi-activity"></i>
                            </span>
                            Fitness Goals
                        </h3>
                        
                        <div class="row mb-4">
                            <!-- Primary Fitness Goal Dropdown -->
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
                            
                            <!-- Activity Level Dropdown (Used for BMR calculation multiplier) -->
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

                    <!-- 
                    BODY MEASUREMENTS SECTION
                    Contains: Height (cm), Weight (kg)
                    Critical for BMR and maintenance calorie calculations
                    -->
                    <div class="form-section">
                        <h3>
                            <span class="section-icon">
                                <i class="bi bi-rulers"></i>
                            </span>
                            Body Measurements
                        </h3>
                        
                        <div class="measurement-row">
                            <!-- Height Input with Icon -->
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
                            
                            <!-- Weight Input with Icon -->
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

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>
                            <?= htmlspecialchars($button_label) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 
    JAVASCRIPT FUNCTIONS
    Handle image preview, form validation, and user interactions
    -->
    <script>
        /**
         * Preview uploaded profile image before form submission
         * @param {Event} event - File input change event
         */
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('profilePreview');
                preview.src = reader.result;
                
                // Add smooth scale animation for visual feedback
                preview.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    preview.style.transform = 'scale(1)';
                }, 100);
            };
            
            // Only process if file is selected
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        /**
         * Enhanced form validation on submission
         * Prevents submission if required fields are empty
         */
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let allValid = true;
            let firstInvalidField = null;
            
            // Check each required field
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                    allValid = false;
                    
                    // Remember first invalid field for focusing
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            // Prevent submission and show error if validation fails
            if (!allValid) {
                e.preventDefault();
                document.getElementById('errorText').textContent = 'Please fill in all required fields.';
                document.getElementById('errorMessage').style.display = 'block';
                
                // Focus on first invalid field
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }
                
                // Hide error message after 5 seconds
                setTimeout(() => {
                    document.getElementById('errorMessage').style.display = 'none';
                }, 5000);
            }
        });

        /**
         * Real-time validation on field blur
         * Provides immediate feedback as user completes fields
         */
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
            
            // Also validate on input for immediate feedback
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid') && this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        /**
         * Age validation - ensure reasonable age range
         */
        document.getElementById('dob').addEventListener('input', function() {
            const age = parseInt(this.value);
            if (age < 10 || age > 120) {
                this.setCustomValidity('Please enter a valid age between 10 and 120');
            } else {
                this.setCustomValidity('');
            }
        });

        /**
         * Height validation - ensure reasonable height range
         */
        document.getElementById('height').addEventListener('input', function() {
            const height = parseFloat(this.value);
            if (height < 100 || height > 250) {
                this.setCustomValidity('Please enter a height between 100cm and 250cm');
            } else {
                this.setCustomValidity('');
            }
        });

        /**
         * Weight validation - ensure reasonable weight range
         */
        document.getElementById('weight').addEventListener('input', function() {
            const weight = parseFloat(this.value);
            if (weight < 30 || weight > 300) {
                this.setCustomValidity('Please enter a weight between 30kg and 300kg');
            } else {
                this.setCustomValidity('');
            }
        });

        /**
         * File size validation for profile image
         */
        document.getElementById('profileImage').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file size (2MB = 2,097,152 bytes)
                if (file.size > 2097152) {
                    alert('Image file is too large. Please choose a file under 2MB.');
                    this.value = '';
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or GIF).');
                    this.value = '';
                    return;
                }
            }
        });

        /**
         * Smooth scroll to top after successful form submission
         */
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        /**
         * Auto-hide notifications after specified time
         */
        function autoHideNotifications() {
            const successMsg = document.getElementById('successMessage');
            const errorMsg = document.getElementById('errorMessage');
            
            if (successMsg.style.display === 'block') {
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 5000);
            }
            
            if (errorMsg.style.display === 'block') {
                setTimeout(() => {
                    errorMsg.style.display = 'none';
                }, 5000);
            }
        }

        // Initialize auto-hide functionality
        document.addEventListener('DOMContentLoaded', autoHideNotifications);
    </script>

    <!-- External JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <!-- 
    Particles.js Background Animation
    Creates an animated particle background for visual appeal
    -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize particles.js with custom configuration
            particlesJS("particles-js", {
                "particles": {
                    "number": { 
                        "value": 80, // Reduced for better mobile performance
                        "density": { "enable": true, "value_area": 800 } 
                    },
                    "color": { "value": "#a64aff" },
                    "shape": { 
                        "type": "circle",
                        "stroke": { "width": 0, "color": "#000000" }
                    },
                    "opacity": { 
                        "value": 0.4, 
                        "random": true,
                        "anim": { "enable": false }
                    },
                    "size": { 
                        "value": 3, 
                        "random": true,
                        "anim": { "enable": false }
                    },
                    "line_linked": { 
                        "enable": true, 
                        "distance": 120, 
                        "color": "#6a00ff", 
                        "opacity": 0.3, 
                        "width": 1 
                    },
                    "move": { 
                        "enable": true, 
                        "speed": 2, 
                        "direction": "none", 
                        "random": true, 
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": { "enable": true, "mode": "repulse" },
                        "onclick": { "enable": true, "mode": "push" },
                        "resize": true
                    },
                    "modes": {
                        "grab": { "distance": 140, "line_linked": { "opacity": 1 } },
                        "bubble": { "distance": 400, "size": 40, "duration": 2, "opacity": 8, "speed": 3 },
                        "repulse": { "distance": 100, "duration": 0.4 },
                        "push": { "particles_nb": 4 },
                        "remove": { "particles_nb": 2 }
                    }
                },
                "retina_detect": true
            });
        });
    </script>
</body>
</html>