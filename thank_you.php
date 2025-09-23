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
    <title>Thank You</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #e0f2e9;
        }

        .thank-you-container {
            text-align: center;
            background-color: #fff;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <h1>Thank You!</h1>
        <p>Your message has been sent successfully.</p>
        <a href="<?php echo $isLoggedIn ? 'CusHome.php' : 'index.php'; ?>"> Go To Home</a>
    </div>
</body>
</html>
