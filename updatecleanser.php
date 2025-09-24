<?php
session_start();
include 'connect.php';

// Prevent direct GET access
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

// === Secure file delete helper ===
function safeDelete($filename, $uploadsDir = 'uploads/')
{
    if (empty($filename)) {
        return false;
    }

    $uploadsDir = realpath(__DIR__ . '/' . $uploadsDir) . DIRECTORY_SEPARATOR;
    $filePath   = realpath($uploadsDir . $filename);

    if (
        $filePath &&
        strpos($filePath, $uploadsDir) === 0 &&
        is_file($filePath) &&
        preg_match('/^clenser_[a-zA-Z0-9]+[_a-zA-Z0-9-]*\.(jpg|jpeg|png|gif|webp)$/i', $filename)
    ) {
        // Verify it's actually an image
        $allowed_image_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if (in_array($mime_type, $allowed_image_mimes)) {
            return unlink($filePath);
        }
    }
    return false;
}

if (isset($_POST['submit'])) {
    // Validate and sanitize input
    $id    = intval($_POST['clenser_id']);
    $name  = trim($_POST['clenser_name']);
    $price = floatval($_POST['clenser_price']);
    $size  = trim($_POST['clenser_size']);

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
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 2 * 1024 * 1024; // 2MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($file_mime_type, $allowed_types)) {
            $_SESSION['error_message'] = 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.';
            header("Location: editClenser.php?Id=" . $id);
            exit();
        }

        // Validate file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            $_SESSION['error_message'] = 'Invalid file extension.';
            header("Location: editClenser.php?Id=" . $id);
            exit();
        }

        // Validate file size
        if ($file['size'] > $max_size) {
            $_SESSION['error_message'] = 'File size too large. Maximum 2MB allowed.';
            header("Location: editClenser.php?Id=" . $id);
            exit();
        }

        // Generate secure unique filename
        $sanitized_basename = preg_replace('/[^a-zA-Z0-9\._-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $image = 'clenser_' . uniqid() . '_' . $sanitized_basename . '.' . $file_extension;
        $target_path = "uploads/" . $image;

        // Ensure uploads directory exists and is secure
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

        // Add .htaccess protection to uploads directory
        $htaccess_content = "Order Deny,Allow\nDeny from all\n<FilesMatch '\.(jpg|jpeg|png|gif|webp)$'>\nAllow from all\n</FilesMatch>";
        file_put_contents('uploads/.htaccess', $htaccess_content);

        // Move uploaded file securely
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Delete old image if different
            if (!empty($current_image) && $current_image !== $image) {
                safeDelete($current_image);
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

            // Rollback: delete new image if update failed
            if (isset($file) && $image !== $current_image) {
                safeDelete($image);
            }
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = 'Database error. Please try again.';

        // Rollback: delete new image if statement prep failed
        if (isset($file) && $image !== $current_image) {
            safeDelete($image);
        }
    }
} else {
    $_SESSION['error_message'] = 'Invalid form submission.';
}

header("Location: Clenser.php");
exit();
?>
