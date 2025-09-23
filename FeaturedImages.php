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
        
        // Delete the image file from the server
        $file_path = 'uploads/' . $image_path;
        if (file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        }
        
        // Delete the image record using prepared statement
        $sql_delete_image = "DELETE FROM featured_images WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete_image);
        
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $image_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);
        }
        
        mysqli_stmt_close($stmt);
        
        // Regenerate CSRF token after successful action
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        header("Location: FeaturedImages.php");
        exit();
    } else {
        echo "Image not found!";
        mysqli_stmt_close($stmt);
    }
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all featured images
$sql = "SELECT * FROM featured_images";
$result = mysqli_query($conn, $sql);
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
        /* Your existing CSS styles */
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
    </style>
</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">
            <img src="Logo/logo.png">
        </div>
        <nav>
            <ul>
                <li><a href="<?php echo $isLoggedIn ? 'CusHome.php' : 'index.php'; ?>">Home</a></li>
                <li><a href="Product.php">Products</a></li>
                <li><a href="About.php">About</a></li>
                <li><a href="contactus.php">Contact</a></li>
            </ul>
        </nav>
        <div class="header-buttons">
            <?php if ($isLoggedIn): ?>
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?></span>
                <a href="cart.php" class="btn">cart</a>
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
            
            <div class="images-grid">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="image-card">';
                        echo '<img src="uploads/' . htmlspecialchars($row['image_path']) . '" alt="Featured Image">';
                        echo '<form method="POST" action="FeaturedImages.php" class="delete-form">';
                        echo '<input type="hidden" name="image_id" value="' . htmlspecialchars($row['id']) . '">';
                        echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
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