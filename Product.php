<?php
session_start();
$isLoggedIn = isset($_SESSION['Usname']);
$username = $isLoggedIn ? $_SESSION['Usname'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleansers - Beauty Skin Care</title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .cleansers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        

        .cleanser-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: 100%; 
            display: flex;
            flex-direction: column;
            justify-content: space-between; 
            min-height: 300px; 
            overflow: hidden; 
        }

        .cleanser-card img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
            display: block; 
        }

        .btn-buy-now {
            display: inline-block;
            padding: 8px 16px;
            background-color: #84c32f; 
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        

        @media (max-width: 768px) {
            .cleansers-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                padding: 0 25px;
            }
            
        }

        @media (max-width: 576px) {
            .cleansers-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }
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

    <section class="cleansers-page">
        <div class="container">
            <h2 class="section-title">Shop Products</h2>
            <div class="cleansers-grid">
                <?php
                include 'connect.php';

                $sql = "SELECT * FROM clenser";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="cleanser-card">';
                        echo '<img src="uploads/' . $row['image'] . '" alt="' . $row['Clenser_name'] . '">';
                        echo '<h3>' . $row['Clenser_name'] . '</h3>';
                        echo '<p><strong>Price:</strong> Rs. ' . $row['Clenser_price'] . '</p>';
                        echo '<p><strong>Size:</strong> ' . $row['Clenser_size'] . '</p>';
                        echo '<a href="productDetail.php?id=' . $row['Cl_ID'] . '" class="btn-buy-now">Buy Now</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No cleansers found.</p>';
                }

                mysqli_close($conn);
                ?>
            </div>
        </div>
    </section>

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
