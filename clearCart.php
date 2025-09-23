<?php
session_start();
include 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'];

$sql = "DELETE FROM cart WHERE user_id = '$userId'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

mysqli_close($conn);
?>
