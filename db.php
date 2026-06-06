<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "vendorbridge"
);

if (!$conn) {
    die("Database Connection Failed");
}

?>