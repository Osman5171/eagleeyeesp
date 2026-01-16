<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// ১. নতুন ছবি আপলোড লজিক
if (isset($_POST['upload_slider'])) {
    $target_dir = "uploads/slider/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES["slide_image"]["type"], $allowed_types)) {
        $msg = "Only image files allowed!";
    } elseif ($_FILES["slide_image"]["size"] > $max_size) {
        $msg = "File too large! Max 5MB.";
    } else {
        $file_name = time() . "_" . basename($_FILES["slide_image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["slide_image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO slider_images (image_path) VALUES (?)");
            $stmt->bind_param("s", $file_name);
            if ($stmt->execute()) {
                $msg = "Slide Added Successfully!";
            }
        }
    }
}

// ২. টাইমিং আপডেট লজিক
if (isset($_POST['update_timer'])) {
    $new_timer = intval($_POST['timer_value']) * 1000;
    $stmt = $conn->prepare("UPDATE settings SET key_value=? WHERE key_name='slider_timer'");
    $stmt->bind_param("i", $new_timer);
    if ($stmt->execute()) {
        $msg = "Timer Updated to " . intval($_POST['timer_value']) . " Seconds!";
    }
}

// ৩. ছবি ডিলিট লজিক
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM slider_images WHERE id=?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: admin_slider.php");
}

// বর্তমান টাইমিং আনা
$res_timer = $conn->query("SELECT key_value FROM settings WHERE key_name='slider_timer'");
$current_timer = $res_timer->fetch_assoc()['key_value'] / 1000;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Slider - Admin</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .box { background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155; }
        input, button { padding: 10px; margin: 5px 0; border-radius: 5px; border: none; }
        .btn-add { background: #22c55e; color: white; cursor: pointer; }
        .btn-del { background: #ef4444; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #334155; padding: 10px; text-align: center; }
        img { border-radius: 5px; border: 1px solid #475569; }
    </style>
</head>
<body>
    <h2>Slider Control Panel</h2>
    <?php if($msg) echo "<p style='color:#fbbf24;'>$msg</p>"; ?>

    <div class="box">
        <h3>Change Slide Timing</h3>
        <form method="POST">
            <input type="number" name="timer_value" value="<?php echo $current_timer; ?>" placeholder="Seconds (e.g. 3)" required>
            <button type="submit" name="update_timer" class="btn-add">Set Timing</button>
        </form>
    </div>

    <div class="box">
        <h3>Add New Image (Unlimited)</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="slide_image" required>
            <button type="submit" name="upload_slider" class="btn-add">Upload Photo</button>
        </form>
    </div>

    <div class="box">
        <h3>Current Slides</h3>
        <table>
            <tr><th>Preview</th><th>Action</th></tr>
            <?php
            $res = $conn->query("SELECT * FROM slider_images");
            while($row = $res->fetch_assoc()) {
                echo "<tr>
                    <td><img src='uploads/slider/".$row['image_path']."' width='150'></td>
                    <td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='delete_id' value='".$row['id']."'>
                            <button type='submit' class='btn-del' onclick='return confirm(\"Delete?\")'>Delete</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>
    <a href="admin_dashboard.php" style="color:#94a3b8; text-decoration:none;">← Back to Dashboard</a>
</body>
</html>