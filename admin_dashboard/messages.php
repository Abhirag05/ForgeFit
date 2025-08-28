<?php
include '../db.php';
$result = $conn->query("SELECT * FROM contact_details ORDER BY created_at DESC");
?>

<style>
.messages-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background: #f8fafc;
    min-height: 100vh;
}

.messages-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.messages-header h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
}

.messages-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.messages-table-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.messages-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.messages-table thead {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
}

.messages-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: none;
}

.messages-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.messages-table tbody tr:hover {
    background: linear-gradient(135deg, #f3f4f6 0%, #f9fafb 100%);
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.messages-table tbody tr:last-child {
    border-bottom: none;
}

.messages-table td {
    padding: 1.25rem 1rem;
    vertical-align: top;
    border: none;
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

.search-filter {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-input {
    flex: 1;
    min-width: 250px;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
}

.filter-badge {
    background: #e0e7ff;
    color: #4338ca;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .messages-container {
        padding: 1rem;
    }
    
    .messages-header h1 {
        font-size: 2rem;
    }
    
    .messages-stats {
        flex-direction: column;
        align-items: center;
    }
    
    .messages-table-container {
        overflow-x: auto;
    }
    
    .messages-table {
        min-width: 600px;
    }
    
    .search-filter {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="messages-container">
    <div class="messages-header">
        <h1>Contact Messages</h1>
        <div class="messages-stats">
            <div class="stat-item">
                <strong><?php echo $result ? $result->num_rows : 0; ?></strong> Total Messages
            </div>
            <div class="stat-item">
                <strong><?php echo date('M d, Y'); ?></strong> Last Updated
            </div>
        </div>
    </div>

    <div class="search-filter">
        <input type="text" class="search-input" placeholder="Search messages..." id="searchInput">
        <span class="filter-badge">All Messages</span>
    </div>

    <div class="messages-table-container">
        <table class="messages-table">
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
                            <div style="margin-top: 0.5rem; font-size: 0.875rem;">Messages will appear here once received.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('.messages-table tbody tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            if (row.querySelector('.no-messages')) return;
            
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>