<?php
session_start();
require '../login&signup/backend/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get counts for dashboard
$bid_count = $pdo->prepare("SELECT COUNT(*) FROM bids WHERE user_id = ?");
$bid_count->execute([$user_id]);
$bid_count = $bid_count->fetchColumn();

$watchlist_count = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ?");
$watchlist_count->execute([$user_id]);
$watchlist_count = $watchlist_count->fetchColumn();

$won_count = $pdo->prepare("SELECT COUNT(*) FROM won_items WHERE user_id = ?");
$won_count->execute([$user_id]);
$won_count = $won_count->fetchColumn();

// Set active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'bidding';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | SportBid Auctions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="profile-style.css">
</head>
<body>
    <!-- Reuse header from home page -->
    <header class="header">
        <div class="header__container container">
            <div class="header__logo">
                <a href="index.php" class="logo">
                    <span class="logo__icon"><i class="fas fa-gavel"></i></span>
                    <span class="logo__text">BidSphere</span>
                </a>
            </div>

            <nav class="header__nav">
                <ul class="nav__list">
                    <li class="nav__item"><a href="index.php" class="nav__link active">Home</a></li>
                    <li class="nav__item"><a href="auctions.php" class="nav__link">Auctions</a></li>
                    <li class="nav__item"><a href="profile.php" class="nav__link">Profile</a></li>
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
                            <a href="profile/profile.php" class="dropdown__item"><i class="fas fa-user"></i> Profile</a>
                            <a href="my_bids.php" class="dropdown__item"><i class="fas fa-gavel"></i> My Bids</a>
                            <a href="watchlist.php" class="dropdown__item"><i class="fas fa-heart"></i> Watchlist</a>
                            <div class="dropdown__divider"></div>
                            <a href="../login&signup/logout.php" class="dropdown__item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="action__btn user-auth">
                        <a href="../login&signup/login.php" class="auth__link">Login</a>
                    </div>
                <?php endif; ?>
                
                <button class="action__btn mobile-menu-toggle" aria-label="Toggle mobile menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="profile-main">
        <div class="profile-container">
            <!-- User Summary Card -->
            <div class="profile-card profile-card--summary">
                <div class="profile-avatar">
                    <?php if ($user['avatar']): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="User Avatar">
                    <?php else: ?>
                        <div class="profile-avatar__initials">
                            <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h1>
                    <!-- <div class="profile-rating">
                        <span class="rating-stars" style="--rating: <?php echo $user['rating']; ?>;"></span>
                        <span class="rating-value"><?php echo number_format($user['rating'], 1); ?></span>
                    </div> -->
                    <div class="profile-meta">
                        <span><i class="fas fa-calendar-alt"></i> Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        <!-- <span><i class="fas fa-clock"></i> Last active <?php echo $user['last_login'] ? date('M j, g:i a', strtotime($user['last_login'])) : 'Never'; ?></span> -->
                    </div>
                </div>
                <div class="profile-stats">
                <div class="rating-container">
    <div class="star-rating" style="--rating: 4.5;"></div>
    <span class="rating-value">4.5</span>
</div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $watchlist_count; ?></span>
                        <span class="stat-label">Watching</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $won_count; ?></span>
                        <span class="stat-label">Won</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tabs -->
            <div class="profile-tabs">
                <nav class="tab-nav">
                    <a href="?tab=bidding" class="tab-link <?php echo $active_tab === 'bidding' ? 'active' : ''; ?>">
                        <i class="fas fa-gavel"></i> Bidding History
                    </a>
                    <a href="?tab=watchlist" class="tab-link <?php echo $active_tab === 'watchlist' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i> Watchlist
                    </a>
                    <a href="?tab=won" class="tab-link <?php echo $active_tab === 'won' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy"></i> Won Items
                    </a>
                    <a href="?tab=settings" class="tab-link <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </nav>

                <div class="tab-content">
                    <!-- Bidding History Tab -->
                    <div class="tab-pane <?php echo $active_tab === 'bidding' ? 'active' : ''; ?>" id="bidding">
                        <h2 class="tab-title">Your Bidding History</h2>
                        <div class="table-responsive">
                            <table class="bids-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="sortable" data-sort="amount">Bid Amount <i class="fas fa-sort"></i></th>
                                        <th class="sortable" data-sort="time">Bid Time <i class="fas fa-sort"></i></th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
