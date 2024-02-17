<?php

use Crwlr\Crawler\Cache\FileCache;
use Crwlr\Crawler\HttpCrawler;
use Crwlr\Crawler\Loader\Http\HttpLoader;
use Crwlr\Crawler\Loader\Http\Politeness\TimingUnits\MultipleOf;
use Crwlr\Crawler\Loader\LoaderInterface;
use Crwlr\Crawler\UserAgents\UserAgent;
use Crwlr\Crawler\UserAgents\UserAgentInterface;
use Crwlr\Utils\Microseconds;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class TestServerProcess
{
    public static ?Process $process = null;
}

uses()
    ->group('integration')
    ->beforeEach(function () {
        if (!isset(TestServerProcess::$process)) {
            TestServerProcess::$process = Process::fromShellCommandline(
                'php -S localhost:8000 ' . __DIR__ . '/_Integration/Server.php'
            );

            TestServerProcess::$process->start();

            usleep(100000);
        }
    })
    ->afterAll(function () {
        TestServerProcess::$process?->stop(3, SIGINT);

        TestServerProcess::$process = null;
    })
    ->in('_Integration');

function helper_getFastLoader(UserAgentInterface $userAgent, ?LoggerInterface $logger = null): HttpLoader
{
    $loader = new HttpLoader($userAgent, logger: $logger);

    $loader->throttle()
        ->waitBetween(new MultipleOf(0.0001), new MultipleOf(0.0002))
        ->waitAtLeast(Microseconds::fromSeconds(0.0001));

    return $loader;
}

function helper_getFastCrawler(): HttpCrawler
{
    return new class () extends HttpCrawler {
        protected function userAgent(): UserAgentInterface
        {
            return new UserAgent(
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) ' .
                'Chrome/120.0.0.0 Safari/537.36'
            );
        }

        protected function loader(UserAgentInterface $userAgent, LoggerInterface $logger): LoaderInterface|array
        {
            return helper_getFastLoader($userAgent, $logger);
        }
    };
}

function helper_getFastCrawlerWithCache(): HttpCrawler
{
    $crawler = helper_getFastCrawler();

    $loader = $crawler->getLoader();

    /** @var HttpLoader $loader */

    $loader->setCache(new FileCache(__DIR__ . '/_cache'));

    return $crawler;
}

function helper_testFilePath(string $inPath = ''): string
{
    $basePath = __DIR__ . '/_files';

    if (!empty($inPath)) {
        return $basePath . (!str_starts_with($inPath, '/') ? '/' : '') . $inPath;
    }

    return $basePath;
}

function helper_testCachePath(string $inPath = ''): string
{
    $basePath = __DIR__ . '/_cache';

    if (!empty($inPath)) {
        return $basePath . (!str_starts_with($inPath, '/') ? '/' : '') . $inPath;
    }

    return $basePath;
}

function helper_cleanFiles(): void
{
    $scanDir = scandir(helper_testFilePath());

    if (is_array($scanDir)) {
        foreach ($scanDir as $file) {
            if ($file === '.' || $file === '..' || $file === 'demo-screenshot.png') {
                continue;
            }

            unlink(helper_testFilePath() . '/' . $file);
        }
    }
}

function helper_cleanCache(): void
{
    $scanDir = scandir(helper_testCachePath());

    if (is_array($scanDir)) {
        foreach ($scanDir as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }

            unlink(helper_testCachePath() . '/' . $file);
        }
    }
}

function helper_dump(mixed $var): void
{
    error_log(var_export($var, true));
}

function helper_dieDump(mixed $var): void
{
    error_log(var_export($var, true));

    exit;
}
