<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Loader\Http\HttpLoader;
use Crwlr\Crawler\Loader\LoaderInterface;
use Crwlr\Crawler\Steps\Loading\HttpBase;
use Crwlr\CrawlerExtBrowser\Exceptions\InvalidStepConfiguration;

abstract class BrowserBaseStep extends HttpBase
{
    /**
     * @var HttpLoader
     */
    protected LoaderInterface $loader;

    protected bool $_switchBackToHttpClientLoaderAfterwards = false;

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

    protected function _switchLoaderBefore(): void
    {
        if (!$this->loader->usesHeadlessBrowser()) {
            $this->loader->useHeadlessBrowser();

            $this->_switchBackToHttpClientLoaderAfterwards = true;
        }
    }

    protected function _switchLoaderAfterwards(): void
    {
        if ($this->_switchBackToHttpClientLoaderAfterwards) {
            $this->loader->useHttpClient();

            $this->_switchBackToHttpClientLoaderAfterwards = false;
        }
    }
}
