<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use Laminas\Http\Client as RestClient;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Olcs\Logging\Log\Logger;

class InrClient implements InrClientInterface
{
    public function __construct(protected RestClient $restClient)
    {
    }

    private ?int $lastStatusCode;

    public function getLastStatusCode(): int
    {
        return $this->lastStatusCode;
    }

    public function makeRequestReturnStatusCode(string $xml): int
    {
        return $this->makeRequest($xml)->getStatusCode();
    }

    public function makeRequestReturnResponse(string $xml): string
    {
        return $this->makeRequest($xml)->toString();
    }

    public function getRestClient(): RestClient
    {
        return $this->restClient;
    }

    public function close(): void
    {
        $this->restClient->getAdapter()->close();
    }

    private function makeRequest(string $xml): Response
    {
        $this->restClient->setEncType('text/xml');
        $this->restClient->getRequest()->setMethod(Request::METHOD_POST);
        $this->restClient->getRequest()->setContent($xml);

        Logger::info('INR request', ['data' => $this->restClient->getRequest()->toString()]);

        $response = $this->restClient->send();
        $this->lastStatusCode = $response->getStatusCode();

        Logger::info('INR response', ['data' => $response->toString()]);

        return $response;
    }
}
