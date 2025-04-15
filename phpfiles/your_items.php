<?php
session_start();
require '../login&signup/backend/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle item update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    $item_id = $_POST['item_id'];
    
    try {
        // Verify the item belongs to the user
        $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([$item_id, $user_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception("Item not found or you don't have permission to edit it");
        }

        // Prepare update data
        $update_data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'starting_price' => (float)$_POST['starting_price'],
            'category' => $_POST['category'],
            'condition' => $_POST['condition'], // This is the problematic field
            'end_time' => $_POST['end_time'],
            'item_id' => $item_id
        ];

        // Handle image upload if a new one was provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $fileName = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES['image']['name']));
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $update_data['image_url'] = $targetPath;
                // Optionally delete the old image file
                if ($item['image_url'] && file_exists($item['image_url'])) {
                    unlink($item['image_url']);
                }
            }
        }

        // Build the SQL update query with proper escaping for reserved keywords
        $setParts = [];
        foreach ($update_data as $key => $value) {
            if ($key !== 'item_id') {
                // Escape reserved keywords with backticks
                $escapedKey = ($key === 'condition') ? '`condition`' : $key;
                $setParts[] = "$escapedKey = :$key";
            }
        }
        
        $sql = "UPDATE items SET " . implode(', ', $setParts) . " WHERE item_id = :item_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($update_data);
        
        $_SESSION['update_success'] = "Item updated successfully!";
        header("Location: your_items.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['update_error'] = $e->getMessage();
        header("Location: edit_item.php?item_id=$item_id");
        exit();
    }
}

