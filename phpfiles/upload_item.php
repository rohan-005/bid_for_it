<?php
session_start();
require '../login&signup/backend/db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = false;
$error = '';
$duplicate = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check for duplicate item (only if not editing)
        if (!isset($_POST['update_item'])) {
            $checkStmt = $pdo->prepare("SELECT item_id FROM items WHERE name = ? AND seller_id = ?");
            $checkStmt->execute([$_POST['name'], $user_id]);
            
            if ($checkStmt->fetch()) {
                $duplicate = true;
                throw new Exception("This item already exists in the database.");
            }
        }

        // Handle file upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = preg_replace("/[^a-zA-Z0-9._-]/", "", $_FILES['image']['name']);
            $fileName = uniqid() . '_' . $fileName;
            
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $destPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_path = $destPath;
            } else {
                throw new Exception("Failed to move uploaded file");
            }
        } elseif (isset($_POST['existing_image'])) {
            $image_path = $_POST['existing_image'];
        }

        // Prepare item data
        $current_time = date('Y-m-d H:i:s');
        $start_price = (float)$_POST['starting_price'];
        
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'starting_price' => $start_price,
            'starting_bid' => $start_price,
            'current_bid' => $start_price,
            'current_price' => $start_price,
            'end_time' => $_POST['end_time'],
            'seller_id' => $user_id,
            'category' => $_POST['category'],
            'condition' => $_POST['condition'],
            'image_url' => $image_path,
            'start_time' => $_POST['start_time'] ?: $current_time,
            'status' => 'active'
        ];

        // Handle update or insert
        if (isset($_POST['update_item']) && isset($_POST['item_id'])) {
            // Verify the item belongs to the user
            $verifyStmt = $pdo->prepare("SELECT item_id FROM items WHERE item_id = ? AND seller_id = ?");
            $verifyStmt->execute([$_POST['item_id'], $user_id]);
            
            if (!$verifyStmt->fetch()) {
                throw new Exception("You don't have permission to edit this item.");
            }

            // Build the update query
            $update_fields = [];
            foreach ($data as $key => $value) {
                $update_fields[] = "$key = :$key";
            }
            
            $data['item_id'] = $_POST['item_id'];
            $sql = "UPDATE items SET " . implode(', ', $update_fields) . " WHERE item_id = :item_id";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($data);
            
            $_SESSION['success_message'] = "Item updated successfully!";
        } else {
            // Insert new item
            $columns = implode(', ', array_map(function($col) {
                return $col === 'condition' ? '`condition`' : $col;
            }, array_keys($data)));
            
            $placeholders = ':' . implode(', :', array_keys($data));
            $stmt = $pdo->prepare("INSERT INTO items ($columns) VALUES ($placeholders)");
            $success = $stmt->execute($data);
            
            $_SESSION['success_message'] = "Item uploaded successfully!";
        }

        if ($success) {
            header("Location: your_items.php");
            exit();
        } else {
            throw new Exception("Database operation failed");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle item deletion
if (isset($_GET['delete'])) {
    try {
        $item_id = $_GET['delete'];
        
        // Verify the item belongs to the user
        $verifyStmt = $pdo->prepare("SELECT image_url FROM items WHERE item_id = ? AND seller_id = ?");
        $verifyStmt->execute([$item_id, $user_id]);
        $item = $verifyStmt->fetch();
        
        if (!$item) {
            throw new Exception("You don't have permission to delete this item.");
        }

        // Delete the item
        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
        $stmt->execute([$item_id]);
        
        // Delete the associated image file if it exists
        if ($item['image_url'] && file_exists($item['image_url'])) {
            unlink($item['image_url']);
        }
        
        $_SESSION['success_message'] = "Item deleted successfully!";
        header("Location: your_items.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: your_items.php");
        exit();
    }
}

// Set default times
$now = new DateTime();
$defaultStart = $now->format('Y-m-d\TH:i');
$defaultEnd = $now->add(new DateInterval('P7D'))->format('Y-m-d\TH:i');

// Check if we're editing an existing item
$editing = false;
$item_to_edit = null;
if (isset($_GET['edit'])) {
    try {
        $item_id = $_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([$item_id, $user_id]);
        $item_to_edit = $stmt->fetch();
        
        if ($item_to_edit) {
            $editing = true;
            $defaultStart = date('Y-m-d\TH:i', strtotime($item_to_edit['start_time']));
            $defaultEnd = date('Y-m-d\TH:i', strtotime($item_to_edit['end_time']));
        }
    } catch (PDOException $e) {
        $error = "Error fetching item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Edit Item' : 'Upload Item' ?> | Auction System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --bg-color: #ffffff;
            --bg-secondary: #f9fafb;
            --border-color: #e5e7eb;
            --card-bg: #ffffff;
            --hover-color: #f3f4f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
        }

        [data-theme="dark"] {
            --primary-color: #3b82f6;
            --secondary-color: #1e40af;
            --accent-color: #60a5fa;
            --text-color: #f9fafb;
            --text-light: #9ca3af;
            --bg-color: #1f2937;
            --bg-secondary: #111827;
            --border-color: #374151;
            --card-bg: #1f2937;
            --hover-color: #374151;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }

        .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .alert {
            border-radius: 4px;
        }

        .form-control, .form-select {
            background-color: var(--bg-color);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-color);
            color: var(--text-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-color), 0.25);
        }
    </style>
</head>
<body>
    <?php include 'header_footer/header.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4"><?= $editing ? 'Edit Auction Item' : 'Upload Auction Item' ?></h2>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message'] ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php if ($editing): ?>
                    <input type="hidden" name="update_item" value="1">
                    <input type="hidden" name="item_id" value="<?= $item_to_edit['item_id'] ?>">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($item_to_edit['image_url']) ?>">
                <?php endif; ?>
                
                <div class="form-section">
                    <h4>Basic Information</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Item Name*</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= $editing ? htmlspecialchars($item_to_edit['name']) : '' ?>" required>
                            <div class="invalid-feedback">Please provide an item name.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category*</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select a category</option>
                                <?php
                                $categories = ['art', 'books', 'collectibles', 'electronics', 'fashion', 'home', 'jewelry', 'music', 'sports', 'toys', 'other'];
                                foreach ($categories as $category) {
                                    $selected = $editing && $item_to_edit['category'] === $category ? 'selected' : '';
                                    echo "<option value=\"$category\" $selected>" . ucfirst($category) . "</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description*</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= $editing ? htmlspecialchars($item_to_edit['description']) : '' ?></textarea>
                        <div class="invalid-feedback">Please provide a description.</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="condition" class="form-label">Condition*</label>
                            <select class="form-select" id="condition" name="condition" required>
                                <option value="">Select condition</option>
                                <?php
                                $conditions = ['new', 'like_new', 'used', 'refurbished'];
                                foreach ($conditions as $cond) {
                                    $selected = $editing && $item_to_edit['condition'] === $cond ? 'selected' : '';
                                    echo "<option value=\"$cond\" $selected>" . ucwords(str_replace('_', ' ', $cond)) . "</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Please select the item condition.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="starting_price" class="form-label">Starting Price ($)*</label>
                            <input type="number" class="form-control" id="starting_price" name="starting_price" 
                                   min="0.01" step="0.01" value="<?= $editing ? $item_to_edit['starting_price'] : '' ?>" required>
                            <div class="invalid-feedback">Please enter a valid price.</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4>Auction Details</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Auction Start Time*</label>
                            <input type="datetime-local" class="form-control" id="start_time" name="start_time" 
                                   value="<?= $defaultStart ?>" required>
                            <div class="invalid-feedback">Please select a start time.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="end_time" class="form-label">Auction End Time*</label>
                            <input type="datetime-local" class="form-control" id="end_time" name="end_time" 
                                   value="<?= $defaultEnd ?>" required>
                            <div class="invalid-feedback">Please select an end time.</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4>Item Images</h4>
                    <div class="mb-3">
                        <label for="image" class="form-label"><?= $editing ? 'Update Image' : 'Main Image*' ?></label>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*" <?= $editing ? '' : 'required' ?>>
                        <div class="invalid-feedback">Please upload an image.</div>
                        <?php if ($editing && $item_to_edit['image_url']): ?>
                            <div class="mt-2">
                                <p>Current Image:</p>
                                <img src="<?= htmlspecialchars($item_to_edit['image_url']) ?>" class="preview-image" alt="Current item image">
                            </div>
                        <?php endif; ?>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?= $editing ? 'Update Item' : 'Submit Item' ?>
                    </button>
                    <?php if ($editing): ?>
                        <a href="your_items.php" class="btn btn-secondary btn-lg">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php include 'header_footer/footer.php'; ?>
    <script src="../script.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('imagePreview');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            // Update end time minimum when start time changes
            startTimeInput.addEventListener('change', function() {
                endTimeInput.min = this.value;
                if (new Date(endTimeInput.value) < new Date(this.value)) {
                    endTimeInput.value = this.value;
                }
            });

            // Image preview
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.innerHTML = `
                            <p>New Image Preview:</p>
                            <img src="${e.target.result}" class="preview-image" alt="Preview">
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
</body>
</html>