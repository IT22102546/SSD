<?php
session_start();
include 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'];
$productId = $data['productId'];

$sql = "DELETE FROM cart WHERE user_id = '$userId' AND product_id = '$productId'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

mysqli_close($conn);
?>
