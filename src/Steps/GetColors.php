<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Crwlr\Crawler\Steps\Step;
use Crwlr\CrawlerExtBrowser\Aggregates\RespondedRequestWithScreenshot;
use Crwlr\CrawlerExtBrowser\Utils\ImageColors;
use Exception;
use Generator;

class GetColors extends Step
{
    protected ?float $onlyAbovePercentageOfImage = null;

    public static function fromImage(): self
    {
        return new self();
    }

    public function onlyAbovePercentageOfImage(float $percentage): self
    {
        $this->onlyAbovePercentageOfImage = $percentage;

        return $this;
    }

    /**
     * @param string $input
     * @return Generator
     */
    protected function invoke(mixed $input): Generator
    {
        try {
            yield ['colors' => ImageColors::getFrom($input, $this->onlyAbovePercentageOfImage)];
        } catch (Exception $exception) {
            $this->logger?->error('Failed to get colors from image: ' . $exception->getMessage());
        }
    }

    protected function validateAndSanitizeInput(mixed $input): mixed
    {
        if (is_array($input) && array_key_exists('screenshotPath', $input)) {
            $input = $input['screenshotPath'];
        } elseif ($input instanceof RespondedRequestWithScreenshot) {
            $input = $input->screenshotPath;
        }

        return $this->validateAndSanitizeStringOrStringable($input);
    }
}
