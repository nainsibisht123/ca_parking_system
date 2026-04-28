<?php
include "db.php";

$total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM parking_entries"));
?>

<h2>Dashboard</h2>
<p>Total Vehicles: <?php echo $total; ?></p>