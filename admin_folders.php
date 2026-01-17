<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// ১. নতুন ফোল্ডার অ্যাড করা (লেভেল সহ)
if (isset($_POST['add_folder'])) {
    $name = mysqli_real_escape_string($conn, $_POST['folder_name']);
    $lvl = intval($_POST['folder_level']); // নতুন: লেভেল ইনপুট নেওয়া হলো
    
    // ডাটাবেসে নাম এবং লেভেল সেভ করা
    $conn->query("INSERT INTO folders (name, level) VALUES ('$name', '$lvl')");
    echo "<script>alert('Folder Added Successfully!'); window.location.href='admin_folders.php';</script>";
}

// ২. ফোল্ডার এডিট/আপডেট করা (নাম ও লেভেল পরিবর্তন) - নতুন ফিচার
if (isset($_POST['update_folder'])) {
    $fid = $_POST['folder_id'];
    $fname = mysqli_real_escape_string($conn, $_POST['update_name']);
    $flvl = intval($_POST['update_level']);

    $conn->query("UPDATE folders SET name='$fname', level='$flvl' WHERE id='$fid'");
    echo "<script>alert('Folder Updated!'); window.location.href='admin_folders.php';</script>";
}

// ৩. ফোল্ডার ডিলিট করা
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM folders WHERE id='$id'");
    header("Location: admin_folders.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Folders</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .box { background: #1e293b; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; border: 1px solid #334155; }
        
        /* ইনপুট ফিল্ড ডিজাইন */
        input, button { padding: 8px; margin: 5px 0; border-radius: 5px; border: 1px solid #475569; }
        input { background: #334155; color: white; width: 100%; box-sizing: border-box; }
        
        .btn-add { background: #22c55e; color: white; border: none; font-weight: bold; cursor: pointer; width: 100%; }
        
        /* এডিট লিস্ট ডিজাইন */
        .item { background: #334155; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #475569; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .btn-save { background: #3b82f6; color: white; border: none; cursor: pointer; padding: 5px 10px; }
        .btn-del { background: #ef4444; color: white; text-decoration: none; padding: 6px 10px; border-radius: 5px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center; color:#fbbf24;">Add Folder & Position</h2>
        
        <form method="POST">
            <label style="font-size:13px; color:#cbd5e1;">Folder Name:</label>
            <input type="text" name="folder_name" placeholder="Ex: Daily Scrim" required>
            
            <label style="font-size:13px; color:#cbd5e1;">Level / Position (1 = Top):</label>
            <input type="number" name="folder_level" placeholder="Ex: 1" required>
            
            <button type="submit" name="add_folder" class="btn-add">Create Folder</button>
        </form>

        <h3 style="border-top:1px solid #475569; padding-top:15px; margin-top:20px;">Manage Existing Folders</h3>
        
        <?php
        // লেভেল অনুযায়ী ফোল্ডারগুলো সাজানো (ORDER BY level ASC)
        $res = $conn->query("SELECT * FROM folders ORDER BY level ASC");
        
        if($res->num_rows > 0) {
            while($row = $res->fetch_assoc()) {
        ?>
        <form method="POST" class="item">
            <input type="hidden" name="folder_id" value="<?php echo $row['id']; ?>">
            
            <div style="flex:2;">
                <small style="color:#fbbf24;">Name:</small>
                <input type="text" name="update_name" value="<?php echo htmlspecialchars($row['name']); ?>">
            </div>
            
            <div style="flex:1;">
                <small style="color:#fbbf24;">Lvl:</small>
                <input type="number" name="update_level" value="<?php echo $row['level']; ?>">
            </div>

            <div style="display:flex; flex-direction:column; gap:5px;">
                <button type="submit" name="update_folder" class="btn-save">Save</button>
                <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete Folder?')">Del</a>
            </div>
        </form>
        <?php 
            }
        } else {
            echo "<p style='color:#94a3b8; text-align:center;'>No folders found.</p>";
        }
        ?>
        
        <a href="admin_dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#94a3b8; text-decoration:none;">← Back to Dashboard</a>
    </div>
</body>
</html>