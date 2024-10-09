<?php

namespace Crwlr\CrawlerExtBrowser\StepBuilders;

use Crwlr\Crawler\Steps\StepInterface;
use Crwlr\Crawler\Steps\StepOutputType;
use Crwlr\CrawlerExtBrowser\Steps\GetColors;
use Crwlr\CrwlExtensionUtils\ConfigParam;
use Crwlr\CrwlExtensionUtils\StepBuilder;
use Exception;

class GetColorsBuilder extends StepBuilder
{
    public function stepId(): string
    {
        return 'browser.getColorsFromImage';
    }

    public function label(): string
    {
        return 'Get the most used colors from an image (e.g. screenshot).';
    }

    /**
     * @throws Exception
     */
    public function configToStep(array $stepConfig): StepInterface
    {
        $step = GetColors::fromImage();

        $onlyAbovePercentage = $this->getValueFromConfigArray('onlyAbovePercentage', $stepConfig);

        if ($onlyAbovePercentage !== null && $onlyAbovePercentage > 0.0) {
            $step->onlyAbovePercentageOfImage($onlyAbovePercentage);
        }

        return $step;
    }

    public function configParams(): array
    {
        return [
            ConfigParam::float('onlyAbovePercentage')
                ->inputLabel('Only Above Percentage')
                ->description('Get only colors that make up at least a certain percentage of the image.'),
        ];
    }

    public function outputType(): StepOutputType
    {
        return StepOutputType::AssociativeArrayOrObject;
    }
}
