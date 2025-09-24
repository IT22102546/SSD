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

    // First, check if user exists by Google UID (exact match)
    $sql = "SELECT * FROM customer WHERE google_uid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $google_uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // User exists with this Google UID, log them in
            $row = mysqli_fetch_assoc($result);
            $_SESSION['Usname'] = $row['U_name'];
            $_SESSION['customerID'] = $row['CID'];
            header("location: CusHome.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }

    // If no user found by Google UID, check by email
    $sql = "SELECT * FROM customer WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $google_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // User exists with this email but no Google UID
            $row = mysqli_fetch_assoc($result);
            
            // Check if this account already has a different Google UID
            if (!empty($row['google_uid']) && $row['google_uid'] != $google_uid) {
                echo '<script type="text/javascript"> 
                    alert("This email is already associated with a different Google account. Please use your original sign-in method.");
                </script>';
                exit();
            }
            
            // Update the existing account with Google UID
            $update_sql = "UPDATE customer SET google_uid = ?, profile_picture = ? WHERE email = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "sss", $google_uid, $google_photo, $google_email);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $_SESSION['Usname'] = $row['U_name'];
                    $_SESSION['customerID'] = $row['CID'];
                    header("location: CusHome.php");
                    exit();
                } else {
                    echo '<script type="text/javascript"> 
                        alert("Error updating account with Google authentication.");
                    </script>';
                }
                mysqli_stmt_close($update_stmt);
            }
        } else {
            // User doesn't exist, create a new account
            $username = generateUsername($google_name);
            $password = generateRandomPassword();
            
            // Check if username already exists and make it unique
            $username = makeUniqueUsername($conn, $username);
            
            $insert_sql = "INSERT INTO customer (U_name, email, password, google_uid, profile_picture) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $google_email, $password, $google_uid, $google_photo);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    // Get the newly created user
                    $new_user_id = mysqli_insert_id($conn);
                    $_SESSION['Usname'] = $username;
                    $_SESSION['customerID'] = $new_user_id;
                    header("location: CusHome.php");
                    exit();
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

// Helper function to make username unique
function makeUniqueUsername($conn, $username) {
    $original_username = $username;
    $counter = 1;
    
    // Check if username exists
    $sql = "SELECT U_name FROM customer WHERE U_name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while (mysqli_num_rows($result) > 0) {
        $username = $original_username . $counter;
        $counter++;
        
        mysqli_stmt_close($stmt);
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
    
    mysqli_stmt_close($stmt);
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
    <title>Sign In</title>
    <style>
        /* Your existing CSS styles remain the same */
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

        .google-g {
            display: inline-block;
            width: 18px;
            height: 18px;
            background: conic-gradient(from -45deg, #ea4335 0deg 90deg, #4285f4 90deg 180deg, #34a853 180deg 270deg, #fbbc05 270deg 360deg);
            border-radius: 50%;
            color: white;
            font-weight: bold;
            font-size: 14px;
            line-height: 18px;
            text-align: center;
            margin-right: 10px;
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
        
        .error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background-color: #ffe6e6;
            border: 1px solid red;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div id="back" align="center">
    <form id="frm" action="Signin.php" method="post" class="reg">
        <h3 id="top">Owshadham Signin</h3>
        
        <!-- Display error messages if any -->
        <?php
        if (isset($_GET['error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
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
        <input type="submit" id="btn" onclick="return checkpassword()" value="Sign In" name="signin" disabled>
        
        <!-- Divider -->
        <div class="divider">OR</div>
        
        <!-- Google Sign-in Button -->
       <button type="button" id="googleSignIn" class="google-signin-btn">
    <span class="google-g">G</span>
    Sign in with Google
</button>

        
        <!-- Loading indicator -->
        <div id="googleLoading" class="loading">
            <div class="loading-spinner"></div>
            <p>Signing in with Google...</p>
        </div>
    </form>
</div>

<script>
    // Firebase configuration
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

    provider.addScope('email');
    provider.addScope('profile');

    document.getElementById('googleSignIn').addEventListener('click', function() {
        document.getElementById('googleLoading').style.display = 'block';
        
        auth.signInWithPopup(provider)
            .then((result) => {
                const user = result.user;
                sendGoogleUserDataToServer(user);
            })
            .catch((error) => {
                document.getElementById('googleLoading').style.display = 'none';
                console.error('Google Sign-in Error:', error.code, error.message);
                alert('Google Sign-in failed: ' + error.message);
            });
    });

    function sendGoogleUserDataToServer(user) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'Signin.php';
        
        const fields = [
            { name: 'google_uid', value: user.uid },
            { name: 'google_email', value: user.email },
            { name: 'google_name', value: user.displayName || '' },
            { name: 'google_photo', value: user.photoURL || '' }
        ];
        
        fields.forEach(field => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = field.name;
            input.value = field.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }

    function enableButton() {
        const checkbox = document.getElementById('cb');
        const button = document.getElementById('btn');
        button.disabled = !checkbox.checked;
    }

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