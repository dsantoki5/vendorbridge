<?php

include '../config/db.php';

$id=$_GET['id'];

mysqli_query(
$conn,
"UPDATE approvals

SET
status='Rejected',
remarks='Rejected by Manager',
approved_by='Admin',
approval_date=NOW()

WHERE id='$id'"
);

header("Location: approval_list.php");
exit();

?>