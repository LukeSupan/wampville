<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>
<body>
  <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
  <p>You're now logged in.</p>
  <a href="logout.php">Logout</a>
</body>
</html>
