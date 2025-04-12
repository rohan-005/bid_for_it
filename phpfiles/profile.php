<?php
session_start();
require '../login&signup/backend/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get counts
$watchlist_count = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ?");
$watchlist_count->execute([$user_id]);
$watchlist_count = $watchlist_count->fetchColumn();

$won_count = $pdo->prepare("SELECT COUNT(*) FROM won_items WHERE user_id = ?");
$won_count->execute([$user_id]);
$won_count = $won_count->fetchColumn();

// Determine active tab
$tabs = ['bidding', 'won', 'settings'];
$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], $tabs) ? $_GET['tab'] : 'bidding';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?> | Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-md);
    background-color: var(--bg-color);
    color: var(--text-color);
    font-family: var(--font-family);
    min-height: 100vh;
}

.profile-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
    position: relative;
    padding-bottom: var(--spacing-md);
}

.profile-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: var(--radius-full);
}

.profile-stats {
    display: flex;
    justify-content: center;
    gap: var(--spacing-xl);
    margin: var(--spacing-md) 0;
    flex-wrap: wrap;
}

.profile-stat {
    text-align: center;
    padding: var(--spacing-md);
    min-width: 120px;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.profile-stat:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    background: var(--hover-color);
}

.profile-stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: var(--spacing-xs);
}

.profile-stat-label {
    font-size: 0.9rem;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.profile-nav {
    display: flex;
    justify-content: center;
    margin-bottom: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
    position: relative;
}

.profile-nav::before {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 1px;
    background: var(--border-color);
    z-index: 0;
}

.profile-nav a {
    padding: var(--spacing-sm) var(--spacing-md);
    text-decoration: none;
    color: var(--text-light);
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    margin: 0 var(--spacing-xs);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
}

.profile-nav a:hover {
    color: var(--primary-color);
    background: rgba(var(--primary-color-rgb), 0.05);
}

.profile-nav a.active {
    color: var(--primary-color);
    font-weight: 600;
    background: rgba(var(--primary-color-rgb), 0.1);
    border-bottom: 3px solid var(--primary-color);
}

.tab-content {
    display: none;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.tab-content.active {
    display: block;
}

.item-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-md);
    padding: var(--spacing-sm) 0;
}

.item-card {
    border: 1px solid var(--border-color);
    padding: var(--spacing-md);
    border-radius: var(--radius-lg);
    background-color: var(--card-bg);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
}

.item-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--border-color);
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.item-card:hover::before {
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
}

.won-item {
    border-left: 4px solid var(--success-color);
    background: rgba(16, 185, 129, 0.05);
    position: relative;
}

.won-item::after {
    content: 'WON';
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--success-color);
    color: white;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.7rem;
    font-weight: bold;
}

.bid-history-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: var(--spacing-md);
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.bid-history-table th, .bid-history-table td {
    padding: var(--spacing-sm) var(--spacing-md);
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.bid-history-table th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.bid-history-table tr:last-child td {
    border-bottom: none;
}

.bid-history-table tr:hover td {
    background: rgba(var(--primary-color-rgb), 0.05);
}

.settings-container {
    max-width: 600px;
    margin: 0 auto;
    padding: var(--spacing-xl);
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    position: relative;
    overflow: hidden;
}

.settings-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
}

.settings-title {
    text-align: center;
    margin-bottom: var(--spacing-xl);
    color: var(--text-color);
    font-size: 1.8rem;
    position: relative;
    display: inline-block;
    width: 100%;
}

.settings-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: var(--primary-color);
    border-radius: var(--radius-full);
}

.form-group {
    margin-bottom: var(--spacing-lg);
    position: relative;
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    color: var(--text-color);
    font-size: 0.95rem;
}

.input-with-icon {
    position: relative;
}

.input-with-icon i {
    position: absolute;
    left: var(--spacing-md);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    transition: color 0.3s;
}

.form-input {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md) var(--spacing-sm) calc(var(--spacing-md) + 25px);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: all 0.3s;
    background-color: var(--bg-color);
    color: var(--text-color);
}

.form-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
    outline: none;
}

.form-input:focus + i {
    color: var(--primary-color);
}

.save-btn {
    width: 100%;
    padding: var(--spacing-sm);
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    opacity: 0.9;
}

.save-btn:active {
    transform: translateY(0);
}

.save-btn i {
    margin-right: var(--spacing-xs);
}


/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-stats {
        gap: var(--spacing-md);
    }
    
    .profile-stat {
        min-width: 100px;
        padding: var(--spacing-sm);
    }
    
    .profile-stat-value {
        font-size: 1.5rem;
    }
    
    .profile-nav {
        flex-wrap: wrap;
        background: var(--bg-secondary);
        padding: var(--spacing-sm);
        border-radius: var(--radius-md);
    }
    
    .profile-nav a {
        margin: var(--spacing-xs);
        padding: var(--spacing-xs) var(--spacing-sm);
    }
    
    .item-grid {
        grid-template-columns: 1fr;
    }
    
    .settings-container {
        padding: var(--spacing-md);
        border-radius: 0;
        border: none;
        box-shadow: none;
    }
    
    .fab {
        bottom: var(--spacing-md);
        right: var(--spacing-md);
    }
}

