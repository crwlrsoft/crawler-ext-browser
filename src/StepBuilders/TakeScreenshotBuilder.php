<?php

namespace Crwlr\CrawlerExtBrowser\StepBuilders;

use Crwlr\Crawler\Steps\StepInterface;
use Crwlr\Crawler\Steps\StepOutputType;
use Crwlr\CrawlerExtBrowser\Steps\Screenshot;
use Crwlr\CrwlExtensionUtils\ConfigParam;
use Crwlr\CrwlExtensionUtils\StepBuilder;
use Exception;

class TakeScreenshotBuilder extends StepBuilder
{
    public function stepId(): string
    {
        return 'browser.takeScreenshot';
    }

    public function label(): string
    {
        return 'Take a screenshot of a previously loaded page.';
    }

    /**
     * @throws Exception
     */
    public function configToStep(array $stepConfig): StepInterface
    {
        if (empty($this->fileStoragePath)) {
            throw new Exception('No file storage path defined.');
        }

        $step = Screenshot::take($this->fileStoragePath);

        $waitAfterPageLoaded = $this->getValueFromConfigArray('waitAfterPageLoaded', $stepConfig);

        if ($waitAfterPageLoaded !== null && $waitAfterPageLoaded > 0.0) {
            $step->waitAfterPageLoaded($waitAfterPageLoaded);
        }

        return $step;
    }

    public function configParams(): array
    {
        return [
            ConfigParam::float('waitAfterPageLoaded')
                ->inputLabel('Wait X seconds')
                ->description('Wait X seconds after the page is fully loaded, before taking the screenshot.'),
        ];
    }

    public function outputType(): StepOutputType
    {
        return StepOutputType::AssociativeArrayOrObject;
    }
}
