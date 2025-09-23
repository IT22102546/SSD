<?php
session_start();
include "connect.php";



// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Display success/error messages
if (isset($_SESSION['success_message'])) {
    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 10px; border-radius: 4px;">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border-radius: 4px;">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" type="text/css" href="adminfood.css"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Clenser Admin</title>
    <link rel="icon" href="Logo/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script type="text/javascript" language="javascript" src="home.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        table th {
            background-color: #84c32f;
            color: white;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        table img {
            max-width: 80px;
            height: auto;
            border-radius: 4px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-add {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background-color: #218838;
        }

        .delete-form {
            display: inline;
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 20px;
        }

        .welcome-message {
            font-size: 16px;
            color: #333;
        }

        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            
            table th, table td {
                padding: 8px 4px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Cleanser Management</h1>
            <div>
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['Usname']); ?></span>
                <a href="logout.php" class="btn btn-delete" style="margin-left: 10px;">Logout</a>
            </div>
        </div>

        <a href="AddClensers.php" class="btn btn-add">Add New Cleanser</a>

        <?php
        $sql = "SELECT * FROM clenser ORDER BY Cl_ID DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo '<table border="1" cellspacing="0" cellpadding="10">
            <tr>
                <th>Cleanser ID</th>
                <th>Cleanser Name</th>
                <th>Cleanser Price</th>
                <th>Cleanser Size</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>';
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>
                    <td>' . htmlspecialchars($row['Cl_ID']) . '</td>
                    <td>' . htmlspecialchars($row['Clenser_name']) . '</td>
                    <td>Rs. ' . htmlspecialchars($row['Clenser_price']) . '</td>
                    <td>' . htmlspecialchars($row['Clenser_size']) . '</td>
                    <td><img src="uploads/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['Clenser_name']) . '" onerror="this.src=\'Logo/logo.png\'"></td>
                    <td>
                        <a href="editClenser.php?Id=' . htmlspecialchars($row['Cl_ID']) . '" class="btn btn-edit">Edit</a>
                        <form method="POST" action="deletecleanser.php" class="delete-form" onsubmit="return confirm(\'Are you sure you want to delete this cleanser? This action cannot be undone.\');">
                            <input type="hidden" name="Id" value="' . htmlspecialchars($row['Cl_ID']) . '">
                            <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                            <button type="submit" class="btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p style="text-align: center; padding: 20px; color: #666;">No cleansers found.</p>';
        }
        
        mysqli_close($conn);
        ?>
    </div>
    
    <script>
        // Add confirmation for delete actions
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('.delete-form');
            
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to delete this cleanser? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>