<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('mobile_redirect')) {
    function mobile_redirect($route)
    {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        header('Location: ' . $basePath . '/index.php/' . ltrim($route, '/'), TRUE, 302);
        exit;
    }
}
