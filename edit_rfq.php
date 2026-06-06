<?php

include '../config/db.php';

$id=$_GET['id'];

$result=mysqli_query(
$conn,
"SELECT * FROM rfqs
WHERE id='$id'"
);

$row=mysqli_fetch_assoc($result);

if(isset($_POST['update']))
{
    $rfq_title=$_POST['rfq_title'];
    $quantity=$_POST['quantity'];
    $deadline=$_POST['deadline'];

    mysqli_query(
    $conn,
    "UPDATE rfqs
    SET

    rfq_title='$rfq_title',
    quantity='$quantity',
    deadline='$deadline'

    WHERE id='$id'"
    );

    header("Location: rfq_list.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit RFQ</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="form-container">

<h2 class="form-title">
Edit RFQ
</h2>

<form method="POST">

<div class="form-row">
<label>RFQ Title</label>
<input
type="text"
name="rfq_title"
value="<?php echo $row['rfq_title']; ?>">
</div>

<div class="form-row">
<label>Quantity</label>
<input
type="number"
name="quantity"
value="<?php echo $row['quantity']; ?>">
</div>

<div class="form-row">
<label>Deadline</label>
<input
type="date"
name="deadline"
value="<?php echo $row['deadline']; ?>">
</div>

<button
class="btn-save"
name="update">

Update RFQ

</button>

</form>

</div>

</body>
</html>