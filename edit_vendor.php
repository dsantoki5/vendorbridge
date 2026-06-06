<?php

include '../config/db.php';

$id = $_GET['id'];

$result = mysqli_query(
    $conn,
    "SELECT * FROM vendors WHERE id='$id'"
);

$row = mysqli_fetch_assoc($result);

if(isset($_POST['update']))
{
    $vendor_name = $_POST['vendor_name'];
    $vendor_code = $_POST['vendor_code'];
    $category = $_POST['category'];
    $gst = $_POST['gst'];
    $contact_person = $_POST['contact_person'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $status = $_POST['status'];

    mysqli_query(
        $conn,
        "UPDATE vendors SET
        vendor_name='$vendor_name',
        vendor_code='$vendor_code',
        category='$category',
        gst_number='$gst',
        contact_person='$contact_person',
        email='$email',
        phone='$phone',
        address='$address',
        status='$status'
        WHERE id='$id'"
    );

    header("Location: vendor_list.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vendor</title>
</head>
<body>

<h2>Edit Vendor</h2>

<form method="POST">

    Vendor Name<br>
    <input type="text"
           name="vendor_name"
           value="<?php echo $row['vendor_name']; ?>"
           required>
    <br><br>

    Vendor Code<br>
    <input type="text"
           name="vendor_code"
           value="<?php echo $row['vendor_code']; ?>"
           required>
    <br><br>

    Category<br>
    <select name="category">

        <option <?php if($row['category']=="Hardware") echo "selected"; ?>>
            Hardware
        </option>

        <option <?php if($row['category']=="Software") echo "selected"; ?>>
            Software
        </option>

        <option <?php if($row['category']=="Networking") echo "selected"; ?>>
            Networking
        </option>

        <option <?php if($row['category']=="Services") echo "selected"; ?>>
            Services
        </option>

    </select>

    <br><br>

    GST Number<br>
    <input type="text"
           name="gst"
           value="<?php echo $row['gst_number']; ?>">
    <br><br>

    Contact Person<br>
    <input type="text"
           name="contact_person"
           value="<?php echo $row['contact_person']; ?>">
    <br><br>

    Email<br>
    <input type="email"
           name="email"
           value="<?php echo $row['email']; ?>">
    <br><br>

    Phone<br>
    <input type="text"
           name="phone"
           value="<?php echo $row['phone']; ?>">
    <br><br>

    Address<br>
    <textarea name="address"><?php echo $row['address']; ?></textarea>

    <br><br>

    Status<br>

    <select name="status">

        <option value="Active"
        <?php if($row['status']=="Active") echo "selected"; ?>>
            Active
        </option>

        <option value="Inactive"
        <?php if($row['status']=="Inactive") echo "selected"; ?>>
            Inactive
        </option>

    </select>

    <br><br>

    <button name="update">
        Update Vendor
    </button>

</form>

</body>
</html>