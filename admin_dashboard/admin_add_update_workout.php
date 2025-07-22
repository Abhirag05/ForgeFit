<?php
include '../db.php';

// Initialize variables
$workout = null;
$is_edit = false;

// Check if we're editing a workout
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM all_workouts WHERE id = $edit_id");
    if ($result && $result->num_rows > 0) {
        $workout = $result->fetch_assoc();
        $is_edit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_workout'])) {
        // Update workout logic
        $workout_id = intval($_POST['workout_id']);
        $name = trim($_POST['name']);
        $MET = floatval($_POST['MET']);

        if ($name && is_numeric($MET)) {
            $stmt = $conn->prepare("UPDATE all_workouts SET name = ?, MET = ? WHERE id = ?");
            $stmt->bind_param("sdi", $name, $MET, $workout_id);
            $stmt->execute();
            $_SESSION['message'] = "Workout updated successfully!";
            $_SESSION['msg_type'] = "success";
               echo "<script>window.location.href='admin_layout.php?page=admin_workout';</script>";
            exit();
        } else {
            $_SESSION['message'] = "Please fill all required fields correctly.";
            $_SESSION['msg_type'] = "error";
        }
    } elseif (isset($_POST['add_workout'])) {
        // Add new workout logic
        $name = trim($_POST['name']);
        $MET = floatval($_POST['MET']);

        if ($name && is_numeric($MET)) {
            $stmt = $conn->prepare("INSERT INTO all_workouts (name, MET) VALUES (?, ?)");
            $stmt->bind_param("sd", $name, $MET);
            $stmt->execute();
            $_SESSION['message'] = "Workout added successfully!";
            $_SESSION['msg_type'] = "success";
            echo "<script>window.location.href='admin_layout.php?page=admin_workout';</script>";
            exit();
        } else {
            $_SESSION['message'] = "Please fill all required fields correctly.";
            $_SESSION['msg_type'] = "error";
        }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }
    
    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        color: black;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: white;
    }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: fadeIn 0.3s ease-in-out;
    }
    
    .alert-success {
        background-color: rgba(76, 201, 240, 0.1);
        border-left: 4px solid #4cc9f0;
        color: #4cc9f0;
    }
    
    .alert-error {
        background-color: rgba(247, 37, 133, 0.1);
        border-left: 4px solid #f72585;
        color: #f72585;
    }
    
    .alert-close {
        background: none;
        border: none;
        color: inherit;
        font-size: 1.25rem;
        cursor: pointer;
    }
    
    .card {
        background: rgba(255,255,255,0.13);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        overflow: hidden;
        color: black;
        backdrop-filter: blur(5px);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: white;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s;
        background-color: rgba(255, 255, 255, 0.9);
    }
    
    .form-control:focus {
        outline: none;
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        font-size: 0.9rem;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background-color: #4361ee;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #3a56d4;
    }
    
    .btn-danger {
        background-color: #f72585;
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #e5177b;
    }
    
    .back-btn-container {
        margin-bottom: 1.5rem;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] === 'success' ? 'success' : 'error' ?>">
            <span><?= $_SESSION['message'] ?></span>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
    <?php endif; ?>
    
    <div class="back-btn-container">
        <a href="admin_layout.php?page=admin_workout" class="btn btn-danger">
            <i class="fas fa-arrow-left"></i> Back to Workouts
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="page-title"><?= $is_edit ? 'Update Workout' : 'Add New Workout' ?></h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="workout_id" value="<?= $workout['id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">Workout Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= $is_edit ? htmlspecialchars($workout['name']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="MET" class="form-label">MET Value *</label>
                        <input type="number" id="MET" name="MET" step="0.01" class="form-control" 
                               value="<?= $is_edit ? $workout['MET'] : '' ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <?php if ($is_edit): ?>
                        <button type="submit" name="update_workout" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Workout
                        </button>
                    <?php else: ?>
                        <button type="submit" name="add_workout" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Workout
                        </button>
                    <?php endif; ?>
                    
                    <a href="admin_layout.php?page=admin_workout" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-close alert after 5 seconds
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    }
</script>