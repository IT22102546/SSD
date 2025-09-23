<?php
session_start();


// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Display error messages if any
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Add Cleansers</title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .container h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #333;
            border-bottom: 2px solid #84c32f;
            padding-bottom: 10px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #84c32f;
            outline: none;
            box-shadow: 0 0 5px rgba(132, 195, 47, 0.3);
        }

        input[type="submit"] {
            background-color: #84c32f;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #6dbd1f;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #84c32f;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .file-note {
            font-size: 12px;
            color: #666;
            margin-top: -15px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Cleanser</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <form action="validateClensers.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label for="Clensername">Cleanser Name *</label>
            <input type="text" name="Clensername" id="Clensername" required 
                   maxlength="100" pattern="[A-Za-z0-9\s\-]+" 
                   title="Only letters, numbers, spaces, and hyphens are allowed">
            
            <label for="price">Price (Rs.) *</label>
            <input type="number" name="price" id="price" required 
                   min="0" step="0.01" max="100000" 
                   title="Price must be greater than 0">
            
            <label for="size">Size *</label>
            <input type="text" name="size" id="size" required 
                   maxlength="50" pattern="[A-Za-z0-9\smlML]+" 
                   title="Enter size (e.g., 100ml, 200g)">
            
            <label for="image">Product Image *</label>
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <div class="file-note">Allowed formats: JPEG, PNG, GIF, WebP. Maximum size: 2MB</div>
            
            <input type="submit" name="sub" value="Add Cleanser">
        </form>
        
        <div class="back-link">
            <a href="Clenser.php">← Back to Cleanser List</a>
        </div>
    </div>

    <script>
        function validateForm() {
            const price = document.getElementById('price').value;
            const fileInput = document.getElementById('image');
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            
            // Validate price
            if (price <= 0) {
                alert('Price must be greater than 0.');
                return false;
            }
            
            // Validate file
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                // Validate file size
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
            
            return confirm('Are you sure you want to add this cleanser?');
        }
        
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Remove existing preview if any
                const existingPreview = document.getElementById('image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.id = 'image-preview';
                    preview.innerHTML = `
                        <div style="margin-top: 10px; text-align: center;">
                            <p style="margin-bottom: 5px; font-weight: bold;">Image Preview:</p>
                            <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                    `;
                    document.querySelector('.file-note').after(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>