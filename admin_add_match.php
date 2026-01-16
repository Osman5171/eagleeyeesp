<?php
session_start();
include 'db.php';

// ১. অ্যাডমিন লগইন চেক
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// ২. ফর্ম সাবমিট লজিক
if (isset($_POST['publish_match'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $folder_id = intval($_POST['folder_id']);
    $category = mysqli_real_escape_string($conn, $_POST['category']); // Mode: BR/CS etc.
    $match_type = mysqli_real_escape_string($conn, $_POST['match_type']);
    
    // ম্যাপ চেকলিস্ট হ্যান্ডেল করা (ম্যাপ ১ + ম্যাপ ২)
    $maps = isset($_POST['maps']) ? implode(' + ', $_POST['maps']) : 'Random';

    $start_time = $_POST['start_time']; // কাউন্টডাউনের জন্য সঠিক সময়
    $show_time = date("d M, h:i A", strtotime($start_time)); // ইউজারকে দেখানোর জন্য ফরম্যাট

    $entry_fee = $_POST['entry_fee'];
    $prize = $_POST['total_prize'];
    $per_kill = $_POST['per_kill'];
    $total_slots = $_POST['total_slots'];

    // ডাইনামিক প্রাইজ পুল (JSON ফরম্যাটে সেভ হবে)
    $prize_pool = [];
    if(isset($_POST['rank_prize'])){
        foreach($_POST['rank_prize'] as $key => $val){
            if(!empty(trim($val))) {
                $prize_pool[] = "Rank ".($key+1).": " . trim($val);
            }
        }
    }
    $prize_details_json = json_encode($prize_pool);

    $sql = "INSERT INTO matches (title, folder_id, category, match_type, map, time, start_time, entry_fee, prize, per_kill, total_slots, prize_details, status) 
            VALUES ('$title', '$folder_id', '$category', '$match_type', '$maps', '$show_time', '$start_time', '$entry_fee', '$prize', '$per_kill', '$total_slots', '$prize_details_json', 'Active')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('New Match Published Successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Match - EagleEye Admin</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: 'Arial', sans-serif; padding: 20px; }
        .container { max-width: 650px; margin: 0 auto; background: #1e293b; padding: 30px; border-radius: 10px; border: 1px solid #334155; }
        h2 { text-align: center; color: #fbbf24; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold; color: #fbbf24; font-size: 14px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 5px; border-radius: 5px; border: 1px solid #475569; background: #334155; color: white; box-sizing: border-box; outline: none; }
        input:focus, select:focus { border-color: #fbbf24; }
        
        /* Map Checklist Grid */
        .map-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 5px; background: #0f172a; padding: 10px; border-radius: 5px; }
        .map-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #cbd5e1; cursor: pointer; }
        .map-item input { width: auto; margin: 0; cursor: pointer; }

        .prize-row { margin-bottom: 10px; }
        .btn-add-prize { background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; margin-top: 5px; }
        
        .btn-submit { width: 100%; padding: 14px; background: #22c55e; color: white; border: none; font-weight: bold; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 30px; transition: 0.3s; }
        .btn-submit:hover { background: #16a34a; transform: translateY(-2px); }
        .back-btn { display: block; text-align: center; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Publish New Tournament</h2>
        
        <form method="POST" action="">
            
            <label>Select Package/Folder</label>
            <select name="folder_id" required>
                <option value="">-- Select Folder --</option>
                <?php
                $f_res = $conn->query("SELECT * FROM folders");
                while($f = $f_res->fetch_assoc()) {
                    echo "<option value='".$f['id']."'>".htmlspecialchars($f['name'])."</option>";
                }
                ?>
            </select>

            <label>Tournament Title</label>
            <input type="text" name="title" placeholder="Ex: Grand Solo Scrim #101" required>

            <label>Match Mode (BR/CS)</label>
            <select name="category">
                <option value="BR">Battle Royale (BR)</option>
                <option value="CS">Clash Squad (CS)</option>
                <option value="Ludo">Ludo Matches</option>
                <option value="TDM">TDM Mode</option>
            </select>

            <label>Match Type</label>
            <select name="match_type">
                <option value="Solo">Solo</option>
                <option value="Duo">Duo</option>
                <option value="Squad">Squad</option>
            </select>

            <label>Select Maps (Multiple selection will combine with '+')</label>
            <div class="map-grid">
                <label class="map-item"><input type="checkbox" name="maps[]" value="Bermuda"> Bermuda</label>
                <label class="map-item"><input type="checkbox" name="maps[]" value="Purgatory"> Purgatory</label>
                <label class="map-item"><input type="checkbox" name="maps[]" value="Kalahari"> Kalahari</label>
                <label class="map-item"><input type="checkbox" name="maps[]" value="Alpine"> Alpine</label>
                <label class="map-item"><input type="checkbox" name="maps[]" value="Nexterra"> Nexterra</label>
                <label class="map-item"><input type="checkbox" name="maps[]" value="Bermuda Remastered"> Bermuda REM</label>
            </div>

            <label>Match Date & Time (For Countdown)</label>
            <input type="datetime-local" name="start_time" required>

            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label>Entry Fee (৳)</label>
                    <input type="number" name="entry_fee" placeholder="Ex: 20" required>
                </div>
                <div style="flex: 1;">
                    <label>Total Prize (৳)</label>
                    <input type="number" name="total_prize" placeholder="Ex: 500" required>
                </div>
            </div>

            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label>Per Kill Rate (৳)</label>
                    <input type="number" name="per_kill" value="0">
                </div>
                <div style="flex: 1;">
                    <label>Total Slots</label>
                    <input type="number" name="total_slots" placeholder="Ex: 48" required>
                </div>
            </div>

            <label>Prize Pool Breakdown</label>
            <div id="prize_fields">
                <input type="text" name="rank_prize[]" placeholder="1st Prize (Ex: 200)" class="prize-row">
                <input type="text" name="rank_prize[]" placeholder="2nd Prize (Ex: 100)" class="prize-row">
            </div>
            <button type="button" class="btn-add-prize" onclick="addPrizeInput()">+ Add More Rank</button>

            <button type="submit" name="publish_match" class="btn-submit">Publish Match</button>
        </form>

        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <script>
        function addPrizeInput() {
            const container = document.getElementById('prize_fields');
            const count = container.getElementsByTagName('input').length + 1;
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'rank_prize[]';
            input.placeholder = count + (count === 3 ? 'rd' : 'th') + ' Prize Amount';
            input.className = 'prize-row';
            container.appendChild(input);
        }
    </script>

</body>
</html>