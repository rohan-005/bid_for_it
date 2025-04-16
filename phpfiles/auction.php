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

// Handle bid submission
// In your auction.php, update the bid handling section:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    $item_id = $_POST['item_id'];
    $bid_amount = $_POST['bid_amount'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Verify item exists and is active
        $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND status = 'active' AND end_time > NOW()");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception("Item not available for bidding");
        }
        
        // 2. Verify bid is higher than current price
        $min_bid = $item['current_price'] + ($item['current_price'] * 0.05); // 5% increment
        if ($bid_amount < $min_bid) {
            throw new Exception("Your bid must be at least $" . number_format($min_bid, 2));
        }
        
        // 3. Place the bid
        $stmt = $pdo->prepare("INSERT INTO bids (item_id, user_id, amount) VALUES (?, ?, ?)");
        $stmt->execute([$item_id, $user_id, $bid_amount]);
        
        // 4. Update item's current price
        $stmt = $pdo->prepare("UPDATE items SET current_price = ? WHERE item_id = ?");
        $stmt->execute([$bid_amount, $item_id]);
        
        // 5. Update winning bid status
        $stmt = $pdo->prepare("UPDATE bids SET is_winning = 0 WHERE item_id = ?");
        $stmt->execute([$item_id]);
        
        $stmt = $pdo->prepare("UPDATE bids SET is_winning = 1 WHERE bid_id = LAST_INSERT_ID()");
        $stmt->execute();
        
        $pdo->commit();
        $_SESSION['bid_success'] = "Bid placed successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['bid_error'] = $e->getMessage();
    }
    
    header("Location: auction.php");
    exit();
}

// Get all active auction items
$stmt = $pdo->prepare("SELECT * FROM items WHERE status = 'active' AND end_time > NOW() ORDER BY end_time ASC");
$stmt->execute();
$auction_items = $stmt->fetchAll();

// Get watchlist items for current user
$watchlist = [];
$stmt = $pdo->prepare("SELECT item_id FROM watchlist WHERE user_id = ?");
$stmt->execute([$user_id]);
$watchlist_items = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction Page | BidSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../auction/auction.css">
    <style>
        /* Bid Modal Styles */
.bid-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    overflow-y: auto;
    animation: fadeIn 0.3s;
    backdrop-filter: blur(5px);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.bid-modal-content {
    background-color: var(--card-bg);
    margin: 50px auto;
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 600px;
    box-shadow: var(--shadow-xl);
    position: relative;
    animation: slideDown 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.bid-modal-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
}

@keyframes slideDown {
    from { transform: translateY(-20px) scale(0.98); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.close-bid-modal {
    position: absolute;
    right: 25px;
    top: 25px;
    font-size: 28px;
    font-weight: bold;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.3s;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-bid-modal:hover {
    color: var(--danger-color);
    background-color: rgba(239, 68, 68, 0.1);
    transform: rotate(90deg);
}

.bid-modal-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-md);
    text-align: center;
    position: relative;
}

.bid-modal-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: var(--radius-full);
}

.bid-modal-header h2 {
    margin: 0 0 var(--spacing-sm) 0;
    color: var(--text-color);
    font-size: 1.5rem;
}

.bid-item-image {
    width: 100%;
    height: 220px;
    overflow: hidden;
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-md);
    box-shadow: var(--shadow-sm);
    position: relative;
}

.bid-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.bid-item-image:hover img {
    transform: scale(1.05);
}

.bid-info {
    display: flex;
    justify-content: space-between;
    background: var(--bg-secondary);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-lg);
    gap: var(--spacing-md);
    box-shadow: var(--shadow-sm);
}

.bid-info > div {
    flex: 1;
    text-align: center;
    padding: var(--spacing-xs);
}

.bid-info span {
    display: block;
}

.bid-info span:first-child {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: var(--spacing-xs);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bid-info span:last-child {
    font-weight: bold;
    font-size: 1.2rem;
    color: var(--danger-color);
}

.bid-info .current-bid span:last-child {
    color: var(--primary-color);
}

.bid-info .time-left span:last-child {
    color: var(--warning-color);
}

#bidForm {
    margin-bottom: var(--spacing-lg);
}

.form-group {
    margin-bottom: var(--spacing-md);
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    color: var(--text-color);
    font-size: 0.95rem;
}

.form-group input {
    width: 100%;
    padding: var(--spacing-sm);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 1rem;
    background-color: var(--bg-color);
    color: var(--text-color);
    transition: all 0.3s;
}

.form-group input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
    outline: none;
}

.form-group small {
    display: block;
    margin-top: var(--spacing-xs);
    color: var(--text-light);
    font-size: 0.8rem;
}

.btn-submit-bid {
    width: 100%;
    padding: var(--spacing-sm);
    background: linear-gradient(135deg, var(--success-color), #1dd1a1);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: var(--shadow-sm);
    margin-top: var(--spacing-sm);
}

.btn-submit-bid:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    opacity: 0.9;
}

