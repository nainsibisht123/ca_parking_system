<?php
session_start();

// Step 1: Check if user is logged in
if(!isset($_SESSION['username'])){
    header("Location: login.php"); // agar user login nahi hai to login page
    exit();
}
$user = $_SESSION['username'];

// Step 2: Database connection
$conn = mysqli_connect("localhost", "root", "", "car_parking_system");
if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

// Step 3: Fetch user bookings
$sql = "SELECT * FROM booking WHERE owner_name='$user' ORDER BY booking_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Booking History</title>
<style>
table { width: 100%; border-collapse: collapse; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
th { background-color: #f2f2f2; }
tr:nth-child(even) { background-color: #f9f9f9; }
</style>
</head>
<body>

<h2>My Booking History</h2>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Vehicle Name</th>
      <th>Vehicle Number</th>
      <th>Date</th>
      <th>Start Time</th>
      <th>End Time</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= $row['vehicle_name'] ?></td>
      <td><?= $row['vehicle_number'] ?></td>
      <td><?= $row['booking_date'] ?></td>
      <td><?= $row['start_time'] ?></td>
      <td><?= $row['end_time'] ?></td>
      <td><?= ucfirst($row['status']) ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

</body>
</html>
