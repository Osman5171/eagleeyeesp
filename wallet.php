<?php 
session_start();
include 'db.php'; // ডাটাবেস কানেকশন

// ১. লগইন চেক (না থাকলে লগইন পেজে পাঠাবে)
if (!isset($_SESSION['user_name'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user_email'];

// ২. ডিপোজিট রিকোয়েস্ট সাবমিট লজিক
if (isset($_POST['deposit_btn'])) {
    $method = $_POST['method'];
    $amount = $_POST['amount'];
    $sender_number = $_POST['sender_number'];
    $trx_id = $_POST['trx_id'];

    // ডাটাবেসে রিকোয়েস্ট জমা করা
    $sql = "INSERT INTO deposits (user_email, method, sender_number, amount, trx_id) 
            VALUES ('$email', '$method', '$sender_number', '$amount', '$trx_id')";
    
    if ($conn->query($sql) === TRUE) {
        // --- পরিবর্তন: পেজ রিফ্রেশ লজিক যুক্ত করা হলো ---
        echo "<script>
                alert('Deposit Request Submitted! Please wait for admin approval.');
                window.location.href='wallet.php';
              </script>";
        exit();
        // ----------------------------------------------
    } else {
        echo "<script>alert('Something went wrong!');</script>";
    }
}

// ৩. বর্তমান ব্যালেন্স বের করা
$balance = 0;
// ব্যালেন্স কলাম চেক করার সেফটি লজিক
if(isset($email)){
    $sql_bal = "SELECT balance FROM users WHERE email='$email'";
    $res_bal = $conn->query($sql_bal);
    if ($res_bal && $res_bal->num_rows > 0) {
        $balance = $res_bal->fetch_assoc()['balance'];
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - EagleEye ESP</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ওয়ালেট পেজের স্পেশাল স্টাইল */
        .wallet-card {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            margin: 20px;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid #334155;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
            color: white;
        }
        .balance-title { font-size: 14px; color: #e2e8f0; }
        .balance-amount { font-size: 32px; font-weight: bold; margin: 10px 0; }

        .payment-methods {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .method-card {
            background: #334155;
            min-width: 140px;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #475569;
            color: white;
        }
        .method-card h4 { margin: 5px 0; color: #fbbf24; }
        .copy-num { 
            background: #1e293b; padding: 5px; border-radius: 5px; font-size: 12px; color: #cbd5e1; display: block; margin-top: 5px; cursor: pointer;
        }

        .deposit-form {
            background: #1e293b;
            margin: 20px;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #334155;
        }
        .form-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            background: #334155;
            border: 1px solid #475569;
            color: white;
            border-radius: 5px;
            box-sizing: border-box; 
        }
        .btn-deposit-submit {
            width: 100%;
            padding: 12px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .history-list { padding: 0 20px; padding-bottom: 80px; }
        .history-item {
            background: #334155;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #475569;
        }
        .status-Pending { color: #fbbf24; font-size: 12px; font-weight: bold; }
        .status-Approved { color: #4ade80; font-size: 12px; font-weight: bold; }
        .status-Rejected { color: #f87171; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

    <header>
        <div class="logo"><h2>My <span class="highlight">Wallet</span></h2></div>
        <div class="header-icons">
             <a href="logout.php"><i class="fas fa-sign-out-alt" style="color: #ef4444; font-size: 20px;"></i></a>
        </div>
    </header>

    <div class="wallet-card">
        <p class="balance-title">Current Balance</p>
        <h1 class="balance-amount">৳<?php echo $balance; ?></h1>
        <small>Deposit money to join matches</small>
    </div>

    <div class="section-head">Payment Methods</div>
    <div class="payment-methods">
        <?php
        $pay_sql = "SELECT * FROM payment_methods";
        $pay_res = $conn->query($pay_sql);
        
        // যদি ডাটাবেসে কোনো মেথড না থাকে
        if ($pay_res->num_rows == 0) {
             echo "<p style='color:#94a3b8; padding-left:20px;'>No payment methods added yet.</p>";
        }

        while($pay = $pay_res->fetch_assoc()) {
        ?>
            <div class="method-card">
                <i class="fas fa-money-bill-wave" style="color: #e11d48; font-size: 24px;"></i>
                <h4><?php echo $pay['method_name']; ?></h4>
                <span class="copy-num" onclick="alert('Number Copied!');"><?php echo $pay['number']; ?> <i class="fas fa-copy"></i></span>
                <small style="font-size: 10px; color: #94a3b8;"><?php echo $pay['instruction']; ?></small>
            </div>
        <?php } ?>
    </div>

    <div class="section-head">Add Money Request</div>
    <form class="deposit-form" method="POST">
        <select name="method" class="form-input" required>
            <option value="">Select Method</option>
            <option value="Bkash">Bkash</option>
            <option value="Nagad">Nagad</option>
        </select>
        <input type="number" name="amount" class="form-input" placeholder="Amount (Ex: 50)" required>
        <input type="text" name="sender_number" class="form-input" placeholder="Sender Number (Last 4 digits)" required>
        <input type="text" name="trx_id" class="form-input" placeholder="Transaction ID (TrxID)" required>
        <button type="submit" name="deposit_btn" class="btn-deposit-submit">Submit Request</button>
    </form>

    <div class="section-head">Recent Transactions</div>
    <div class="history-list">
        <?php
        $hist_sql = "SELECT * FROM deposits WHERE user_email='$email' ORDER BY id DESC LIMIT 5";
        $hist_res = $conn->query($hist_sql);
        
        if ($hist_res->num_rows > 0) {
            while($hist = $hist_res->fetch_assoc()) {
                $status = $hist['status']; // Pending / Approved
        ?>
            <div class="history-item">
                <div>
                    <div style="font-weight: bold; color: white;"><?php echo $hist['method']; ?> Deposit</div>
                    <div style="font-size: 12px; color: #94a3b8;"><?php echo $hist['created_at']; ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold; color: white;">+৳<?php echo $hist['amount']; ?></div>
                    <div class="status-<?php echo $status; ?>"><?php echo $status; ?></div>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<p style='text-align:center; color:#64748b;'>No transactions found.</p>";
        }
        ?>
    </div>

    <?php 
    $page = 'wallet'; 
    include 'menu.php'; 
?>

</body>
</html>