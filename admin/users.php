<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";

// Only admins can access this page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    echo "Access denied. Admin access only.";
    exit;
}

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

    $stmt->execute();

    $stmt->close();

    header("Location: users.php");
    exit;
}

// Get all users
$sql = "SELECT id, username, email, role, created_at
        FROM users
        ORDER BY id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Manage Users</title>

</head>

<body>

<h1>Manage Users</h1>

<p>
    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
</p>

<a href="dashboard.php">Dashboard</a> |
<a href="logout.php">Logout</a>

<hr>

<h2>Registered Users</h2>

<table border="1" cellpadding="10">

    <tr>

        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Registered</th>
        <th>Action</th>

    </tr>

    <?php if ($result->num_rows > 0): ?>

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
                    <?php echo htmlspecialchars($user["role"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($user["created_at"]); ?>
                </td>

                <td>

                    <a href="edit_user.php?id=<?php echo $user["id"]; ?>">
                        Edit
                    </a>

                    |

                    <?php if ($user["id"] != $_SESSION["user_id"]): ?>

                        <form method="POST" style="display:inline;">

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
                                name="delete_user"
                                onclick="return confirm('Are you sure you want to delete this user?');"
                            >
                                Delete
                            </button>

                        </form>

                    <?php else: ?>

                        Current Admin

                    <?php endif; ?>

                </td>

            </tr>

        <?php endwhile; ?>

    <?php else: ?>

        <tr>

            <td colspan="5">
                No users found.
            </td>

        </tr>

    <?php endif; ?>

</table>

</body>

</html>