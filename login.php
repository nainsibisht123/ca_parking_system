<?php
$conn = mysqli_connect("localhost", "root", "", "car_parking_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- LOGIN ---
if (isset($_POST['login'])) {
    $username = $_POST['user_name'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM info_login WHERE user_name='$username'");
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            echo "<script>alert('✅ Login successful!'); window.location.href='home.html';</script>";
        } else {
            echo "<script>alert('❌ Invalid password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('❌ Username not found!'); window.history.back();</script>";
    }
}

mysqli_close($conn);
?>
