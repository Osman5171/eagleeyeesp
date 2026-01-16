<?php
session_start();
// চেক করা হচ্ছে অ্যাডমিন লগইন আছে কিনা। না থাকলে লগইন পেজে পাঠিয়ে দেবে।
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EagleEye</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #0f172a;
            color: white;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .header h2 { margin: 0; color: #fbbf24; }
        .logout-btn {
            background: #ef4444;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        /* ড্যাশবোর্ড গ্রিড */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: #334155;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            border: 1px solid #475569;
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .card:hover {
            background: #475569;
            transform: translateY(-5px);
            border-color: #fbbf24;
        }
        .card i {
            font-size: 40px;
            margin-bottom: 15px;
            color: #fbbf24;
        }
        .card h3 { margin: 0; font-size: 18px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Admin Panel</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-grid">
        
        <a href="admin_folders.php" class="card">
            <i class="fas fa-folder-plus"></i>
            <h3>Manage Folders</h3>
        </a>

        <a href="admin_add_match.php" class="card">
            <i class="fas fa-plus-circle"></i>
            <h3>Add New Match</h3>
        </a>

        <a href="admin_matches.php" class="card">
            <i class="fas fa-gamepad"></i>
            <h3>Manage Matches</h3>
        </a>

        <a href="admin_deposits.php" class="card">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Deposit Requests</h3>
        </a>

        <a href="admin_users.php" class="card">
            <i class="fas fa-users"></i>
            <h3>All Users</h3>
        </a>

        <a href="admin_settings.php" class="card">
            <i class="fas fa-cogs"></i>
            <h3>Settings</h3>
        </a>

        <a href="admin_slider.php" class="card">
            <i class="fas fa-images"></i>
            <h3>Manage Slider</h3>
        </a>

    </div>

</body>
</html>