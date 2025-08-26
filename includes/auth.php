<?php

if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('redirectIfNotLoggedIn')) {
    function redirectIfNotLoggedIn($redirect_url = 'login.php')
    {
        if (!isLoggedIn()) {
            header("Location: " . $redirect_url);
            exit();
        }
    }
}

if (!function_exists('redirectIfNotAdmin')) {
    function redirectIfNotAdmin($redirect_url = '../pages/login.php')
    {
        if (!isAdmin()) {
            header("Location: " . $redirect_url);
            exit();
        }
    }
}

if (!function_exists('logout')) {
    function logout()
    {
        session_unset();
        session_destroy();
        header("Location: ../pages/login.php");
        exit();
    }
}
?>