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

$service_request_result = $conn->query(
    "SELECT COUNT(*) AS total FROM service_requests"
);

$service_request_count = 0;

if ($service_request_result) {

    $service_request_data = $service_request_result->fetch_assoc();

    $service_request_count = $service_request_data["total"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | JG Digital Solutions</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="dashboard-page">

    <header class="admin-header">

    <div class="admin-header-content">

        <img src="../image/Asset 1.png" class="logo" alt="JG Digital Solutions Logo">

        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="messages.php">Messages</a>
            <a href="service_requests.php"> Service Requests</a>
            <a href="logout.php">Logout</a>
        </nav>

    </div>

</header>


    <main class="dashboard-container">

        <div class="dashboard-heading">
            <h1>Admin Dashboard</h1>

            <p>
                Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>.
            </p>
        </div>


        <div class="dashboard-cards">

            <div class="dashboard-card">

                <h2>Users</h2>

                <div class="dashboard-number">
                    <?php echo $userCount; ?>
                </div>

                <p>Registered users</p>

                <a href="users.php" class="dashboard-button">
                    MANAGE USERS
                </a>

            </div>


            <div class="dashboard-card">

                <h2>Messages</h2>

                <div class="dashboard-number">
                    <?php echo $messageCount; ?>
                </div>

                <p>Contact messages</p>

                <a href="messages.php" class="dashboard-button">
                    VIEW MESSAGES
                </a>

            </div>

            <div class="dashboard-card">

                <h2>Service Requests</h2>

                <div class="dashboard-number">
                    <?php echo $service_request_count; ?>
                </div>

                <p>
                    Service requests submitted by users.
                </p>

                <a href="service_requests.php" class="dashboard-button">
                    VIEW REQUESTS
                </a>

            </div>

        </div>


    </main>

</body>
</html>