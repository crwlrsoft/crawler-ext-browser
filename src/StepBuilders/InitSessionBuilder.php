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
        return 'Loads the input URL to initiate a session.';
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
