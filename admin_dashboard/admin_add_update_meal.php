<?php
include '../db.php';

// Initialize variables
$edit_meal = null;
$is_edit = false;

// Check if we're editing a meal
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $result_edit = $conn->query("SELECT * FROM meals WHERE meal_id = $edit_id");
    if ($result_edit && $result_edit->num_rows > 0) {
        $edit_meal = $result_edit->fetch_assoc();
        $is_edit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_meal'])) {
        // Update meal logic
        $meal_id = intval($_POST['meal_id']);
        $food_name = trim($_POST['food_name']);
        $unit = trim($_POST['unit']);
        $calories = floatval($_POST['calories']);
        $protein = floatval($_POST['protein']);
        $carbs = floatval($_POST['carbs']);
        $fat = floatval($_POST['fat']);

        if ($food_name && $unit && is_numeric($calories)) {
            $stmt = $conn->prepare("UPDATE meals SET food_name = ?, unit = ?, calories = ?, protein = ?, carbs = ?, fat = ? WHERE meal_id = ?");
            $stmt->bind_param("ssddddi", $food_name, $unit, $calories, $protein, $carbs, $fat, $meal_id);
            $stmt->execute();
            $_SESSION['message'] = "Meal updated successfully!";
            $_SESSION['msg_type'] = "success";
            echo "<script>window.location.href='admin_layout.php?page=admin_meals';</script>";
            exit();
        } else {
            $_SESSION['message'] = "Please fill all required fields correctly.";
            $_SESSION['msg_type'] = "error";
        }
    } elseif (isset($_POST['add_meal'])) {
        // Add new meal logic
        $food_name = trim($_POST['food_name']);
        $unit = trim($_POST['unit']);
        $calories = floatval($_POST['calories']);
        $protein = floatval($_POST['protein']);
        $carbs = floatval($_POST['carbs']);
        $fat = floatval($_POST['fat']);

        if ($food_name && $unit && is_numeric($calories)) {
            $stmt = $conn->prepare("INSERT INTO meals (food_name, unit, calories, protein, carbs, fat) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdddd", $food_name, $unit, $calories, $protein, $carbs, $fat);
            $stmt->execute();
            $_SESSION['message'] = "Meal added successfully!";
            $_SESSION['msg_type'] = "success";
            echo "<script>window.location.href='admin_layout.php?page=admin_meals';</script>";
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
        <a href="admin_layout.php?page=admin_meals" class="btn btn-danger">
            <i class="fas fa-arrow-left"></i> Back to Meals
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="page-title"><?= $is_edit ? 'Update Meal' : 'Add New Meal' ?></h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="meal_id" value="<?= $edit_meal['meal_id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="food_name" class="form-label">Food Name *</label>
                        <input type="text" id="food_name" name="food_name" class="form-control" 
                               value="<?= $is_edit ? htmlspecialchars($edit_meal['food_name']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="unit" class="form-label">Unit (e.g., 100g) *</label>
                        <input type="text" id="unit" name="unit" class="form-control" 
                               value="<?= $is_edit ? htmlspecialchars($edit_meal['unit']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="calories" class="form-label">Calories *</label>
                        <input type="number" id="calories" name="calories" step="0.01" class="form-control" 
                               value="<?= $is_edit ? $edit_meal['calories'] : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="protein" class="form-label">Protein (g)</label>
                        <input type="number" id="protein" name="protein" step="0.01" class="form-control" 
                               value="<?= $is_edit ? $edit_meal['protein'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="carbs" class="form-label">Carbs (g)</label>
                        <input type="number" id="carbs" name="carbs" step="0.01" class="form-control" 
                               value="<?= $is_edit ? $edit_meal['carbs'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="fat" class="form-label">Fat (g)</label>
                        <input type="number" id="fat" name="fat" step="0.01" class="form-control" 
                               value="<?= $is_edit ? $edit_meal['fat'] : '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <?php if ($is_edit): ?>
                        <button type="submit" name="update_meal" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Meal
                        </button>
                    <?php else: ?>
                        <button type="submit" name="add_meal" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Meal
                        </button>
                    <?php endif; ?>
                    
                    <a href="admin_layout.php?page=admin_meals" class="btn btn-danger">
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