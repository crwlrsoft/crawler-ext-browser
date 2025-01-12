<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Cache\Exceptions\MissingZlibExtensionException;
use Crwlr\Crawler\Loader\Http\Messages\RespondedRequest;
use Crwlr\Crawler\Steps\Loading\Http;
use Crwlr\Crawler\Steps\StepOutputType;
use Exception;
use Generator;
use GuzzleHttp\Psr7\Utils;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\CommunicationException\ResponseHasError;
use HeadlessChromium\Exception\JavascriptException;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Throwable;

class InfiniteScrolling extends BrowserBaseStep
{
    protected bool $useOpenedPage = false;

    protected bool $failOnScrollDistanceZero = true;

    protected int $maxRetries = 2;

    public function dontFailOnScrollDistanceZero(): static
    {
        $this->failOnScrollDistanceZero = false;

        return $this;
    }

    public function maxRetries(int $maxRetries): static
    {
        $this->maxRetries = $maxRetries;

        return $this;
    }

    public function outputType(): StepOutputType
    {
        return StepOutputType::AssociativeArrayOrObject;
    }

    public function useOpenedPage(): static
    {
        $this->useOpenedPage = true;

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function invoke(mixed $input): Generator
    {
        $this->_switchLoaderBefore();

        if ($this->useOpenedPage && $input instanceof RespondedRequest) {
            yield from $this->scrollDownOpenedPage($input);
        } elseif ($input instanceof UriInterface || is_array($input)) {
            yield from $this->fromUris($input);
        }

        $this->_switchLoaderAfterwards();
    }

    public function scrollDownOpenedPage(RespondedRequest $response): Generator
    {
        for ($i = 0; $i <= $this->maxRetries; $i++) {
            try {
                yield from $this->scrollDownUntilTheEnd($response);
            } catch (Throwable $exception) {
                if ($i === $this->maxRetries) {
                    $this->logger?->error('Failed to scroll down until the end. ' . $exception->getMessage());
                } else {
                    $this->logger?->warning('Failed to scroll down until the end. Retry. ' . $exception->getMessage());
                }
            }
        }
    }

    /**
     * @param UriInterface|UriInterface[] $input
     */
    protected function fromUris(UriInterface|array $input): Generator
    {
        $input = !is_array($input) ? [$input] : $input;

        foreach ($input as $uri) {
            yield from $this->loadAndScrollDownUntilTheEnd($uri);
        }
    }

    /**
     * @return UriInterface|UriInterface[]|RespondedRequest
     * @throws InvalidArgumentException
     */
    protected function validateAndSanitizeInput(mixed $input): mixed
    {
        if (!$this->useOpenedPage) {
            return parent::validateAndSanitizeInput($input);
        }

        if (!$input instanceof RespondedRequest) {
            throw new InvalidArgumentException(
                'The InfiniteScrolling step needs an HTTP response as input when useOpenedPage() is called.',
            );
        }

        return $input;
    }

    protected function loadAndScrollDownUntilTheEnd(UriInterface $uri): Generator
    {
        for ($i = 0; $i <= $this->maxRetries; $i++) {
            try {
                $response = $this->getResponseFromInputUri($uri);

                if ($response) {
                    yield from $this->scrollDownUntilTheEnd($response);

                    break;
                }
            } catch (Throwable $exception) {
                if ($i === $this->maxRetries) {
                    $this->logger?->error(
                        'Failed to load and scroll down until the end. ' . $exception->getMessage(),
                    );
                } else {
                    $this->logger?->warning(
                        'Failed to load and scroll down until the end. Retry. ' . $exception->getMessage(),
                    );
                }

                try {
                    $this->getLoader()->browser()->getOpenPage()?->close();

                    $this->getLoader()->browser()->closeBrowser();
                } catch (Throwable) {
                }
            }
        }

    }

    /**
     * @throws CommunicationException
     * @throws NoResponseAvailable
     * @throws MissingZlibExtensionException
     * @throws JavascriptException
     * @throws OperationTimedOut
     * @throws Exception
     */
    protected function scrollDownUntilTheEnd(RespondedRequest $response): Generator
    {
        $html = Http::getBodyString($response);

        $page = $this->getLoader()->browser()->getOpenPage();

        if ($page) {
            $distance = $this->waitAndGetMaxScrollingDistance($page);

            if ($distance === 0) { // Retry once
                $distance = $this->waitAndGetMaxScrollingDistance($page);
            }

            if ($distance > 0) {
                $scrollingEvents = 0;

                while ($distance > 0 && $scrollingEvents < 1_000) {
                    // This is a temporary fix for an issue that I've created a PR to chrome-php/chrome:
                    // https://github.com/chrome-php/chrome/pull/678
                    try {
                        $page->mouse()->scrollDown($distance);
                    } catch (Exception $exception) {
                        // Probably something changed since getting the max distance.
                        $distance = $this->waitAndGetMaxScrollingDistance($page);

                        if ($distance === 0) {
                            break;
                        }

                        throw $exception;
                    }

                    $distance = $this->waitAndGetMaxScrollingDistance($page);

                    $scrollingEvents++;
                }

                $this->logger?->info('The end of the page is reached.');
            } else {
                $message = 'Scrolling down failed. Couldn’t scroll down even once.';

                if ($this->failOnScrollDistanceZero) {
                    throw new Exception($message);
                } else {
                    $this->logger?->warning($message);
                }
            }

            $html = $page->getHtml($this->getLoader()->browser()->getTimeout());
        }

        $response->response = $response->response->withBody(Utils::streamFor($html));

        yield $response;
    }

    /**
     * @throws OperationTimedOut
     * @throws CommunicationException
     * @throws NoResponseAvailable
     * @throws ResponseHasError
     */
    private function waitAndGetMaxScrollingDistance(Page $page): int
    {
        $distance = $this->getMaxYScrollingDistance($page);

        if ($distance > 0) {
            return $distance;
        }

        for ($i = 1; ($i * 50_000) <= 1_000_000; $i++) {
            usleep(50_000);

            $distance = $this->getMaxYScrollingDistance($page);

            if ($distance > 0) {
                return $distance;
            }
        }

        return 0;
    }

    /**
     * @throws CommunicationException
     * @throws CommunicationException\ResponseHasError
     * @throws NoResponseAvailable
     * @throws OperationTimedOut
     */
    private function getMaxYScrollingDistance(Page $page): int
    {
        $scrollableArea = $page->getLayoutMetrics()->getCssContentSize();

        $visibleArea = $page->getLayoutMetrics()->getCssVisualViewport();

        $maximumY = $scrollableArea['height'] - $visibleArea['clientHeight'];

        return (int) $maximumY - (int) $visibleArea['pageY'];
    }
}
