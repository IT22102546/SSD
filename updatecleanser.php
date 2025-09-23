<?php
session_start();
include 'connect.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Method not allowed.';
    header("Location: Clenser.php");
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = 'Security token validation failed.';
    header("Location: Clenser.php");
    exit();
}

if (isset($_POST['submit'])) {
    // Validate and sanitize input
    $id = intval($_POST['clenser_id']);
    $name = trim($_POST['clenser_name']);
    $price = floatval($_POST['clenser_price']);
    $size = trim($_POST['clenser_size']);
    
    // Basic validation
    if ($id <= 0 || empty($name) || $price <= 0 || empty($size)) {
        $_SESSION['error_message'] = 'Invalid input data.';
        header("Location: editClenser.php?Id=" . $id);
        exit();
    }
    
    // Additional validation
    if (strlen($name) > 100 || strlen($size) > 50) {
        $_SESSION['error_message'] = 'Input data too long.';
        header("Location: editClenser.php?Id=" . $id);
        exit();
    }
    
    // Check if cleanser exists
    $check_sql = "SELECT image FROM clenser WHERE Cl_ID = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $existing = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($existing) === 0) {
        $_SESSION['error_message'] = 'Cleanser not found.';
        header("Location: Clenser.php");
        exit();
    }
    
    $row = mysqli_fetch_assoc($existing);
    $current_image = $row['image'];
    mysqli_stmt_close($check_stmt);
    
    $image = $current_image; // Default to current image
    
    // Handle file upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error_message'] = 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.';
            header("Location: editClenser.php?Id=" . $id);
            exit();
        }
        
        if ($file['size'] > $max_size) {
            $_SESSION['error_message'] = 'File size too large. Maximum 2MB allowed.';
            header("Location: editClenser.php?Id=" . $id);
            exit();
        }
        
        // Generate unique filename to prevent conflicts
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $image = uniqid('clenser_') . '.' . $file_extension;
        $target_path = "uploads/" . $image;
        
        // Move uploaded file securely
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Delete old image if it exists and is different from new one
            if (!empty($current_image) && $current_image !== $image) {
                $old_image_path = "uploads/" . $current_image;
                if (file_exists($old_image_path) && is_file($old_image_path)) {
                    // Security check: ensure the file is within the uploads directory
                    $real_old_path = realpath($old_image_path);
                    $real_uploads_path = realpath('uploads/');
                    
                    if ($real_old_path && $real_uploads_path && strpos($real_old_path, $real_uploads_path) === 0) {
                        unlink($old_image_path);
                    }
                }
            }
        } else {
            $_SESSION['error_message'] = 'Sorry, there was an error uploading your file.';
            header("Location: editClenser.php?Id=" . $id);
            exit();
        }
    }
    
    // Update database using prepared statement
    $sql = "UPDATE clenser SET Clenser_name = ?, Clenser_price = ?, Clenser_size = ?, image = ? WHERE Cl_ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sdssi", $name, $price, $size, $image, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Regenerate CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['success_message'] = 'Cleanser updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Error updating cleanser: ' . mysqli_stmt_error($stmt);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = 'Database error. Please try again.';
    }
} else {
    $_SESSION['error_message'] = 'Invalid form submission.';
}

header("Location: Clenser.php");
exit();
?>