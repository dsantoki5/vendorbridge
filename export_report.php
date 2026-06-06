<?php

include '../config/db.php';

header('Content-Type:text/csv');
header('Content-Disposition:attachment;filename=procurement_report.csv');

$output=fopen("php://output","w");

fputcsv(
$output,
[
"Vendor",
"Email",
"Category",
"Status"
]
);

$result=mysqli_query(
$conn,
"SELECT * FROM vendors"
);

while($row=mysqli_fetch_assoc($result))
{
    fputcsv(
    $output,
    [
        $row['vendor_name'],
        $row['email'],
        $row['category'],
        $row['status']
    ]
    );
}

fclose($output);
exit();
?>