<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";

$csrfToken = getCsrfToken();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!verifyCsrfToken($token)) {

        $error = "Invalid security token.";

    } else {

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

    // Validation
    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Find user
        $stmt = $conn->prepare(
            "SELECT id, username, email, password, role
            FROM users
            WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Check password
            if (password_verify($password, $user["password"])) {

            session_regenerate_id(true);

                // Create session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];
                

                // Login successful
                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Incorrect email or password.";
            }

        } else {

            $error = "Incorrect email or password.";
        }

        $stmt->close();
    }
}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>

<h1>Login</h1>

<?php if ($error !== ""): ?>

    <p><?php echo htmlspecialchars($error); ?></p>

<?php endif; ?>

<form method="POST">

    <input
    type="hidden"
    name="csrf_token"
    value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
>

    <label>Email</label>
    <br>

    <input
        type="email"
        name="email"
        required
    >

    <br><br>

    <label>Password</label>
    <br>

    <input
        type="password"
        name="password"
        required
    >

    <br><br>

    <button type="submit">Login</button>

</form>

<br>

<a href="register.php">Create an account</a>

</body>

</html>