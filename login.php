<?php
session_start();
include 'db.php';

if (isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ১. ইমেইল দিয়ে ইউজার খোঁজা (Prepared Statement)
    $stmt = $conn->prepare("SELECT id, name, email, password, balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ২. পাসওয়ার্ড যাচাই করা (password_verify ফাংশন দিয়ে)
        if (password_verify($password, $row['password'])) {
            
            // লগইন সফল - সেশন সেট করা
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            
            echo "<script>
                    alert('Login Successful!');
                    window.location.href='index.php'; 
                  </script>";
        } else {
            // পাসওয়ার্ড ভুল
            echo "<script>
                    alert('Wrong Password!');
                    window.location.href='login.html';
                  </script>";
        }
    } else {
        // ইমেইল পাওয়া যায়নি
        echo "<script>
                alert('No account found with this email!');
                window.location.href='login.html';
              </script>";
    }
    $stmt->close();
}
$conn->close();
?>