<?php
session_start();
require 'db.php';

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        if(password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "Admin not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<h2>Admin Login</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit" name="login">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
