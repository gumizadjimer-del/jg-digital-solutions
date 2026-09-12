<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";
require_once "../config/admin_auth.php";

requireAdmin();

// Check if user ID exists
if (!isset($_GET["id"])) {
    header("Location: users.php");
    exit;
}

$id = intval($_GET["id"]);

$error = "";
$success = "";

// Get user information
$stmt = $conn->prepare(
    "SELECT id, username, email, role
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    http_response_code(404);
    exit("User not found.");
}

$user = $result->fetch_assoc();

$stmt->close();


// Update user
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!verifyCsrfToken($token)) {

        $error = "Invalid security token.";

    } else {

        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $role = $_POST["role"] ?? "";

    // Validation
    if ($username === "" || $email === "" || $role === "") {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif ($role !== "user" && $role !== "admin") {

        $error = "Invalid role.";

    } else {

        // Check if username or email belongs to another user
        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE (username = ? OR email = ?)
             AND id != ?"
        );

        $check->bind_param("ssi", $username, $email, $id);
        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            $error = "Username or email is already being used.";

        } else {

            // Update user
            $update = $conn->prepare(
                "UPDATE users
                 SET username = ?, email = ?, role = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "sssi",
                $username,
                $email,
                $role,
                $id
            );

            if ($update->execute()) {

                $success = "User updated successfully.";

                // Update displayed information
                $user["username"] = $username;
                $user["email"] = $email;
                $user["role"] = $role;

                // If admin edited their own account,
                // update their current session too.
                if ($id == $_SESSION["user_id"]) {

                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;
                    $_SESSION["role"] = $role;
                }

            } else {

                $error = "Something went wrong. Please try again.";
            }

            $update->close();
        }

        $check->close();
    }
}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Edit User</title>

</head>

<body>

<h1>Edit User</h1>

<a href="dashboard.php">Dashboard</a> |
<a href="users.php">Manage Users</a> |
<a href="logout.php">Logout</a>

<hr>

<?php if ($error !== ""): ?>

    <p>
        <?php echo htmlspecialchars($error); ?>
    </p>

<?php endif; ?>


<?php if ($success !== ""): ?>

    <p>
        <?php echo htmlspecialchars($success); ?>
    </p>

<?php endif; ?>


<form method="POST">

    <input
    type="hidden"
    name="csrf_token"
    value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
>

    <label>Username</label>

    <br>

    <input
        type="text"
        name="username"
        value="<?php echo htmlspecialchars($user["username"]); ?>"
        required
    >

    <br><br>


    <label>Email</label>

    <br>

    <input
        type="email"
        name="email"
        value="<?php echo htmlspecialchars($user["email"]); ?>"
        required
    >

    <br><br>


    <label>Role</label>

    <br>

    <select name="role" required>

        <option
            value="user"
            <?php if ($user["role"] === "user") echo "selected"; ?>
        >
            User
        </option>

        <option
            value="admin"
            <?php if ($user["role"] === "admin") echo "selected"; ?>
        >
            admin
        </option>

    </select>

    <br><br>


    <button type="submit">
        Update User
    </button>

</form>

</body>

</html>