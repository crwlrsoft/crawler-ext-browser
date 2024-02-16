<?php

namespace Crwlr\CrawlerExtBrowser\Aggregates;

use Crwlr\Crawler\Loader\Http\Messages\RespondedRequest;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RespondedRequestWithScreenshot extends RespondedRequest
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        public string $screenshotPath,
    ) {
        parent::__construct($request, $response);
    }

    /**
     * @throws Exception
     */
    public static function fromRespondedRequest(RespondedRequest $respondedRequest, string $screenshotPath): self
    {
        return new self($respondedRequest->request, $respondedRequest->response, $screenshotPath);
    }

    public static function fromArray(array $data): RespondedRequestWithScreenshot
    {
        $respondedRequest = parent::fromArray($data);

        return self::fromRespondedRequest($respondedRequest, $data['screenshotPath']);
    }

    public function __serialize(): array
    {
        $serialized = parent::__serialize();

        $serialized['screenshotPath'] = $this->screenshotPath;

        return $serialized;
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->screenshotPath = $data['screenshotPath'] ?? '';
    }

    public function toArrayForAddToResult(): array
    {
        $array = parent::toArrayForAddToResult();

        $array['screenshotPath'] = $this->screenshotPath;

        return $array;
    }
}
