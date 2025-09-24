<?php
include "connect.php";
session_start();

$isLoggedIn = isset($_SESSION['Usname']);
$username = $isLoggedIn ? $_SESSION['Usname'] : '';

// Check if the user is logged in
if (!isset($_SESSION['Usname'])) {
    header("Location: Signin.php");
    exit();
}

// Handle image deletion
if (isset($_POST['delete_image'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed. Please go back and try again.");
    }
    
    $image_id = intval($_POST['image_id']);
    
    if ($image_id <= 0) {
        die("Invalid image ID");
    }
    
    // Fetch the image path using prepared statement
    $sql_fetch_image = "SELECT image_path FROM featured_images WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql_fetch_image);
    
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        die("An error occurred. Please try again later.");
    }
    
    mysqli_stmt_bind_param($stmt, "i", $image_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        die("An error occurred. Please try again later.");
    }
    
    $result_fetch_image = mysqli_stmt_get_result($stmt);
    
    if ($result_fetch_image && mysqli_num_rows($result_fetch_image) > 0) {
        $row = mysqli_fetch_assoc($result_fetch_image);
        $image_path = $row['image_path'];
        
        // SECURE FILE DELETION: Delete the image file from the server with validation
        $file_path = 'uploads/' . $image_path;
        if (secureDeleteFile($file_path, 'uploads')) {
            error_log("Successfully deleted image: " . $file_path);
        } else {
            error_log("Failed to delete image: " . $file_path);
            // Continue with database deletion even if file deletion fails
        }
        
        // Delete the image record using prepared statement
        $sql_delete_image = "DELETE FROM featured_images WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete_image);
        
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $image_id);
            if (mysqli_stmt_execute($stmt_delete)) {
                // Regenerate CSRF token after successful action
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                mysqli_stmt_close($stmt_delete);
                mysqli_stmt_close($stmt);
                
                header("Location: FeaturedImages.php");
                exit();
            } else {
                error_log("Database deletion failed: " . mysqli_stmt_error($stmt_delete));
                mysqli_stmt_close($stmt_delete);
            }
        }
        
        mysqli_stmt_close($stmt);
        die("An error occurred while deleting the image record.");
    } else {
        echo "Image not found!";
        mysqli_stmt_close($stmt);
    }
}

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
    $realAllowedDir = realpath($allowedDirectory);
    
    if ($realAllowedDir === false) {
        error_log("Allowed directory does not exist: " . $allowedDirectory);
        return false;
    }
    
    $realAllowedDir = $realAllowedDir . DIRECTORY_SEPARATOR;
    
    // Validate that the file exists
    if ($realFilePath === false || !file_exists($realFilePath)) {
        error_log("File does not exist: " . $filePath);
        return false;
    }
    
    // Ensure the file is within the allowed directory (directory traversal protection)
    if (strpos($realFilePath, $realAllowedDir) !== 0) {
        error_log("Security warning: Attempted to delete file outside allowed directory. File: " . $realFilePath . ", Allowed: " . $realAllowedDir);
        return false;
    }
    
    // Validate it's actually a file (not a directory)
    if (!is_file($realFilePath)) {
        error_log("Security warning: Attempted to delete a non-file: " . $realFilePath);
        return false;
    }
    
    // Additional security: validate filename pattern
    $filename = basename($realFilePath);
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        error_log("Security warning: Invalid filename pattern: " . $filename);
        return false;
    }
    
    // Check file permissions before deletion
    if (!is_writable($realFilePath)) {
        error_log("Cannot delete file: No write permissions for " . $realFilePath);
        return false;
    }
    
    // Attempt to delete the file
    if (unlink($realFilePath)) {
        return true;
    } else {
        error_log("Failed to delete file: " . $realFilePath);
        return false;
    }
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all featured images
$sql = "SELECT * FROM featured_images";
$result = mysqli_query($conn, $sql);

// Sanitize home URL for HTML output
$homeUrl = $isLoggedIn ? 'CusHome.php' : 'index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Featured Images</title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <style>
        .featured-images-section .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .image-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .image-card img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .delete-form {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .btn-delete {
            background-color: rgba(255, 0, 0, 0.8);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn-delete:hover {
            background-color: rgba(255, 0, 0, 1);
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">
            <img src="Logo/logo.png" alt="Sulos Owshadham Logo">
        </div>
        <nav>
            <ul>
                <li><a href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>">Home</a></li>
                <li><a href="Product.php">Products</a></li>
                <li><a href="About.php">About</a></li>
                <li><a href="contactus.php">Contact</a></li>
            </ul>
        </nav>
        <div class="header-buttons">
            <?php if ($isLoggedIn): ?>
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="cart.php" class="btn">Cart</a>
                <a href="logout.php" class="btn">Log Out</a>
            <?php else: ?>
                <a href="Signin.php" class="btn">Log In</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>
    <section class="featured-images-section">
        <div class="container">
            <h2>Manage Featured Images</h2>
            
            <!-- Display success/error messages if needed -->
            <?php
            if (isset($_GET['success']) && $_GET['success'] == '1') {
                echo '<div class="success-message">Image deleted successfully!</div>';
            }
            if (isset($_GET['error']) && $_GET['error'] == '1') {
                echo '<div class="error-message">Error deleting image. Please try again.</div>';
            }
            ?>
            
            <div class="images-grid">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="image-card">';
                        echo '<img src="uploads/' . htmlspecialchars($row['image_path'], ENT_QUOTES, 'UTF-8') . '" alt="Featured Image">';
                        echo '<form method="POST" action="FeaturedImages.php" class="delete-form">';
                        echo '<input type="hidden" name="image_id" value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<button type="submit" name="delete_image" class="btn-delete" onclick="return confirm(\'Are you sure you want to delete this image?\')">Delete</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No featured images found.</p>';
                }
                ?>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="footer-container">
        <div class="footer-section logo-section">
            <img src="Logo/logo.png" alt="Sulos Owshadham Herbal Health Care Logo">
        </div>
        <div class="footer-section">
            <h3>About us</h3>
            <ul>
                <li><a href="Product.php">Products</a></li>
                <li><a href="contactus.php">Contact us</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Support</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Terms of use</h3>
            <ul>
                <li><a href="#">Privacy policy</a></li>
                <li><a href="#">Customer service</a></li>
                <li><a href="#">Help</a></li>
                <li><a href="#">Support</a></li>
            </ul>
        </div>
        <div class="footer-section subscribe-section">
            <h3>Subscribe</h3>
            <p>Join our mailing to receive updates and offers</p>
            <input type="email" placeholder="Enter your email">
            <button>Subscribe</button>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2024 Sulos Owshadham Herbal Health Care. All rights reserved.</p>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</footer>

</body>
</html>