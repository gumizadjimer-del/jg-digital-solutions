<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";
require_once "../config/admin_auth.php";

requireAdmin();

// Count users
$result = $conn->query("SELECT COUNT(*) AS total FROM users");

if (!$result) {
    exit("Something went wrong. Please try again later.");
}

$userCount = $result->fetch_assoc()["total"];

// Count messages
$result = $conn->query("SELECT COUNT(*) AS total FROM messages");

if (!$result) {
    exit("Something went wrong. Please try again later.");
}

$messageCount = $result->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>

<body>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>


<p>You are successfully logged in.</p>

<p>
    Email:
    <?php echo htmlspecialchars($_SESSION["email"]); ?>
</p>

<p>
    Role:
    <?php echo htmlspecialchars($_SESSION["role"]); ?>
</p>

<hr>

<h2>Administration</h2>


<p>
    <a href="users.php">Manage Users</a>
</p>

<h2>Users</h2>

<p>
    Total Registered Users:
    <?php echo htmlspecialchars($userCount); ?>
</p>

<hr>
<h2>Messages</h2>

<p>
    Total Messages:
    <?php echo htmlspecialchars($messageCount); ?>
</p>

<a href="messages.php">View Messages</a>
<hr>



<a href="logout.php">Logout</a>

</body>

</html>