<?php
session_start();
include 'db.php';

// অলরেডি লগইন থাকলে ড্যাশবোর্ডে পাঠাবে
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // ১. ইউজারনেম চেক করা (Prepared Statement)
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // ২. পাসওয়ার্ড ভেরিফাই করা (হ্যাশ চেক)
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $row['username'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Wrong Password!";
        }
    } else {
        $error = "Admin Username not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Secure</title>
    <style>
        body {
            background-color: #0f172a;
            color: white;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: #1e293b;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            width: 320px;
            text-align: center;
            border: 1px solid #334155;
        }
        .login-box h2 { margin-bottom: 20px; color: #fbbf24; }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #475569;
            background: #334155;
            color: white;
            box-sizing: border-box;
            outline: none;
        }
        input:focus { border-color: #fbbf24; }
        .btn-admin {
            width: 100%;
            padding: 12px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            font-size: 15px;
            transition: 0.3s;
        }
        .btn-admin:hover { background: #dc2626; }
        .error { 
            background: rgba(239, 68, 68, 0.2); 
            color: #f87171; 
            padding: 10px; 
            border-radius: 5px;
            font-size: 13px; 
            margin-bottom: 15px; 
            border: 1px solid #ef4444;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Admin Panel</h2>
        
        <?php if(!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Admin Username" required autocomplete="off">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="admin_login" class="btn-admin">Login securely</button>
        </form>
    </div>

</body>
</html>