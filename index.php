<?php
session_start();
include 'db.php';

// ১. পেজের নাম সেট করা (হেডার ও মেনুর জন্য)
$page = 'home';

// ২. স্লাইডার টাইমিং নিয়ে আসা
$slider_speed = 3000; 
$res_timer = $conn->query("SELECT key_value FROM settings WHERE key_name='slider_timer'");
if ($res_timer && $res_timer->num_rows > 0) {
    $slider_speed = (int)$res_timer->fetch_assoc()['key_value'];
}

// ৩. ইউজারের ব্যালেন্স বের করা
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
                                <p>অ্যাডমিন প্যানেল থেকে নিয়ন্ত্রিত অফারসমূহ।</p>
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

    <div class="section-head" style="text-align: center; border: none; font-size: 24px; margin-top: 10px;">Free Fire Matches</div>
    
    <section class="folder-grid">
        <?php
        // ডাটাবেস থেকে সব ফোল্ডার নিয়ে আসা
        $f_res = $conn->query("SELECT * FROM folders ORDER BY id DESC");
        if($f_res && $f_res->num_rows > 0) {
            while($f = $f_res->fetch_assoc()) {
                $f_id = $f['id'];
                // প্রতিটি ফোল্ডারে কতটি একটিভ ম্যাচ আছে তা গুনে নেওয়া
                $count_res = $conn->query("SELECT COUNT(*) as total FROM matches WHERE folder_id='$f_id' AND status='Active'");
                $m_count = $count_res->fetch_assoc()['total'];
        ?>
            <a href="folder_matches.php?folder_id=<?php echo $f_id; ?>" class="folder-card" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://wallpapers.com/images/hd/free-fire-classic-match-background.jpg');">
                <div class="folder-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/2883/2883824.png" alt="Icon">
                </div>
                <div class="folder-info">
                    <h3><?php echo htmlspecialchars($f['name']); ?></h3>
                    <span><?php echo $m_count; ?> Matches Found</span>
                </div>
            </a>
        <?php 
            }
        } else {
            echo "<p style='text-align:center; width:100%; color:#94a3b8;'>No Folders Available!</p>";
        }
        ?>
    </section>

    <br><br><br>

    <?php include 'menu.php'; ?>

    <script>
        // স্লাইডার স্ক্রিপ্ট
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const speed = <?php echo $slider_speed; ?>;
        function showNextSlide() {
            if (slides.length <= 1) return;
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        if (slides.length > 1) { setInterval(showNextSlide, speed); }
    </script>
</body>
</html>