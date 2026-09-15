<?php

session_start();

require_once "config/database.php";
require_once "config/security.php";

$csrfToken = getCsrfToken();


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$token = $_POST["csrf_token"] ?? "";

if (!verifyCsrfToken($token)) {
    die("Invalid security token.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$message = trim($_POST["message"] ?? "");

// Server-side validation
if ($name === "" || $email === "" || $message === "") {
    die("Please fill in all required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}

// Prepared statement
$sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Something went wrong. Please try again later.");
}

$stmt->bind_param("sss", $name, $email, $message);

if ($stmt->execute()) {
    echo "Thank you! Your message has been submitted.";
} else {
    echo "Something went wrong. Please try again later.";
}

$stmt->close();
$conn->close();

?>