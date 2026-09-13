<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";
require_once "../config/admin_auth.php";

requireAdmin();


// Delete user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {

    $id = intval($_POST["user_id"] ?? 0);
    $token = $_POST["csrf_token"] ?? "";

    // Check CSRF token
    if (!verifyCsrfToken($token)) {
        echo "Invalid security token.";
        exit;
    }

    // Prevent admin from deleting their own account
    if ($id == $_SESSION["user_id"]) {
        echo "You cannot delete your own account.";
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM users WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

    $stmt->close();

    header("Location: users.php");
    exit;

        } else {

            $stmt->close();

            exit("Something went wrong. Please try again later.");
        }
}

// Get all users
$sql = "SELECT id, username, email, role, created_at
        FROM users
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

    <title>Manage Users | JG Digital Solutions</title>

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

            <h1>Manage Users</h1>

            <p>
                View and manage registered users.
            </p>

        </div>


        <div class="table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($user = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($user["id"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["username"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["email"]); ?>
                            </td>

                            <td>

                                <span class="role-badge
                                    <?php echo $user["role"] === "admin"
                                        ? "role-admin"
                                        : "role-user"; ?>">

                                    <?php echo htmlspecialchars($user["role"]); ?>

                                </span>

                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["created_at"]); ?>
                            </td>

                            <td class="action-buttons">

                                <a
                                    href="edit_user.php?id=<?php echo $user["id"]; ?>"
                                    class="edit-button"
                                >
                                    EDIT
                                </a>


                                <?php if ($user["id"] != $_SESSION["user_id"]): ?>

                                    <form method="POST" class="delete-form"
                                          onsubmit="return confirm('Are you sure you want to delete this user?');">

                                        <input
                                            type="hidden"
                                            name="delete_user"
                                            value="1"
                                        >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?php echo $user["id"]; ?>"
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

                                <?php endif; ?>

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