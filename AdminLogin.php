<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="signIn.css"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="adminLogin.js" type="text/javascript"></script>
<title>sign in</title>
<link rel="icon" href="Logo/logo.png" type="image/x-icon">
</head>
	
<body>

<div id="back" align="center">

	<form id="frm" action="AdminLogin.php" method="post" class="reg" >
	<h3 id="top">Admin Signin</h3>
	<label class="lbl"><b>User Name</b></label>
	<br>
	<input type="text" id="Uname" name="Usname" class="wdth" placeholder="Enter Your User Name" required="required">
	<br><br>
			
	 <label for="email" class="lbl"><b>Email</b></label>
     <br>
    <input type="text" placeholder="Enter Email" name="email" class= "wdth"required>

	<br><br>
	
	<label  class="lbl"><b>Password</b></label>
	<br>
	 <input type="password" id="pwrd" name="pass" class="wdth" placeholder="Enter Password" required="required">

	<br><br>
	
	<label  class="lbl">Accept privacy Policy and terms<label>
	<input type="checkbox" id="cb" name="accept" value="true" onclick="enableButton()">
	<br>
	<p id="text" style="display:none"></p>
	<br>
	<input type="submit" id="btn" onclick="checkpassword()" value="Sign In" name="signin"disabled>
</form>
 </div>
 
 
<?php

session_start();
include 'connect.php';

if(isset($_POST['signin']))
{
    $username = isset($_POST['Usname']) ? $_POST['Usname'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $password = isset($_POST['pass']) ? $_POST['pass'] : "";
    
    // Use prepared statement to prevent SQL injection
    $sql = "SELECT * FROM admin WHERE U_name = ? AND email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1)
        {
            $_SESSION['Usname'] = $username;
            $_SESSION['user_role'] = 'admin';
            mysqli_stmt_close($stmt);
            header("location:AdminDashBoard.php");
            exit();
        }
        else
        {
            echo '<script type="text/JavaScript"> 
                    alert("Invalid Username, Email or Password. Try again!");
                  </script>';
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo '<script type="text/JavaScript"> 
                alert("Database error. Please try again!");
              </script>';
    }
}

?>
</body>
</html>