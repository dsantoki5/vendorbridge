<?php

include '../config/db.php';

$id=$_GET['id'];

mysqli_query(
$conn,
"DELETE FROM rfqs
WHERE id='$id'"
);

header("Location: rfq_list.php");
exit();

?>