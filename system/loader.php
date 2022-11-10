<?php

include_once __DIR__ . "/global/config.php";

include_once __DIR__ . "/helpers/data.php";

include_once __DIR__ . "/helpers/dump.php";

include_once __DIR__ . "/helpers/session.php";

require __DIR__ . '/../vendor/autoload.php';

require_once(__DIR__ . "/../data/mysql/panel.functions.php");

date_default_timezone_set('America/New_York');
setlocale(LC_TIME, "es_ec");

function gen_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

function getCurrentTimestamp()
{
    $panelFunctions = new PanelFunctions();
    return $panelFunctions->getCurrentTimestamp();
}

function getMyArea()
{
    $panelFunctions = new PanelFunctions();
    return $panelFunctions->getMyArea();
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
