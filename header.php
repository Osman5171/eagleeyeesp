<header>
    <?php if(isset($page) && $page == 'home') { ?>
        <div class="logo">
            <h2>EagleEye<span class="highlight">ESP</span></h2>
        </div>
        
        <div class="header-right">
            <?php if(isset($_SESSION['user_name'])): ?>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="wallet.php" class="coin-badge" style="text-decoration: none; color: white;">
                        <i class="fas fa-coins"></i>
                        <span><?php echo isset($user_balance) ? $user_balance : '0'; ?></span>
                    </a>
                    <a href="logout.php" onclick="return confirm('Logout?');">
                        <i class="fas fa-sign-out-alt" style="color: #ef4444; font-size: 22px;"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="register.html" class="btn-auth-register">Register</a>
                    <a href="login.html" class="btn-auth-login">Login</a>
                </div>
            <?php endif; ?>
        </div>

    <?php } else { ?>
        <div class="logo">
            <h2>My <span class="highlight"><?php echo isset($page) ? ucfirst($page) : 'Page'; ?></span></h2>
        </div>
        <div class="header-icons">
             <a href="logout.php"><i class="fas fa-sign-out-alt" style="color: #ef4444; font-size: 20px;"></i></a>
        </div>
    <?php } ?>
</header>