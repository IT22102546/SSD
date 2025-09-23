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
    <link rel="stylesheet" href="styles.css">
    <title>Contact Us - Beauty Skin Care</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background-color: #255269;
        }
.welcome-message {
    font-size: 18px;
    font-weight: bold;
    color: #fff; /* White color */
    margin-right: 15px;
    padding: 5px 10px;
    background-color: #240975; /* Dark background to stand out */
    border-radius: 5px;
}

/* Media Queries for Responsive Design */
@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        align-items: center;
        padding: 10px;
    }

    .logo {
        margin-bottom: 10px;
    }

    nav ul {
        justify-content: center;
    }

    .header-buttons {
        justify-content: center;
    }

    .logo img {
        height: 60px; /* Adjust logo size for smaller screens */
    }

    nav ul li a {
        font-size: 14px;
    }

    .header-buttons .btn {
        font-size: 12px;
        padding: 8px 12px;
    }
}

        .contact-banner {
            background: #e0f2e9;
            padding: 50px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .banner-content {
            width: 50%;
        }

        .banner-content h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .banner-content p {
            font-size: 16px;
            color: #555;
        }

        .banner-image {
            width: 50%;
            display: flex;
            justify-content: flex-end;
        }

        .banner-image img {
            max-width: 100%;
            height: auto;
        }

        #back {
    background-color: #255269;
    min-height: 100vh;
    background-repeat: no-repeat;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px; 
}

        .form-container {
            background: rgba(255, 255, 255, 0.9); 
    color: #333;
    width: 90%;
    max-width: 700px; 
    padding: 40px; 
    border-radius: 12px; 
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); 
    margin: 40px 0;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
        }

       /* Contact form header */
.contact-form h3 {
    font-family: "Times New Roman", Times, serif;
    color: #84c32f;
    font-size: 32px; 
    margin-bottom: 30px; 
    text-align: center;
}


        .lbl {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .input-field {
            height: 50px;
            width: 100%;
            margin: 10px 0;
            padding: 0 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        textarea.input-field {
            height: 150px;
            padding-top: 15px;
        }

        #btn {
    background-color: #84c32f;
    color: white;
    padding: 18px; 
    border: none;
    cursor: pointer;
    border-radius: 10px; 
    font-size: 20px;
    font-weight: bold;
    transition: background-color 0.3s;
    width: 100%;
}
#btn:hover {
    background-color: #6ab83f;
}

        .contact-details {
            background: #e0f2e9;
            padding: 50px 0;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .contact-item {
            margin-bottom: 20px;
        }

        .contact-item h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
        }

        .contact-item p {
            font-size: 16px;
            color: #555;
        }

        footer {
    background-color: #002147;
    color: white;
    padding: 40px 0;
    text-align: left;
}

.footer-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-section {
    flex: 1;
    margin: 10px;
    min-width: 200px;
}

.logo-section {
    flex: 0 1 150px;
    text-align: center;
}

.logo-section img {
    max-width: 100%;
    height: auto;
}

.footer-section h3 {
    font-size: 1.2em;
    margin-bottom: 15px;
    color: white;
}

.footer-section p,
.footer-section ul,
.footer-section ul li {
    margin: 0;
    padding: 0;
    list-style: none;
    color: white;
}

.footer-section ul {
    padding: 0;
    margin-top: 10px;
}

.footer-section ul li {
    margin-bottom: 8px;
}

.footer-section ul li a {
    text-decoration: none;
    color: white;
    font-size: 0.9em;
}

.footer-section ul li a:hover {
    color: #84c32f;
}

.footer-section p {
    font-size: 0.9em;
    margin-bottom: 10px;
}

.subscribe-section {
    max-width: 250px;
}

.subscribe-section input[type="email"] {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    margin-bottom: 10px;
}

.subscribe-section button {
    width: 100%;
    padding: 10px;
    background-color: #84c32f;
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 0.9em;
    cursor: pointer;
}

.subscribe-section button:hover {
    background-color: #6ba829;
}

.social-icons {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.social-icons a {
    color: white;
    font-size: 1.5em;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
 
}

.social-icons a:hover {
    background-color: #fff;
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    margin-top: 20px;
    font-size: 0.9em;
}

    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="Logo/logo.png" alt="Logo">
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
                    <a href="cart.php" class="btn">Cart</a>
                    <a href="logout.php" class="btn">Log Out</a>
                <?php else: ?>
                    <a href="SignIn.php" class="btn">Log In</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div id="back">
        <div class="form-container">
            <form id="contact-form" action="submit_contact.php" method="post" class="contact-form">
                <h3>Contact Us</h3>
                
                <label for="name" class="lbl"><b>Name</b></label>
                <input type="text" id="name" name="name" class="input-field" placeholder="Enter Your Name" required>

                <label for="email" class="lbl"><b>Email</b></label>
                <input type="email" id="email" name="email" class="input-field" placeholder="Enter Your Email" required>

                <label for="subject" class="lbl"><b>Subject</b></label>
                <input type="text" id="subject" name="subject" class="input-field" placeholder="Enter Subject" required>

                <label for="message" class="lbl"><b>Message</b></label>
                <textarea id="message" name="message" class="input-field" placeholder="Enter Your Message" rows="5" required></textarea>

                <input type="submit" id="btn" value="Send Message">
            </form>
        </div>
    </div>

    <footer>
        <div class="container footer-container">
            <div class="footer-section logo-section">
                <img src="Logo/logo.png" alt="Footer Logo">
            </div>
            <div class="footer-section">
                <h3>About Us</h3>
                <p>We are committed to providing high-quality herbal beauty products.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="Product.php">Products</a></li>
                    <li><a href="About.php">About</a></li>
                    <li><a href="contactus.php">Contact us</a></li>
                </ul>
            </div>
            <div class="footer-section subscribe-section">
                <h3>Subscribe</h3>
                <input type="email" placeholder="Enter your email">
                <button>Subscribe</button>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Your Company. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
