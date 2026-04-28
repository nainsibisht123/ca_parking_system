<?php
$conn = mysqli_connect("localhost", "root", "", "car_parking_system");
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
