<?php
// get_won_items.php
$stmt = $pdo->prepare("
    SELECT i.*, w.win_date 
    FROM won_tems w
    JOIN items i ON w.item_id = i.item_id
    WHERE w.user_id = ?
    ORDER BY w.win_date DESC
");
$stmt->execute([$user_id]);
$won_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Items You've Won</h3>
<?php if (count($won_items) > 0): ?>
    <div class="item-grid">
        <?php foreach ($won_items as $item): ?>
            <div class="item-card won-item">
                <h4><?php echo htmlspecialchars($item['item_name']); ?></h4>
                <p>Winning bid: $<?php echo number_format($item['current_bid'], 2); ?></p>
                <p>Won on: <?php echo date('M j, Y', strtotime($item['win_date'])); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>You haven't won any items yet.</p>
<?php endif; ?>