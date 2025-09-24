<?php
session_start();
include 'connect.php';

// Handle regular sign-in
if (isset($_POST['signin'])) {
    $username = isset($_POST['Usname']) ? trim($_POST['Usname']) : "";
    $email = isset($_POST['email']) ? trim($_POST['email']) : "";
    $password = isset($_POST['pass']) ? $_POST['pass'] : "";

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        echo '<script type="text/javascript"> 
            alert("All fields are required!");
        </script>';
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $sql = "SELECT * FROM customer WHERE U_name = ? AND email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['Usname'] = $username;
            $_SESSION['customerID'] = $row['CID']; 
            header("location: CusHome.php");
            exit();
        } else {
            echo '<script type="text/javascript"> 
                alert("Invalid Username, Email, or Password. Try again!");
            </script>';
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo '<script type="text/javascript"> 
            alert("Database error. Please try again later.");
        </script>';
    }
}

// Handle Google authentication
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['google_uid'])) {
    $google_uid = mysqli_real_escape_string($conn, $_POST['google_uid']);
    $google_email = mysqli_real_escape_string($conn, $_POST['google_email']);
    $google_name = mysqli_real_escape_string($conn, $_POST['google_name']);
    $google_photo = mysqli_real_escape_string($conn, $_POST['google_photo']);

    // Validate that we have the required data
    if (empty($google_uid) || empty($google_email)) {
        echo '<script type="text/javascript"> 
            alert("Google authentication failed: Missing required data.");
        </script>';
        exit();
    }

    // Check if user already exists in the database by Google UID or email
    $sql = "SELECT * FROM customer WHERE google_uid = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $google_uid, $google_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // User exists, log them in
            $row = mysqli_fetch_assoc($result);
            $_SESSION['Usname'] = $row['U_name'];
            $_SESSION['customerID'] = $row['CID'];
            
            // Update Google data if needed
            if (empty($row['google_uid'])) {
                $update_sql = "UPDATE customer SET google_uid = ? WHERE CID = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "si", $google_uid, $row['CID']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
            
            header("location: CusHome.php");
            exit();
        } else {
            // User doesn't exist, create a new account
            // Generate a username from the Google name
            $username = generateUsername($google_name);
            $password = generateRandomPassword(); // For security, even though they'll use Google auth
            
            $insert_sql = "INSERT INTO customer (U_name, email, password, google_uid, profile_picture) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $google_email, $password, $google_uid, $google_photo);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    // Get the newly created user
                    $select_sql = "SELECT * FROM customer WHERE email = ?";
                    $select_stmt = mysqli_prepare($conn, $select_sql);
                    mysqli_stmt_bind_param($select_stmt, "s", $google_email);
                    mysqli_stmt_execute($select_stmt);
                    $new_user_result = mysqli_stmt_get_result($select_stmt);
                    
                    if (mysqli_num_rows($new_user_result) == 1) {
                        $new_user = mysqli_fetch_assoc($new_user_result);
                        $_SESSION['Usname'] = $new_user['U_name'];
                        $_SESSION['customerID'] = $new_user['CID'];
                        header("location: CusHome.php");
                        exit();
                    }
                } else {
                    echo '<script type="text/javascript"> 
                        alert("Error creating account. Please try again.");
                    </script>';
                }
                
                mysqli_stmt_close($insert_stmt);
            }
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo '<script type="text/javascript"> 
            alert("Database error. Please try again later.");
        </script>';
    }
}

// Helper function to generate a username from Google name
function generateUsername($name) {
    $base_username = preg_replace('/[^a-zA-Z0-9]/', '', $name);
    $username = strtolower($base_username);
    
    // Add random numbers if username is too short
    if (strlen($username) < 5) {
        $username .= rand(100, 999);
    }
    
    return $username;
}

// Helper function to generate a random password
function generateRandomPassword($length = 12) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr(str_shuffle($chars), 0, $length);
    return $password;
}
?>

