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
    <title>Beauty Skin Care</title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="about.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
                <li><a href="#">About</a></li>
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

<section class="about-section">
    <div class="container">
        <h2>About Us</h2>
        <div class="about-content">
            <div class="about-text">
                <h3>Our Philosophy</h3>
                <p>Sulos Owshadham is dedicated to embracing the power of nature. Our brand offers premium herbal cosmetics, with products crafted to suit various skin types and beauty needs. We believe in enhancing your natural beauty through sustainable, eco-friendly practices.</p>
                <p>We pride ourselves on using the finest herbal ingredients to ensure the health and vitality of your skin and hair. Our commitment to sustainability ensures that every product not only makes you feel beautiful but also respects the planet.</p>
                <p>Join us in discovering the magic of herbal beauty and sustainable care.</p>
                <a href="#" class="read-more">Read more &gt;&gt;</a>
            </div>
            <div class="about-image">
                <img src="uploads/bg4.jpg" alt="Sulos Owshadham herbal beauty products">
            </div>
        </div>
    </div>
</section>

<section class="services-section">
    <div class="container">
        <h2>Our Services</h2>
        <div class="services-content">
            <div class="service">
                <span class="service-icon">
                    <i class="fas fa-leaf"></i> <!-- Skincare Icon -->
                </span>
                <p>We offer a variety of herbal skincare products that rejuvenate and refresh your skin, helping you achieve a natural glow.</p>
            </div>
            <div class="service">
                <span class="service-icon">
                    <i class="fas fa-tint"></i> <!-- Haircare Icon -->
                </span>
                <p>Our hair care solutions, including our 100% natural Rabbit Oil, promote healthy hair growth and prevent hair loss.</p>
            </div>
            <div class="service">
                <span class="service-icon">
                    <i class="fas fa-spa"></i> <!-- Herbal Treatments Icon -->
                </span>
                <p>Experience the healing power of nature with our range of herbal treatments designed to enhance your beauty.</p>
            </div>
            <div class="service">
                <span class="service-icon">
                    <i class="fas fa-heart"></i> <!-- Wellness Icon -->
                </span>
                <p>We believe in holistic wellness. Our products are designed not only to enhance beauty but also to nourish and promote overall well-being.</p>
            </div>
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
