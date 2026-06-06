<?php
include 'config/db.php';

if(isset($_POST['register']))
{
    $name=$_POST['name'];
    $email=$_POST['email'];
    $password=password_hash($_POST['password'],PASSWORD_DEFAULT);
    $role=$_POST['role'];

    $sql="INSERT INTO users(name,email,password,role)
    VALUES('$name','$email','$password','$role')";

    if(mysqli_query($conn,$sql))
    {
        header("Location: login.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>VendorBridge Signup</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="auth-container">

<div class="auth-card">

<div class="logo">
<h1>VendorBridge</h1>
<p>Procurement ERP System</p>
</div>

<form method="POST">

<div class="form-group">
<label>Full Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="form-group">
<label>Select Role</label>

<select name="role" class="form-control" required>

<option value="">Choose Role</option>

<option value="admin">Admin</option>

<option value="procurement">Procurement Officer</option>

<option value="vendor">Vendor</option>

<option value="manager">Manager</option>

</select>

</div>

<button class="btn" name="register">
Create Account
</button>

<div class="auth-link">
Already have account?
<a href="login.php">Login</a>
</div>

</form>

</div>

</div>

</body>
</html>