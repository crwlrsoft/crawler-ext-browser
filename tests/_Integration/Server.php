<?php

$route = $_SERVER['REQUEST_URI'];

if ($route === '/screenshot') {
    return include(__DIR__ . '/_Server/Screenshot.php');
}

if ($route === '/screenshot-wait') {
    return include(__DIR__ . '/_Server/ScreenshotWait.php');
}
