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

<?php if($booking_id != "") { ?>
    <p><b>Booking ID:</b> <?php echo $booking_id; ?></p>
<?php } else { ?>
    <p style="color:red;">Booking ID not found ❌</p>
<?php } ?>

<?php if($pdf != "") { ?>
    <a href="<?php echo $pdf; ?>" download>
        <button>📄 Download Receipt</button>
    </a>
<?php } else { ?>
    <p style="color:red;">PDF not available ❌</p>
<?php } ?>

</body>
</html>