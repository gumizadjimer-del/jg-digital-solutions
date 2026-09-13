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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit User | JG Digital Solutions</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

    <div class="auth-container">

        <div class="auth-box">

            <h1>Edit User</h1>

            <p class="auth-subtitle">
                Update the user's account information.
            </p>

            <?php if ($error !== ""): ?>
                <div class="form-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="form-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>


            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
                >

                <div class="form-group">

                    <label>Username</label>

                    <input
                        type="text"
                        name="username"
                        minlength="3"
                        maxlength="50"
                        value="<?php echo htmlspecialchars($user["username"]); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($user["email"]); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Role</label>

                    <select name="role" required>

                        <option
                            value="user"
                            <?php echo $user["role"] === "user" ? "selected" : ""; ?>
                        >
                            User
                        </option>

                        <option
                            value="admin"
                            <?php echo $user["role"] === "admin" ? "selected" : ""; ?>
                        >
                            Admin
                        </option>

                    </select>

                </div>


                <button type="submit" class="auth-button">
                    SAVE CHANGES
                </button>

            </form>


            <p class="auth-link">
                <a href="users.php">← Back to Users</a>
            </p>

            <p class="auth-link">
                <a href="dashboard.php">Back to Dashboard</a>
            </p>

        </div>

    </div>

</body>
</html>