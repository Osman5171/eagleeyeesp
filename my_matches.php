<?php 
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_name'])) {
    header("Location: login.html");
    exit();
}
$email = $_SESSION['user_email'];
?>  

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Matches - EagleEye ESP</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tabs { display: flex; justify-content: space-around; background: #1e293b; padding: 10px; margin-bottom: 20px; border-bottom: 1px solid #334155; }
        .tab-btn { background: none; border: none; color: #94a3b8; font-size: 14px; font-weight: 600; padding-bottom: 5px; cursor: pointer; }
        .tab-btn.active { color: #8b5cf6; border-bottom: 2px solid #8b5cf6; }

        .room-details { background: #334155; padding: 10px; margin: 10px; border-radius: 8px; text-align: center; border: 1px dashed #94a3b8; }
        .room-text { font-size: 13px; color: #cbd5e1; margin-bottom: 5px; }
        .room-code { font-size: 15px; font-weight: bold; color: #fbbf24; letter-spacing: 1px; }

        .announcement-box {
            background: rgba(234, 179, 8, 0.1);
            border-left: 3px solid #eab308;
            padding: 10px;
            margin: 10px;
            font-size: 13px;
            color: #fef08a;
            text-align: left;
        }
        
        .btn-download {
            display: block;
            background: #2563eb;
            color: white;
            text-align: center;
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #1d4ed8;
            transition: 0.3s;
        }
        .btn-download:hover { background: #1d4ed8; transform: translateY(-2px); }
    </style>
</head>
<body>

    <header>
        <div class="logo"><h2>My <span class="highlight">Matches</span></h2></div>
        <div class="header-icons">
             <a href="logout.php"><i class="fas fa-sign-out-alt" style="color: #ef4444; font-size: 20px;"></i></a>
        </div>
    </header>

    <div class="tabs">
        <button class="tab-btn active">Joined Matches</button>
    </div>

    <section class="match-container" style="padding-bottom: 80px;">
        
        <?php
        // Prepared Statement ব্যবহার করা হচ্ছে
        $stmt = $conn->prepare("SELECT m.* FROM matches m 
                                JOIN participants p ON m.id = p.match_id 
                                WHERE p.user_email = ? 
                                ORDER BY m.id DESC");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // htmlspecialchars দিয়ে আউটপুট স্যানিটাইজ করা
                $roomId = ($row['room_id'] == 'Wait' || $row['room_id'] == '') ? 'Waiting...' : htmlspecialchars($row['room_id']);
                $roomPass = ($row['room_pass'] == 'Wait' || $row['room_pass'] == '') ? 'Waiting...' : htmlspecialchars($row['room_pass']);
                $title = htmlspecialchars($row['title']);
                $entry = htmlspecialchars($row['entry_fee']);
                $time = htmlspecialchars($row['time']);
                $prize = htmlspecialchars($row['prize']);
        ?>

        <div class="match-card">
            <div class="card-top" style="height: 100px; background-image: url('https://wallpapers.com/images/hd/free-fire-bermuda-map-4k-jmb6q8p9r1e4j3t0.jpg');"> 
                <div class="match-title"><?php echo $title; ?></div>
                <div class="live-badge" style="background: #22c55e; top: 10px; right: 10px; position:absolute; padding: 2px 8px; border-radius: 4px; font-size: 10px;">Joined</div>
            </div>
            
            <div class="room-details">
                <p class="room-text"><i class="fas fa-key"></i> Room ID & Password</p>
                <div class="room-code">ID: <?php echo $roomId; ?> | Pass: <?php echo $roomPass; ?></div>
            </div>

            <?php if(!empty($row['announcement'])) { ?>
                <div class="announcement-box">
                    <i class="fas fa-bullhorn"></i> <b>Admin Notice:</b><br>
                    <?php echo nl2br(htmlspecialchars($row['announcement'])); ?>
                </div>
            <?php } ?>

            <?php if(!empty($row['result_file'])) { ?>
                <a href="results/<?php echo htmlspecialchars($row['result_file']); ?>" class="btn-download" download>
                    <i class="fas fa-download"></i> Download Full Result
                </a>
            <?php } ?>

            <div class="card-info">
                <div class="info-box"><span>Entry</span><b style="color: #e94560;">৳<?php echo $entry; ?></b></div>
                <div class="info-box"><span>Time</span><b><?php echo $time; ?></b></div>
                <div class="info-box"><span>Prize</span><b>৳<?php echo $prize; ?></b></div>
            </div>
        </div>
        <?php 
            }
        } else {
            echo "<div style='text-align:center; padding:50px; color:#64748b;'>
                    <i class='fas fa-folder-open' style='font-size:40px; margin-bottom:15px;'></i>
                    <p>No matches joined yet!</p>
                  </div>";
        }
        $stmt->close();
        ?>

    </section>

   <?php 
    $page = 'matches'; // এই পেজের নাম 'matches'
    include 'menu.php'; 
?>

</body>
</html>