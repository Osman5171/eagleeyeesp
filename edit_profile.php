<?php
session_start();
include 'db.php';

// লগইন চেক
if (!isset($_SESSION['user_name'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user_email'];
$msg = ""; // মেসেজ দেখানোর জন্য ভেরিয়েবল
$msg_color = "red";

// আপডেট লজিক
if (isset($_POST['update_btn'])) {
    // ইনপুট স্যানিটাইজেশন
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $squad_name = trim($_POST['squad_name']);
    $p1 = trim($_POST['p1']);
    $p2 = trim($_POST['p2']);
    $p3 = trim($_POST['p3']);
    $p4 = trim($_POST['p4']);

    $upload_ok = true;
    $new_photo_name = "";

    // ছবি আপলোড লজিক (নিরাপত্তা সহ)
    if (!empty($_FILES['photo']['name'])) {
        $file_name = $_FILES['photo']['name'];
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        
        // ১. এক্সটেনশন চেক
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // ২. ফাইল টাইপ চেক (MIME Type) - হ্যাকাররা এক্সটেনশন রিনেম করলেও ধরা পড়বে
        $check_image = getimagesize($file_tmp);

        if (in_array($file_ext, $allowed_ext) && $check_image !== false) {
            
            // ৩. সাইজ চেক (উদাহরণ: ২ মেগাবাইটের বেশি হতে পারবে না)
            if ($file_size < 2000000) {
                // ৪. ফাইলের নাম রিনেম করা (Random String)
                $new_photo_name = uniqid("IMG_", true) . "." . $file_ext;
                $target = "uploads/" . $new_photo_name;

                if (!move_uploaded_file($file_tmp, $target)) {
                    $msg = "Error uploading file!";
                    $upload_ok = false;
                }
            } else {
                $msg = "File size must be less than 2MB!";
                $upload_ok = false;
            }
        } else {
            $msg = "Only JPG, JPEG & PNG files are allowed!";
            $upload_ok = false;
        }
    }

    // যদি ফাইলে সমস্যা না থাকে, তাহলে ডাটাবেস আপডেট হবে
    if ($upload_ok) {
        if (!empty($new_photo_name)) {
            // যদি নতুন ছবি থাকে
            $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, saved_squad_name=?, saved_player_1=?, saved_player_2=?, saved_player_3=?, saved_player_4=?, photo=? WHERE email=?");
            $stmt->bind_param("sssssssss", $name, $phone, $squad_name, $p1, $p2, $p3, $p4, $new_photo_name, $email);
        } else {
            // যদি ছবি না পাল্টানো হয়
            $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, saved_squad_name=?, saved_player_1=?, saved_player_2=?, saved_player_3=?, saved_player_4=? WHERE email=?");
            $stmt->bind_param("ssssssss", $name, $phone, $squad_name, $p1, $p2, $p3, $p4, $email);
        }

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name; // সেশনের নাম আপডেট
            $msg = "Profile Updated Successfully!";
            $msg_color = "green";
        } else {
            $msg = "Database Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// বর্তমান তথ্য আনা (Prepared Statement দিয়ে)
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - EagleEye</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #0f172a; color: white; padding: 20px; font-family: 'Poppins', sans-serif; }
        .container { max-width: 500px; margin: 0 auto; background: #1e293b; padding: 20px; border-radius: 10px; border: 1px solid #334155; }
        h2 { text-align: center; color: #fbbf24; margin-bottom: 20px; }
        
        label { display: block; margin-top: 10px; color: #94a3b8; font-size: 13px; }
        input { width: 100%; padding: 10px; margin-top: 5px; background: #334155; border: 1px solid #475569; color: white; border-radius: 5px; box-sizing: border-box; }
        
        .section-title { margin-top: 25px; border-bottom: 1px solid #475569; padding-bottom: 5px; color: #8b5cf6; font-weight: bold; font-size: 14px; }
        
        .btn-save { width: 100%; padding: 12px; background: #22c55e; color: white; border: none; border-radius: 5px; font-weight: bold; margin-top: 25px; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { background: #16a34a; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 14px; }
        
        .msg-box { text-align: center; padding: 10px; margin-bottom: 10px; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Edit Profile</h2>
        
        <?php if(!empty($msg)) { ?>
            <div class="msg-box" style="background: <?php echo ($msg_color=='green') ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo ($msg_color=='green') ? '#166534' : '#991b1b'; ?>;">
                <?php echo $msg; ?>
            </div>
        <?php } ?>
        
        <form method="POST" enctype="multipart/form-data">
            
            <div class="section-title">Personal Info</div>
            <label>Profile Photo (Max 2MB, JPG/PNG only)</label>
            <input type="file" name="photo" accept="image/png, image/jpeg, image/jpg">

            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <div class="section-title">Default Squad Details (Auto-fill)</div>
            <p style="font-size: 11px; color: #64748b; margin-top: 5px;">Save your squad info here to auto-fill when joining matches.</p>

            <label>Squad Name</label>
            <input type="text" name="squad_name" value="<?php echo htmlspecialchars($user['saved_squad_name']); ?>" placeholder="Team Name">

            <label>Player 1 (Leader/You)</label>
            <input type="text" name="p1" value="<?php echo htmlspecialchars($user['saved_player_1']); ?>" placeholder="UID or Name">

            <label>Player 2</label>
            <input type="text" name="p2" value="<?php echo htmlspecialchars($user['saved_player_2']); ?>" placeholder="UID or Name">

            <label>Player 3</label>
            <input type="text" name="p3" value="<?php echo htmlspecialchars($user['saved_player_3']); ?>" placeholder="UID or Name">

            <label>Player 4</label>
            <input type="text" name="p4" value="<?php echo htmlspecialchars($user['saved_player_4']); ?>" placeholder="UID or Name">

            <button type="submit" name="update_btn" class="btn-save">Save Changes</button>
        </form>

        <a href="profile.php" class="btn-back">Cancel & Go Back</a>
    </div>

</body>
</html>