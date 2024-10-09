<?php

namespace Crwlr\CrawlerExtBrowser;

use Crwlr\CrawlerExtBrowser\StepBuilders\GetColorsBuilder;
use Crwlr\CrawlerExtBrowser\StepBuilders\ScreenshotBuilder;
use Crwlr\CrawlerExtBrowser\StepBuilders\TakeScreenshotBuilder;
use Crwlr\CrwlExtensionUtils\Exceptions\DuplicateExtensionPackageException;
use Crwlr\CrwlExtensionUtils\Exceptions\DuplicateStepIdException;
use Crwlr\CrwlExtensionUtils\Exceptions\InvalidStepException;
use Crwlr\CrwlExtensionUtils\ExtensionPackageManager;
use Illuminate\Contracts\Container\BindingResolutionException;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @throws DuplicateExtensionPackageException
     * @throws DuplicateStepIdException
     * @throws InvalidStepException
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->app->make(ExtensionPackageManager::class)
            ->registerPackage('crwlr/crawler-ext-browser')
            ->registerStep(ScreenshotBuilder::class)
            ->registerStep(TakeScreenshotBuilder::class)
            ->registerStep(GetColorsBuilder::class);
    }
}
