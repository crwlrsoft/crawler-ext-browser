<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Loader\LoaderInterface;
use Crwlr\Crawler\Steps\Loading\HttpBase;
use Crwlr\CrawlerExtBrowser\Exceptions\InvalidStepConfiguration;
use Crwlr\CrawlerExtBrowser\Loaders\HeadlessBrowserLoader;

abstract class BrowserBaseStep extends HttpBase
{
    /**
     * @var HeadlessBrowserLoader
     */
    protected LoaderInterface $loader;

    /**
     * @param (string|string[])[] $headers
     */
    public function __construct(array $headers = [])
    {
        parent::__construct('GET', $headers);
    }

    /**
     * @throws InvalidStepConfiguration
     */
    public function useInputKeyAsBody(string $key): static
    {
        throw new InvalidStepConfiguration('Steps utilizing a headless browser can\'t send an HTTP body.');
    }
}
