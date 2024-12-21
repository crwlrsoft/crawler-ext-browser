<?php

use Crwlr\Crawler\Steps\Loading\Http;
use Crwlr\CrawlerExtBrowser\Steps\InitSession;

it(
    'initiates a session via a request performed by a headless browser, yields the input URL ' .
    'and session cookies are used by a following non browser loading step',
    function () {
        $crawler = helper_getFastCrawler();

        $crawler
            ->input('http://localhost:8000/init_session')
            ->addStep(new InitSession())
            ->addStep(Http::get()->keep('body'));

        $results = iterator_to_array($crawler->run());

        expect($results)->toHaveCount(1);

        $response = $results[0]->get('body');

        expect($response)->toBe('foo');
    },
);
