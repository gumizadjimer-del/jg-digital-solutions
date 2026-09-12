<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only admins can access this page
if ($_SESSION["role"] !== "admin") {
    echo "Access denied. Admin access only.";
    exit;
}

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

    $stmt->execute();

    $stmt->close();

    header("Location: messages.php");
    exit;
}

// Get all messages
$sql = "SELECT id, name, email, message, created_at
        FROM messages
        ORDER BY id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <title>Messages</title>

</head>

<body>

<h1>Contact Messages</h1>

<p>
    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
</p>

<a href="dashboard.php">Dashboard</a> |
<a href="users.php">Manage Users</a> |
<a href="logout.php">Logout</a>

<hr>

<h2>Customer Messages</h2>

<table border="1" cellpadding="10">

    <tr>

        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Message</th>
        <th>Date</th>
        <th>Action</th>

    </tr>

    <?php if ($result->num_rows > 0): ?>

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

                <td>
                    <?php echo htmlspecialchars($message["message"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($message["created_at"]); ?>
                </td>

                <td>

                    <form method="POST" style="display:inline;">

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
                            name="delete_message"
                            onclick="return confirm('Are you sure you want to delete this message?');"
                        >
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

        <?php endwhile; ?>

    <?php else: ?>

        <tr>

            <td colspan="5">
                No messages found.
            </td>

        </tr>

    <?php endif; ?>

</table>

</body>

</html>