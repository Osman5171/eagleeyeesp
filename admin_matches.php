<?php
session_start();
include 'db.php';

// ১. অ্যাডমিন লগইন চেক
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// ২. আপডেট লজিক
if (isset($_POST['update_match'])) {
    $match_id = $_POST['match_id'];
    $room_id = $_POST['room_id'];
    $room_pass = $_POST['room_pass'];
    $announcement = $_POST['announcement'];

    // ক. তথ্য আপডেট
    $stmt = $conn->prepare("UPDATE matches SET room_id=?, room_pass=?, announcement=? WHERE id=?");
    $stmt->bind_param("sssi", $room_id, $room_pass, $announcement, $match_id);
    $stmt->execute();
    $stmt->close();

    // খ. ফাইল আপলোড লজিক
    if (!empty($_FILES['result_file']['name'])) {
        $file_name = time() . "_" . basename($_FILES['result_file']['name']);
        $target = "results/" . $file_name;
        
        // ফোল্ডার না থাকলে তৈরি করে নেওয়া
        if (!is_dir('results')) { mkdir('results', 0777, true); }

        if (move_uploaded_file($_FILES['result_file']['tmp_name'], $target)) {
            $stmt = $conn->prepare("UPDATE matches SET result_file=? WHERE id=?");
            $stmt->bind_param("si", $file_name, $match_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "<script>alert('Match Details Updated!'); window.location.href='admin_matches.php';</script>";
}

// ৩. ডিলিট লজিক
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM matches WHERE id='$del_id'");
    echo "<script>alert('Match Deleted!'); window.location.href='admin_matches.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Matches - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h2 { text-align: center; color: #fbbf24; }
        
        table { width: 100%; border-collapse: collapse; background: #1e293b; margin-top: 20px; font-size: 14px; }
        th, td { padding: 10px; border: 1px solid #334155; text-align: center; vertical-align: middle; }
        th { background: #334155; color: #fbbf24; }
        tr:nth-child(even) { background: #0f172a; }

        input[type="text"], textarea {
            padding: 5px; background: #334155; border: 1px solid #475569; color: white; border-radius: 4px; width: 90%;
        }
        input[type="file"] { font-size: 11px; max-width: 150px; }
        
        .btn { padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; color: white; font-weight: bold; }
        .btn-update { background: #22c55e; margin-top: 5px; width: 100%; }
        .btn-delete { background: #ef4444; padding: 6px 10px; display: inline-block; color: white; }
        .btn-view { background: #3b82f6; padding: 6px 10px; text-decoration: none; display: inline-block; color: white; border-radius: 4px; }

        .back-btn { display: block; margin-top: 20px; text-align: center; color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Manage Matches (Room, Notice & Result)</h2>

        <table>
            <thead>
                <tr>
                    <th>Info</th>
                    <th>Room Details</th>
                    <th>Announcement</th>
                    <th>Result Upload</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM matches ORDER BY id DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // প্রতিটি রো-এর জন্য একটি ইউনিক ফর্ম আইডি তৈরি করা হচ্ছে
                        $form_id = "form_match_" . $row['id'];
                ?>
                <tr>
                    <td width="15%">
                        #<?php echo $row['id']; ?> <br>
                        <?php echo htmlspecialchars($row['title']); ?> <br>
                        <a href="admin_view_players.php?match_id=<?php echo $row['id']; ?>" class="btn-view">View Players</a>
                    </td>

                    <td width="20%">
                        <input type="text" name="room_id" form="<?php echo $form_id; ?>" value="<?php echo htmlspecialchars($row['room_id']); ?>" placeholder="Room ID"><br><br>
                        <input type="text" name="room_pass" form="<?php echo $form_id; ?>" value="<?php echo htmlspecialchars($row['room_pass']); ?>" placeholder="Password">
                    </td>

                    <td width="25%">
                        <textarea name="announcement" form="<?php echo $form_id; ?>" rows="3" placeholder="Write notice here..."><?php echo htmlspecialchars($row['announcement']); ?></textarea>
                    </td>

                    <td width="20%">
                        <input type="file" name="result_file" form="<?php echo $form_id; ?>">
                        <?php if($row['result_file']) { ?>
                            <br><small style="color:#fbbf24;">(File Uploaded)</small>
                        <?php } ?>
                    </td>

                    <td width="10%">
                        <form id="<?php echo $form_id; ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="match_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="update_match" class="btn btn-update">Update</button>
                        </form>
                        
                        <br>
                        <a href="admin_matches.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this match?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5'>No matches found!</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>

</body>
</html>