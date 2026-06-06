<?php

include '../config/db.php';

$result=mysqli_query(
$conn,
"SELECT * FROM notifications
ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>
<head>
<title>Notifications</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="page-card">

<h2>Notifications</h2>

<table class="vendor-table">

<tr>
<th>ID</th>
<th>Message</th>
<th>Type</th>
<th>Date</th>
</tr>

<?php
while($row=mysqli_fetch_assoc($result))
{
?>

<tr>

<td><?php echo $row['id']; ?></td>
<td><?php echo $row['message']; ?></td>
<td><?php echo $row['type']; ?></td>
<td><?php echo $row['created_at']; ?></td>

</tr>

<?php
}
?>

</table>

</div>

</body>
</html>