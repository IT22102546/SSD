<?php
session_start();
include 'connect.php';


// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$clenser_id = "";
$clenser_name = "";
$clenser_price = "";
$clenser_size = "";
$image = "";
$error = "";

// Validate and sanitize the ID
if (isset($_GET['Id'])) {
    $id = intval($_GET['Id']);
    
    if ($id <= 0) {
        $error = "Invalid product ID.";
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT * FROM clenser WHERE Cl_ID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $clenser_id = $row['Cl_ID'];
                $clenser_name = $row['Clenser_name'];
                $clenser_price = $row['Clenser_price'];
                $clenser_size = $row['Clenser_size'];
                $image = $row['image'];
            } else {
                $error = "Cleanser not found.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error. Please try again later.";
        }
    }
} else {
    $error = "No product ID provided.";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" type="text/css" href="adminfood.css"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Cleanser Update</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            border-bottom: 2px solid #84c32f;
            padding-bottom: 10px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus {
            border-color: #84c32f;
            outline: none;
            box-shadow: 0 0 5px rgba(132, 195, 47, 0.3);
        }

        .form-group input[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .current-image {
            margin-top: 10px;
            text-align: center;
        }

        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }

        .image-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .form-group button {
            width: 100%;
            padding: 15px;
            border: none;
            background-color: #84c32f;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #6dbd1f;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #84c32f;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            .form-group input[type="text"],
            .form-group input[type="number"] {
                padding: 10px;
            }

            .form-group button {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Update Cleanser</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
                <div style="margin-top: 10px;">
                    <a href="Clenser.php" style="color: #721c24; text-decoration: underline;">Back to Cleanser List</a>
                </div>
            </div>
        <?php else: ?>
            <form action="updatecleanser.php" method="post" class="reg" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="clenser_id">Cleanser ID</label>
                    <input type="text" name="clenser_id" id="clenser_id" value="<?php echo htmlspecialchars($clenser_id); ?>" readonly="readonly"/>
                </div>
                
                <div class="form-group">
                    <label for="clenser_name">Cleanser Name *</label>
                    <input type="text" name="clenser_name" id="clenser_name" value="<?php echo htmlspecialchars($clenser_name); ?>" required 
                           maxlength="100" pattern="[A-Za-z0-9\s\-]+" title="Only letters, numbers, spaces, and hyphens are allowed"/>
                </div>
                
                <div class="form-group">
                    <label for="clenser_price">Cleanser Price (Rs.) *</label>
                    <input type="number" name="clenser_price" id="clenser_price" value="<?php echo htmlspecialchars($clenser_price); ?>" 
                           required min="0" step="0.01" max="100000"/>
                </div>
                
                <div class="form-group">
                    <label for="clenser_size">Cleanser Size *</label>
                    <input type="text" name="clenser_size" id="clenser_size" value="<?php echo htmlspecialchars($clenser_size); ?>" 
                           required maxlength="50" pattern="[A-Za-z0-9\smlML]+" title="Enter size (e.g., 100ml, 200g)"/>
                </div>
                
                <div class="form-group">
                    <label for="image">Update Image</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp"/>
                    <div class="image-note">Allowed formats: JPEG, PNG, GIF, WebP. Max size: 2MB</div>
                    
                    <?php if ($image): ?>
                        <div class="current-image">
                            <p><strong>Current Image:</strong></p>
                            <img src="uploads/<?php echo htmlspecialchars($image); ?>" 
                                 alt="Current Cleanser Image"
                                 onerror="this.src='Logo/logo.png'">
                            <p class="image-note"><?php echo htmlspecialchars($image); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="submit" onclick="return validateForm()">Update Cleanser</button>
                </div>
            </form>
            
            <div class="back-link">
                <a href="Clenser.php">← Back to Cleanser List</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function validateForm() {
            const price = document.getElementById('clenser_price').value;
            const fileInput = document.getElementById('image');
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            
            // Validate price
            if (price <= 0) {
                alert('Price must be greater than 0.');
                return false;
            }
            
            // Validate file size if a file is selected
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > maxSize) {
                    alert('Image size must be less than 2MB.');
                    return false;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, or WebP).');
                    return false;
                }
            }
            
            return confirm('Are you sure you want to update this cleanser?');
        }
        
        // Preview image before upload
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create or update preview
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'image-preview';
                        preview.className = 'current-image';
                        preview.innerHTML = '<p><strong>New Image Preview:</strong></p><img src="" alt="New Image Preview">';
                        document.querySelector('.form-group:last-child').insertBefore(preview, document.querySelector('.back-link'));
                    }
                    preview.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>