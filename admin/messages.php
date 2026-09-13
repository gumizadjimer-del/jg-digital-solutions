<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";
require_once "../config/admin_auth.php";

requireAdmin();

// Check if user is logged in


// Delete message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_message"])) {

    $id = intval($_POST["message_id"] ?? 0);
    $token = $_POST["csrf_token"] ?? "";

    // Check CSRF token
    if (!verifyCsrfToken($token)) {
        echo "Invalid security token.";
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM messages WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

    $stmt->close();

    header("Location: messages.php");
    exit;

        } else {

            $stmt->close();

            exit("Something went wrong. Please try again later.");
        }
}

// Get all messages
$sql = "SELECT id, name, email, message, created_at
        FROM messages
        ORDER BY id DESC";

$result = $conn->query($sql);

if (!$result) {
    exit("Something went wrong. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Messages | JG Digital Solutions</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">

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


    <main class="admin-container">

        <div class="admin-heading">

            <h1>Contact Messages</h1>

            <p>
                View messages submitted through the website.
            </p>

        </div>


        <div class="table-container">

            <table class="admin-table messages-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($message = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($message["id"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($message["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($message["email"]); ?>
                            </td>

                            <td class="message-cell">
                                <?php echo nl2br(htmlspecialchars($message["message"])); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($message["created_at"]); ?>
                            </td>

                            <td>

                                <form
                                    method="POST"
                                    class="delete-form"
                                    onsubmit="return confirm('Are you sure you want to delete this message?');"
                                >

                                    <input
                                        type="hidden"
                                        name="delete_message"
                                        value="1"
                                    >

                                    <input
                                        type="hidden"
                                        name="message_id"
                                        value="<?php echo $message["id"]; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="delete-button"
                                    >
                                        DELETE
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>


        <a href="dashboard.php" class="back-button">
            ← Back to Dashboard
        </a>

    </main>

</body>
</html>