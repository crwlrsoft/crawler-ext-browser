<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Loader\Http\HttpLoader;
use Crwlr\Crawler\Steps\Loading\HttpBase;
use Crwlr\CrawlerExtBrowser\Exceptions\InvalidStepConfiguration;
use Exception;

/**
 * @method HttpLoader getLoader()
 */

abstract class BrowserBaseStep extends HttpBase
{
    protected HttpLoader $loader;

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
        if (!$this->getLoader()->usesHeadlessBrowser()) {
            $this->getLoader()->useHeadlessBrowser();

            $this->_switchBackToHttpClientLoaderAfterwards = true;
        }
    }

    /**
     * @throws Exception
     */
    protected function _switchLoaderAfterwards(): void
    {
        if ($this->_switchBackToHttpClientLoaderAfterwards) {
            $this->getLoader()->useHttpClient();

            $this->_switchBackToHttpClientLoaderAfterwards = false;
        }
    }
}
