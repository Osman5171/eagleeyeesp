<?php
session_start();
include 'db.php'; //

if (!isset($_SESSION['admin_logged_in'])) { //
    header("Location: admin_login.php");
    exit();
}

// ফোল্ডার অ্যাড করা
if (isset($_POST['add_folder'])) {
    $name = mysqli_real_escape_string($conn, $_POST['folder_name']);
    $conn->query("INSERT INTO folders (name) VALUES ('$name')");
    echo "<script>alert('Folder Added!'); window.location.href='admin_folders.php';</script>";
}

// ফোল্ডার ডিলিট করা
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM folders WHERE id='$id'");
    header("Location: admin_folders.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Folders</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .box { background: #1e293b; padding: 20px; border-radius: 10px; max-width: 500px; margin: auto; border: 1px solid #334155; }
        input, button { padding: 10px; width: 100%; margin-top: 10px; box-sizing: border-box; border-radius: 5px; border: 1px solid #475569; background: #334155; color: white; }
        .item { background: #334155; padding: 10px; margin-top: 10px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .del-btn { color: #ef4444; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center; color:#fbbf24;">Add New Package/Folder</h2>
        <form method="POST">
            <input type="text" name="folder_name" placeholder="Folder Name (Ex: BR Tournament)" required>
            <button type="submit" name="add_folder" style="background:#22c55e; cursor:pointer;">Create Folder</button>
        </form>

        <div style="margin-top:20px;">
            <h4>Existing Folders:</h4>
            <?php
            $res = $conn->query("SELECT * FROM folders ORDER BY id DESC");
            while($row = $res->fetch_assoc()) {
                echo "<div class='item'>
                        <span>📂 ".$row['name']."</span>
                        <a href='?delete=".$row['id']."' class='del-btn' onclick='return confirm(\"Delete Folder?\")'>Delete</a>
                      </div>";
            }
            ?>
        </div>
        <a href="admin_dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#94a3b8; text-decoration:none;">← Back to Dashboard</a>
    </div>
</body>
</html>