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


$sample_items = [
    [
        'name' => 'Autographed Football',
        'description' => 'Official match ball signed by Cristiano Ronaldo',
        'starting_price' => 500.00,
        'current_price' => 500.00,
        'image_url' => 'images/AutographedFootball.jpeg',
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
        'image_url' => 'images/gloves.jpg',
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
        'image_url' => 'images/tourch.avif',
        'category' => 'collectibles',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+3 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Vintage Rolex Submariner',
        'description' => 'Classic 1960s Rolex Submariner watch, excellent condition',
        'starting_price' => 12000.00,
        'current_price' => 12000.00,
        'image_url' => 'images/rolex.jpeg',
        'category' => 'jewelry',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+10 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Rare Picasso Sketch',
        'description' => 'Original pencil sketch by Pablo Picasso, circa 1952',
        'starting_price' => 25000.00,
        'current_price' => 25000.00,
        'image_url' => 'images/stketch.jpg',
        'category' => 'art',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+14 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Signed First Edition Harry Potter',
        'description' => 'Harry Potter and the Philosopher\'s Stone, signed by J.K. Rowling',
        'starting_price' => 8000.00,
        'current_price' => 8000.00,
        'image_url' => 'images/harrypotter.jpg',
        'category' => 'books',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+5 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => '1957 Gibson Les Paul',
        'description' => 'Vintage Gibson Les Paul guitar in sunburst finish "Limited Edition"',
        'starting_price' => 15000.00,
        'current_price' => 15000.00,
        'image_url' => 'images/paul.jpg',
        'category' => 'music',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+12 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Limited Edition Star Wars Poster',
        'description' => '1977 original Star Wars movie poster, mint condition',
        'starting_price' => 3500.00,
        'current_price' => 3500.00,
        'image_url' => 'images/starwars.jpg',
        'category' => 'collectibles',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Diamond Tennis Bracelet',
        'description' => '14k white gold bracelet with 5 carats of diamonds',
        'starting_price' => 7500.00,
        'current_price' => 7500.00,
        'image_url' => 'images/braclet.webp',
        'category' => 'jewelry',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+9 days')),
        'seller_id' => 1,
        'status' => 'active'
    ],
    [
        'name' => 'Michael Jordan Rookie Card',
        'description' => '1986 Fleer Michael Jordan rookie card, PSA 9 grade',
        'starting_price' => 20000.00,
        'current_price' => 20000.00,
        'image_url' => 'images/card.webp',
        'category' => 'sports',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+14 days')),
        'seller_id' => 1,
        'status' => 'active'
    ]
];

try {
    $pdo->beginTransaction();
    
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