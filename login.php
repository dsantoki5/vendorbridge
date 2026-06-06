<?php

session_start();
include 'config/db.php';

$error = "";

if(isset($_POST['login']))
{
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password))
    {
        $error = "Please fill all fields";
    }
    else
    {
        $query = mysqli_query(
            $conn,
            "SELECT * FROM users WHERE email='$email'"
        );

        if(mysqli_num_rows($query) > 0)
        {
            $user = mysqli_fetch_assoc($query);

            if(password_verify($password, $user['password']))
            {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                header("Location: dashboard.php");
                exit();
            }
            else
            {
                $error = "Invalid Password";
            }
        }
        else
        {
            $error = "Email Not Found";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VendorBridge — Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">

        <!-- LOGO -->
        <div class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" width="26" height="26">
                    <path d="M4 8h20v14a2 2 0 01-2 2H6a2 2 0 01-2-2V8z" fill="rgba(255,255,255,0.92)"/>
                    <path d="M2 8h24M10 8V5a2 2 0 012-2h4a2 2 0 012 2v3" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M10 14h8M10 18h5" stroke="rgba(37,99,235,0.65)" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h1>VendorBridge</h1>
            <p>Procurement &amp; Vendor Management ERP</p>
        </div>

        <!-- ERROR MESSAGE -->
        <?php if($error != ""): ?>
            <div class="error">⚠&nbsp; <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <form method="POST" autocomplete="off">

            <div class="form-group">
                <label>Email Address</label>
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required>
            </div>

            <button type="submit" name="login" class="btn">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Login to VendorBridge
            </button>

        </form>

        <!-- LINKS -->
        <div class="auth-link">
            <a href="forgot_password.php">Forgot Password?</a>
            <br><br>
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>

    </div>
</div>

</body>
</html>
