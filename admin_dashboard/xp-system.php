<?php
include '../db.php';

// Handle Add Level
if (isset($_POST['add_level'])) {
    $level = intval($_POST['level']);
    $required_exp = intval($_POST['required_exp']);
    $stmt = $conn->prepare("INSERT INTO levels (level, required_exp) VALUES (?, ?)");
    $stmt->bind_param("ii", $level, $required_exp);
    if ($stmt->execute()) {
        $_SESSION['alert_message'] = "Level added successfully!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert_message'] = "Error adding level: " . $conn->error;
        $_SESSION['alert_type'] = "error";
    }
    $stmt->close();
    echo "<script>window.location.href='admin_layout.php?page=xp-system';</script>";
    exit();
}
// Handle Edit Level
if (isset($_POST['edit_level'])) {
    $level_id = intval($_POST['level_id']);
    $required_exp = intval($_POST['required_exp']);
    $stmt = $conn->prepare("UPDATE levels SET required_exp = ? WHERE level = ?");
    $stmt->bind_param("ii", $required_exp, $level_id);
    if ($stmt->execute()) {
        $_SESSION['alert_message'] = "Level updated successfully!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert_message'] = "Error updating level: " . $conn->error;
        $_SESSION['alert_type'] = "error";
    }
    $stmt->close();
    echo "<script>window.location.href='admin_layout.php?page=xp-system';</script>";
    exit();
}
// Handle Delete Level
if (isset($_POST['delete_level'])) {
    $level_id = intval($_POST['level_id']);
    $stmt = $conn->prepare("DELETE FROM levels WHERE level = ?");
    $stmt->bind_param("i", $level_id);
    if ($stmt->execute()) {
        $_SESSION['alert_message'] = "Level deleted successfully!";
        $_SESSION['alert_type'] = "error";
    } else {
        $_SESSION['alert_message'] = "Error deleting level: " . $conn->error;
        $_SESSION['alert_type'] = "error";
    }
    $stmt->close();
    echo "<script>window.location.href='admin_layout.php?page=xp-system';</script>";
    exit();
}
// Fetch all levels
$levels = [];
$res = $conn->query("SELECT * FROM levels ORDER BY level ASC");
while ($row = $res->fetch_assoc()) {
    $levels[] = $row;
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
    background-color: #0a0a12 !important;
    color: white;
}
.navbar {
    background: rgba(255,255,255,0.13) !important;
    color: white !important;
    backdrop-filter: blur(4px);
}
    .xp-management-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .xp-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .xp-header h1 {
        font-weight: 600;
        margin: 0;
    }
    
    .xp-header p {
        color: #6c757d;
        margin: 0.5rem 0 0;
    }
    
    .xp-card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
        overflow: hidden;
        background: #fff;
    }
    
    .xp-card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .xp-card-header i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }
    
    .xp-card-body {
        padding: 1.5rem;
    }
    
    .xp-table {
        background: #fff;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .xp-table thead th {
        background-color: #f8f9fa;
        border-bottom-width: 1px;
        font-weight: 600;
        color: #495057;
        padding: 1rem 1.5rem;
    }
    
    .xp-table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-color: #f1f3f5;
    }
    
    .xp-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .xp-table tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.03);
    }
    
    .xp-input {
        border-radius: 0.375rem;
        border: 1px solid #e1e5eb;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s;
    }
    
    .xp-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    .btn-xp-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        transition: all 0.2s;
    }
    
    .btn-xp-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        transform: translateY(-1px);
    }
    
    .btn-xp-danger {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
        transition: all 0.2s;
    }
    
    .btn-xp-danger:hover {
        background-color: #e5177e;
        border-color: #e5177e;
        transform: translateY(-1px);
    }
    
    .alert-xp {
        border-radius: 0.5rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border: none;
        box-shadow: var(--card-shadow);
    }
    
    .alert-xp-success {
        background-color: rgba(76, 201, 240, 0.15);
        color: #0d6efd;
    }
    
    .alert-xp-error {
        background-color: rgba(247, 37, 133, 0.15);
        color: #dc3545;
    }
    
    .xp-form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .xp-actions-cell {
        white-space: nowrap;
    }
    
    .xp-actions-cell .btn {
        margin-right: 0.5rem;
    }
    
    .xp-actions-cell .btn:last-child {
        margin-right: 0;
    }
</style>

<div class="xp-management-container">
    <div class="xp-header">
        <h1><i class="fas fa-trophy me-2"></i>XP Levels Management</h1>
        <p>Configure the experience points required for each level in your system</p>
    </div>
    
    <?php if (isset($_SESSION['alert_message'])): ?>
        <div class="alert alert-xp alert-<?= $_SESSION['alert_type'] === 'success' ? 'xp-success' : 'xp-error' ?> d-flex align-items-center">
            <i class="fas <?= $_SESSION['alert_type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
            <?= $_SESSION['alert_message'] ?>
        </div>
        <?php unset($_SESSION['alert_message'], $_SESSION['alert_type']); ?>
    <?php endif; ?>
    
    <div class="xp-card">
        <div class="xp-card-header">
            <i class="fas fa-plus-circle"></i>
            <span>Add New Level</span>
        </div>
        <div class="xp-card-body">
            <form method="post" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="level" class="xp-form-label">Level Number</label>
                    <input type="number" class="form-control xp-input" name="level" min="1" required placeholder="Enter level number">
                </div>
                <div class="col-md-4">
                    <label for="required_exp" class="xp-form-label">Required Experience Points</label>
                    <input type="number" class="form-control xp-input" name="required_exp" min="1" required placeholder="Enter required XP">
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_level" class="btn btn-xp-primary w-100">
                        <i class="fas fa-plus-circle me-2"></i> Add Level
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="xp-card">
        <div class="xp-card-header">
            <i class="fas fa-list-ol"></i>
            <span>Level Progression</span>
        </div>
        <div class="xp-card-body p-0">
            <div class="table-responsive">
                <table class="table xp-table m-0">
                    <thead>
                        <tr>
                            <th width="20%">Level</th>
                            <th width="40%">Required EXP</th>
                            <th width="30%" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($levels)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No levels configured yet
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($levels as $lvl): ?>
                                <tr>
                                    <form method="post">
                                        <td class="fw-semibold"><?= $lvl['level'] ?></td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" name="required_exp" value="<?= $lvl['required_exp'] ?>" class="form-control xp-input" min="1" required>
                                                <span class="input-group-text bg-light">XP</span>
                                            </div>
                                            <input type="hidden" name="level_id" value="<?= $lvl['level'] ?>">
                                        </td>
                                        <td class="xp-actions-cell text-end">
                                            <button type="submit" name="edit_level" class="btn btn-sm btn-xp-primary">
                                                <i class="fas fa-save me-1"></i> Save
                                            </button>
                                            <button type="submit" name="delete_level" class="btn btn-sm btn-xp-danger" onclick="return confirm('Are you sure you want to delete Level <?= $lvl['level'] ?>? This action cannot be undone.')">
                                                <i class="fas fa-trash-alt me-1"></i> Delete
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>