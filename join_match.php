<?php
session_start();
include 'db.php';

// ১. লগইন চেক
if (!isset($_SESSION['user_name'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user_email'];
$match_id = $_GET['match_id'];

// ২. ম্যাচের তথ্য আনা
$sql_match = "SELECT * FROM matches WHERE id='$match_id'";
$result_match = $conn->query($sql_match);
$match = $result_match->fetch_assoc();
$type = $match['match_type']; // Solo, Duo, or Squad

// ৩. ইউজারের ব্যালেন্স এবং সেভ করা স্কোয়াড তথ্য আনা (Updated)
$sql_user = "SELECT * FROM users WHERE email='$email'";
$result_user = $conn->query($sql_user);
$user_data = $result_user->fetch_assoc();
$user_balance = $user_data['balance'];

// ৪. জয়েন লজিক
if (isset($_POST['confirm_join'])) {
    // ফর্ম থেকে ডাটা নেওয়া
    $squad_name = isset($_POST['squad_name']) ? $_POST['squad_name'] : '';
    $p1 = $_POST['player_1'];
    $p2 = isset($_POST['player_2']) ? $_POST['player_2'] : '';
    $p3 = isset($_POST['player_3']) ? $_POST['player_3'] : '';
    $p4 = isset($_POST['player_4']) ? $_POST['player_4'] : '';
    
    $fee = $match['entry_fee'];

    // ক. ব্যালেন্স চেক
    if ($user_balance < $fee) {
        echo "<script>alert('Insufficient Balance! Please deposit money.'); window.location.href='wallet.php';</script>";
        exit();
    }

    // খ. সিট চেক
    if ($match['joined_slots'] >= $match['total_slots']) {
        echo "<script>alert('Match is Full!'); window.location.href='index.php';</script>";
        exit();
    }

    // গ. ডুপ্লিকেট জয়েন চেক
    $check_join = "SELECT * FROM participants WHERE match_id='$match_id' AND user_email='$email'";
    if ($conn->query($check_join)->num_rows > 0) {
        echo "<script>alert('You already joined this match!'); window.location.href='my_matches.php';</script>";
        exit();
    }

    // ঘ. টাকা কাটা ও ডাটা সেভ করা
    $conn->query("UPDATE users SET balance = balance - $fee WHERE email='$email'");
    
    $stmt = $conn->prepare("INSERT INTO participants (match_id, user_email, squad_name, player_1, player_2, player_3, player_4) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $match_id, $email, $squad_name, $p1, $p2, $p3, $p4);
    $stmt->execute();

    $conn->query("UPDATE matches SET joined_slots = joined_slots + 1 WHERE id='$match_id'");

    echo "<script>alert('Joined Successfully!'); window.location.href='my_matches.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Match - EagleEye</title>
    <style>
        body {
            background-color: #0f172a;
            color: white;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }
        .join-box {
            background: #1e293b;
            padding: 30px;
            border-radius: 15px;
            width: 350px;
            border: 1px solid #334155;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        h2 { color: #fbbf24; margin-bottom: 5px; text-align: center; }
        .info { margin: 10px 0; font-size: 14px; color: #cbd5e1; text-align: center; }
        .match-type-badge {
            background: #6366f1; padding: 5px 10px; border-radius: 15px; font-size: 12px; margin-left: 5px;
        }
        .fee-tag { font-size: 20px; font-weight: bold; color: #e11d48; display: block; text-align: center; margin-bottom: 15px; }
        
        label { display: block; font-size: 13px; color: #94a3b8; margin-top: 10px; margin-bottom: 3px; }
        input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #475569;
            background: #334155;
            color: white;
            box-sizing: border-box;
        }
        .btn-confirm {
            width: 100%;
            padding: 12px;
            background: #22c55e;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-confirm:hover { background: #16a34a; }
        .btn-cancel {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="join-box">
        <h2>Enter Details</h2>
        <p class="info">
            Match: <b><?php echo $match['title']; ?></b>
            <span class="match-type-badge"><?php echo $type; ?></span>
        </p>
        <span class="fee-tag">Entry Fee: ৳<?php echo $match['entry_fee']; ?></span>

        <form method="POST">
            
            <?php if($type != 'Solo'): ?>
                <label>Team/Squad Name (Optional):</label>
                <input type="text" name="squad_name" value="<?php echo $user_data['saved_squad_name']; ?>" placeholder="Ex: Team Eagle">
            <?php endif; ?>

            <label>Player 1 (You) <span style="color:red">*</span>:</label>
            <input type="text" name="player_1" value="<?php echo $user_data['saved_player_1']; ?>" placeholder="UID or In-Game Name" required>

            <?php if($type == 'Duo' || $type == 'Squad'): ?>
                <label>Player 2:</label>
                <input type="text" name="player_2" value="<?php echo $user_data['saved_player_2']; ?>" placeholder="UID or Name" required>
            <?php endif; ?>

            <?php if($type == 'Squad'): ?>
                <label>Player 3:</label>
                <input type="text" name="player_3" value="<?php echo $user_data['saved_player_3']; ?>" placeholder="UID or Name">

                <label>Player 4:</label>
                <input type="text" name="player_4" value="<?php echo $user_data['saved_player_4']; ?>" placeholder="UID or Name">
            <?php endif; ?>
            
            <?php if($user_balance >= $match['entry_fee']): ?>
                <button type="submit" name="confirm_join" class="btn-confirm">Pay & Join</button>
            <?php else: ?>
                <a href="wallet.php" class="btn-confirm" style="background: #e11d48; display:block; text-decoration:none; text-align:center;">Add Money (৳<?php echo $user_balance; ?>)</a>
            <?php endif; ?>
        </form>

        <a href="index.php" class="btn-cancel">Cancel</a>
    </div>

</body>
</html>