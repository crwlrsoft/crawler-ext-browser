<?php

use Crwlr\Crawler\Loader\Http\Messages\RespondedRequest;
use Crwlr\Crawler\Steps\Loading\Http;
use Crwlr\CrawlerExtBrowser\Steps\Screenshot;

afterEach(function () {
    helper_cleanFiles();
});

it('takes a screenshot', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/screenshot')
        ->addStep(Screenshot::loadAndTake(helper_testFilePath()));

    $results = iterator_to_array($crawler->run());

    expect($results)
        ->toHaveCount(1);

    $result = $results[0]->toArray();

    expect($result['response'])
        ->toBeInstanceOf(RespondedRequest::class)
        ->and(Http::getBodyString($result['response']))
        ->toContain('<h1>Hello World!</h1>')
        ->and($result['screenshotPath'])
        ->toBeString()
        ->and(file_exists($result['screenshotPath']))
        ->toBeTrue();
});
