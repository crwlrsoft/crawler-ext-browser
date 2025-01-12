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

if (str_starts_with($route, '/init_session')) {
    $cookie = $_COOKIE['session'] ?? null;

    if ($cookie) {
        return include(__DIR__ . '/_Server/PrintCookie.php');
    }

    $redirectParam = $_GET['redirect'] ?? null;

    if (!$redirectParam) {
        http_response_code(403);
    }

    return include(__DIR__ . '/_Server/InitSessionRedirect.php');
}

if (str_starts_with($route, '/infinite-scrolling')) {
    if ($_GET['lowHeight'] === '1') {
        echo <<<HTML
            <!Doctype html>
            <html lang="en">
            <head></head>
            <body>Hey</body>
            </html>
            HTML;
        return;
    }

    return include(__DIR__ . '/_Server/InfiniteScrolling.php');
}