/* Loading animation */
@keyframes pulse {
    0% { opacity: 0.6; transform: scale(0.98); }
    50% { opacity: 1; transform: scale(1); }
    100% { opacity: 0.6; transform: scale(0.98); }
}

.loading {
    animation: pulse 1.5s ease-in-out infinite;
}
    </style>
</head>
<body>
    <?php include 'header_footer/header.php'; ?>

    <main class="profile-main">
        <div class="profile-container">
            <div class="profile-header">
                <h1># <?php echo htmlspecialchars($user['username']); ?></h1>
                <p>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                <div class="profile-rating">4.5</div>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <strong><?php echo $watchlist_count; ?></strong>
                        <span>WATCHING</span>
                    </div>
                    <div class="profile-stat">
                        <strong><?php echo $won_count; ?></strong>
                        <span>WON</span>
                    </div>
                </div>
            </div>

            <div class="profile-nav">
                <a href="?tab=bidding" class="<?php echo $active_tab === 'bidding' ? 'active' : ''; ?>">Bidding History</a>
                <a href="?tab=won" class="<?php echo $active_tab === 'won' ? 'active' : ''; ?>">Won Items</a>
                <a href="?tab=settings" class="<?php echo $active_tab === 'settings' ? 'active' : ''; ?>">Settings</a>
            </div>

            <!-- Bidding History Tab -->
<div id="bidding" class="tab-content <?php echo $active_tab === 'bidding' ? 'active' : ''; ?>">
    <h2>Bidding History</h2>
    <?php
    $bids = $pdo->prepare("SELECT b.*, i.name as item_name, i.end_time, 
                          (SELECT COUNT(*) FROM bids b2 
                           WHERE b2.item_id = b.item_id AND b2.amount > b.amount) as outbid_count,
                          (SELECT COUNT(*) FROM won_items w 
                           WHERE w.item_id = b.item_id AND w.user_id = ?) as is_winner
                          FROM bids b 
                          JOIN items i ON b.item_id = i.item_id 
                          WHERE b.user_id = ? 
                          ORDER BY b.bid_time DESC");
    $bids->execute([$user_id, $user_id]);
    
    if ($bids->rowCount() > 0): ?>
        <table class="bid-history-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Bid Amount</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($bid = $bids->fetch()): 
                    $is_ended = strtotime($bid['end_time']) < time();
                    $status = 'Active';
                    
                    if ($is_ended) {
                        $status = $bid['is_winner'] > 0 ? 'Won' : 'Lost';
                    } elseif ($bid['outbid_count'] > 0) {
                        $status = 'Outbid';
                    }
                ?>
                <tr>
                    <td>
                        <a href="../auction/auction.php?item=<?php echo $bid['item_id']; ?>">
                            <?php echo htmlspecialchars($bid['item_name']); ?>
                        </a>
                    </td>
                    <td>$<?php echo number_format($bid['amount'], 2); ?></td>
                    <td><?php echo date('M j, Y g:i a', strtotime($bid['bid_time'])); ?></td>
                    <td><?php echo $status; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No bidding history found.</p>
    <?php endif; ?>
</div>

            <!-- Won Items Tab -->
            <div id="won" class="tab-content <?php echo $active_tab === 'won' ? 'active' : ''; ?>">
    <h2>Won Items</h2>
    <?php
    // Simplified won items query
    $won_items = $pdo->prepare("SELECT i.* FROM won_items w 
                              JOIN items i ON w.item_id = i.item_id 
                              WHERE w.user_id = ?");
    $won_items->execute([$user_id]);
    
    if ($won_items->rowCount() > 0): ?>
        <div class="item-grid">
            <?php while ($item = $won_items->fetch()): ?>
            <div class="item-card won-item">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <p>Winning Price: $<?php echo number_format($item['current_price'], 2); ?></p>
                <?php if (!empty($item['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         style="max-width: 100%; height: auto; margin-top: 10px;">
                <?php endif; ?>
                <a href="../auction/auction.php?item=<?php echo $item['item_id']; ?>" class="btn">View Details</a>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No won items yet.</p>
    <?php endif; ?>
</div>

            <!-- Settings Tab -->
            <div id="settings" class="tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
    <div class="settings-container">
        <h2 class="settings-title">Account Settings</h2>
        
        <form class="settings-form" method="POST" action="update_profile.php">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" 
                           class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" 
                           placeholder="Leave blank to keep current" 
                           class="form-input">
                </div>
            </div>
            
            <button type="submit" class="save-btn">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>
    </main>

    <?php include 'header_footer/footer.php'; ?>
    <script src="../script.js"></script>
    <script>

    document.addEventListener('DOMContentLoaded', function() {
        // Get current tab from URL
        const urlParams = new URLSearchParams(window.location.search);
        const currentTab = urlParams.get('tab') || 'bidding';
        
        // Highlight the active tab
        const tabLinks = document.querySelectorAll('.profile-nav a');
        tabLinks.forEach(link => {
            const tabName = link.getAttribute('href').split('=')[1];
            if (tabName === currentTab) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
        
        // Show the active tab content
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            if (content.id === currentTab) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });
    });
    </script>
</body>
</html>