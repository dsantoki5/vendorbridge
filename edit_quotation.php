<?php

include '../config/db.php';

$id=$_GET['id'];

$result=mysqli_query(
$conn,
"SELECT * FROM quotations
WHERE id='$id'"
);

$row=mysqli_fetch_assoc($result);

if(isset($_POST['update']))
{
    $price=$_POST['price'];
    $delivery_days=$_POST['delivery_days'];
    $notes=$_POST['notes'];

    mysqli_query(
        $conn,
        "UPDATE quotations
        SET

        price='$price',
        delivery_days='$delivery_days',
        notes='$notes'

        WHERE id='$id'"
    );

    header("Location: quotation_list.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Quotation</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="form-container">

<h2 class="form-title">
Edit Quotation
</h2>

<form method="POST">

<div class="form-row">
<label>Price</label>
<input
type="number"
step="0.01"
name="price"
value="<?php echo $row['price']; ?>">
</div>

<div class="form-row">
<label>Delivery Days</label>
<input
type="number"
name="delivery_days"
value="<?php echo $row['delivery_days']; ?>">
</div>

<div class="form-row">
<label>Notes</label>
<textarea
name="notes"><?php echo $row['notes']; ?></textarea>
</div>

<button
class="btn-save"
name="update">

Update Quotation

</button>

</form>

</div>

</body>
</html>