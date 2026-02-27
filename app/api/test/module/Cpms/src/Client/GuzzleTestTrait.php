<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

trait GuzzleTestTrait
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @return Client
     */
    public function setUpMockClient(): Client
    {
        $this->mockHandler = new MockHandler();
        $handler = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handler]);
        return $client;
    }

    public function appendToHandler(int $statusCode = 200, array $headers = [], string $body = '', string $version = '1.1', mixed $reason = null): void
    {
        if (!$this->mockHandler instanceof MockHandler) {
            $this->setUpMockClient();
        }
        $this->response = new Response($statusCode, $headers, $body, $version, $reason);

        $this->mockHandler->append($this->response);
    }

    public function getLastRequest(): mixed
    {
        return $this->mockHandler->getLastRequest();
    }
}
