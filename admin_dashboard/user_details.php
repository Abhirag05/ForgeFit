<?php
if (isset($_SESSION['message'])) {
    $msg = $_SESSION['message'];
    $msg_type = $_SESSION['msg_type'];

    // Set color based on message type
    $color = ($msg_type === "success") ? "#4CAF50" : "#f44336";

    echo "
    <div style='
        background-color: $color;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        margin: 20px auto;
        width: 80%;
        font-family: Segoe UI, sans-serif;
        text-align: center;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
    '>$msg</div>
    ";

    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
}
?>

<style>
    .user-details-container {
        max-width: 1100px;
        margin: 30px auto;
        padding: 20px;
        background: rgba(255,255,255,0.13) !important;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.13);
        transition: transform 0.3s, box-shadow 0.3s;
        font-family: 'Segoe UI', sans-serif;
        backdrop-filter: blur(4px);
    }

    .user-details-container h1 {
        text-align: center;
        margin-bottom: 20px;
        color: white;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .user-table thead {
        background-color: #f5f5f5;
    }

    .user-table th,
    .user-table td {
        padding: 12px 16px;
        border: 1px solid #ddd;
        text-align: center;
    }

    .user-table th {
        font-weight: bold;
        color: #444;
    }

    .user-table tr:nth-child(even) {
        background-color: #f9f9f9;
        color:black
    }

    .action-buttons button {
        margin: 0 4px;
        padding: 6px 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .view-btn { background-color: #4CAF50; color: white; }
    .edit-btn { background-color: #2196F3; color: white; }
    .ban-btn  { background-color: #f44336; color: white; }
</style>
<div class="user-details-container">
     <h1>Registered Users</h1>
    <table class="user-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../db.php';

            $sql = "SELECT u.id, u.fullname, u.email, u.role
                    FROM users u";

            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['fullname']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['role']}</td>
                            <td class='action-buttons'>
                                <a href='view_user.php?id={$row['id']}'><button class='view-btn'>View</button></a>
                                <button class='edit-btn'>Edit</button>
                                <a href='delete_user.php?id={$row['id']}' onclick=\"return confirm('Are you sure you want to delete this user?')\">
                                 <button class='ban-btn'>Delete</button>
                                </a>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No users found.</td></tr>";
            }

            ?>
        </tbody>
    </table>
</div>
