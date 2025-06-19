<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch workouts from DB
$workoutOptions = "";
$sql = "SELECT id, name FROM workouts";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $workoutOptions .= "<option value='{$row['id']}'>{$row['name']}</option>";
}

// Fetch workout history
$user_id = $_SESSION['user_id'];
$query = "SELECT lw.date, w.name AS workout, lw.sets, lw.duration, lw.weight, lw.calories_burned
          FROM logged_workouts lw
          JOIN workouts w ON lw.workout_id = w.id
          WHERE lw.user_id = $user_id
          ORDER BY lw.date DESC";
$historyResult = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Log | ForgeFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <Link rel="stylesheet" href="user_workout.css">
</head>
<body>
    <div class="container">
        <h1>Workout Log</h1>
        
        <div class="log-form">
            <form action="save_workout.php" method="POST">
                <div class="form-group">
                    <label for="workout">Workout Type</label>
                    <select name="workout_id" id="workout" required>
                        <option value="">-- Select Workout --</option>
                        <?= $workoutOptions ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" name="date" id="date" required>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="form-group">
                        <label for="sets">Sets</label>
                        <input type="number" name="sets" id="sets" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Duration</label>
                        <input type="number" name="duration" id="duration" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" name="weight" id="weight" min="0" step="0.1" required>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Save Workout
                </button>
            </form>
        </div>
        
        <h2>Your Workout History</h2>
        
        <table class="history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Workout</th>
                    <th>Sets</th>
                    <th>Duration</th>
                    <th>Weight</th>
                    <th>Calories Burned</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($historyResult) > 0): ?>
                    <?php while ($log = mysqli_fetch_assoc($historyResult)): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($log['date'])) ?></td>
                            <td><?= htmlspecialchars($log['workout']) ?></td>
                            <td><?= $log['sets'] ?></td>
                            <td><?= $log['duration'] ?></td>
                            <td><?= $log['weight'] ?> kg</td>
                            <td><?= $log['calories_burned'] ?> kcal</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">No workouts logged yet. Get started above!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Set default date to today
        document.getElementById('date').valueAsDate = new Date();
        
        // Add animation to table rows
        const rows = document.querySelectorAll('.history-table tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            row.style.transition = `all 0.3s ease ${index * 0.1}s`;
            
            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>