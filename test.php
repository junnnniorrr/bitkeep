<?php
$conn = mysqli_connect("localhost", "root", "YourNewPassword", "file_management");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
?>
