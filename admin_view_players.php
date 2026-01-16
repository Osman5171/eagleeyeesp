<?php
session_start();
include 'db.php';

// ১. অ্যাডমিন লগইন চেক
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// ২. ম্যাচ আইডি ধরা (নিরাপদভাবে ইনটিজারে কনভার্ট করা হচ্ছে)
if (!isset($_GET['match_id'])) {
    header("Location: admin_matches.php");
    exit();
}

$match_id = intval($_GET['match_id']); // intval() ব্যবহার করা হলো যাতে SQL Injection না হয়

// ৩. ম্যাচের নাম জানা
$stmt = $conn->prepare("SELECT title, match_type FROM matches WHERE id=?");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$res_match = $stmt->get_result();
$match_info = $res_match->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Players - Admin</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h2 { text-align: center; color: #fbbf24; margin-bottom: 5px; }
        h4 { text-align: center; color: #94a3b8; margin-top: 0; }
        
        table { width: 100%; border-collapse: collapse; background: #1e293b; margin-top: 20px; font-size: 14px; }
        th, td { padding: 12px; border: 1px solid #334155; text-align: center; }
        th { background: #334155; color: #fbbf24; }
        tr:nth-child(even) { background: #0f172a; }

        .back-btn { 
            display: inline-block; margin-bottom: 15px; 
            padding: 8px 15px; background: #3b82f6; color: white; 
            text-decoration: none; border-radius: 5px; font-weight: bold;
        }
        .print-btn {
            float: right; padding: 8px 15px; background: #22c55e; color: white;
            text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="admin_matches.php" class="back-btn">Back</a>
        <button onclick="window.print()" class="print-btn">Print List</button>

        <h2>Participant List</h2>
        <h4>Match: <?php echo htmlspecialchars($match_info['title']); ?> (<?php echo htmlspecialchars($match_info['match_type']); ?>)</h4>

        <table>
            <thead>
                <tr>
                    <th>SL</th>
                    <th>User Email</th>
                    <th>Squad Name</th>
                    <th>Player 1 (Leader)</th>
                    <th>Player 2</th>
                    <th>Player 3</th>
                    <th>Player 4</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Prepared Statement ব্যবহার করা হচ্ছে
                $stmt = $conn->prepare("SELECT * FROM participants WHERE match_id=?");
                $stmt->bind_param("i", $match_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $count = 1;

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td style="font-size: 12px; color: #cbd5e1;"><?php echo htmlspecialchars($row['user_email']); ?></td>
                    <td style="color: #fbbf24; font-weight:bold;"><?php echo $row['squad_name'] ? htmlspecialchars($row['squad_name']) : '-'; ?></td>
                    <td style="font-weight: bold;"><?php echo htmlspecialchars($row['player_1']); ?></td>
                    <td><?php echo $row['player_2'] ? htmlspecialchars($row['player_2']) : '-'; ?></td>
                    <td><?php echo $row['player_3'] ? htmlspecialchars($row['player_3']) : '-'; ?></td>
                    <td><?php echo $row['player_4'] ? htmlspecialchars($row['player_4']) : '-'; ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='7' style='padding: 30px; color: #94a3b8;'>No players joined yet!</td></tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>