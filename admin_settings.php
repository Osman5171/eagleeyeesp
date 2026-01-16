<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_POST['update_notice'])) {
    $new_notice = $_POST['notice_text'];
    $conn->query("UPDATE settings SET key_value='$new_notice' WHERE key_name='home_notice'");
    echo "<script>alert('Notice Updated!');</script>";
}

$res = $conn->query("SELECT key_value FROM settings WHERE key_name='home_notice'");
$current_notice = ($res->num_rows > 0) ? $res->fetch_assoc()['key_value'] : "Welcome to EagleEye ESP!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Settings</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .box { background: #1e293b; padding: 20px; border-radius: 10px; border: 1px solid #334155; max-width: 500px; margin: auto; }
        textarea { width: 100%; padding: 10px; background: #334155; border: 1px solid #475569; color: white; border-radius: 5px; margin-top: 10px; }
        button { background: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 15px; width: 100%; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Update Home Notice</h3>
        <form method="POST">
            <textarea name="notice_text" rows="4"><?php echo $current_notice; ?></textarea>
            <button type="submit" name="update_notice">Save Notice</button>
        </form>
        <a href="admin_dashboard.php" style="color:#94a3b8; text-decoration:none; display:block; margin-top:20px; text-align:center;">← Back</a>
    </div>
</body>
</html>