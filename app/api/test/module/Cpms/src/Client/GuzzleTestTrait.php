<?php

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

    public function appendToHandler($statusCode = 200, $headers = [], $body = '', $version = '1.1', $reason = null)
    {
        if (!$this->mockHandler instanceof MockHandler) {
            $this->setUpMockClient();
        }
        $this->response = new Response($statusCode, $headers, $body, $version, $reason);

        $this->mockHandler->append($this->response);
    }

    public function getLastRequest()
    {
        return $this->mockHandler->getLastRequest();
    }
}
