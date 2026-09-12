<?php

// Create a CSRF token
function getCsrfToken()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}


// Check a submitted CSRF token
function verifyCsrfToken($token)
{
    return isset($_SESSION["csrf_token"]) &&
           hash_equals($_SESSION["csrf_token"], $token);
}

?>