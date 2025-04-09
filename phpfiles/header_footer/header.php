<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BidSphere - Live Auction Platform</title>
    <link rel="stylesheet" href="../../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="header">
        <div class="header__container container">
            <div class="header__logo">
                <a href="../phpfiles/home.php" class="logo">
                    <span class="logo__icon"><i class="fas fa-gavel"></i></span>
                    <span class="logo__text">BidSphere</span>
                </a>
            </div>

            <nav class="header__nav">
                <ul class="nav__list">
                    <li class="nav__item"><a href="../phpfiles/home.php" class="nav__link ">Home</a></li>
                    <li class="nav__item"><a href="../phpfiles/profile.php" class="nav__link ">Dashboard</a></li>
                    <li class="nav__item"><a href="../phpfiles/auction.php" class="nav__link">Auctions</a></li>
                    <li class="nav__item"><a href="about.php" class="nav__link">About</a></li>
                </ul>
            </nav>

            <div class="header__actions">
                <button class="action__btn theme-toggle" aria-label="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                
                <div class="action__btn notification" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification__badge">3</span>
                    <div class="notification__dropdown">
                        <div class="dropdown__header">
                            <h3>Notifications</h3>
                            <button class="dropdown__close">&times;</button>
                        </div>
                        <ul class="dropdown__list">
                            <li class="dropdown__item unread">
                                <i class="fas fa-exclamation-circle"></i>
                                <div class="item__content">
                                    <p>You've been outbid on "Vintage Rolex Watch"</p>
                                    <small>2 minutes ago</small>
                                </div>
                            </li>
                            <li class="dropdown__item unread">
                                <i class="fas fa-clock"></i>
                                <div class="item__content">
                                    <p>"Signed Basketball" auction ending in 15 minutes</p>
                                    <small>10 minutes ago</small>
                                </div>
                            </li>
                            <li class="dropdown__item">
                                <i class="fas fa-trophy"></i>
                                <div class="item__content">
                                    <p>You won "Rare Comic Book Collection" for $1,250</p>
                                    <small>1 hour ago</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="action__btn user-profile">
                        <div class="profile__avatar">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <span class="profile__name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <div class="profile__dropdown">
                            <a href="../phpfiles/profile.php" class="dropdown__item"><i class="fas fa-user"></i> Profile</a>
                            <a href="my_bids.php" class="dropdown__item"><i class="fas fa-gavel"></i> My Bids</a>
                            <a href="watchlist.php" class="dropdown__item"><i class="fas fa-heart"></i> Watchlist</a>
                            <div class="dropdown__divider"></div>
                            <a href="../../login&signup/logout.php" class="dropdown__item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="action__btn user-auth">
                        <a href="../../login&signup/login.php" class="auth__link">Login</a>
                    </div>
                <?php endif; ?>
                
                <button class="action__btn mobile-menu-toggle" aria-label="Toggle mobile menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    
    <script src="../../script.js"></script>
</body>
</html>