<?php
session_start();
$isLoggedIn = isset($_SESSION['Usname']);
include 'connect.php';

if (!isset($_SESSION['customerID'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['customerID'];

$sql = "SELECT * FROM cart WHERE user_id = '$userId'";
$result = mysqli_query($conn, $sql);

$cartItems = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cartItems[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body, html {
    height: 100%;
    font-family: Arial, Helvetica, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scrolling */
    
}

* {
    box-sizing: border-box;
}

body {
    background-color: #255269;
}

.reg {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    margin-top: 220px;
    background: rgba(153, 153, 153, 0.4); 
    color: #f1f1f1;
    width: 45%;
    padding: 20px;
    border-radius: 15px;
}

#back {
    background-image: url("Logo/hi.png");
    height: 100vh; 
    background-repeat: no-repeat;
    background-size: cover; 
    position: relative;
}

#top {
    font-family: "Times New Roman", Times, serif;
    color: #84c32f;
    font-size: 24px;
    text-align: center;
    margin-bottom: 20px; /* Add margin for spacing */
}

.cart-container {
    max-width: 800px;
    margin: 40px auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.cart-container h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #2c3e50;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f8f8f8;
}

.btn {
    padding: 5px 10px;
    background-color: #3498db;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #2980b9;
}

.btn.decrease, .btn.increase {
    padding: 5px 15px;
    font-size: 16px;
}

.btn.remove {
    background-color: #e74c3c;
}

.btn.remove:hover {
    background-color: #c0392b;
}

.cart-actions {
    text-align: center;
    margin-top: 20px;
}

.cart-actions .btn {
    background-color: #e67e22;
}

.cart-actions .btn:hover {
    background-color: #d35400;
}

/* Footer Styles */
.footer-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
   
    padding: 20px;
    color: white;
}

.footer-section {
    width: 23%;
    margin-bottom: 20px;
}

.footer-section h3 {
    margin-bottom: 10px;
    color: white;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    text-decoration: none;
    color: white;
}

.footer-section ul li a:hover {
    text-decoration: underline;
}

/* Media Query for Tablets */
/* Media Query for Tablets */
@media (max-width: 768px) {
    .cart-container {
        width: 90%;
        margin: 20px auto;
    }

    th, td {
        padding: 8px;
    }

    .btn {
        padding: 5px 8px;
        font-size: 14px;
    }

    .footer-section {
        width: 48%;
    }

    .btn.decrease, .btn.increase {
        padding: 4px 10px;
        font-size: 14px;
    }
}

/* Media Query for Mobile Devices */
@media (max-width: 480px) {
    .cart-container {
        width: 100%;
        padding: 15px;
    }

    table, th, td {
        display: block;
        width: 100%;
    }

    th, td {
        padding: 6px;
        font-size: 12px;
    }

    td {
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    tr {
        margin-bottom: 10px;
    }

    td:first-child {
        font-weight: bold;
    }

    .cart-container table thead {
        display: none;
    }

    .cart-container table tbody tr {
        display: flex;
        flex-direction: column;
        margin-bottom: 15px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
    }

    .cart-container table tbody tr td {
        padding-left: 0;
    }

    .btn {
        padding: 4px 6px;
        font-size: 12px;
    }

    .btn.decrease, .btn.increase {
        padding: 3px 8px;
        font-size: 12px;
    }

    .footer-section {
        width: 100%;
    }
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
        </div>
    </header>

    <div class="cart-container">
        <h1>Shopping Cart</h1>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <tr data-id="<?php echo $item['product_id']; ?>">
                        <td><img src="uploads/<?php echo $item['image']; ?>" alt="<?php echo $item['p_name']; ?>" width="50"></td>
                        <td><?php echo $item['p_name']; ?></td>
                        <td><?php echo $item['price']; ?></td>
                        <td><?php echo $item['size']; ?></td>
                        <td><button class="btn decrease">-</button> <span class="quantity"><?php echo $item['quantity'] ?? 1; ?></span> <button class="btn increase">+</button></td>
                        <td class="total"><?php echo $item['price'] * ($item['quantity'] ?? 1); ?></td>
                        <td><button class="btn remove">Remove</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cart-actions">
            <button class="btn clear-cart">Clear Cart</button>
            <button class="btn buy-cart">Buy</button>
        </div>
    </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cartItems = document.getElementById('cart-items');

            cartItems.addEventListener('click', (e) => {
                if (e.target.classList.contains('increase')) {
                    updateQuantity(e.target, 'increase');
                } else if (e.target.classList.contains('decrease')) {
                    updateQuantity(e.target, 'decrease');
                } else if (e.target.classList.contains('remove')) {
                    removeItem(e.target);
                }
            });

            document.querySelector('.clear-cart').addEventListener('click', () => {
                if (confirm('Are you sure you want to clear the cart?')) {
                    clearCart();
                }
            });

            document.querySelector('.buy-cart').addEventListener('click', () => {
                if (confirm('Are you sure you want to proceed with the purchase?')) {
                    buyCart();
                }
            });

            function updateQuantity(button, action) {
                const row = button.closest('tr');
                const productId = row.getAttribute('data-id');
                const quantityElement = row.querySelector('.quantity');
                let quantity = parseInt(quantityElement.textContent);

                if (action === 'increase') {
                    quantity++;
                } else if (action === 'decrease' && quantity > 1) {
                    quantity--;
                }

                fetch('updateQuantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId: '<?php echo $userId; ?>', productId, quantity }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        quantityElement.textContent = quantity;
                        row.querySelector('.total').textContent = data.total;
                    } else {
                        alert('Error updating quantity.');
                    }
                });
            }

            function removeItem(button) {
                const row = button.closest('tr');
                const productId = row.getAttribute('data-id');

                fetch('removeItem.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId: '<?php echo $userId; ?>', productId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                    } else {
                        alert('Error removing item.');
                    }
                });
            }

            function clearCart() {
                fetch('clearCart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId: '<?php echo $userId; ?>' }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartItems.innerHTML = '';
                    } else {
                        alert('Error clearing cart.');
                    }
                });
            }

            function buyCart() {
                fetch('buyCart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId: '<?php echo $userId; ?>' }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Purchase successful!');
                        cartItems.innerHTML = '';
                    } else {
                        alert('Error processing purchase.');
                    }
                });
            }
        });
    </script>
</body>
</html>
