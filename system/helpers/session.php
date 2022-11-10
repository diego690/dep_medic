<?php

if (!isset($_SESSION)) {
    session_start();
}

if (!function_exists('is_logged')) {

    function is_logged()
    {
        return isset($_SESSION) && isset($_SESSION['dep_logged_in']) && $_SESSION['dep_logged_in'];
    }
}

if (!function_exists('logout')) {

    function logout()
    {
        session_destroy();
    }
}
