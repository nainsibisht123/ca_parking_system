<?php
session_start();
require 'db.php';
if(!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Daily Bookings
$dailyBookings = [];
$sql = "SELECT booking_date, COUNT(*) as total_bookings
        FROM bookings_info
        GROUP BY booking_date
        ORDER BY booking_date DESC";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) $dailyBookings[] = $row;

// Total Revenue
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total_revenue FROM bookings_info"))['total_revenue'];

// Most Booked Slots
$mostBookedSlots = [];
$sql = "SELECT slot, COUNT(id) as total_bookings
        FROM bookings_info
        GROUP BY slot
        ORDER BY total_bookings DESC
        LIMIT 5";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) $mostBookedSlots[] = $row;

// Busy Hours (extract hour from parking_start_time)
$busyHours = array_fill(0, 24, 0);
$sql = "SELECT SUBSTRING_INDEX(parking_start_time, ':', 1) as hour, COUNT(*) as total_bookings
        FROM bookings_info
        GROUP BY hour";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) {
    $hour = intval($row['hour']);
    if($hour >=0 && $hour <=23) $busyHours[$hour] = $row['total_bookings'];
}

// Cancelled Bookings (we don't have status column, so just show 0)
$cancelledBookings = [];
// If you later add a status column, you can update this query
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h1>Welcome, <?php echo $_SESSION['admin']; ?></h1>
<a href="logout.php">Logout</a>

<div class="dashboard-cards">
    <div class="card">Total Revenue: ₹<?php echo $totalRevenue ?: 0; ?></div>
</div>

<h2>Daily Bookings</h2>
<table border="1">
<tr><th>Date</th><th>Total Bookings</th></tr>
<?php foreach($dailyBookings as $d) echo "<tr><td>{$d['booking_date']}</td><td>{$d['total_bookings']}</td></tr>"; ?>
</table>

<h2>Most Booked Slots</h2>
<table border="1">
<tr><th>Slot Name</th><th>Bookings</th></tr>
<?php foreach($mostBookedSlots as $s) echo "<tr><td>{$s['slot']}</td><td>{$s['total_bookings']}</td></tr>"; ?>
</table>

<h2>Busy Hours Graph</h2>
<canvas id="busyChart" width="600" height="300"></canvas>
<script>
var ctx = document.getElementById('busyChart').getContext('2d');
var busyChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', range(0,23)); ?>],
        datasets: [{
            label: 'Bookings per Hour',
            data: [<?php echo implode(',', $busyHours); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
        }]
    },
});
</script>

<h2>Cancelled Bookings</h2>
<p>Currently not tracked (no status column)</p>

</body>
</html>