try {
    $bids_query = "SELECT b.*, i.name, i.image FROM bids b 
                  JOIN items i ON b.item_id = i.id 
                  WHERE b.user_id = ? 
                  ORDER BY b.bid_time DESC LIMIT 10";
    $bids_stmt = $pdo->prepare($bids_query);
    $bids_stmt->execute([$user_id]);
    
    if ($bids_stmt->rowCount() > 0):
        while ($bid = $bids_stmt->fetch()): ?>
        <tr>
            <td>
                <div class="bid-item">
                    <?php if (!empty($bid['image'])): ?>
                        <img src="<?php echo htmlspecialchars($bid['image']); ?>" alt="<?php echo htmlspecialchars($bid['name']); ?>" class="bid-item__image">
                    <?php endif; ?>
                    <span class="bid-item__name"><?php echo htmlspecialchars($bid['name']); ?></span>
                </div>
            </td>
            <td>$<?php echo number_format($bid['amount'], 2); ?></td>
            <td><?php echo date('M j, g:i a', strtotime($bid['bid_time'])); ?></td>
            <td>
                <span class="status-badge <?php echo $bid['is_winning'] ? 'status-badge--winning' : 'status-badge--outbid'; ?>">
                    <?php echo $bid['is_winning'] ? 'Winning' : 'Outbid'; ?>
                </span>
            </td>
        </tr>
        <?php endwhile;
    else: ?>
        <tr>
            <td colspan="4" class="text-center">No bidding history found</td>
        </tr>
    <?php endif;
} catch (PDOException $e) { ?>
    <tr>
        <td colspan="4" class="text-center">Unable to load bidding history</td>
    </tr>
<?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination">
                            <button class="btn btn--outline" disabled>Previous</button>
                            <span class="pagination-info">Page 1 of 1</span>
                            <button class="btn btn--outline" disabled>Next</button>
                        </div>
                    </div>

                    <!-- Watchlist Tab -->
                    <div class="tab-pane <?php echo $active_tab === 'watchlist' ? 'active' : ''; ?>" id="watchlist">
                        <h2 class="tab-title">Your Watchlist</h2>
                        <?php if ($watchlist_count > 0): ?>
                            <div class="watchlist-grid">
                                <?php
                                $watchlist_query = "SELECT w.*, i.name, i.image, i.current_bid, i.end_time 
                                                   FROM watchlist w 
                                                   JOIN items i ON w.item_id = i.id 
                                                   WHERE w.user_id = ?";
                                $watchlist_stmt = $pdo->prepare($watchlist_query);
                                $watchlist_stmt->execute([$user_id]);
                                while ($item = $watchlist_stmt->fetch()):
                                ?>
                                <div class="auction-card">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="auction-card__image">
                                    <h3 class="auction-card__title"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="auction-card__bid">$<?php echo number_format($item['current_bid'], 2); ?></div>
                                    <div class="auction-card__time">Ends in <?php echo time_remaining($item['end_time']); ?></div>
                                    <div class="auction-card__actions">
                                        <button class="btn btn--primary btn--small">Bid Now</button>
                                        <button class="btn btn--outline btn--small btn--remove" data-item-id="<?php echo $item['item_id']; ?>">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-binoculars empty-state__icon"></i>
                                <h3>Your watchlist is empty</h3>
                                <p>Start adding items to track auctions you're interested in</p>
                                <a href="index.php" class="btn btn--primary">Browse Auctions</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Won Items Tab -->
                    <div class="tab-pane <?php echo $active_tab === 'won' ? 'active' : ''; ?>" id="won">
                        <h2 class="tab-title">Items You've Won</h2>
                        <?php if ($won_count > 0): ?>
                            <div class="won-items-list">
                                <?php
                                $won_query = "SELECT w.*, i.name, i.image 
                                            FROM won_items w 
                                            JOIN items i ON w.item_id = i.id 
                                            WHERE w.user_id = ? 
                                            ORDER BY w.won_date DESC";
                                $won_stmt = $pdo->prepare($won_query);
                                $won_stmt->execute([$user_id]);
                                while ($item = $won_stmt->fetch()):
                                ?>
                                <div class="won-item">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="won-item__image">
                                    <div class="won-item__info">
                                        <h3 class="won-item__name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <div class="won-item__price">Won for $<?php echo number_format($item['final_price'], 2); ?></div>
                                        <div class="won-item__status">
                                            <span class="status-badge status-badge--<?php echo strtolower($item['payment_status']); ?>">
                                                <?php echo ucfirst($item['payment_status']); ?>
                                            </span>
                                        </div>
                                        <?php if ($item['payment_status'] === 'completed' && !$item['rating']): ?>
                                            <div class="won-item__review">
                                                <button class="btn btn--small btn--outline btn--review" data-item-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-star"></i> Leave Review
                                                </button>
                                            </div>
                                        <?php elseif ($item['rating']): ?>
                                            <div class="won-item__rating">
                                                <span class="rating-stars" style="--rating: <?php echo $item['rating']; ?>;"></span>
                                                <span class="rating-text">Your rating</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="won-item__actions">
                                        <button class="btn btn--small btn--outline">
                                            <i class="fas fa-envelope"></i> Contact Seller
                                        </button>
                                        <?php if ($item['payment_status'] === 'pending'): ?>
                                            <button class="btn btn--small btn--primary">
                                                <i class="fas fa-credit-card"></i> Complete Payment
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-trophy empty-state__icon"></i>
                                <h3>No won items yet</h3>
                                <p>Start bidding on auctions to win amazing items</p>
                                <a href="../home.php" class="btn btn--primary">Browse Auctions</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane <?php echo $active_tab === 'settings' ? 'active' : ''; ?>" id="settings">
                        <h2 class="tab-title">Account Settings</h2>
                        <form class="settings-form" id="profileSettings">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="avatar">Avatar URL</label>
                                <input type="url" id="avatar" name="avatar" value="<?php echo htmlspecialchars($user['avatar']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="current_password">Current Password (to change password)</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" class="btn btn--primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Reuse footer from home page -->
    <footer class="footer">
        <div class="footer__container container">
            <div class="footer__grid">
                <div class="footer__col">
                    <h3 class="footer__title">BidSphere</h3>
                    <p class="footer__text">The premier platform for live auctions of rare and collectible items.</p>
                    <div class="footer__social">
                        <a href="#" class="social__link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social__link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social__link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social__link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer__col">
                    <h3 class="footer__title">Quick Links</h3>
                    <ul class="footer__list">
                        <li><a href="index.php" class="footer__link">Home</a></li>
                        <li><a href="auctions.php" class="footer__link">Auctions</a></li>
                        <li><a href="about.php" class="footer__link">About Us</a></li>
                        <li><a href="contact.php" class="footer__link">Contact</a></li>
                    </ul>
                </div>
                
               
                
                <div class="footer__col">
                    <h3 class="footer__title">Contact Us</h3>
                    <ul class="footer__list">
                        <li><i class="fas fa-map-marker-alt"></i>Lovely Professional University</li>
                        <li><i class="fas fa-phone"></i>+91 7894561237</li>
                        <li><i class="fas fa-envelope"></i> greeenxd@bidshpere.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer__bottom">
                <p class="footer__copyright">&copy; 2025 greeenxd. All rights reserved.</p>
                <div class="footer__links">
                    <a href="#" class="footer__link">Privacy Policy</a>
                    <a href="#" class="footer__link">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Review Modal (hidden by default) -->

    <!-- <script src="profile-script.js"></script> -->
    <script src="../script.js"></script>
</body>
</html>

<?php
// Helper function to calculate time remaining
function time_remaining($end_time) {
    $now = new DateTime();
    $end = new DateTime($end_time);
    $interval = $now->diff($end);
    
    if ($interval->invert) {
        return 'Ended';
    }
    
    if ($interval->days > 0) {
        return $interval->days . ' days';
    } elseif ($interval->h > 0) {
        return $interval->h . ' hours';
    } else {
        return $interval->i . ' minutes';
    }
}
?>