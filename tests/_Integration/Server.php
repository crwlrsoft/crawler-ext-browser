<?php

$route = $_SERVER['REQUEST_URI'];

if ($route === '/screenshot') {
    return include(__DIR__ . '/_Server/Screenshot.php');
}

if ($route === '/screenshot-wait') {
    return include(__DIR__ . '/_Server/ScreenshotWait.php');
}

if ($route === '/print-headers') {
    return include(__DIR__ . '/_Server/PrintHeaders.php');
}

if ($route === '/timeout') {
    sleep(1);

    echo 'Hi';

    return;
}

if (str_starts_with($route, '/crawl-screenshot')) {
    $split = explode('/crawl-screenshot/', $route);

    if (count($split) === 1) {
        $page = '0';
    } else {
        $page = $split[1];
    }

    return include(__DIR__ . '/_Server/CrawlScreenshot.php');
}
