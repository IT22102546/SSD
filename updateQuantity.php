<?php
session_start();
include 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'];
$productId = $data['productId'];
$quantity = $data['quantity'];

$sql = "UPDATE cart SET quantity = $quantity WHERE user_id = '$userId' AND product_id = '$productId'";
if (mysqli_query($conn, $sql)) {
    $sql = "SELECT price FROM cart WHERE user_id = '$userId' AND product_id = '$productId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $total = $row['price'] * $quantity;
    echo json_encode(['success' => true, 'total' => $total]);
} else {
    echo json_encode(['success' => false]);
}

mysqli_close($conn);
?>
