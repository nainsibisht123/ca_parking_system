<?php
$conn = mysqli_connect("localhost", "root", "", "car_parking_system");
require "slots_utils.php";
$slot = $_POST['slot'];
$date = $_POST['booking_date'];
$start_time = $_POST['start_time'];
$hours = $_POST['hours'];

$busy = checkSlotOverlap($conn, $slot, $date, $start_time, $hours);
//die();
echo json_encode(["busy" => $busy]);
?>
