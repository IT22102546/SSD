<?php
session_start();
include 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Method not allowed.';
    header("Location: AddClensers.php");
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = 'Security token validation failed.';
    header("Location: AddClensers.php");
    exit();
}

if (isset($_POST['sub'])) {
    // Validate and sanitize input
    $Clensername = trim($_POST['Clensername'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    
    // Basic validation
    if (empty($Clensername) || $price <= 0 || empty($size)) {
        $_SESSION['error_message'] = 'Please fill all required fields with valid data.';
        header("Location: AddClensers.php");
        exit();
    }
    
    // Length validation
    if (strlen($Clensername) > 100 || strlen($size) > 50) {
        $_SESSION['error_message'] = 'Input data too long. Name max 100 chars, size max 50 chars.';
        header("Location: AddClensers.php");
        exit();
    }
    
    // Check if image was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = 'Please select an image to upload.';
        header("Location: AddClensers.php");
        exit();
    }
    
    $file = $_FILES['image'];
    
    // Validate file type
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileType, $allowTypes)) {
        $_SESSION['error_message'] = 'Sorry, only JPG, JPEG, PNG, GIF, and WebP files are allowed.';
        header("Location: AddClensers.php");
        exit();
    }
    
    // Validate file size (2MB max)
    $max_size = 2 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        $_SESSION['error_message'] = 'File size too large. Maximum 2MB allowed.';
        header("Location: AddClensers.php");
        exit();
    }
    
    // Additional security: validate file content
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    if (!in_array($mimeType, $allowedMimeTypes)) {
        $_SESSION['error_message'] = 'Invalid file type detected. Please upload a valid image.';
        header("Location: AddClensers.php");
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            $_SESSION['error_message'] = 'Could not create uploads directory.';
            header("Location: AddClensers.php");
            exit();
        }
    }
    
    // Generate unique filename to prevent overwrites
    $fileName = uniqid('clenser_') . '.' . $fileType;
    $targetFilePath = $targetDir . $fileName;
    
    // Secure file path validation
    $realTargetDir = realpath($targetDir) . DIRECTORY_SEPARATOR;
    $realTargetFilePath = realpath($targetDir) . DIRECTORY_SEPARATOR . $fileName;
    
    // Ensure the file path is within the intended directory
    if (strpos($realTargetFilePath, $realTargetDir) !== 0) {
        $_SESSION['error_message'] = 'Invalid file path.';
        header("Location: AddClensers.php");
        exit();
    }
    
    // Move uploaded file securely
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Insert into database using prepared statement
        $sql = "INSERT INTO clenser (Clenser_name, Clenser_price, Clenser_size, image) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sdss", $Clensername, $price, $size, $fileName);
            
            if (mysqli_stmt_execute($stmt)) {
                // Regenerate CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['success_message'] = 'Cleanser added successfully!';
                header("Location: Clenser.php");
                exit();
            } else {
                // SECURE FILE DELETION: Delete the uploaded file if database insertion fails
                secureDeleteFile($targetFilePath, $targetDir);
                $_SESSION['error_message'] = 'Error adding cleanser to database: ' . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            // SECURE FILE DELETION: Delete the uploaded file if prepare fails
            secureDeleteFile($targetFilePath, $targetDir);
            $_SESSION['error_message'] = 'Database preparation error.';
        }
    } else {
        $_SESSION['error_message'] = 'Sorry, there was an error uploading your file. Check directory permissions.';
    }
} else {
    $_SESSION['error_message'] = 'Invalid form submission.';
}

header("Location: AddClensers.php");
exit();

/**
 * Securely delete a file with validation
 * 
 * @param string $filePath The path to the file to delete
 * @param string $allowedDirectory The directory where files are allowed to be deleted from
 * @return bool True if file was deleted successfully, false otherwise
 */
function secureDeleteFile($filePath, $allowedDirectory) {
    // Normalize paths
    $realFilePath = realpath($filePath);
    $realAllowedDir = realpath($allowedDirectory) . DIRECTORY_SEPARATOR;
    
    // Validate that the file exists and is within the allowed directory
    if ($realFilePath === false || !file_exists($realFilePath)) {
        return false;
    }
    
    // Ensure the file is within the allowed directory (directory traversal protection)
    if (strpos($realFilePath, $realAllowedDir) !== 0) {
        error_log("Security warning: Attempted to delete file outside allowed directory: " . $filePath);
        return false;
    }
    
    // Validate it's actually a file (not a directory)
    if (!is_file($realFilePath)) {
        error_log("Security warning: Attempted to delete a non-file: " . $filePath);
        return false;
    }
    
    // Check file permissions before deletion
    if (!is_writable($realFilePath)) {
        error_log("Cannot delete file: No write permissions for " . $filePath);
        return false;
    }
    
    // Attempt to delete the file
    if (unlink($realFilePath)) {
        return true;
    } else {
        error_log("Failed to delete file: " . $filePath);
        return false;
    }
}

/**
 * Alternative secure file deletion function with additional checks
 * 
 * @param string $filename The filename (without path) to delete
 * @param string $directory The directory where the file should be located
 * @return bool True if file was deleted successfully, false otherwise
 */
function secureDeleteFileByFilename($filename, $directory) {
    // Validate filename (alphanumeric, underscore, hyphen, dot)
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        error_log("Invalid filename pattern: " . $filename);
        return false;
    }
    
    // Construct full path
    $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
    
    // Use the main secure deletion function
    return secureDeleteFile($fullPath, $directory);
}
?>