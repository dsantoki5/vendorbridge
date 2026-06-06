<?php

include '../config/db.php';

$id=$_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM vendors WHERE id='$id'"
);

header("Location: vendor_list.php");