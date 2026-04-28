<?php
$conn = mysqli_connect("localhost", "root", "", "car_parking_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Slot name receive
    $slot = $_POST['slot'];

    // Check if slot already booked
    $check = mysqli_query($conn, "SELECT * FROM slots WHERE slot_name='$slot' AND status='booked'");

    if (mysqli_num_rows($check) > 0) {
        echo "<h3 style='color:red;'>❌ Slot already booked! Please choose another slot.</h3>";
        echo "<br><a href='booking_form.php'>Back</a>";
        exit;
    }

    // Update slot to booked
    $update = mysqli_query($conn, "UPDATE slots SET status='booked' WHERE slot_name='$slot'");

    if ($update) {
        echo "<h2 style='color:green;'>✔ Slot Booked Successfully!</h2>";
        echo "<h3>You booked: <b>$slot</b></h3>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    echo "<br><a href='booking.html'>Back to Booking</a>";
}
?>
