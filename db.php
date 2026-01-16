<?php
$servername = "localhost";
$username = "root";  // XAMPP এর ডিফল্ট ইউজারনেম
$password = "";      // XAMPP এর ডিফল্ট পাসওয়ার্ড (খালি থাকে)
$dbname = "eagleeye_db"; // আমাদের ডাটাবেসের নাম

// কানেকশন তৈরি করা হচ্ছে
$conn = new mysqli($servername, $username, $password, $dbname);

// চেক করা হচ্ছে কানেকশন ঠিক আছে কিনা
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>