.btn-submit-bid:active {
    transform: translateY(0);
}

.bid-history {
    border-top: 1px solid var(--border-color);
    padding-top: var(--spacing-md);
}

.bid-history h3 {
    margin: 0 0 var(--spacing-md) 0;
    color: var(--text-color);
    font-size: 1.2rem;
    position: relative;
    display: inline-block;
}

.bid-history h3::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 40px;
    height: 2px;
    background: var(--primary-color);
}

.bid-history-list {
    max-height: 200px;
    overflow-y: auto;
    padding-right: var(--spacing-sm);
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) var(--bg-secondary);
}

.bid-history-list::-webkit-scrollbar {
    width: 6px;
}

.bid-history-list::-webkit-scrollbar-track {
    background: var(--bg-secondary);
    border-radius: var(--radius-full);
}

.bid-history-list::-webkit-scrollbar-thumb {
    background-color: var(--primary-color);
    border-radius: var(--radius-full);
}

.bid-history-item {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
    border-radius: var(--radius-sm);
    padding-left: var(--spacing-xs);
}

.bid-history-item:hover {
    background: rgba(var(--primary-color-rgb), 0.05);
}

.bid-history-item:last-child {
    border-bottom: none;
}

.bid-history-user {
    font-weight: 500;
    color: var(--text-color);
}

.bid-history-amount {
    color: var(--danger-color);
    font-weight: bold;
}

