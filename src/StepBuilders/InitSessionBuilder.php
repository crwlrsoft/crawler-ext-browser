<?php

namespace Crwlr\CrawlerExtBrowser\StepBuilders;

use Crwlr\Crawler\Steps\StepInterface;
use Crwlr\Crawler\Steps\StepOutputType;
use Crwlr\CrawlerExtBrowser\Steps\InitSession;
use Crwlr\CrwlExtensionUtils\StepBuilder;
use Exception;

class InitSessionBuilder extends StepBuilder
{
    public function stepId(): string
    {
        return 'browser.initSession';
    }

    public function label(): string
    {
        return 'Initiates a session by making a headless browser request to a URL. After completing the request, ' .
            'it yields the same input URL as output, allowing it to be subsequently loaded using a simple HTTP ' .
            'client with session cookie headers obtained from the initial browser request.';
    }

    /**
     * @throws Exception
     */
    public function configToStep(array $stepConfig): StepInterface
    {
        return new InitSession();
    }

    public function configParams(): array
    {
        return [];
    }

    public function outputType(): StepOutputType
    {
        return StepOutputType::Scalar;
    }

    public function isLoadingStep(): bool
    {
        return true;
    }
}
