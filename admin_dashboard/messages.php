<?php
include '../db.php';
$result = $conn->query("SELECT * FROM contact_details ORDER BY created_at DESC");
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
    
    .id-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        min-width: 40px;
        text-align: center;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.95rem;
    }

    .user-email {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .message-content {
        max-width: 300px;
        line-height: 1.6;
        color: #374151;
        word-wrap: break-word;
    }

    .date-info {
        color: #6b7280;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .no-messages {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }

    .no-messages-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }
        
        .search-container {
            width: 100%;
            margin-top: 1rem;
        }
        
        .search-input {
            width: 100%;
        }
    }
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1 class="page-title">Contact Messages</h1>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search messages...">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="messagesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Contact Information</th>
                    <th>Message</th>
                    <th>Received</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="id-badge">#<?php echo htmlspecialchars($row['c_id']); ?></span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($row['email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <?php 
                                    $date = new DateTime($row['created_at']);
                                    echo $date->format('M d, Y') . '<br>' . $date->format('H:i A');
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="no-messages">
                            <span class="no-messages-icon">📬</span>
                            <div>No messages found.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const input = this.value.toLowerCase();
        const rows = document.querySelectorAll('#messagesTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    });
</script>