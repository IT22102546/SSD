<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Featured Image</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
        }

        .upload-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #555;
        }

        input[type="file"] {
            display: block;
            margin: 10px auto 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        @media (max-width: 480px) {
            .upload-container {
                padding: 20px;
            }

            input[type="file"],
            input[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h1>Upload Featured Image</h1>
        <form action="uploadFeaturedImageHandler.php" method="post" enctype="multipart/form-data">
            <label for="featured_image">Select image to upload:</label>
            <input type="file" name="featured_image" id="featured_image" required>
            <input type="submit" value="Upload Image" name="submit">
        </form>
    </div>
</body>
</html>
