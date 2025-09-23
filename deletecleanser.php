<?php
session_start();
include "connect.php";


// Only allow POST requests for deletion
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

if (isset($_POST['Id'])) {
    // Validate and sanitize the ID
    $id = intval($_POST['Id']);
    
    if ($id <= 0) {
        $_SESSION['error_message'] = 'Invalid product ID.';
        header("Location: Clenser.php");
        exit();
    }

    // Use transaction for atomic operations
    mysqli_begin_transaction($conn);
    
    try {
        // Use prepared statement to fetch image path
        $query = "SELECT image FROM clenser WHERE Cl_ID = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $imagePath = 'uploads/' . $row['image'];
            
            // Delete the image file safely
            if (file_exists($imagePath) && is_file($imagePath)) {
                $realImagePath = realpath($imagePath);
                $realUploadsPath = realpath('uploads/');
                
                if ($realImagePath && $realUploadsPath && strpos($realImagePath, $realUploadsPath) === 0) {
                    unlink($imagePath);
                } else {
                    error_log("Security alert: Attempted to delete file outside uploads directory: " . $imagePath);
                }
            }
            
            mysqli_stmt_close($stmt);
            
            // Use prepared statement to delete the record
            $sql = "DELETE FROM clenser WHERE Cl_ID = ?";
            $delete_stmt = mysqli_prepare($conn, $sql);
            
            if (!$delete_stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($delete_stmt, "i", $id);
            
            if (!mysqli_stmt_execute($delete_stmt)) {
                throw new Exception("Delete failed: " . mysqli_stmt_error($delete_stmt));
            }
            
            mysqli_stmt_close($delete_stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Regenerate CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['success_message'] = 'Cleanser deleted successfully';
            
        } else {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = 'Cleanser not found';
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Deletion error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error deleting cleanser: ' . $e->getMessage();
    }
    
} else {
    $_SESSION['error_message'] = 'No product ID provided.';
}

// Redirect back to cleanser management page
header("Location: Clenser.php");
exit();
?>