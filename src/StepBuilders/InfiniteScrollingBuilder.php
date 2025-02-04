<?php

namespace Crwlr\CrawlerExtBrowser\StepBuilders;

use Crwlr\Crawler\Steps\StepInterface;
use Crwlr\Crawler\Steps\StepOutputType;
use Crwlr\CrawlerExtBrowser\Steps\InfiniteScrolling;
use Crwlr\CrwlExtensionUtils\ConfigParam;
use Crwlr\CrwlExtensionUtils\StepBuilder;
use Exception;

class InfiniteScrollingBuilder extends StepBuilder
{
    public function stepId(): string
    {
        return 'browser.infiniteScrolling';
    }

    public function label(): string
    {
        return 'Scroll down to end of Page (infinite scrolling)';
    }

    /**
     * @throws Exception
     */
    public function configToStep(array $stepConfig): StepInterface
    {
        $step = new InfiniteScrolling();

        $step->maxRetries($this->getValueFromConfigArray('maxRetries', $stepConfig));

        if ($this->getValueFromConfigArray('dontFailOnScrollDistanceZero', $stepConfig)) {
            $step->dontFailOnScrollDistanceZero();
        }

        if ($this->getValueFromConfigArray('useOpenedPage', $stepConfig)) {
            $step->useOpenedPage();
        }

        return $step;
    }

    public function configParams(): array
    {
        return [
            ConfigParam::int('maxRetries')
                ->default(2)
                ->inputLabel('Max Retries')
                ->description('Max number of retries if scrolling down fails for some reason.'),
            ConfigParam::bool('dontFailOnScrollDistanceZero')
                ->default(false)
                ->inputLabel('Don’t fail when no scrolling is needed')
                ->description(
                    'If you’re loading pages where some might be so short that scrolling isn’t necessary to reach ' .
                    'the bottom, enable this option.',
                ),
            ConfigParam::bool('useOpenedPage')
                ->default(false)
                ->inputLabel('Use opened page')
                ->description(
                    'Enable this option if you want to scroll to the bottom of a page that was already loaded and ' .
                    'opened in a previous step.',
                ),
        ];
    }

    public function outputType(): StepOutputType
    {
        return StepOutputType::AssociativeArrayOrObject;
    }

    public function isLoadingStep(): bool
    {
        return true;
    }
}
