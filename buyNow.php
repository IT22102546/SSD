<?php
session_start();
$isLoggedIn = isset($_SESSION['Usname']);
$username = $isLoggedIn ? $_SESSION['Usname'] : '';
$userId = $isLoggedIn ? $_SESSION['customerID'] : null;
$productId = isset($_GET['id']) ? $_GET['id'] : '';

// Include database connection
include 'connect.php';

// Initialize product name
$productName = '';

if ($productId) {
    // Fetch product name from database
    $sql = "SELECT Clenser_name FROM clenser WHERE Cl_ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $productName);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>Buy Now</title>
    
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        header {
            background: #333;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
        }

        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-bottom: 1.5rem;
            font-size: 24px;
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"] {
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="text"]:focus {
            border-color: #007BFF;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        }

        button {
            padding: 0.8rem;
            background: #007BFF;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
    </style>
    <link rel="stylesheet" href="styles.css">
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
        <h1>Purchase Form</h1>
        <form id="purchase-form">
            <label for="productname">Product Name:</label>
            <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($productName); ?>">
            
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>
            
            <label for="mobile">Mobile Number:</label>
            <input type="text" id="mobile" name="mobile" required><br><br>
            
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required><br><br>

            
            
            <button type="submit">Submit</button>
        </form>

        <script src="https://cdn.emailjs.com/dist/email.min.js"></script>
        <script>
            (function() {
                emailjs.init('0dC8U10tkq7mtNRcK');
            })();

            document.getElementById('purchase-form').addEventListener('submit', function(event) {
    event.preventDefault();

    emailjs.sendForm('service_x71pvhm', 'template_b0qx4am', this)
        .then(function(response) {
            // Redirect to thank_you.php on success
            window.location.href = 'thank_you.php';
        }, function(error) {
            alert('Failed to send message: ' + error.text);
        });
});
        </script>
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
    <a href="https://www.facebook.com/tamilocean1" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-facebook"></i>
    </a>
    <a href="https://www.instagram.com/sulosowshadham?igsh=YzljYTk1ODg3Zg==" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-instagram"></i>
    </a>
    <a href="https://wa.me/message/WVFLY3HNQOMCB1" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-whatsapp"></i>
    </a>
</div>
    </div>
</footer>
</body>
</html>
