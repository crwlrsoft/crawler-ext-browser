<?php

use Crwlr\Crawler\Steps\Loading\Http;
use Crwlr\CrawlerExtBrowser\Aggregates\RespondedRequestWithScreenshot;
use Crwlr\CrawlerExtBrowser\Steps\GetColors;
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

it('does not wait to take a screenshot by default', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/screenshot-wait')
        ->addStep(Screenshot::loadAndTake(helper_testFilePath()))
        ->addStep(GetColors::fromImage()->addToResult());

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $colors = $results[0]->get('colors');

    expect($colors)
        ->toHaveCount(1)
        ->and($colors[0])
        ->toBe([
            'red' => 255,
            'green' => 255,
            'blue' => 255,
            'rgb' => '(255,255,255)',
            'percentage' => 100.0,
        ]);
});

it('waits the defined amount of time before taking a screenshot', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/screenshot-wait')
        ->addStep(
            Screenshot::loadAndTake(helper_testFilePath())
                ->waitAfterPageLoaded(1.1)
        )
        ->addStep(GetColors::fromImage()->addToResult());

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $colors = $results[0]->get('colors');

    expect($colors[0]['red'])
        ->toBe(234)
        ->and($colors[0]['green'])
        ->toBe(51)
        ->and($colors[0]['blue'])
        ->toBe(35)
        ->and($colors[0]['percentage'])
        ->toBeGreaterThanOrEqual(97.0)
        ->and($colors[1]['red'])
        ->toBe(255)
        ->and($colors[1]['green'])
        ->toBe(255)
        ->and($colors[1]['blue'])
        ->toBe(255)
        ->and($colors[1]['percentage'])
        ->toBeLessThanOrEqual(3.0);
});
