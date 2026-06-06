<?php

include 'config/db.php';

$msg="";

if(isset($_POST['send_otp']))
{
    $email=$_POST['email'];

    $otp=rand(100000,999999);

    mysqli_query(
        $conn,
        "INSERT INTO password_resets(email,otp)
         VALUES('$email','$otp')"
    );

    $msg="Your OTP is : ".$otp;
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-container">
<div class="auth-card">

<h2>Forgot Password</h2>

<?php
if($msg!="")
{
    echo "<p style='color:green;'>$msg</p>";
}
?>

<form method="POST">

<input type="email"
name="email"
class="form-control"
placeholder="Enter Email"
required>

<br><br>

<button
name="send_otp"
class="btn">

Send OTP

</button>

</form>

</div>
</div>

</body>
</html>