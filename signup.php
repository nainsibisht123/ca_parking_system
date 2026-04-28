<?php
// --- Database Connection ---
$conn = mysqli_connect("localhost", "root", "", "car_parking_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- SIGNUP ---
if (isset($_POST['signup'])) {
    $username = $_POST['user_name'];
    $password = $_POST['password'];

    // Check if username already exists
    $check = mysqli_query($conn, "SELECT * FROM info_login WHERE user_name='$username'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('⚠️ Username already exists!'); window.history.back();</script>";
    } else {
        // Hash password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = "INSERT INTO info_login (user_name, password) VALUES ('$username', '$hashedPassword')";
        
        if (mysqli_query($conn, $insert)) {
            echo "<script>alert('✅ Signup successful! Redirecting to login...'); window.location.href='home.html';</script>";
        } else {
            echo "<script>alert('❌ Error: Could not sign up.'); window.history.back();</script>";
        }
    }
}

mysqli_close($conn);
?>
