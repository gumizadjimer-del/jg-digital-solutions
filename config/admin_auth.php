<?php

function requireLogin()
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../admin/login.php");
        exit;
    }
}

function requireAdmin()
{
    requireLogin();

    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        http_response_code(403);
        exit("Access denied.");
    }
}