// Handle item deletion
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    
    try {
        // Verify the item belongs to the user
        $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([$item_id, $user_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new Exception("Item not found or you don't have permission to delete it");
        }

        // Delete the item
        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
        $stmt->execute([$item_id]);
        
        // Optionally delete the associated image file
        if ($item['image_url'] && file_exists($item['image_url'])) {
            unlink($item['image_url']);
        }
        
        $_SESSION['delete_success'] = "Item deleted successfully!";
        header("Location: your_items.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['delete_error'] = $e->getMessage();
        header("Location: your_items.php");
        exit();
    }
}

// Fetch user's items
try {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE seller_id = ? ORDER BY end_time DESC");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Items | SportBid Auctions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Previous styles remain the same, adding new styles for modals */
        .your-items-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-xl);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: var(--spacing-md);
        }
        
        .page-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary-color);
            margin: 0;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            color: var(--secondary-color);
            transform: translateX(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-xl) 0;
            color: var(--text-light);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-md);
        }
        
        .item-card {
            background-color: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: 1px solid var(--border-color);
        }
        
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .item-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }
        
        .item-badge {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
            background-color: var(--primary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .item-body {
            padding: var(--spacing-md);
        }
        
        .item-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            color: var(--text-color);
        }
        
        .item-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: var(--spacing-md);
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .item-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .item-time {
            color: var(--text-light);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .item-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: var(--spacing-sm);
        }
        
        .status-active {
            background-color: rgba(var(--success-color-rgb), 0.1);
            color: var(--success-color);
        }
        
        .status-closed {
            background-color: rgba(var(--danger-color-rgb), 0.1);
            color: var(--danger-color);
        }
        
        .status-upcoming {
            background-color: rgba(var(--warning-color-rgb), 0.1);
            color: var(--warning-color);
        }
        
        .item-actions {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-md);
        }
        
        .btn-edit {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: white;
            flex: 1;
        }
        
        .btn-view {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .your-items-container {
                padding: var(--spacing-md);
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-sm);
            }
        }
        /* Edit Modal Styles */
        .edit-modal {
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
        }
        
        .edit-modal-content {
            background-color: var(--card-bg);
            margin: 50px auto;
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 700px;
            box-shadow: var(--shadow-xl);
            position: relative;
            border: 1px solid var(--border-color);
        }
        
        .close-edit-modal {
            position: absolute;
            right: 25px;
            top: 25px;
            font-size: 28px;
            font-weight: bold;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-edit-modal:hover {
            color: var(--danger-color);
        }
        
        .edit-form-group {
            margin-bottom: var(--spacing-md);
        }
        
        .edit-form-group label {
            display: block;
            margin-bottom: var(--spacing-xs);
            font-weight: 500;
            color: var(--text-color);
        }
        
        .edit-form-group input,
        .edit-form-group textarea,
        .edit-form-group select {
            width: 100%;
            padding: var(--spacing-sm);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        
        .edit-form-group textarea {
            min-height: 100px;
        }
        
        .image-preview-container {
            margin-top: var(--spacing-sm);
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: var(--spacing-xs);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }
        
        .btn-delete {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        /* Confirmation Modal */
        .confirmation-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }
        
        .confirmation-content {
            background-color: var(--card-bg);
            margin: 15% auto;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            width: 80%;
            max-width: 500px;
            text-align: center;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
        }
        
        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: var(--spacing-md);
            margin-top: var(--spacing-lg);
        }
    </style>
</head>
<body>
    <?php include 'header_footer/header.php'; ?>
    
    <main class="your-items-container">
        <div class="page-header">
            <h1 class="page-title">Your Listed Items</h1>
            <a href="auction.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Auctions
            </a>
        </div>
        
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['update_success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['update_success']; unset($_SESSION['update_success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['update_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['update_error']; unset($_SESSION['update_error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['delete_success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['delete_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open empty-state-icon"></i>
                <h3>You haven't listed any items yet</h3>
                <p>Get started by listing your first item for auction</p>
                <a href="upload_item.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> List New Item
                </a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($items as $item): 
                    $status_class = 'status-' . $item['status'];
                ?>
                    <div class="col">
                        <div class="item-card">
                            <img src="<?= htmlspecialchars($item['image_url'] ?: '../images/default-item.jpg') ?>" class="item-image" alt="<?= htmlspecialchars($item['name']) ?>">
                            <span class="item-badge"><?= ucfirst($item['category']) ?></span>
                            
                            <div class="item-body">
                                <h3 class="item-title"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
                                
                                <div class="item-meta">
                                    <span class="item-price">$<?= number_format($item['current_price'], 2) ?></span>
                                    <span class="item-time">
                                        <i class="far fa-clock"></i>
                                        <?= date('M j, Y', strtotime($item['end_time'])) ?>
                                    </span>
                                </div>
                                
                                <span class="item-status <?= $status_class ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                                
                                <div class="item-actions">
                                    <button class="btn btn-warning btn-edit edit-item-btn" 
                                            data-item-id="<?= $item['item_id'] ?>"
                                            data-item-name="<?= htmlspecialchars($item['name']) ?>"
                                            data-item-description="<?= htmlspecialchars($item['description']) ?>"
                                            data-item-starting-price="<?= $item['starting_price'] ?>"
                                            data-item-category="<?= htmlspecialchars($item['category']) ?>"
                                            data-item-condition="<?= htmlspecialchars($item['condition']) ?>"
                                            data-item-end-time="<?= date('Y-m-d\TH:i', strtotime($item['end_time'])) ?>"
                                            data-item-image="<?= htmlspecialchars($item['image_url'] ?: '../images/default-item.jpg') ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-delete delete-item-btn" 
                                            data-item-id="<?= $item['item_id'] ?>"
                                            data-item-name="<?= htmlspecialchars($item['name']) ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Edit Item Modal -->
    <div class="edit-modal" id="editModal">
        <div class="edit-modal-content">
            <span class="close-edit-modal">&times;</span>
            <h2>Edit Item</h2>
            
            <form id="editItemForm" method="POST" action="your_items.php" enctype="multipart/form-data">
                <input type="hidden" name="item_id" id="editItemId" value="">
                <input type="hidden" name="update_item" value="1">
                
                <div class="edit-form-group">
                    <label for="editName">Item Name</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                
                <div class="edit-form-group">
                    <label for="editDescription">Description</label>
                    <textarea id="editDescription" name="description" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="edit-form-group">
                            <label for="editStartingPrice">Starting Price ($)</label>
                            <input type="number" id="editStartingPrice" name="starting_price" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="edit-form-group">
                            <label for="editEndTime">Auction End Time</label>
                            <input type="datetime-local" id="editEndTime" name="end_time" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="edit-form-group">
                            <label for="editCategory">Category</label>
                            <select id="editCategory" name="category" required>
                                <option value="art">Art</option>
                                <option value="books">Books</option>
                                <option value="collectibles">Collectibles</option>
                                <option value="electronics">Electronics</option>
                                <option value="fashion">Fashion</option>
                                <option value="home">Home</option>
                                <option value="jewelry">Jewelry</option>
                                <option value="music">Music</option>
                                <option value="sports">Sports</option>
                                <option value="toys">Toys</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="edit-form-group">
                            <label for="editCondition">Condition</label>
                            <select id="editCondition" name="condition" required>
                                <option value="new">New</option>
                                <option value="like_new">Like New</option>
                                <option value="used">Used</option>
                                <option value="refurbished">Refurbished</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="edit-form-group">
                    <label for="editImage">Item Image</label>
                    <input type="file" id="editImage" name="image" accept="image/*">
                    <div class="image-preview-container">
                        <p>Current Image:</p>
                        <img id="editImagePreview" class="image-preview" src="" alt="Current Image">
                    </div>
                </div>
                
                <div class="edit-form-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="confirmation-modal" id="deleteConfirmationModal">
        <div class="confirmation-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
            <p>This action cannot be undone.</p>
            <div class="confirmation-buttons">
                <button class="btn btn-secondary" id="cancelDelete">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDelete">Delete Item</a>
            </div>
        </div>
    </div>
    
    <?php include 'header_footer/footer.php'; ?>

    <script src="../script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Modal Functionality
            const editModal = document.getElementById('editModal');
            const editButtons = document.querySelectorAll('.edit-item-btn');
            const closeEditModal = document.querySelector('.close-edit-modal');
            
            // Open edit modal when edit button is clicked
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('editItemId').value = this.getAttribute('data-item-id');
                    document.getElementById('editName').value = this.getAttribute('data-item-name');
                    document.getElementById('editDescription').value = this.getAttribute('data-item-description');
                    document.getElementById('editStartingPrice').value = this.getAttribute('data-item-starting-price');
                    document.getElementById('editCategory').value = this.getAttribute('data-item-category');
                    document.getElementById('editCondition').value = this.getAttribute('data-item-condition');
                    document.getElementById('editEndTime').value = this.getAttribute('data-item-end-time');
                    document.getElementById('editImagePreview').src = this.getAttribute('data-item-image');
                    
                    editModal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });
            
            // Close edit modal
            closeEditModal.addEventListener('click', function() {
                editModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
            
            // Close when clicking outside modal
            window.addEventListener('click', function(e) {
                if (e.target === editModal) {
                    editModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Preview new image when selected
            document.getElementById('editImage').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('editImagePreview').src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Delete Confirmation Modal
            const deleteModal = document.getElementById('deleteConfirmationModal');
            const deleteButtons = document.querySelectorAll('.delete-item-btn');
            const cancelDelete = document.getElementById('cancelDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            
            // Open delete confirmation modal
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    const itemName = this.getAttribute('data-item-name');
                    
                    document.getElementById('deleteItemName').textContent = itemName;
                    confirmDelete.href = `your_items.php?delete=${itemId}`;
                    
                    deleteModal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });
            
            // Close delete modal
            cancelDelete.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
            
            // Close when clicking outside modal
            window.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    deleteModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>
</body>
</html>