.bid-history-time {
    color: var(--text-light);
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .bid-modal-content {
        width: 95%;
        margin: var(--spacing-md) auto;
        padding: var(--spacing-md);
    }
    
    .bid-info {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .bid-item-image {
        height: 180px;
    }
    
    .close-bid-modal {
        right: 15px;
        top: 15px;
        font-size: 24px;
    }
}

/* Success state animation */
@keyframes bidSuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.bid-success {
    animation: bidSuccess 0.5s ease;
}
    </style>
</head>
<body>
    <!-- Header from home page -->
    <?php include 'header_footer/header.php'; ?>

    <main class="auction-main">
        <div class="auction-container">
            <h1 class="auction-title">Live Auctions</h1>
            
            <!-- Display bid messages -->
            <?php if (isset($_SESSION['bid_success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['bid_success']; unset($_SESSION['bid_success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['bid_error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['bid_error']; unset($_SESSION['bid_error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Auction Filters -->
            <div class="auction-filters">
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category">
                        <option value="all">All Categories</option>
                        <option value="sports">Sports</option>
                        <option value="collectibles">Collectibles</option>
                        <option value="electronics">Electronics</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort">Sort By:</label>
                    <select id="sort" name="sort">
                        <option value="ending">Ending Soonest</option>
                        <option value="newest">Newest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Search:</label>
                    <input type="text" id="search" name="search" placeholder="Search items...">
                </div>
            </div>
            
            <!-- Auction Items Grid -->
            <!-- Auction Items Grid -->
<div class="auction-grid">
    <?php if (empty($auction_items)): ?>
        <div class="empty-state">
            <i class="fas fa-gavel empty-state-icon"></i>
            <h3>No active auctions at the moment</h3>
            <p>Check back later for new items</p>
        </div>
    <?php else: ?>
        <?php foreach ($auction_items as $item): 
            // Skip items belonging to the current user
            if ($item['seller_id'] == $user_id) continue;
            
            $is_watched = in_array($item['item_id'], $watchlist_items);
            $time_left = time_remaining($item['end_time']);
        ?>
            <div class="auction-item" 
                 data-item-id="<?php echo $item['item_id']; ?>"
                 data-category="<?php echo htmlspecialchars($item['category']); ?>">
                <div class="auction-item-image">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?: '../images/default-item.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="auction-time-left" data-end-time="<?php echo $item['end_time']; ?>">
                        <i class="fas fa-clock"></i> <?php echo $time_left; ?>
                    </div>
                    
                    <!-- Watchlist button -->
                    <button class="watchlist-btn <?php echo $is_watched ? 'watched' : ''; ?>" 
                            data-item-id="<?php echo $item['item_id']; ?>">
                        <!-- <i class="fas fa-heart"></i> -->
                    </button>
                </div>
                
                <div class="auction-item-details">
                    <div class="item-header">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <span class="item-category"><?php echo ucfirst($item['category']); ?></span>
                    </div>
                    
                    <p class="auction-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <div class="auction-item-meta">
                        <div class="auction-item-price">
                            <span>Current Bid:</span>
                            <span class="price-amount">$<?php echo number_format($item['current_price'], 2); ?></span>
                        </div>
                        
                        
                    </div>
                    
                    <div class="auction-item-actions">
                        <button class="btn btn-primary btn-bid" 
                                data-item-id="<?php echo $item['item_id']; ?>"
                                data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
                                data-item-image="<?php echo htmlspecialchars($item['image_url'] ?: '../images/default-item.jpg'); ?>"
                                data-current-price="<?php echo $item['current_price']; ?>"
                                data-end-time="<?php echo $item['end_time']; ?>">
                            Place Bid
                        </button>
                        
                        
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
        </div>
    </main>

    <!-- Bid Modal -->
    <div class="bid-modal" id="bidModal">
        <div class="bid-modal-content">
            <span class="close-bid-modal">&times;</span>
            <div class="bid-modal-header">
                <h2 id="bidItemTitle">Item Title</h2>
                <div class="bid-item-image">
                    <img id="bidItemImage" src="" alt="">
                </div>
            </div>
            <div class="bid-modal-body">
                <div class="bid-info">
                    <div class="current-bid">
                        <span>Current Bid:</span>
                        <span id="currentBidAmount">$0.00</span>
                    </div>
                    <div class="time-left">
                        <span>Time Left:</span>
                        <span id="bidTimeLeft">24h 30m</span>
                    </div>
                </div>
                
                <form id="bidForm" method="POST" action="auction.php">
                    <input type="hidden" name="item_id" id="modalItemId" value="">
                    <div class="form-group">
                        <label for="bidAmount">Your Bid ($):</label>
                        <input type="number" id="bidAmount" name="bid_amount" min="0" step="0.01" required>
                        <small id="minBidHint">Minimum bid: $0.00</small>
                    </div>
                    <button type="submit" name="place_bid" class="btn-submit-bid">Submit Bid</button>
                </form>
                
                
            </div>
        </div>
    </div>

    <!-- Footer from home page -->
    <?php include 'header_footer/footer.php'; ?>

    <script src="../script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {

    
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});
        // Bid Modal Functionality
        const bidModal = document.getElementById('bidModal');
        const bidButtons = document.querySelectorAll('.btn-bid');
        const closeModal = document.querySelector('.close-bid-modal');
        const bidForm = document.getElementById('bidForm');
        
        // Open modal when bid button is clicked
        bidButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const itemName = this.getAttribute('data-item-name');
                const itemImage = this.getAttribute('data-item-image');
                const currentBid = this.getAttribute('data-current-price');
                const endTime = this.getAttribute('data-end-time');
                
                // Calculate time left
                const now = new Date();
                const end = new Date(endTime);
                const diff = end - now;
                let timeLeft = '';
                
                if (diff <= 0) {
                    timeLeft = 'Ended';
                } else {
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    timeLeft = `${days}d ${hours}h`;
                }
                
                // Populate modal
                document.getElementById('bidItemTitle').textContent = itemName;
                document.getElementById('bidItemImage').src = itemImage;
                document.getElementById('currentBidAmount').textContent = `$${parseFloat(currentBid).toFixed(2)}`;
                document.getElementById('bidTimeLeft').textContent = timeLeft;
                document.getElementById('modalItemId').value = itemId;
                
                // Set minimum bid (current bid + 5%)
                const minBid = parseFloat(currentBid) * 1.05;
                document.getElementById('bidAmount').min = minBid.toFixed(2);
                document.getElementById('bidAmount').value = minBid.toFixed(2);
                document.getElementById('minBidHint').textContent = `Minimum bid: $${minBid.toFixed(2)}`;
                
                
                loadBidHistory(itemId);
                
                // Show modal
                bidModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        });
        
        // Close modal
        closeModal.addEventListener('click', function() {
            bidModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        // Close when clicking outside modal
        window.addEventListener('click', function(e) {
            if (e.target === bidModal) {
                bidModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Load bid history function
        function loadBidHistory(itemId) {
            fetch(`get_bid_history.php?item_id=${itemId}`)
                .then(response => response.json())
                .then(data => {
                    const historyList = document.getElementById('bidHistoryList');
                    historyList.innerHTML = '';
                    
                    if (data.success && data.bids.length > 0) {
                        data.bids.forEach(bid => {
                            const bidItem = document.createElement('div');
                            bidItem.className = 'bid-history-item';
                            bidItem.innerHTML = `
                                <div>
                                    <span class="bid-history-user">${bid.username}</span>
                                    <span class="bid-history-time">${new Date(bid.bid_time).toLocaleString()}</span>
                                </div>
                                <span class="bid-history-amount">$${bid.amount.toFixed(2)}</span>
                            `;
                            historyList.appendChild(bidItem);
                        });
                    } else {
                        historyList.innerHTML = '<p>No bids yet</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading bid history:', error);
                    document.getElementById('bidHistoryList').innerHTML = '<p>Error loading bid history</p>';
                });
        }
        
        // Filter functionality
        document.getElementById('category').addEventListener('change', function() {
            const category = this.value;
            document.querySelectorAll('.auction-item').forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.auction-item').forEach(item => {
                const name = item.querySelector('h3').textContent.toLowerCase();
                const desc = item.querySelector('.auction-item-description').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || desc.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    
    </script>
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
        return $interval->days . 'd ' . $interval->h . 'h';
    } elseif ($interval->h > 0) {
        return $interval->h . 'h ' . $interval->i . 'm';
    } else {
        return $interval->i . 'm ' . $interval->s . 's';
    }
}
?>