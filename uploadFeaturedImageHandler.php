<?php
include 'connect.php';

if (isset($_POST['submit'])) {
    $image = $_FILES['featured_image'];

    // Define upload directory
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Define file path and type
    $fileName = basename($image["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Allowed file types
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        // Move uploaded file to target directory
        if (move_uploaded_file($image["tmp_name"], $targetFilePath)) {
            // Insert a new record into the database with the image path
            $sql = "INSERT INTO featured_images (image_path) VALUES (?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $fileName);
                if ($stmt->execute()) {
                    echo "Image uploaded and database updated successfully.";
                    header("Location: AdminDashBoard.php"); // Redirect to the desired page
                    exit();
                } else {
                    // Error occurred during statement execution
                    echo "Failed to insert record: " . $stmt->error;
                }
                $stmt->close();
            } else {
                // Error occurred during statement preparation
                echo "Failed to prepare insert statement: " . $conn->error;
            }
        } else {
            // Error occurred during file upload
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        // Invalid file type
        echo "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
    }
}

// Close the database connection
$conn->close();
?>
