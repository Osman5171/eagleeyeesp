<?php 
session_start();
include 'db.php'; // ডাটাবেস কানেকশন

// ১. লগইন চেক
if (!isset($_SESSION['user_name'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user_email'];

// ২. পাসওয়ার্ড পরিবর্তনের লজিক (আগের মতোই রাখা হলো)
if (isset($_POST['change_pass'])) {
    $new_pass = $_POST['new_password'];
    if (!empty($new_pass)) {
        $conn->query("UPDATE users SET password='$new_pass' WHERE email='$email'");
        echo "<script>alert('Password Changed Successfully!');</script>";
    }
}

// ৩. ইউজারের তথ্য আনা
$sql_user = "SELECT * FROM users WHERE email='$email'";
$res_user = $conn->query($sql_user);
$user_data = $res_user->fetch_assoc();

// ৪. স্ট্যাটাস (কতগুলো ম্যাচ খেলেছে)
$sql_matches = "SELECT COUNT(*) as total FROM participants WHERE user_email='$email'";
$res_matches = $conn->query($sql_matches);
$matches_played = $res_matches->fetch_assoc()['total'];

// ৫. প্রোফাইল ছবি সেট করা (নতুন)
// যদি ডাটাবেসে ছবি থাকে তবে uploads ফোল্ডার থেকে দেখাবে, না থাকলে ডিফল্ট আইকন
$photo_path = !empty($user_data['photo']) ? "uploads/" . $user_data['photo'] : "https://cdn-icons-png.flaticon.com/512/149/149071.png";
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EagleEye ESP</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* আপনার দেওয়া ডিজাইন */
        .profile-header {
            background: linear-gradient(to bottom, #1e293b, #0f172a);
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #334155;
        }
        .avatar-box {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
        }
        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid #8b5cf6;
            object-fit: cover;
        }
        .user-name { font-size: 22px; font-weight: bold; color: white; margin-bottom: 5px; }
        .user-id { font-size: 13px; color: #94a3b8; background: #334155; padding: 4px 10px; border-radius: 15px; display: inline-block; }

        /* স্ট্যাটাস বক্স */
        .stats-container {
            display: flex;
            justify-content: space-between;
            background: #1e293b;
            margin: 20px;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #334155;
            text-align: center;
        }
        .stat-box { width: 33%; }
        .stat-box b { font-size: 20px; color: #fff; display: block; }
        .stat-box span { font-size: 12px; color: #94a3b8; }
        .stat-border { border-right: 1px solid #334155; }

        /* মেনু লিস্ট */
        .profile-menu { padding: 0 20px; margin-bottom: 20px; }
        .menu-item {
            background: #1e293b;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            border: 1px solid #334155;
            transition: 0.3s;
            color: white;
            text-decoration: none;
        }
        
        /* পাসওয়ার্ড ফর্ম ডিজাইন */
        .pass-form {
            background: #1e293b;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #334155;
            margin-bottom: 10px;
        }
        .pass-input {
            width: 70%;
            padding: 8px;
            background: #0f172a;
            border: 1px solid #475569;
            color: white;
            border-radius: 5px;
        }
        .pass-btn {
            padding: 8px 12px;
            background: #8b5cf6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .menu-left { display: flex; align-items: center; gap: 15px; }
        .menu-left i { font-size: 18px; color: #8b5cf6; width: 25px; text-align: center; }
        .menu-left span { font-size: 14px; color: #cbd5e1; font-weight: 500; }
        
        .logout-btn {
            display: block;
            margin: 0 20px 30px;
            background: #ef4444;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="profile-header">
        <div class="avatar-box">
            <img src="<?php echo $photo_path; ?>" alt="User" class="avatar-img">
        </div>
        <h2 class="user-name"><?php echo $user_data['name']; ?></h2>
        <span class="user-id">ID: <?php echo $user_data['id']; ?></span>
    </div>

    <div class="stats-container">
        <div class="stat-box stat-border">
            <b><?php echo $matches_played; ?></b>
            <span>Matches</span>
        </div>
        <div class="stat-box stat-border">
            <b>0</b> <span>Kills</span>
        </div>
        <div class="stat-box">
            <b style="color: #4ade80;">৳<?php echo $user_data['balance']; ?></b>
            <span>Balance</span>
        </div>
    </div>

    <div class="section-head">Settings</div>
    <div class="profile-menu">
        
        <a href="edit_profile.php" class="menu-item">
            <div class="menu-left">
                <i class="fas fa-user-edit"></i>
                <span>Edit Profile & Squad</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size: 12px; color: #64748b;"></i>
        </a>

        <div class="pass-form">
            <div style="margin-bottom:10px; color:#cbd5e1; font-size:14px;">
                <i class="fas fa-lock" style="color: #8b5cf6; margin-right:10px;"></i> Change Password
            </div>
            <form method="POST" style="display:flex; gap:10px;">
                <input type="text" name="new_password" class="pass-input" placeholder="New Password" required>
                <button type="submit" name="change_pass" class="pass-btn">Save</button>
            </form>
        </div>

        <a href="#" class="menu-item">
            <div class="menu-left">
                <i class="fas fa-headset"></i>
                <span>Support</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size: 12px; color: #64748b;"></i>
        </a>

        <a href="#" class="menu-item">
            <div class="menu-left">
                <i class="fas fa-shield-alt"></i>
                <span>Privacy Policy</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size: 12px; color: #64748b;"></i>
        </a>

    </div>

    <a href="logout.php" class="logout-btn" onclick="return confirm('Logout?');">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>

    <?php 
    $page = 'profile'; 
    include 'menu.php'; 
?>

</body>
</html>