<?php

use Crwlr\Crawler\Crawler;
use Crwlr\Crawler\Result;
use Crwlr\CrawlerExtBrowser\Steps\GetColors;
use Crwlr\CrawlerExtBrowser\Steps\Screenshot;

afterEach(function () {
    helper_cleanFiles();
});

it('gets the colors from an image, taken in a preceding screenshot step', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/screenshot')
        ->addStep(Screenshot::loadAndTake(helper_testFilePath()))
        ->addStep(GetColors::fromImage());

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $result = $results[0]->toArray();

    expect($result['colors'][0])->toHaveKeys(['red', 'green', 'blue', 'rgb', 'percentage'])
        ->and($result['colors'][0]['percentage'])->toBeGreaterThanOrEqual(75.0)
        ->and($result['colors'][1])->toHaveKeys(['red', 'green', 'blue', 'rgb', 'percentage'])
        ->and($result['colors'][1]['percentage'])->toBeGreaterThanOrEqual(15.3)
        ->and($result['colors'][2])->toHaveKeys(['red', 'green', 'blue', 'rgb', 'percentage'])
        ->and($result['colors'][2]['percentage'])->toBeGreaterThanOrEqual(3.1)
        ->and($result['colors'][2])->toHaveKeys(['red', 'green', 'blue', 'rgb', 'percentage'])
        ->and($result['colors'][2]['percentage'])->toBeGreaterThanOrEqual(3.1);
});

it('gets the colors from an image', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input(['screenshotPath' => helper_testFilePath('demo-screenshot.png')])
        ->addStep(
            GetColors::fromImage()
                ->onlyAbovePercentageOfImage(0.4),
        );

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1);

    $result = $results[0]->toArray();

    expect($result['colors'])
        ->toBe([
            ['red' => 96, 'green' => 177, 'blue' => 119, 'rgb' => '(96,177,119)', 'percentage' => 80.3],
            ['red' => 255, 'green' => 255, 'blue' => 255, 'rgb' => '(255,255,255)', 'percentage' => 15.6],
            ['red' => 181, 'green' => 145, 'blue' => 144, 'rgb' => '(181,145,144)', 'percentage' => 3.1],
            ['red' => 0, 'green' => 0, 'blue' => 0, 'rgb' => '(0,0,0)', 'percentage' => 0.5],
        ]);
});

it('does not run out of memory with a very colorful image and 100MB of memory', function () {
    Crawler::setMemoryLimit('500M');

    $crawler = helper_getFastCrawler();

    $crawler
        ->input(['screenshotPath' => helper_testFilePath('demo-screenshot2.png')])
        ->addStep(GetColors::fromImage());

    $results = iterator_to_array($crawler->run());

    $result = $results[0];

    /** @var Result $result */

    expect(count($result->get('colors')))->toBe(596002);
});

it('gets colors that make up at least a certain percentage when onlyAbovePercentageOfImage() was used', function () {
    Crawler::setMemoryLimit('500M');

    $crawler = helper_getFastCrawler();

    $crawler
        ->input(['screenshotPath' => helper_testFilePath('demo-screenshot2.png')])
        ->addStep(
            GetColors::fromImage()
                ->onlyAbovePercentageOfImage(0.1),
        );

    $results = iterator_to_array($crawler->run());

    $result = $results[0];

    /** @var Result $result */

    expect(count($result->get('colors')))->toBe(1);
});
