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
$query = "SELECT lw.date, w.name AS workout, lw.sets, lw.reps, lw.weight
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
    <style>
        :root {
            --primary: #ff4d4d;
            --secondary: #6a00ff;
            --dark: #0a0a12;
            --light: #f0f0f0;
            --accent: #00f0ff;
            --card-bg: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 77, 77, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--dark);
            color: var(--light);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        h1 {
            font-size: 2.5rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            display: inline-block;
        }

        h2 {
            font-size: 2rem;
            margin-top: 40px;
        }

        .log-form {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--accent);
        }

        select, input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.1);
            color: var(--dark);
            font-size: 16px;
            transition: all 0.3s;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 77, 77, 0.3);
        }

        .btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 77, 77, 0.4);
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .history-table th {
            background: rgba(255, 77, 77, 0.2);
            color: var(--accent);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .history-table tr:hover {
            background: rgba(255, 77, 77, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: rgba(255, 255, 255, 0.5);
            font-style: italic;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .log-form {
                padding: 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
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
                        <label for="reps">Reps</label>
                        <input type="number" name="reps" id="reps" min="1" required>
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
                    <th>Reps</th>
                    <th>Weight</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($historyResult) > 0): ?>
                    <?php while ($log = mysqli_fetch_assoc($historyResult)): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($log['date'])) ?></td>
                            <td><?= htmlspecialchars($log['workout']) ?></td>
                            <td><?= $log['sets'] ?></td>
                            <td><?= $log['reps'] ?></td>
                            <td><?= $log['weight'] ?> kg</td>
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