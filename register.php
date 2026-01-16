<?php
// ডাটাবেস কানেকশন
include 'db.php';

if (isset($_POST['register_btn'])) {
    // ইনপুট স্যানিটাইজেশন (বাড়তি স্পেস বা ক্ষতিকর ক্যারেক্টার রিমুভ)
    $name = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // পাসওয়ার্ড কনফার্মেশন চেক
    if ($password !== $confirm_password) {
        echo "<script>alert('Password did not match!'); window.location.href='register.html';</script>";
        exit();
    }

    // ১. ইমেইল বা ফোন নম্বর আগে আছে কিনা চেক (Prepared Statement ব্যবহার করে)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>
                alert('Email or Phone Number already exists!');
                window.location.href='register.html';
              </script>";
    } else {
        // ২. পাসওয়ার্ড হ্যাশ করা (এনক্রিপশন)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ৩. নতুন ইউজার ইনসার্ট (Prepared Statement)
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, balance) VALUES (?, ?, ?, ?, 0)");
        // 'ssssi' মানে string, string, string, string, integer (balance 0)
        // তবে এখানে balance ডিফল্ট থাকলে SQL এ না দিলেও চলে, আমি সেইফ থাকার জন্য 0 দিয়েছি
        // আপনার টেবিলে balance কলামের ডিফল্ট ভ্যালু সেট করা থাকলে নিচের লাইনটি ব্যবহার করুন:
        // $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
        
        $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Registration Successful! Please Login.');
                    window.location.href='login.html';
                  </script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
    $stmt->close();
}
$conn->close();
?>