<?php
if (isset($_POST['submit'])) {

    // Clean input function
    function clean($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Get all values from the form
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $message = clean($_POST['message']);

    // -------------------
    // PHP VALIDATION
    // -------------------

    // Name validation (only letters & spaces)
    if (empty($name) || !preg_match("/^[A-Za-z ]+$/", $name)) {
        echo "<script>alert('Invalid Name! Only letters and spaces allowed.'); window.history.back();</script>";
        exit();
    }

    // Email validation (valid email format)
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid Email Address!'); window.history.back();</script>";
        exit();
    }

    // Message validation (min length 5)
    if (empty($message) || strlen($message) < 5) {
        echo "<script>alert('Message must be at least 5 characters long!'); window.history.back();</script>";
        exit();
    }

    // -------------------
    // DATABASE CONNECTION
    // -------------------

    $host = "localhost";
    $uname = "root";
    $pwd = "";
    $dbname = "car_parking_system";

    // Create a connection
    $con = mysqli_connect($host, $uname, $pwd, $dbname);

    // Check connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare SQL statement to avoid SQL injection
    $stmt = $con->prepare("INSERT INTO contact_us (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    // Execute query
    if ($stmt->execute()) {
        echo "<script>alert('Your message has been sent successfully!'); window.location.href='contact.php';</script>";
    } else {
        echo "<script>alert('Error occurred while sending message!'); window.history.back();</script>";
    }

    // Close connection
    $stmt->close();
    mysqli_close($con);
}
?>