<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" type="text/css" href="signIn.css"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="Signin.js" type="text/javascript"></script>
    <title>Sign In</title>
    <style>
        /* Additional styles for Google Sign-in button */
        .google-signin-container {
            margin: 20px 0;
            text-align: center;
        }
        
        .google-signin-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            color: #757575;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            max-width: 240px;
        }
        
        .google-signin-btn:hover {
            background-color: #f8f8f8;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .google-icon {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #757575;
            font-size: 14px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider::before {
            margin-right: 10px;
        }
        
        .divider::after {
            margin-left: 10px;
        }
        
        /* Loading indicator */
        .loading {
            display: none;
            text-align: center;
            margin: 10px 0;
        }
        
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div id="back" align="center">
    <form id="frm" action="Signin.php" method="post" class="reg">
        <h3 id="top">Owshadham Signin</h3>
        <label class="lbl"><b>User Name</b></label>
        <br>
        <input type="text" id="Uname" name="Usname" class="wdth" placeholder="Enter Your User Name" required="required">
        <br><br>
        <label for="email" class="lbl"><b>Email</b></label>
        <br>
        <input type="text" placeholder="Enter Email" name="email" class="wdth" required>
        <br><br>
        <label class="lbl"><b>Password</b></label>
        <br>
        <input type="password" id="pwrd" name="pass" class="wdth" placeholder="Enter Password" required="required">
        <br><br>
        <label class="lbl">Accept privacy Policy and terms</label>
        <input type="checkbox" id="cb" name="accept" value="true" onclick="enableButton()">
        <br>
        <p id="text" style="display:none"></p>
        <br>
        <input type="submit" id="btn" onclick="checkpassword()" value="Sign In" name="signin" disabled>
        
        <!-- Divider -->
        <div class="divider">OR</div>
        
        <!-- Google Sign-in Button -->
        <div class="google-signin-container">
            <button type="button" id="googleSignIn" class="google-signin-btn">
                <svg class="google-icon" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                    <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                        <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                        <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                        <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"/>
                        <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"/>
                    </g>
                </svg>
                Sign in with Google
            </button>
        </div>
        
        <!-- Loading indicator -->
        <div id="googleLoading" class="loading">
            <div class="loading-spinner"></div>
            <p>Signing in with Google...</p>
        </div>
    </form>
</div>

<!-- Firebase Configuration and Google Auth Script -->
<script>
    // Firebase configuration - Replace with your actual Firebase config
    const firebaseConfig = {
        apiKey: "AIzaSyBv18E0JbtMWZ1blH1onK8Gyo-9rSxlck8", 
        authDomain: "music-bible-66186.firebaseapp.com",
        projectId: "music-bible-66186",
        storageBucket: "music-bible-66186.appspot.com",
        messagingSenderId: "529628953811",
        appId: "1:529628953811:web:c2abde7f903181613a2826"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();
    const provider = new firebase.auth.GoogleAuthProvider();

    // Add scopes if needed
    provider.addScope('email');
    provider.addScope('profile');

    // Google Sign-in function
    document.getElementById('googleSignIn').addEventListener('click', function() {
        // Show loading indicator
        document.getElementById('googleLoading').style.display = 'block';
        
        auth.signInWithPopup(provider)
            .then((result) => {
                // The signed-in user info
                const user = result.user;
                
                // Send user data to server for verification/registration
                sendGoogleUserDataToServer(user);
            })
            .catch((error) => {
                // Hide loading indicator
                document.getElementById('googleLoading').style.display = 'none';
                
                // Handle Errors here
                const errorCode = error.code;
                const errorMessage = error.message;
                
                console.error('Google Sign-in Error:', errorCode, errorMessage);
                alert('Google Sign-in failed: ' + errorMessage);
            });
    });

    // Function to send Google user data to PHP backend
    function sendGoogleUserDataToServer(user) {
        // Create a form to submit the data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'Signin.php'; // Submit to the same page
        
        // Add user data as hidden inputs
        const uidInput = document.createElement('input');
        uidInput.type = 'hidden';
        uidInput.name = 'google_uid';
        uidInput.value = user.uid;
        form.appendChild(uidInput);
        
        const emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'google_email';
        emailInput.value = user.email;
        form.appendChild(emailInput);
        
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'google_name';
        nameInput.value = user.displayName || '';
        form.appendChild(nameInput);
        
        const photoInput = document.createElement('input');
        photoInput.type = 'hidden';
        photoInput.name = 'google_photo';
        photoInput.value = user.photoURL || '';
        form.appendChild(photoInput);
        
        // Add the form to the document and submit it
        document.body.appendChild(form);
        form.submit();
    }

    // Enable/disable signin button based on checkbox
    function enableButton() {
        const checkbox = document.getElementById('cb');
        const button = document.getElementById('btn');
        button.disabled = !checkbox.checked;
    }

    // Password validation function
    function checkpassword() {
        const password = document.getElementById('pwrd').value;
        if (password.length < 6) {
            alert('Password must be at least 6 characters long');
            return false;
        }
        return true;
    }
</script>
</body>
</html>