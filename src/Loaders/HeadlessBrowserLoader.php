<?php

namespace Crwlr\CrawlerExtBrowser\Loaders;

use Crwlr\Crawler\Loader\Http\Exceptions\LoadingException;
use Crwlr\Crawler\Loader\Http\HeadlessBrowserLoaderHelper;
use Crwlr\Crawler\Loader\Http\HttpBaseLoader;
use Crwlr\Crawler\Loader\Http\Messages\RespondedRequest;
use Crwlr\Crawler\Loader\Http\Politeness\RetryErrorResponseHandler;
use Crwlr\Crawler\Loader\Http\Politeness\Throttler;
use Crwlr\Crawler\UserAgents\UserAgentInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class HeadlessBrowserLoader extends HttpBaseLoader
{
    public HeadlessBrowserLoaderHelper $browserHelper;

    public function __construct(
        UserAgentInterface $userAgent,
        ?LoggerInterface $logger = null,
        ?Throttler $throttler = null,
        RetryErrorResponseHandler $retryErrorResponseHandler = new RetryErrorResponseHandler(),
    ) {
        parent::__construct($userAgent, $logger, $throttler, $retryErrorResponseHandler);

        $this->browserHelper = new HeadlessBrowserLoaderHelper();
    }

    public function load(mixed $subject): mixed
    {
        return $this->handleLoad($subject, function (RequestInterface $request) {
            $proxy = $this->proxies?->getProxy() ?? null;

            return $this->browserHelper->navigateToPageAndGetRespondedRequest($request, $this->throttler, $proxy);
        });
    }

    /**
     * @throws LoadingException
     */
    public function loadOrFail(mixed $subject): RespondedRequest
    {
        return $this->handleLoadOrFail($subject, function (RequestInterface $request) {
            $proxy = $this->proxies?->getProxy() ?? null;

            return $this->browserHelper->navigateToPageAndGetRespondedRequest($request, $this->throttler, $proxy);
        });
    }
}
