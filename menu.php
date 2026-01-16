<nav class="bottom-nav">
    <a href="index.php" class="nav-link <?php if($page=='home'){echo 'active';} ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>

    <a href="my_matches.php" class="nav-link <?php if($page=='matches'){echo 'active';} ?>">
        <i class="fas fa-trophy"></i>
        <span>My Match</span>
    </a>

    <a href="wallet.php" class="nav-link <?php if($page=='wallet'){echo 'active';} ?>">
        <i class="fas fa-wallet"></i>
        <span>Wallet</span>
    </a>

    <a href="profile.php" class="nav-link <?php if($page=='profile'){echo 'active';} ?>">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</nav>