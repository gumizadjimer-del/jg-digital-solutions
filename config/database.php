<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "jg_digital";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {

    error_log(
        "Database connection failed: " .
        $conn->connect_error
    );

    exit("Something went wrong. Please try again later.");
}

$conn->set_charset("utf8mb4");

?>