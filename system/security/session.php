<?php

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Check if authenticated
 */
if (!is_logged()) {
    header('Location: /' . BASE_URL . 'login');
    return;
}

/**
 * Prevent Session Hijack
 */
if ($_SESSION['dep_ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    header('Location: /' . BASE_URL . 'logout');
    return;
}
