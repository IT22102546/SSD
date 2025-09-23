<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'connect.php';

    $userId = $_POST['user_id'];
    $productId = $_POST['product_id'];
    $productname = $_POST['p_name'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $image = $_POST['image'];

 
    $userId = mysqli_real_escape_string($conn, $userId);
    $productId = mysqli_real_escape_string($conn, $productId);
    $productname = mysqli_real_escape_string($conn, $productname);
    $price = mysqli_real_escape_string($conn, $price);
    $size = mysqli_real_escape_string($conn, $size);
    $image = mysqli_real_escape_string($conn, $image);

    $sql = "SELECT * FROM cart WHERE user_id = '$userId' AND product_id = '$productId'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo 'Product is already in the cart.';
    } else {
        $sql = "INSERT INTO cart (user_id, product_id, p_name, price, size, image) VALUES ('$userId', '$productId', '$productname', '$price', '$size', '$image')";
        if (mysqli_query($conn, $sql)) {
            echo 'Product added to cart successfully.';
            header("location:cart.php");

        } else {
            echo 'Error: ' . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
} else {
    echo 'Invalid request method.';
    
}
?>
