<?php

namespace Crwlr\CrawlerExtBrowser\Crawlers;

use Crwlr\Crawler\Crawler;
use Crwlr\Crawler\Loader\LoaderInterface;
use Crwlr\Crawler\UserAgents\UserAgentInterface;
use Crwlr\CrawlerExtBrowser\Crawlers\HeadlessBrowserCrawler\AnonymousHeadlessBrowserCrawlerBuilder;
use Crwlr\CrawlerExtBrowser\Loaders\HeadlessBrowserLoader;
use Psr\Log\LoggerInterface;

abstract class HeadlessBrowserCrawler extends Crawler
{
    /**
     * @return LoaderInterface|array<string, LoaderInterface>
     */
    protected function loader(UserAgentInterface $userAgent, LoggerInterface $logger): LoaderInterface|array
    {
        return new HeadlessBrowserLoader($userAgent, $logger);
    }

    public static function make(): AnonymousHeadlessBrowserCrawlerBuilder
    {
        return new AnonymousHeadlessBrowserCrawlerBuilder();
    }
}
