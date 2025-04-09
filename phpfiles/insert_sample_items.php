<?php
require '../login&signup/backend/db_config.php';

// Verify required columns exist
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `items`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['name', 'description', 'starting_price', 'current_price', 'image_url', 'category', 'start_time', 'end_time', 'seller_id', 'status'];
    
    foreach ($required_columns as $col) {
        if (!in_array($col, $columns)) {
            throw new Exception("Required column '$col' is missing from items table. Please run update_schema.php first.");
        }
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Sample items data (updated with placeholder images)
$sample_items = [
    [
        'name' => 'Autographed Football',
        'description' => 'Official match ball signed by Cristiano Ronaldo',
        'starting_price' => 500.00,
        'current_price' => 500.00,
        'image_url' => 'https://via.placeholder.com/400x300?text=Autographed+Football',
        'category' => 'sports',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Vintage Baseball Glove',
        'description' => 'Authentic 1960s leather baseball glove in excellent condition',
        'starting_price' => 250.00,
        'current_price' => 250.00,
        'image_url' => 'https://via.placeholder.com/400x300?text=Baseball+Glove',
        'category' => 'sports',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+5 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Olympic Torch Replica',
        'description' => 'Limited edition replica of the 2012 London Olympic torch',
        'starting_price' => 350.00,
        'current_price' => 350.00,
        'image_url' => 'https://via.placeholder.com/400x300?text=Olympic+Torch',
        'category' => 'collectibles',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+3 days')),
        'seller_id' => 1,
        'status' => 'active'
    ]
];

try {
    $pdo->beginTransaction();
    
    // Clear existing sample items (optional)
    // $pdo->exec("DELETE FROM items WHERE seller_id = 1");
    
    // Insert sample items
    $stmt = $pdo->prepare("INSERT INTO items 
        (name, description, starting_price, current_price, image_url, category, start_time, end_time, seller_id, status) 
        VALUES 
        (:name, :description, :starting_price, :current_price, :image_url, :category, :start_time, :end_time, :seller_id, :status)");
    
    foreach ($sample_items as $item) {
        $stmt->execute($item);
    }
    
    $pdo->commit();
    echo "Successfully added " . count($sample_items) . " sample items to the auction!";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error adding sample items: " . $e->getMessage();
}