<?php

session_start();

require_once "../config/database.php";


// Check if user is logged in
if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {

    echo "Access denied. Admin access only.";
    exit;
}

$result = $conn->query("SELECT COUNT(*) AS total FROM users");

$userCount = $result->fetch_assoc()["total"];

// Count messages
$result = $conn->query("SELECT COUNT(*) AS total FROM messages");

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