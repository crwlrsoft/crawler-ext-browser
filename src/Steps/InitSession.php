<?php

namespace Crwlr\CrawlerExtBrowser\Steps;

use Exception;
use Generator;

/**
 * This step performs a request, only to initiate a session with the headless browser.
 * After loading the input URL, it simply returns the same URL as output, effectively forwarding it to
 * the next step — such as an HTTP loading step that uses the HTTP (guzzle) client.
 */

class InitSession extends BrowserBaseStep
{
    /**
     * @throws Exception
     */
    protected function invoke(mixed $input): Generator
    {
        $this->_switchLoaderBefore();

        $this->getResponseFromInputUri($input);

        $this->_switchLoaderAfterwards();

        yield $input;
    }
}
