<?php
session_start();
$isLoggedIn = isset($_SESSION['Usname']);
$username = $isLoggedIn ? $_SESSION['Usname'] : '';
$userId = $isLoggedIn ? $_SESSION['customerID'] : null; 

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$productId = null;
$product = null;
$error = '';

include 'connect.php';

// Validate and sanitize product ID
if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    
    if ($productId <= 0) {
        $error = "Invalid product ID.";
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT * FROM clenser WHERE Cl_ID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $productId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $product = mysqli_fetch_assoc($result);
            } else {
                $error = "Product not found.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error. Please try again later.";
            error_log("Prepare failed: " . mysqli_error($conn));
        }
    }
} else {
    $error = "No product ID provided.";
}

// Sanitize redirect URLs
$homeUrl = $isLoggedIn ? 'CusHome.php' : 'index.php';
$redirectParam = urlencode("ProductDetail.php?id=" . $productId);
$buyNowRedirectParam = urlencode("buyNow.php?id=" . $productId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Detail - <?php echo isset($product['Clenser_name']) ? htmlspecialchars($product['Clenser_name'], ENT_QUOTES, 'UTF-8') : 'Product'; ?></title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <style>
        .product-container {
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
            margin: 30px ;
            background-color: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            justify-content: center;
        }

        .product-image {
            flex: 1;
            text-align: center;
            min-width: 300px;
        }

        .product-image img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .product-details {
            flex: 2;
            padding: 20px;
            min-width: 300px;
        }

        .product-details h1 {
            margin-top: 0;
            font-size: 2em;
            color: #343a40;
        }

        .product-details p {
            font-size: 1.1em;
            margin: 10px 0;
            line-height: 1.6;
        }

        .price {
            font-size: 1.5em;
            color: #84c32f;
            font-weight: bold;
        }

        .button-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            background-color: #84c32f;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #6dbd1f;
            transform: translateY(-2px);
        }

        .btn-login {
            background-color: #007bff;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .btn-buy {
            background-color: #ff6b35;
        }

        .btn-buy:hover {
            background-color: #e55a2b;
        }

        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 20px;
            border-radius: 8px;
            margin: 30px;
            text-align: center;
            font-size: 1.1em;
        }

        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .welcome-message {
            margin-right: 15px;
            font-weight: bold;
            color: #343a40;
        }

        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
                align-items: center;
                margin: 15px;
                padding: 15px;
            }

            .product-image,
            .product-details {
                flex: none;
                width: 100%;
                padding: 10px;
            }

            .product-image img {
                max-height: 300px;
            }

            .button-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }

        .product-description {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #84c32f;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="Logo/logo.png" alt="Sulos Owshadham Herbal Health Care Logo">
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

    <!-- Display success message if redirected from add to cart -->
    <?php if (isset($_GET['added']) && $_GET['added'] == 'true'): ?>
        <div class="success-message" style="margin: 20px auto; max-width: 1200px;">
            Product added to cart successfully!
        </div>
    <?php endif; ?>

    <div class="container product-container">
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <p><a href="Product.php" class="btn" style="margin-top: 15px;">Back to Products</a></p>
            </div>
        <?php elseif ($product): ?>
            <div class="product-image">
                <img src="uploads/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="<?php echo htmlspecialchars($product['Clenser_name'], ENT_QUOTES, 'UTF-8'); ?>"
                     onerror="this.src='Logo/logo.png'">
            </div>
            <div class="product-details">
                <h1><?php echo htmlspecialchars($product['Clenser_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                
                <p class="price">Rs. <?php echo number_format($product['Clenser_price'], 2); ?></p>
                
                <p><strong>Size:</strong> <?php echo htmlspecialchars($product['Clenser_size'], ENT_QUOTES, 'UTF-8'); ?></p>
                
                <?php if (!empty($product['description'])): ?>
                    <div class="product-description">
                        <p><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endif; ?>

                <div class="button-container">
                    <?php if ($isLoggedIn): ?>
                        <form action="addToCart.php" method="POST" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['Cl_ID'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="p_name" value="<?php echo htmlspecialchars($product['Clenser_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="price" value="<?php echo htmlspecialchars($product['Clenser_price'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="size" value="<?php echo htmlspecialchars($product['Clenser_size'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="image" value="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn" onclick="return confirm('Add this product to your cart?')">
                                Add to Cart
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="Signin.php?redirect=<?php echo htmlspecialchars($redirectParam, ENT_QUOTES, 'UTF-8'); ?>" 
                           class="btn btn-login">
                            Log in to Add to Cart
                        </a>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <a href="buyNow.php?id=<?php echo htmlspecialchars($product['Cl_ID'], ENT_QUOTES, 'UTF-8'); ?>" 
                           class="btn btn-buy"
                           onclick="return confirm('Proceed to checkout with this product?')">
                            Buy Now
                        </a>
                    <?php else: ?>
                        <a href="Signin.php?redirect=<?php echo htmlspecialchars($buyNowRedirectParam, ENT_QUOTES, 'UTF-8'); ?>" 
                           class="btn btn-buy">
                            Login to Buy Now
                        </a>
                    <?php endif; ?>

                    <a href="Product.php" class="btn" style="background-color: #6c757d;">
                        Back to Products
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Sulos Owshadham Herbal Health Care is your trusted source for premium herbal products and treatments.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>">Home</a></li>
                    <li><a href="Product.php">Products</a></li>
                    <li><a href="About.php">About</a></li>
                    <li><a href="contactus.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">LinkedIn</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@sulosowshadham.com</p>
                <p>Phone: +94 76 5644323</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Sulos Owshadham Herbal Health Care. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Add image zoom effect on hover
            const productImage = document.querySelector('.product-image img');
            if (productImage) {
                productImage.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.transition = 'transform 0.3s ease';
                });

                productImage.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        });
    </script>
</body>
</html>
<?php
// Close connection at the end
if (isset($conn)) {
    mysqli_close($conn);
}
?>