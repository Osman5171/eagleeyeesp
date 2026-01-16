<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// User Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id='$id'");
    header("Location: admin_users.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users - Admin</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #334155; text-align: center; }
        th { background: #334155; color: #fbbf24; }
        .btn-del { background: #ef4444; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .back-btn { color: #94a3b8; text-decoration: none; display: block; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <h2 style="text-align:center; color:#fbbf24;">Registered Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>
        <?php
        $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
        while($row = $res->fetch_assoc()) {
            echo "<tr>
                <td>".$row['id']."</td>
                <td>".$row['name']."</td>
                <td>".$row['email']."</td>
                <td style='color:#4ade80;'>৳".$row['balance']."</td>
                <td><a href='?delete=".$row['id']."' class='btn-del' onclick='return confirm(\"Delete User?\")'>Delete</a></td>
            </tr>";
        }
        ?>
    </table>
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
</body>
</html>