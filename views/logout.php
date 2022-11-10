<?php

require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");

// destroy current session, if have
if (is_logged()) {
    logout();
}

// redirect to login page
header('Location: login');
