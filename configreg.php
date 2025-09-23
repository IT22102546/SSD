<?php
include 'connect.php';


if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $sql = "SELECT * FROM customer WHERE U_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "exists";
    } else {
        echo "unique";
    }

    $stmt->close();
    $conn->close();
    exit();
}

if (isset($_POST['sign'])) {
    $unam = isset($_POST['Uname']) ? $_POST['Uname'] : "";
    $number = isset($_POST['Mnumber']) ? $_POST['Mnumber'] : "0";
    $mail = isset($_POST['myEmail']) ? $_POST['myEmail'] : "";
    $passw = isset($_POST['pwrd']) ? $_POST['pwrd'] : "";

    $sql = "INSERT INTO customer (U_name, mnumber, email, password) VALUES ( ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss",$unam, $number, $mail, $passw);

    if ($stmt->execute()) {
        header("Location: signin.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
