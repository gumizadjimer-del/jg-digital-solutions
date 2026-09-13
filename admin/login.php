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
                if ($_SESSION["role"] === "admin") {

                    header("Location: dashboard.php");

                } else {

                    header("Location: ../user/dashboard.php");

                }

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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | JG Digital Solutions</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">
    <div class="auth-container">

        <div class="auth-box">

            <h1>Login</h1>

            <p class="auth-subtitle">
                Login to your JG Digital Solutions account.
            </p>

            <?php if ($error !== ""): ?>

                <div class="form-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
                >

                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        required
                    >

                </div>

                <button type="submit" class="auth-button">
                    LOGIN
                </button>

            </form>

            <p class="auth-link">
                Don't have an account?
                <a href="register.php">Create an account</a>
            </p>

            <p class="auth-link">
                <a href="../index.php">Back to Website</a>
            </p>

        </div>

    </div>

</body>

</html>