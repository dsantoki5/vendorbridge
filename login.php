<?php

session_start();

include 'config/db.php';

$error="";

if(isset($_POST['login']))
{
    $email=$_POST['email'];
    $password=$_POST['password'];

    $query=mysqli_query(
        $conn,
        "SELECT * FROM users WHERE email='$email'"
    );

    $user=mysqli_fetch_assoc($query);

    if($user && password_verify($password,$user['password']))
    {
        $_SESSION['user_id']=$user['id'];
        $_SESSION['name']=$user['name'];
        $_SESSION['role']=$user['role'];

        header("Location: dashboard.php");
        exit();
    }
    else
    {
        $error="Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="auth-container">

<div class="auth-card">

<h2>VendorBridge Login</h2>

<br>

<?php
if($error!="")
{
    echo "<p style='color:red;'>$error</p>";
}
?>

<form method="POST">

<input type="email"
name="email"
placeholder="Email"
class="form-control"
required>

<br><br>

<input type="password"
name="password"
placeholder="Password"
class="form-control"
required>

<br><br>

<button
type="submit"
name="login"
class="btn">

Login

</button>

</form>

<br>

<a href="signup.php">
Create New Account
</a>

</div>

</div>

</body>
</html>