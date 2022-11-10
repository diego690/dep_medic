<?php

if (!function_exists('dd')) {

    function dd()
    {
        echo var_export(func_get_args());
        die;
    }
}
