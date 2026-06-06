<?php

include '../config/db.php';

$id=$_GET['id'];

mysqli_query(
$conn,
"DELETE FROM quotations
WHERE id='$id'"
);

header("Location: quotation_list.php");
exit();
?>