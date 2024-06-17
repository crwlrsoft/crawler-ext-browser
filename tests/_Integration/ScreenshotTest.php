<?php

use Crwlr\Crawler\Steps\Json;
use Crwlr\Crawler\Steps\Loading\Http;
use Crwlr\CrawlerExtBrowser\Aggregates\RespondedRequestWithScreenshot;
use Crwlr\CrawlerExtBrowser\Steps\GetColors;
use Crwlr\CrawlerExtBrowser\Steps\Screenshot;
use PHPUnit\Framework\TestCase;

/** @var TestCase $this */

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
                ->keepAs('response'),
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
                ->keep(['screenshotPath']),
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
                ->keep(['screenshotPath']),
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
        ->addStep(GetColors::fromImage());

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
                ->waitAfterPageLoaded(1.1),
        )
        ->addStep(GetColors::fromImage());

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $colors = $results[0]->get('colors');

    expect($colors)
        ->toHaveCount(2)
        ->and($colors[0]['red'])
        ->toBeGreaterThanOrEqual(230)
        ->and($colors[0]['green'])
        ->toBeLessThanOrEqual(60)
        ->and($colors[0]['blue'])
        ->toBeLessThanOrEqual(40)
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

it('sends custom headers', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/print-headers')
        ->addStep(
            Screenshot::loadAndTake(helper_testFilePath(), ['x-custom-header' => 'foo']),
        )
        ->addStep(Json::get(['headers' => '*']));

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1)
        ->and($results[0]->get('headers'))->toBeArray()
        ->and($results[0]->get('headers')['x-custom-header'])->toBe('foo');
});

it('uses the defined timeout and changes it back after execution', function () {
    $crawler = helper_getFastCrawler();

    $defaultTimeout = $crawler->getLoader()->browserHelper()->getTimeout();

    $step = Screenshot::loadAndTake(helper_testFilePath())->timeout(0.5);

    $crawler
        ->input('http://localhost:8000/timeout')
        ->addStep($step);

    $results = iterator_to_array($crawler->run());

    $output = $this->getActualOutputForAssertion();

    expect($results)->toHaveCount(0)
        ->and($output)->toContain('Failed to load http://localhost:8000/timeout: Operation timed out after 500ms')
        ->and($crawler->getLoader()->browserHelper()->getTimeout())->toBe($defaultTimeout);
});
