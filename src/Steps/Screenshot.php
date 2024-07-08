<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Loader\Http\Exceptions\LoadingException;
use Crwlr\Crawler\Loader\Http\Messages\RespondedRequest;
use Crwlr\Crawler\Steps\StepOutputType;
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

    protected ?int $browserTimeout = null;

    protected ?int $_previousBrowserTimeoutValue = null;

    protected bool $fromOpenPage = false;

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
     * @param (string|string[])[] $headers
     */
    public static function loadAndTake(string $storePath, array $headers = []): Screenshot
    {
        return new self($storePath, $headers);
    }

    /**
     * @param (string|string[])[] $headers
     */
    public static function take(string $storePath, array $headers = []): Screenshot
    {
        $step = new self($storePath, $headers);

        $step->fromOpenPage = true;

        return $step;
    }

    public function waitAfterPageLoaded(float $seconds): self
    {
        $this->waitAfterPageLoaded = $seconds;

        return $this;
    }

    public function timeout(float $seconds): self
    {
        $this->browserTimeout = (int) ($seconds * 1000);

        return $this;
    }

    public function outputType(): StepOutputType
    {
        return StepOutputType::AssociativeArrayOrObject;
    }

    /**
     * @return UriInterface|UriInterface[]|RespondedRequest
     */
    protected function validateAndSanitizeInput(mixed $input): mixed
    {
        if (!$this->fromOpenPage) {
            return parent::validateAndSanitizeInput($input);
        }

        if (!$input instanceof RespondedRequest) {
            throw new \InvalidArgumentException('The Screenshot::take() step needs an HTTP response as input.');
        }

        return $input;
    }

    /**
     * @param UriInterface|UriInterface[]|RespondedRequest $input
     * @return Generator<RespondedRequestWithScreenshot>
     * @throws Exception|InvalidArgumentException
     */
    protected function invoke(mixed $input): Generator
    {
        $this->switchBefore();

        if ($this->fromOpenPage && $input instanceof RespondedRequest) {
            yield from $this->fromOpenPage($input);
        } elseif ($input instanceof UriInterface || is_array($input)) {
            yield from $this->fromUris($input);
        }

        $this->switchAfterwards();
    }

    /**
     * @param UriInterface|UriInterface[] $input
     * @throws LoadingException|InvalidArgumentException|Exception
     */
    protected function fromUris(UriInterface|array $input): Generator
    {
        $input = !is_array($input) ? [$input] : $input;

        foreach ($input as $uri) {
            $response = $this->getResponseFromInputUri($uri);

            if ($response) {
                yield from $this->fromOpenPage($response);
            }
        }
    }

    /**
     * @throws InvalidArgumentException|Exception
     */
    protected function fromOpenPage(RespondedRequest $response): Generator
    {
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

    protected function switchBefore(): void
    {
        $this->_switchLoaderBefore();

        if ($this->browserTimeout !== null) {
            $this->_previousBrowserTimeoutValue = $this->loader->browser()->getTimeout();

            $this->loader->browser()->setTimeout($this->browserTimeout);
        }
    }

    protected function switchAfterwards(): void
    {
        $this->resetInputRequestParams();

        $this->_switchLoaderAfterwards();

        if ($this->_previousBrowserTimeoutValue !== null) {
            $this->loader->browser()->setTimeout($this->_previousBrowserTimeoutValue);

            $this->_previousBrowserTimeoutValue = null;
        }
    }

    protected function makeScreenshot(RespondedRequest $response): ?string
    {
        $page = $this->loader->browser()->getOpenPage();

        if ($page) {
            try {
                $fullStorePath = $this->fullStorePathFromResponse($response);

                $page->screenshot()->saveToFile($fullStorePath);

                $this->logger?->info('Took screenshot.');

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
