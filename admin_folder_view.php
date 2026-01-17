<?php
session_start();
include 'db.php';

// লগইন চেক
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// ফোল্ডার আইডি চেক
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;
$f_query = $conn->query("SELECT name FROM folders WHERE id='$folder_id'");
$f_name = ($f_query->num_rows > 0) ? $f_query->fetch_assoc()['name'] : "Unknown Folder";

// --- আপডেট লজিক (Level এবং অন্যান্য) ---
if (isset($_POST['update_match'])) {
    $match_id = $_POST['match_id'];
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $room_pass = mysqli_real_escape_string($conn, $_POST['room_pass']);
    $announcement = mysqli_real_escape_string($conn, $_POST['announcement']);
    
    $total_slots = intval($_POST['total_slots']);
    $level = intval($_POST['level']); // এই যে ম্যাচের লেভেল

    // ডাটাবেস আপডেট
    $stmt = $conn->prepare("UPDATE matches SET room_id=?, room_pass=?, announcement=?, total_slots=?, level=? WHERE id=?");
    $stmt->bind_param("sssiis", $room_id, $room_pass, $announcement, $total_slots, $level, $match_id);
    $stmt->execute();
    $stmt->close();

    // ফাইল আপলোড
    if (!empty($_FILES['result_file']['name'])) {
        $file_name = time() . "_" . basename($_FILES['result_file']['name']);
        $target = "results/" . $file_name;
        if (!is_dir('results')) { mkdir('results', 0777, true); }

        if (move_uploaded_file($_FILES['result_file']['tmp_name'], $target)) {
            $stmt = $conn->prepare("UPDATE matches SET result_file=? WHERE id=?");
            $stmt->bind_param("si", $file_name, $match_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo "<script>alert('Updated Successfully!'); window.location.href='admin_folder_view.php?folder_id=$folder_id';</script>";
}

// --- ডিলিট লজিক ---
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM matches WHERE id='$del_id'");
    header("Location: admin_folder_view.php?folder_id=$folder_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage: <?php echo htmlspecialchars($f_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h2 { text-align: center; color: #fbbf24; margin-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; background: #1e293b; margin-top: 20px; font-size: 13px; }
        th, td { padding: 10px; border: 1px solid #334155; text-align: center; vertical-align: middle; }
        th { background: #334155; color: #fbbf24; font-weight: bold; }
        tr:nth-child(even) { background: #0f172a; }

        input[type="text"], textarea {
            padding: 5px; background: #334155; border: 1px solid #475569; color: white; border-radius: 4px; width: 90%;
        }
        
        /* লেভেল ইনপুট ডিজাইন */
        .level-input {
            width: 50px; padding: 5px; text-align: center; font-weight: bold;
            border: 2px solid #fbbf24; background: #0f172a; color: #fbbf24; border-radius: 5px;
        }

        .btn-update { background: #22c55e; margin-top: 5px; width: 100%; padding: 6px; border: none; color: white; font-weight: bold; cursor: pointer; border-radius: 4px; }
        .btn-delete { color: #ef4444; font-size: 12px; margin-top: 5px; display: inline-block; }
        .back-btn { display: inline-block; margin-bottom: 15px; padding: 8px 15px; background: #fbbf24; color: black; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <a href="admin_matches.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Folders</a>
        <h2>Managing: <?php echo htmlspecialchars($f_name); ?></h2>

        <table>
            <thead>
                <tr>
                    <th width="20%">Match Info</th>
                    <th width="20%">Room ID & Pass</th>
                    <th width="10%">Level / Serial</th> <th width="15%">Slots</th>
                    <th width="25%">Notice & Result</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // এইখানে Level অনুযায়ী ম্যাচগুলো সাজিয়ে দেখানো হচ্ছে (ASC মানে ছোট থেকে বড়)
                $sql = "SELECT * FROM matches WHERE folder_id='$folder_id' ORDER BY level ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $form_id = "form_match_" . $row['id'];
                ?>
                <tr>
                    <td>
                        #<?php echo $row['id']; ?> <br>
                        <b><?php echo htmlspecialchars($row['title']); ?></b> <br>
                        <small><?php echo $row['time']; ?></small><br>
                        <a href="admin_view_players.php?match_id=<?php echo $row['id']; ?>" style="color:#3b82f6;">View Players</a>
                    </td>

                    <form id="<?php echo $form_id; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="match_id" value="<?php echo $row['id']; ?>">

                        <td>
                            <input type="text" name="room_id" value="<?php echo htmlspecialchars($row['room_id']); ?>" placeholder="Room ID"><br><br>
                            <input type="text" name="room_pass" value="<?php echo htmlspecialchars($row['room_pass']); ?>" placeholder="Password">
                        </td>

                        <td>
                            <input type="number" name="level" class="level-input" value="<?php echo $row['level']; ?>">
                            <br><small style="color:#94a3b8;">(1 = Top)</small>
                        </td>

                        <td>
                            Joined: <b><?php echo $row['joined_slots']; ?></b><br>
                            Total: <input type="number" name="total_slots" value="<?php echo $row['total_slots']; ?>" style="width:50px;">
                        </td>

                        <td>
                            <textarea name="announcement" rows="2" placeholder="Notice..."><?php echo htmlspecialchars($row['announcement']); ?></textarea><br>
                            <input type="file" name="result_file" style="font-size:10px;">
                            <?php if($row['result_file']) { echo "<br><small style='color:#fbbf24;'>(Uploaded)</small>"; } ?>
                        </td>

                        <td>
                            <button type="submit" name="update_match" class="btn-update">Save</button>
                            <br>
                            <a href="?folder_id=<?php echo $folder_id; ?>&delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete Match?');">Delete</a>
                        </td>
                    </form>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#fbbf24;'>No matches found in this folder!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>