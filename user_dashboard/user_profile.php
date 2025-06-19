<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}
include '../db.php';
$fullName = $_SESSION['n'];
// Collect data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$userId = $_SESSION['user_id'];
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$primaryGoal = $_POST['primaryGoal'];
$targetWeight = !empty($_POST['targetWeight']) ? $_POST['targetWeight'] : NULL;
$weeklyGoal = !empty($_POST['weeklyGoal']) ? $_POST['weeklyGoal'] : NULL;
$activityLevel = !empty($_POST['activityLevel']) ? $_POST['activityLevel'] : NULL;
$height = $_POST['height'];
$weight = $_POST['weight'];
$workoutTypes = isset($_POST['workoutTypes']) ? implode(',', $_POST['workoutTypes']) : NULL;
$workoutFrequency = !empty($_POST['workoutFrequency']) ? $_POST['workoutFrequency'] : NULL;
$workoutDuration = !empty($_POST['workoutDuration']) ? $_POST['workoutDuration'] : NULL;

// Check if profile already exists for user
$check = $conn->prepare("SELECT id FROM fitness_profiles WHERE user_id = ?");
$check->bind_param("i", $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Update existing profile
    $stmt = $conn->prepare("
        UPDATE fitness_profiles SET 
         dob=?, gender=?, primary_goal=?, target_weight=?, weekly_goal=?, activity_level=?, 
            height=?, weight=?, workout_types=?, workout_frequency=?, workout_duration=?
        WHERE user_id=?
    ");
    $stmt->bind_param(
        "sssdssddsssi",
         $dob, $gender, $primaryGoal, $targetWeight, $weeklyGoal, $activityLevel,
        $height, $weight, $workoutTypes, $workoutFrequency, $workoutDuration,
        $userId
    );
} else {
    // Insert new profile
    $stmt = $conn->prepare("
        INSERT INTO fitness_profiles 
        (user_id,dob, gender, primary_goal, target_weight, weekly_goal, activity_level, height, weight, workout_types, workout_frequency, workout_duration)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "issssdssddsss",
        $userId, $dob, $gender, $primaryGoal, $targetWeight, $weeklyGoal,
        $activityLevel, $height, $weight, $workoutTypes, $workoutFrequency, $workoutDuration
    );
}

if ($stmt->execute()) {
    echo "<h2 style='text-align:center;color:green;'>Fitness profile saved successfully!</h2>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Fitness Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h1>Complete Your Fitness Profile</h1>
                <p class="text-muted">Help us personalize your fitness experience by providing these details</p>
            </div>

            <form action="" method="POST">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label required-field">Name</label>
                          <input type="text" class="form-control" id="firstName" name="firstName" required value="<?= htmlspecialchars($fullName) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="dob" class="form-label required-field">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required-field">Gender</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="male" value="male" required>
                                <label class="form-check-label" for="male">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                                <label class="form-check-label" for="female">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="other" value="other">
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
                            <option value="" selected disabled>Select your primary goal</option>
                            <option value="weight_loss">Weight Loss</option>
                            <option value="muscle_gain">Muscle Gain</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="endurance">Endurance Training</option>
                            <option value="sport_specific">Sport-Specific Training</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="targetWeight" class="form-label">Target Weight (kg)</label>
                        <input type="number" class="form-control" id="targetWeight" name="targetWeight" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label for="weeklyGoal" class="form-label">Weekly Goal</label>
                        <select class="form-select" id="weeklyGoal" name="weeklyGoal">
                            <option value="" selected disabled>Select weekly goal</option>
                            <option value="lose_0.5kg">Lose 0.5kg per week</option>
                            <option value="lose_1kg">Lose 1kg per week</option>
                            <option value="gain_0.5kg">Gain 0.5kg per week</option>
                            <option value="gain_1kg">Gain 1kg per week</option>
                            <option value="maintain">Maintain current weight</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Activity Level</label>
                        <select class="form-select" id="activityLevel" name="activityLevel">
                            <option value="" selected disabled>Select your activity level</option>
                            <option value="sedentary">Sedentary (little or no exercise)</option>
                            <option value="light">Lightly active (light exercise 1-3 days/week)</option>
                            <option value="moderate">Moderately active (moderate exercise 3-5 days/week)</option>
                            <option value="active">Very active (hard exercise 6-7 days/week)</option>
                            <option value="extreme">Extremely active (very hard exercise & physical job)</option>
                        </select>
                    </div>
                </div>

                <!-- Body Measurements Section -->
                <div class="form-section">
                    <h3>Body Measurements</h3>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="height" class="form-label required-field">Height (cm)</label>
                            <input type="number" class="form-control" id="height" name="height" required>
                        </div>
                        <div class="col-md-6">
                            <label for="weight" class="form-label required-field">Current Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" step="0.1" required>
                        </div>
                    </div>
                    
                </div>
                <!-- Workout Preferences -->
                <div class="form-section">
                    <h3>Workout Preferences</h3>
                    <div class="mb-3">
                        <label class="form-label">Preferred Workout Types (Select all that apply)</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="strengthTraining" name="workoutTypes[]" value="strength">
                            <label class="form-check-label" for="strengthTraining">Strength Training</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cardio" name="workoutTypes[]" value="cardio">
                            <label class="form-check-label" for="cardio">Cardio</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="hiit" name="workoutTypes[]" value="hiit">
                            <label class="form-check-label" for="hiit">HIIT</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="yoga" name="workoutTypes[]" value="yoga">
                            <label class="form-check-label" for="yoga">Yoga/Pilates</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sports" name="workoutTypes[]" value="sports">
                            <label class="form-check-label" for="sports">Sports</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="workoutFrequency" class="form-label">How many days per week do you typically exercise?</label>
                        <select class="form-select" id="workoutFrequency" name="workoutFrequency">
                            <option value="" selected disabled>Select frequency</option>
                            <option value="0">0 days (I don't exercise)</option>
                            <option value="1-2">1-2 days</option>
                            <option value="3-4">3-4 days</option>
                            <option value="5-6">5-6 days</option>
                            <option value="7">7 days</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="workoutDuration" class="form-label">Typical workout duration</label>
                        <select class="form-select" id="workoutDuration" name="workoutDuration">
                            <option value="" selected disabled>Select duration</option>
                            <option value="30">30 minutes or less</option>
                            <option value="45">45 minutes</option>
                            <option value="60">60 minutes</option>
                            <option value="75">75 minutes</option>
                            <option value="90">90 minutes or more</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>