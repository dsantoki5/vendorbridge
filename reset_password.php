<?php

session_start();

include 'config/db.php';

if(!isset($_SESSION['reset_email']))
{
    header("Location: forgot_password.php");
}

if(isset($_POST['update']))
{
    $email=$_SESSION['reset_email'];

    $password=password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    mysqli_query(
        $conn,
        "UPDATE users
         SET password='$password'
         WHERE email='$email'"
    );

    session_destroy();

    header("Location: login.php");
}

?>

<form method="POST">

<input
type="password"
name="password"
placeholder="New Password"
required>

<br><br>

<button
name="update">

Update Password

</button>

</form>