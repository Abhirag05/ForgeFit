<?php
include '../db.php';

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM meals WHERE meal_id = $delete_id");
    $_SESSION['message'] = "Meal deleted successfully!";
    $_SESSION['msg_type'] = "error";
    echo "<script>window.location.href='admin_layout.php?page=admin_meals';</script>";
    exit();
}

// Pagination
$results_per_page = 9;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $results_per_page;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'meal_id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$valid_sort_columns = ['meal_id', 'food_name', 'calories', 'protein', 'carbs', 'fat','fibre'];
$sort = in_array($sort, $valid_sort_columns) ? $sort : 'meal_id';
$order = $order === 'ASC' ? 'ASC' : 'DESC';

// Get total number of meals
$total_results = $conn->query("SELECT COUNT(*) as total FROM meals")->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);

// Get meals for current page
$result = $conn->query("SELECT * FROM meals ORDER BY $sort $order LIMIT $offset, $results_per_page");
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
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        position: relative;
    }
    
    .table th.sort-asc:after {
        content: "↑";
        margin-left: 5px;
    }
    
    .table th.sort-desc:after {
        content: "↓";
        margin-left: 5px;
    }
    
    .table td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: #212529;
        vertical-align: middle;
    }
    
    .table tr:last-child td {
        border-bottom: none;
    }
    
    .table tr:hover td {
        background-color: rgba(248, 250, 252, 0.7);
    }
    
    .action-btns {
        display: flex;
        gap: 0.5rem;
    }
    
    .search-container {
        display: flex;
        align-items: center;
        border-radius: 8px;
        padding: 0.5rem;
        background-color: rgba(255, 255, 255, 0.9);
        border: 1px solid #e2e8f0;
    }
    
    .search-input {
        border: none;
        outline: none;
        padding: 0.5rem;
        width: 250px;
        font-size: 0.9rem;
        background: transparent;
    }
    
    .search-btn {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 0 0.5rem;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .page-item {
        list-style: none;
    }
    
    .page-link {
        display: block;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        background-color: rgba(255, 255, 255, 0.9);
        color: #4361ee;
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }
    
    .page-link:hover {
        background-color: #4361ee;
        color: white;
    }
    
    .page-item.active .page-link {
        background-color: #4361ee;
        color: white;
        border-color: #4361ee;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
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
    
    .btn-warning {
        background-color: #f8961e;
        color: white;
    }
    
    .btn-warning:hover {
        background-color: #e0871b;
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }
        
        .action-btns {
            flex-direction: column;
        }
        
        .search-container {
            width: 100%;
            margin-top: 1rem;
        }
        
        .search-input {
            width: 100%;
        }
        
        .pagination {
            gap: 0.25rem;
        }
        
        .page-link {
            padding: 0.5rem 0.75rem;
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
    
    <div class="card">
        <div class="card-header">
            <h1 class="page-title">Meals Management</h1>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search meals...">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="mealsTable">
                    <thead>
                        <tr>
                            <th class="<?= $sort === 'meal_id' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('meal_id', '<?= $sort === 'meal_id' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">ID</th>
                            <th class="<?= $sort === 'food_name' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('food_name', '<?= $sort === 'food_name' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">Food Name</th>
                            <th>Unit</th>
                            <th class="<?= $sort === 'calories' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('calories', '<?= $sort === 'calories' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">Calories</th>
                            <th class="<?= $sort === 'protein' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('protein', '<?= $sort === 'protein' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">Protein</th>
                            <th class="<?= $sort === 'carbs' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('carbs', '<?= $sort === 'carbs' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">Carbs</th>
                            <th class="<?= $sort === 'fat' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('fat', '<?= $sort === 'fat' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">Fat</th>
                                <th class="<?= $sort === 'fibre' ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>"
                                onclick="sortTable('fibre', '<?= $sort === 'fibre' && $order === 'DESC' ? 'ASC' : 'DESC' ?>')">Fibre</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['meal_id'] ?></td>
                                <td><?= htmlspecialchars($row['food_name']) ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= number_format($row['calories'], 2) ?></td>
                                <td><?= number_format($row['protein'], 2) ?></td>
                                <td><?= number_format($row['carbs'], 2) ?></td>
                                <td><?= number_format($row['fat'], 2) ?></td>
                                <td><?= number_format($row['fibre'], 2) ?></td>
                                <td class="action-btns">
                                    <a href="admin_layout.php?page=admin_add_update_meal&edit_id=<?= $row['meal_id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="admin_layout.php?page=admin_meals&delete_id=<?= $row['meal_id'] ?>" onclick="return confirm('Are you sure you want to delete this meal?')" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No meals found in the database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                // Previous button
                $prev_page = max(1, $page - 1);
                $prev_disabled = ($page <= 1) ? 'disabled' : '';
                ?>
                <li class="page-item <?= $prev_disabled ?>">
                    <a class="page-link" href="admin_layout.php?page=admin_meals&p=<?= $prev_page ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php 
                // Show first page if not in initial range
                if ($page > 3): ?>
                    <li class="page-item">
                        <a class="page-link" href="admin_layout.php?page=admin_meals&p=1&sort=<?= $sort ?>&order=<?= $order ?>">1</a>
                    </li>
                    <?php if ($page > 4): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php 
                // Page numbers around current page
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="admin_layout.php?page=admin_meals&p=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php 
                // Show last page if not in final range
                if ($page < $total_pages - 2): ?>
                    <?php if ($page < $total_pages - 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="admin_layout.php?page=admin_meals&p=<?= $total_pages ?>&sort=<?= $sort ?>&order=<?= $order ?>"><?= $total_pages ?></a>
                    </li>
                <?php endif; ?>
                
                <?php 
                // Next button
                $next_page = min($total_pages, $page + 1);
                $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
                ?>
                <li class="page-item <?= $next_disabled ?>">
                    <a class="page-link" href="admin_layout.php?page=admin_meals&p=<?= $next_page ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <div class="card-body" style="text-align: center;">
            <a href="admin_layout.php?page=admin_add_update_meal" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New Meal
            </a>
        </div>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const input = this.value.toLowerCase();
        const rows = document.querySelectorAll('#mealsTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    });
    
    // Auto-close alert after 5 seconds
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    }
    
    // Sorting function - improved to maintain all URL parameters
    function sortTable(column, order) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', column);
        url.searchParams.set('order', order);
        // Reset to page 1 when changing sort
        url.searchParams.set('p', 1);
        window.location.href = url.toString();
    }
</script>