<?php

use Crwlr\Crawler\Steps\Loading\Http;
use Crwlr\CrawlerExtBrowser\Aggregates\RespondedRequestWithScreenshot;
use Crwlr\CrawlerExtBrowser\Steps\Screenshot;

afterEach(function () {
    helper_cleanFiles();

    helper_cleanCache();
});

it('takes a screenshot', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/screenshot')
        ->addStep(
            Screenshot::loadAndTake(helper_testFilePath())
                ->addToResult('response')
        );

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $response = $results[0]->get('response');

    expect($response)
        ->toBeInstanceOf(RespondedRequestWithScreenshot::class)
        ->and(Http::getBodyString($response))
        ->toContain('<h1>Hello World!</h1>')
        ->and($response->screenshotPath)
        ->toBeString()
        ->and(file_exists($response->screenshotPath))
        ->toBeTrue();
});

it('can be cached and remembers the screenshot path', function () {
    $crawler = helper_getFastCrawlerWithCache();

    $crawler
        ->input('http://localhost:8000/screenshot')
        ->addStep(
            Screenshot::loadAndTake(helper_testFilePath())
                ->addToResult(['screenshotPath'])
        );

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $screenshotPath = $results[0]->get('screenshotPath');

    expect($screenshotPath)
        ->toBeString()
        ->not()
        ->toBeEmpty();

    $crawler = helper_getFastCrawlerWithCache();

    $crawler
        ->input('http://localhost:8000/screenshot')
        ->addStep(
            Screenshot::loadAndTake(helper_testFilePath())
                ->addToResult(['screenshotPath'])
        );

    $results = iterator_to_array($crawler->run());

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->get('screenshotPath'))
        ->toBe($screenshotPath);
});
