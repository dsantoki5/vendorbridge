<?php

include '../config/db.php';

$result = mysqli_query(
$conn,
"SELECT

approvals.*,
quotations.price,
vendors.vendor_name,
rfqs.rfq_title

FROM approvals

LEFT JOIN quotations
ON approvals.quotation_id = quotations.id

LEFT JOIN vendors
ON quotations.vendor_id = vendors.id

LEFT JOIN rfqs
ON quotations.rfq_id = rfqs.id

ORDER BY approvals.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

<title>Approval History</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="page-card">

<h2>Approval History</h2>

<br>

<table class="vendor-table">

<tr>

<th>ID</th>
<th>RFQ</th>
<th>Vendor</th>
<th>Price</th>
<th>Status</th>
<th>Remarks</th>
<th>Approved By</th>
<th>Date</th>

</tr>

<?php
while($row=mysqli_fetch_assoc($result))
{
?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['rfq_title']; ?></td>

<td><?php echo $row['vendor_name']; ?></td>

<td>₹ <?php echo $row['price']; ?></td>

<td>

<?php

if($row['status']=="Approved")
{
    echo "<span class='badge-active'>Approved</span>";
}
elseif($row['status']=="Rejected")
{
    echo "<span class='badge-inactive'>Rejected</span>";
}
else
{
    echo "<span style='background:#fef3c7;padding:6px;border-radius:6px;'>Pending</span>";
}

?>

</td>

<td><?php echo $row['remarks']; ?></td>

<td><?php echo $row['approved_by']; ?></td>

<td><?php echo $row['approval_date']; ?></td>

</tr>

<?php
}
?>

</table>

</div>

</body>
</html>