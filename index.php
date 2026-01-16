<?php
session_start();
include 'db.php';

// ১. পেজের নাম সেট করা (হেডার ও মেনুর জন্য)
$page = 'home';

// ২. স্লাইডার টাইমিং নিয়ে আসা (settings টেবিল থেকে)
$slider_speed = 3000; // ডিফল্ট ৩ সেকেন্ড
$res_timer = $conn->query("SELECT key_value FROM settings WHERE key_name='slider_timer'");
if ($res_timer && $res_timer->num_rows > 0) {
    $slider_speed = (int)$res_timer->fetch_assoc()['key_value'];
}

// ৩. ইউজারের ব্যালেন্স বের করা (হেডারে দেখানোর জন্য)
$user_balance = 0;
if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $user_balance = $res->fetch_assoc()['balance'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EagleEye ESP - Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="notice-bar">
        <div class="marquee">
            <?php 
            // ডাটাবেস থেকে নোটিশ আনা, না থাকলে ডিফল্ট দেখাবে
            $n_res = $conn->query("SELECT key_value FROM settings WHERE key_name='home_notice'");
            echo ($n_res->num_rows > 0) ? $n_res->fetch_assoc()['key_value'] : "Welcome to EagleEye ESP! প্রতিদিন ফ্রি ফায়ার খেলুন এবং জিতে নিন পুরস্কার।";
            ?>
        </div>
    </div>

    <section class="slider-area">
        <div class="slider-container">
            <?php
            $slides = $conn->query("SELECT * FROM slider_images WHERE status='Active'");
            if ($slides && $slides->num_rows > 0) {
                $count = 0;
                while($s = $slides->fetch_assoc()) {
                    $active_class = ($count == 0) ? 'active' : '';
                    echo '<div class="slide '.$active_class.'" style="background-image: url(\'uploads/slider/'.htmlspecialchars($s['image_path']).'\');">
                            <div class="slider-caption">
                                <h3>Play & Win Unlimited Cash!</h3>
                                <p>অ্যাডমিন প্যানেল থেকে নিয়ন্ত্রিত অফারসমূহ।</p>
                            </div>
                          </div>';
                    $count++;
                }
            } else {
                echo '<div class="slide active" style="background-image: url(\'https://wallpapers.com/images/hd/free-fire-mobile-4k-u184y079j151k3k8.jpg\');"></div>';
            }
            ?>
        </div>
    </section>

    <div class="section-head">Games</div>
    <div class="games-scroll">
        <div class="game-item"><i class="fas fa-fire" style="color: #fca5a5;"></i><span>CS Rank</span></div>
        <div class="game-item"><i class="fas fa-skull" style="color: #fcd34d;"></i><span>Full Map</span></div>
        <div class="game-item"><i class="fas fa-dice" style="color: #6ee7b7;"></i><span>Ludo</span></div>
        <div class="game-item"><i class="fas fa-gamepad" style="color: #93c5fd;"></i><span>TDM</span></div>
    </div>

    <div class="section-head" style="margin-top:20px;">Live Tournaments</div>

    <div style="padding-bottom: 80px;">
        <?php
        // ১. ফোল্ডার লুপ (Folder Loop)
        $folder_sql = "SELECT * FROM folders ORDER BY id DESC";
        $folder_res = $conn->query($folder_sql);

        if ($folder_res->num_rows > 0) {
            while($folder = $folder_res->fetch_assoc()) {
                $f_id = $folder['id'];
                $f_name = $folder['name'];

                // ২. ম্যাচ লুপ (Match Loop inside Folder)
                $match_sql = "SELECT * FROM matches WHERE folder_id='$f_id' AND status='Active' ORDER BY id DESC";
                $match_res = $conn->query($match_sql);

                // যদি ফোল্ডারে ম্যাচ থাকে তবেই ফোল্ডারটি দেখাবে
                if ($match_res->num_rows > 0) {
        ?>
            <div class="folder-container">
                <div class="folder-title"><?php echo htmlspecialchars($f_name); ?> Matches</div>
                
                <div class="match-grid">
                    <?php
                    while($row = $match_res->fetch_assoc()) {
                        $m_id = $row['id'];
                        $joined = $row['joined_slots'];
                        $total = $row['total_slots'];
                        $available = $total - $joined;
                        $percent = ($total > 0) ? ($joined / $total) * 100 : 0;
                        $is_full = ($joined >= $total);
                        $start_time = $row['start_time']; 
                        
                        // গেম আইকন (চাইলে ডাটাবেস থেকেও আনতে পারো, এখন স্ট্যাটিক রাখা হলো)
                        $icon_url = "https://cdn-icons-png.flaticon.com/512/2883/2883824.png";
                    ?>
                    
                    <div class="match-card">
                        
                        <div class="card-header">
                            <img src="<?php echo $icon_url; ?>" class="game-icon">
                            <div class="header-info">
                                <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                                <span><?php echo htmlspecialchars($row['time']); ?></span>
                            </div>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-item">
                                <span>Total Prize</span>
                                <b>৳ <?php echo htmlspecialchars($row['prize']); ?></b>
                            </div>
                            <div class="stat-item">
                                <span>Per Kill</span>
                                <b>৳ <?php echo htmlspecialchars($row['per_kill']); ?></b>
                            </div>
                            <div class="stat-item">
                                <span>Entry Fee</span>
                                <b>৳ <?php echo htmlspecialchars($row['entry_fee']); ?></b>
                            </div>
                        </div>

                        <div class="details-grid">
                            <div class="detail-item">
                                <span>Type</span>
                                <b><?php echo htmlspecialchars($row['match_type']); ?></b>
                            </div>
                            <div class="detail-item">
                                <span>Version</span>
                                <b>Mobile</b>
                            </div>
                            <div class="detail-item">
                                <span>Map</span>
                                <b><?php echo htmlspecialchars($row['map']); ?></b>
                            </div>
                        </div>

                        <div class="action-area">
                            <div class="progress-wrap">
                                <div class="progress-bar-bg">
                                    <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                                <div class="progress-text">
                                    <span><?php echo $joined; ?> Joined</span>
                                    <span><?php echo $available; ?> Available</span>
                                </div>
                            </div>

                            <?php if ($is_full) { ?>
                                <button class="btn-full">FULL</button>
                            <?php } else { ?>
                                <a href="join_match.php?match_id=<?php echo $m_id; ?>">
                                    <button class="btn-join">Join</button>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="footer-buttons">
                            <button class="btn-footer btn-prize" onclick='showPrizeList(<?php echo $row["prize_details"]; ?>)'>Prize List</button>
                            
                            <a href="my_matches.php" class="btn-footer btn-room">ROOM ID</a>
                        </div>

                        <div class="card-timer" id="timer-<?php echo $m_id; ?>" data-start="<?php echo $start_time; ?>">
                            Loading...
                        </div>

                    </div> <?php } // End Match Loop ?>
                </div> </div> <?php 
                } // End If Match Exists
            } // End Folder Loop
        } else {
            echo "<div style='text-align:center; padding:50px; color:#64748b;'>
                    <i class='fas fa-gamepad' style='font-size:40px; margin-bottom:15px;'></i>
                    <p>No Tournaments Available Currently!</p>
                  </div>";
        }
        ?>
    </div>

    <div id="prizeModal" class="modal">
        <div class="modal-content">
            <h3 style="color:#ff6600; margin-bottom:15px;">🏆 Prize Pool</h3>
            <div id="prizeListContent" style="text-align:left; font-weight:500; line-height:2;"></div>
            <button class="close-modal" onclick="closeModal()">Close</button>
        </div>
    </div>

    <?php include 'menu.php'; ?>

    <script>
        // স্লাইডার স্ক্রিপ্ট (আগের মতোই)
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const speed = <?php echo $slider_speed; ?>;

        function showNextSlide() {
            if (slides.length <= 1) return;
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        if (slides.length > 1) { 
            setInterval(showNextSlide, speed); 
        }

        // --- নতুন ফাংশনালিটি ---

        // ১. প্রাইজ লিস্ট মোডাল লজিক
        function showPrizeList(data) {
            let content = "";
            if(data && data.length > 0) {
                // ডাটা JSON আকারে থাকলে লুপ চালাবে
                data.forEach(item => { content += "• " + item + "<br>"; });
            } else { 
                content = "No prize details available."; 
            }
            document.getElementById('prizeListContent').innerHTML = content;
            document.getElementById('prizeModal').style.display = "flex";
        }

        function closeModal() {
            document.getElementById('prizeModal').style.display = "none";
        }

        // ২. কাউন্টডাউন টাইমার লজিক
        function updateTimers() {
            const timers = document.querySelectorAll('.card-timer');
            timers.forEach(timer => {
                const startTimeStr = timer.getAttribute('data-start');
                if (!startTimeStr) return;
                
                const startTime = new Date(startTimeStr).getTime();
                const now = new Date().getTime();
                const distance = startTime - now;

                if (distance < 0) {
                    timer.innerHTML = "<span style='color:red'>MATCH STARTED / ENDED</span>";
                } else {
                    const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    timer.innerHTML = `${d} <span style='font-weight:normal; font-size:10px;'>Days</span> : 
                                     ${h} <span style='font-weight:normal; font-size:10px;'>Hours</span> : 
                                     ${m} <span style='font-weight:normal; font-size:10px;'>Min</span> : 
                                     ${s} <span style='font-weight:normal; font-size:10px;'>Sec</span>`;
                }
            });
        }
        setInterval(updateTimers, 1000);
        updateTimers(); // লোড হওয়ার সাথে সাথে একবার কল হবে
    </script>
</body>
</html>