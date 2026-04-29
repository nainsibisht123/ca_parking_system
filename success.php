<?php
session_start();

$pdf = $_SESSION['pdf'] ?? '';
$booking_id = $_SESSION['booking_id'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Success</title>
</head>
<body>

<h2>✅ Booking Successful</h2>

<?php if($pdf != "") { ?>

<a href="download.php?file=<?php echo urlencode($pdf); ?>">
    <button>📄 Download Receipt</button>
</a>

<?php } else { ?>
<p style="color:red;">PDF not available ❌</p>
<?php } ?>

</body>
</html>