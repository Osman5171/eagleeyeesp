<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Matches - Select Folder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; max-width: 1000px; margin: auto; }
        .card { 
            background: #1e293b; padding: 20px; border-radius: 10px; border: 1px solid #334155; 
            text-align: center; text-decoration: none; color: white; transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); border-color: #fbbf24; }
        .card i { font-size: 40px; color: #fbbf24; margin-bottom: 10px; }
        .card h3 { margin: 10px 0 5px; color: #fbbf24; }
        .count { font-size: 12px; color: #94a3b8; }
        .back-btn { display: block; text-align: center; margin-top: 30px; color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>
    <h2 style="text-align:center; color:#fbbf24;">Select Folder to Manage Matches</h2>
    
    <div class="grid">
        <?php
        // ফোল্ডারগুলো আপনার সেট করা Level অনুযায়ী সাজানো
        $res = $conn->query("SELECT * FROM folders ORDER BY level ASC");
        
        if($res->num_rows > 0) {
            while($row = $res->fetch_assoc()) {
                $fid = $row['id'];
                // এই ফোল্ডারে কয়টি ম্যাচ আছে তা গণনা
                $cnt = $conn->query("SELECT COUNT(*) as t FROM matches WHERE folder_id='$fid'")->fetch_assoc()['t'];
        ?>
        <a href="admin_folder_view.php?folder_id=<?php echo $fid; ?>" class="card">
            <i class="fas fa-folder-open"></i>
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            <span class="count"><?php echo $cnt; ?> Matches inside</span>
        </a>
        <?php 
            }
        } else {
            echo "<p style='text-align:center; width:100%; color:#94a3b8;'>No Folders Found! Go to 'Manage Folders' to create one.</p>";
        }
        ?>
    </div>

    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</body>
</html>