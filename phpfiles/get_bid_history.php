<?php
// get_bid_history.php
$stmt = $pdo->prepare("
    SELECT b.*, i.item_name, i.starting_price 
    FROM bids b
    JOIN items i ON b.item_id = i.item_id
    WHERE b.user_id = ?
    ORDER BY b.bid_time DESC
");
$stmt->execute([$user_id]);
$bids = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Your Bidding History</h3>
<?php if (count($bids) > 0): ?>
    <table class="bid-history">
        <thead>
            <tr>
                <th>Item</th>
                <th>Your Bid</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bids as $bid): ?>
                <tr>
                    <td><?php echo htmlspecialchars($bid['item_name']); ?></td>
                    <td>$<?php echo number_format($bid['bid_amount'], 2); ?></td>
                    <td><?php echo date('M j, Y g:i a', strtotime($bid['bid_time'])); ?></td>
                    <td><?php echo ($bid['is_winner'] ? 'Won' : 'Outbid'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You haven't placed any bids yet.</p>
<?php endif; ?>