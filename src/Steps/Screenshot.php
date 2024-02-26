<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Loader\Http\Messages\RespondedRequest;
use Crwlr\CrawlerExtBrowser\Aggregates\RespondedRequestWithScreenshot;
use Exception;
use Generator;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\FilesystemException;
use HeadlessChromium\Exception\ScreenshotFailed;
use Psr\Http\Message\UriInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class Screenshot extends BrowserBaseStep
{
    protected ?float $waitAfterPageLoaded = null;

    /**
     * @param (string|string[])[] $headers
     */
    public function __construct(
        private readonly string $storePath,
        array $headers = [],
    ) {
        parent::__construct($headers);
    }

    /**
     * @param string $storePath
     * @param (string|string[])[] $headers
     * @return Screenshot
     */
    public static function loadAndTake(string $storePath, array $headers = []): Screenshot
    {
        return new self($storePath, $headers);
    }

    public function waitAfterPageLoaded(float $seconds): self
    {
        $this->waitAfterPageLoaded = $seconds;

        return $this;
    }

    /**
     * @param UriInterface|UriInterface[] $input
     * @return Generator<RespondedRequestWithScreenshot>
     * @throws Exception|InvalidArgumentException
     */
    protected function invoke(mixed $input): Generator
    {
        $this->_switchLoaderBefore();

        $input = !is_array($input) ? [$input] : $input;

        foreach ($input as $uri) {
            $response = $this->getResponseFromInputUri($uri);

            if ($response) {
                if (!$response instanceof RespondedRequestWithScreenshot) {
                    if ($this->waitAfterPageLoaded) {
                        usleep((int) $this->waitAfterPageLoaded * 1000000);
                    }

                    $screenshotPath = $this->makeScreenshot($response);

                    if (is_string($screenshotPath)) {
                        $response = RespondedRequestWithScreenshot::fromRespondedRequest($response, $screenshotPath);

                        $this->loader->addToCache($response);
                    } else {
                        return;
                    }
                }

                yield $response;
            }
        }

        $this->resetInputRequestParams();

        $this->_switchLoaderAfterwards();
    }

    protected function makeScreenshot(RespondedRequest $response): ?string
    {
        $page = $this->loader->browserHelper()->getOpenPage();

        if ($page) {
            try {
                $fullStorePath = $this->fullStorePathFromResponse($response);

                $page->screenshot()->saveToFile($fullStorePath);

                return $fullStorePath;
            } catch (CommunicationException $exception) {
                $this->logger?->error('Failed to take screenshot: ' . $exception->getMessage());
            } catch (ScreenshotFailed $exception) {
                $this->logger?->error($exception->getMessage());
            } catch (FilesystemException) {
                $this->logger?->error('Failed to save the screenshot on the filesystem.');
            } catch (Throwable) {
                $this->logger?->error('Failed to take screenshot.');
            }
        } else {
            $this->logger?->error('Failed to get open page from headless browser to make screenshot');
        }

        return null;
    }

    protected function validateAndSanitizeInput(mixed $input): mixed
    {
        return $this->validateAndSanitizeToUriInterface($input);
    }

    protected function fullStorePathFromResponse(RespondedRequest $response): string
    {
        $fileName = $response->cacheKey() . '-' . time() . '.png';

        return $this->fullStorePath($fileName);
    }

    protected function fullStorePath(string $fileName): string
    {
        return $this->storePath . (!str_ends_with($this->storePath, '/') ? '/' : '') . $fileName;
    }
}
