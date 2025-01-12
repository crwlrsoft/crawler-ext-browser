<?php

use Crwlr\CrawlerExtBrowser\Steps\InfiniteScrolling;

it(
    'Scrolls down until it can not scroll down any further',
    function () {
        $crawler = helper_getFastCrawler();

        $crawler
            ->input('http://localhost:8000/infinite-scrolling')
            ->addStep((new InfiniteScrolling())->keep('body'));

        $results = iterator_to_array($crawler->run());

        expect($results)->toHaveCount(1)
            ->and($results[0]->get('body'))->toContain('Element 4');
    },
);

it('fails when it can not scroll down even once', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/infinite-scrolling?lowHeight=1')
        ->addStep(
            (new InfiniteScrolling())
                ->keep('body')
                ->maxRetries(0),
        );

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(0)
        ->and($this->getActualOutputForAssertion())->toContain('Couldn’t scroll down even once.');
});

it('does not fail when it can not scroll down even once, but dontFailOnScrollDistanceZero() is called', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/infinite-scrolling?lowHeight=1')
        ->addStep(
            (new InfiniteScrolling())
                ->dontFailOnScrollDistanceZero()
                ->maxRetries(0)
                ->keep('body'),
        );

    $results = iterator_to_array($crawler->run());

    expect($results)->toHaveCount(1)
        ->and($results[0]->get('body'))->toContain('Hey');
});

it('retries twice by default', function () {
    $crawler = helper_getFastCrawler();

    $crawler
        ->input('http://localhost:8000/infinite-scrolling?lowHeight=1')
        ->addStep((new InfiniteScrolling())->keep('body'));

    $results = iterator_to_array($crawler->run());

    helper_dump($this->getActualOutputForAssertion());

    expect($results)->toHaveCount(0)
        ->and(explode('Failed to load and scroll down until the end. Retry. ', $this->getActualOutputForAssertion()))
        ->toHaveCount(3);
});
