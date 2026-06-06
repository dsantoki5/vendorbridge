<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

?>

<h1>
Welcome <?php echo $_SESSION['name']; ?>
</h1>

<h2>
Role :
<?php echo $_SESSION['role']; ?>
</h2>

<a href="logout.php">
Logout
</a>