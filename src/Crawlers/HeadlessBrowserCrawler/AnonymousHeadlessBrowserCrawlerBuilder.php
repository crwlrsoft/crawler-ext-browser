<?php

namespace Crwlr\CrawlerExtBrowser\Crawlers\HeadlessBrowserCrawler;

use Crwlr\Crawler\UserAgents\BotUserAgent;
use Crwlr\Crawler\UserAgents\UserAgent;
use Crwlr\Crawler\UserAgents\UserAgentInterface;
use Crwlr\CrawlerExtBrowser\Crawlers\HeadlessBrowserCrawler;

class AnonymousHeadlessBrowserCrawlerBuilder
{
    public function __construct() {}

    public function withBotUserAgent(string $productToken): HeadlessBrowserCrawler
    {
        return new class ($productToken) extends HeadlessBrowserCrawler {
            public function __construct(private readonly string $_botUserAgentProductToken)
            {
                parent::__construct();
            }

            protected function userAgent(): UserAgentInterface
            {
                return new BotUserAgent($this->_botUserAgentProductToken);
            }
        };
    }

    public function withUserAgent(string $userAgent): HeadlessBrowserCrawler
    {
        return new class ($userAgent) extends HeadlessBrowserCrawler {
            public function __construct(private readonly string $_userAgentString)
            {
                parent::__construct();
            }

            protected function userAgent(): UserAgentInterface
            {
                return new UserAgent($this->_userAgentString);
            }
        };
    }
}
