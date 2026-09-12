<?php

require_once "../config/database.php";
require_once "../config/security.php";

$csrfToken = getCsrfToken();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!verifyCsrfToken($token)) {

        $error = "Invalid security token.";

    } else {

        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

    // Validation
    if ($username === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    elseif (strlen($username) < 3) {
    $error = "Username must be at least 3 characters.";
    }
    elseif (strlen($username) > 50) {
        $error = "Username is too long.";
    }
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    }
    else {

        // Check if username or email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        $check->bind_param("ss", $username, $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Username or email already exists.";

        } else {

            // Hash password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $username,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {
                $success = "Account created successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }

            $stmt->close();
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
    <title>Register</title>
</head>

<body>

<h1>Create Account</h1>

<?php if ($error !== ""): ?>
    <p><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($success !== ""): ?>
    <p><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="POST">

 <input
        type="hidden"
        name="csrf_token"
        value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
    >

    <label>Username</label>
    <input type="text" name="username" minlength="3" maxlength="50" required>

    <br><br>

    <label>Email</label>
    <input type="email" name="email" required>

    <br><br>

    <label>Password</label>
    <input type="password" name="password" minlenght="8" required>

    <br><br>

    <button type="submit">Register</button>

</form>

<a href="login.php">Already have an account? Login</a>

</body>

</html>