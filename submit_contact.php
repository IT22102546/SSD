<?php
include 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : "";
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : "";
    $subject = isset($_POST['subject']) ? mysqli_real_escape_string($conn, $_POST['subject']) : "";
    $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : "";

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $sql = "INSERT INTO contactUs (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: thank_you.php"); 
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "All fields are required.";
    }
} else {
    echo "Invalid request.";
}
?>
