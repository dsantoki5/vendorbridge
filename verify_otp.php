<?php

session_start();

include 'config/db.php';

$msg="";

if(isset($_POST['verify']))
{
    $email=$_POST['email'];
    $otp=$_POST['otp'];

    $check=mysqli_query(
        $conn,
        "SELECT * FROM password_resets
         WHERE email='$email'
         AND otp='$otp'
         ORDER BY id DESC LIMIT 1"
    );

    if(mysqli_num_rows($check)>0)
    {
        $_SESSION['reset_email']=$email;

        header("Location: reset_password.php");
        exit();
    }
    else
    {
        $msg="Invalid OTP";
    }
}

?>

<form method="POST">

<input type="email"
name="email"
placeholder="Email"
required>

<br><br>

<input type="text"
name="otp"
placeholder="Enter OTP"
required>

<br><br>

<button
name="verify">

Verify OTP

</button>

</form>

<?php echo $msg; ?>