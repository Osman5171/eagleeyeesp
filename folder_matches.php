<?php
session_start();
include 'db.php';

$page = 'home';
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

// ফোল্ডারের নাম আনা
$f_res = $conn->query("SELECT name FROM folders WHERE id='$folder_id'");
$folder_name = ($f_res->num_rows > 0) ? $f_res->fetch_assoc()['name'] : "Matches";

// ব্যালেন্স বের করা
$user_balance = 0;
if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user_balance = $stmt->get_result()->fetch_assoc()['balance'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $folder_name; ?> - EagleEye</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="section-head" style="margin-top:20px; text-align:center; border:none;">
        <a href="index.php" style="float:left; color:#ff6600; text-decoration:none;"><i class="fas fa-arrow-left"></i> Back</a>
        <?php echo $folder_name; ?> Matches
    </div>

    <section class="match-grid" style="padding: 0 15px 80px;">
        <?php
        $sql = "SELECT * FROM matches WHERE folder_id='$folder_id' AND status='Active' ORDER BY id DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $m_id = $row['id'];
                $joined = $row['joined_slots'];
                $total = $row['total_slots'];
                $percent = ($total > 0) ? ($joined / $total) * 100 : 0;
                $is_full = ($joined >= $total);
                $start_time = $row['start_time']; 
        ?>
            <div class="match-card">
                <div class="card-header">
                    <img src="https://cdn-icons-png.flaticon.com/512/2883/2883824.png" class="game-icon">
                    <div class="header-info">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <span><?php echo htmlspecialchars($row['time']); ?></span>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-item"><span>Prize</span><b>৳<?php echo $row['prize']; ?></b></div>
                    <div class="stat-item"><span>Kill</span><b>৳<?php echo $row['per_kill']; ?></b></div>
                    <div class="stat-item"><span>Fee</span><b>৳<?php echo $row['entry_fee']; ?></b></div>
                </div>

                <div class="details-grid">
                    <div class="detail-item"><span>Type</span><b><?php echo $row['match_type']; ?></b></div>
                    <div class="detail-item"><span>Map</span><b><?php echo $row['map']; ?></b></div>
                </div>

                <div class="action-area">
                    <div class="progress-wrap">
                        <div class="progress-bar-bg"><div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div></div>
                        <div class="progress-text"><span><?php echo $joined; ?> Joined</span><span><?php echo $total - $joined; ?> Left</span></div>
                    </div>
                    <?php if ($is_full) { echo '<button class="btn-full">FULL</button>'; } 
                    else { echo '<a href="join_match.php?match_id='.$m_id.'"><button class="btn-join">Join</button></a>'; } ?>
                </div>

                <div class="footer-buttons">
                    <button class="btn-footer" onclick='showPrizeList(<?php echo $row["prize_details"]; ?>)'>Prize List</button>
                    <a href="my_matches.php" class="btn-footer">ROOM ID</a>
                </div>

                <div class="card-timer" id="timer-<?php echo $m_id; ?>" data-start="<?php echo $start_time; ?>">Loading...</div>
            </div>
        <?php 
            }
        } else {
            echo "<p style='text-align:center; width:100%; color:#555;'>No Matches in this category!</p>";
        }
        ?>
    </section>

    <script>
        // (তোমার আগের দেওয়া টাইমার এবং মোডাল স্ক্রিপ্ট এখানে কপি করে দাও)
    </script>

    <?php include 'menu.php'; ?>

</body>
</html>