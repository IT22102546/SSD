<?php
session_start();
include "connect.php";

// Only allow POST requests
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

// Check if product ID is provided
if (!isset($_POST['Id']) || !is_numeric($_POST['Id'])) {
    $_SESSION['error_message'] = 'Invalid product ID.';
    header("Location: Clenser.php");
    exit();
}

$id = intval($_POST['Id']);
if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID.';
    header("Location: Clenser.php");
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Get image filename
    $query = "SELECT image FROM clenser WHERE Cl_ID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $imagePath = __DIR__ . '/uploads/' . basename($row['image']); 
        // use basename() to prevent directory traversal

        // Delete image safely
        if (is_file($imagePath) && file_exists($imagePath)) {
            if (!unlink($imagePath)) {
                error_log("Warning: Could not delete file: " . $imagePath);
                // still continue deleting DB row
            }
        }
        mysqli_stmt_close($stmt);

        // Delete DB record
        $deleteQuery = "DELETE FROM clenser WHERE Cl_ID = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "i", $id);
        if (!mysqli_stmt_execute($deleteStmt)) {
            throw new Exception("Delete failed: " . mysqli_stmt_error($deleteStmt));
        }
        mysqli_stmt_close($deleteStmt);

        // Commit
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
    $_SESSION['error_message'] = 'Error deleting cleanser.';
}

header("Location: Clenser.php");
exit();
