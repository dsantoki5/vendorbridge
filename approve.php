<?php

include '../config/db.php';

$id=$_GET['id'];

mysqli_query(
$conn,
"UPDATE approvals

SET
status='Approved',
remarks='Approved by Manager',
approved_by='Admin',
approval_date=NOW()

WHERE id='$id'"
);

mysqli_query(
$conn,
"INSERT INTO notifications
(
message,
type
)
VALUES
(
'Quotation Approved',
'Approval'
)"
);

mysqli_query(
$conn,
"INSERT INTO activity_logs
(
activity,
module_name
)
VALUES
(
'Quotation Approved',
'Approval'
)"
);

header("Location: approval_list.php");
exit();

?>